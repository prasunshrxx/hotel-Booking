<?php
/**
 * Khalti Payment Callback Handler
 *
 * Receives redirect from Khalti after payment completion/cancellation,
 * performs server-to-server verification (Lookup), updates booking record,
 * sends confirmation email on success, and redirects guest to receipt.
 */

include_once __DIR__ . '/../conn.php';
require_once __DIR__ . '/../includes/khalti.php';
require_once __DIR__ . '/../includes/mailer.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['isLogin'])) {
    header("Location: login.php");
    exit();
}

$pidx              = trim($_GET['pidx'] ?? '');
$status_from_query = trim($_GET['status'] ?? '');
$purchase_order_id = trim($_GET['purchase_order_id'] ?? '');

$booking_id = 0;
if (!empty($purchase_order_id) && strpos($purchase_order_id, 'LOTUS-') === 0) {
    $booking_id = intval(substr($purchase_order_id, 6));
}

// Fallback: lookup by pidx in booking table
if ($booking_id <= 0 && !empty($pidx)) {
    $stmtFind = mysqli_prepare($conn, "SELECT booking_id FROM booking WHERE pidx = ? LIMIT 1");
    mysqli_stmt_bind_param($stmtFind, "s", $pidx);
    mysqli_stmt_execute($stmtFind);
    $resFind = mysqli_stmt_get_result($stmtFind);
    if ($rowFind = mysqli_fetch_assoc($resFind)) {
        $booking_id = intval($rowFind['booking_id']);
    }
    mysqli_stmt_close($stmtFind);
}

if ($booking_id <= 0) {
    die("Invalid payment callback: Booking not found.");
}

// Fetch booking + room details
$stmtB = mysqli_prepare($conn, "
    SELECT b.*, r.label AS room_label, u.email AS user_email, u.username AS user_name
    FROM booking b
    LEFT JOIN rooms r ON b.room_id = r.room_id
    LEFT JOIN users u ON b.user_id = u.id
    WHERE b.booking_id = ?
");
mysqli_stmt_bind_param($stmtB, "i", $booking_id);
mysqli_stmt_execute($stmtB);
$booking = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtB));
mysqli_stmt_close($stmtB);

if (!$booking) {
    die("Booking record not found.");
}

// Verify payment server-side via Khalti Lookup API
$verification = khalti_verify_payment($pidx);
$is_paid = false;
$txn_id = '';

if ($verification['success'] && ($verification['status'] ?? '') === 'Completed') {
    $is_paid = true;
    $txn_id = $verification['transaction_id'] ?? ('KHLT-' . substr($pidx, 0, 10));
}

if ($is_paid) {
    // Update booking in database to paid & confirmed
    $stmtUp = mysqli_prepare($conn, "
        UPDATE booking 
        SET payment_status = 'paid', 
            payment_method = 'khalti', 
            transaction_id = ?, 
            status = 'confirmed',
            pidx = ?
        WHERE booking_id = ?
    ");
    mysqli_stmt_bind_param($stmtUp, "ssi", $txn_id, $pidx, $booking_id);
    mysqli_stmt_execute($stmtUp);
    mysqli_stmt_close($stmtUp);

    // Send confirmation email
    $email = $booking['user_email'] ?? ($_SESSION['email'] ?? '');
    $name  = $booking['user_name']  ?? ($_SESSION['username'] ?? 'Valued Guest');
    if (!empty($email)) {
        send_booking_email($email, $name, [
            'booking_id'      => $booking_id,
            'room_label'      => $booking['room_label'] ?? 'Hotel Room',
            'checkin_date'    => $booking['checkin_date'],
            'checkout_date'   => $booking['checkout_date'],
            'no_of_guests'    => $booking['no_of_guests'],
            'tprice'          => $booking['tprice'],
            'phone_number'    => $booking['phone_number'],
            'special_request' => $booking['special_request'] ?: 'None',
        ]);
    }

    // Redirect to receipt with payment success flag
    header("Location: receipt.php?booking_id=" . $booking_id . "&payment=success&txn=" . urlencode($txn_id));
    exit();
}

// If payment was cancelled or failed:
$fail_reason = $verification['error'] ?? ($status_from_query ?: 'Payment was not completed');

// Keep booking as unpaid / pay at hotel
$stmtFail = mysqli_prepare($conn, "UPDATE booking SET payment_status = 'failed', payment_method = 'pay_at_hotel' WHERE booking_id = ? AND payment_status != 'paid'");
mysqli_stmt_bind_param($stmtFail, "i", $booking_id);
mysqli_stmt_execute($stmtFail);
mysqli_stmt_close($stmtFail);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Status - LOTUS</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" />
    <link rel="stylesheet" href="../css/style.css" />
</head>
<body class="bg-[#F7F4ED] min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full bg-white rounded-2xl shadow-lg border border-gray-200 p-8 text-center">
        <div class="w-16 h-16 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center mx-auto mb-4 text-2xl">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>

        <h1 class="text-2xl font-bold text-gray-900 mb-2">Payment Incomplete</h1>
        <p class="text-sm text-gray-600 mb-6">
            Your Khalti payment was not completed (<strong><?= htmlspecialchars($status_from_query ?: 'Cancelled') ?></strong>).
        </p>

        <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 text-left text-xs text-gray-700 mb-6 space-y-1.5">
            <div class="flex justify-between">
                <span class="text-gray-500">Booking Reference:</span>
                <span class="font-bold text-gray-900">#LOTUS-<?= $booking_id ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Room:</span>
                <span class="font-semibold text-gray-900"><?= htmlspecialchars($booking['room_label'] ?? 'Room') ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Total Payable:</span>
                <span class="font-bold text-gray-900">Rs. <?= number_format($booking['tprice']) ?></span>
            </div>
            <div class="flex justify-between pt-1 border-t border-gray-200">
                <span class="text-gray-500">Current Status:</span>
                <span class="font-semibold text-amber-700">Reserved (Pay at Hotel)</span>
            </div>
        </div>

        <p class="text-xs text-gray-500 mb-6">
            Don't worry! Your room reservation is still recorded as <strong>Pay at Hotel / Cash on Arrival</strong>. You can retry paying with Khalti anytime or view your booking receipt.
        </p>

        <div class="flex flex-col gap-2.5">
            <a href="khalti_pay.php?booking_id=<?= $booking_id ?>" class="w-full py-3 bg-[#5c2d91] hover:bg-[#4a2275] text-white font-semibold rounded-xl text-sm transition-colors flex items-center justify-center gap-2 shadow-xs cursor-pointer">
                <i class="fa-solid fa-rotate-right"></i> Retry Khalti Payment
            </a>

            <a href="receipt.php?booking_id=<?= $booking_id ?>" class="w-full py-2.5 bg-gray-900 hover:bg-black text-white font-semibold rounded-xl text-sm transition-colors cursor-pointer">
                View Receipt (Pay at Hotel)
            </a>

            <a href="cartpage.php" class="text-xs text-gray-500 hover:text-gray-800 transition-colors mt-2">
                Return to My Cart
            </a>
        </div>
    </div>

</body>
</html>
