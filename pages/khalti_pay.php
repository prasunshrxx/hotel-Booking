<?php
/**
 * Khalti Direct Payment Initiator
 *
 * Initiates payment for an existing unpaid booking and redirects to Khalti.
 */

include_once __DIR__ . '/../conn.php';
require_once __DIR__ . '/../includes/khalti.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['isLogin'])) {
    header("Location: login.php");
    exit();
}

$booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;
if ($booking_id <= 0) {
    header("Location: cartpage.php");
    exit();
}

// Fetch booking
$stmt = mysqli_prepare($conn, "
    SELECT b.*, r.label AS room_label, u.email AS user_email, u.username AS user_name
    FROM booking b
    LEFT JOIN rooms r ON b.room_id = r.room_id
    LEFT JOIN users u ON b.user_id = u.id
    WHERE b.booking_id = ?
");
mysqli_stmt_bind_param($stmt, "i", $booking_id);
mysqli_stmt_execute($stmt);
$booking = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$booking) {
    header("Location: cartpage.php");
    exit();
}

// Access control: Guest can only pay for their own booking unless admin
$current_user_id = $_SESSION['user_id'] ?? $_SESSION['id'] ?? 0;
$user_role = $_SESSION['role'] ?? 'user';
if ($user_role !== 'admin' && intval($booking['user_id']) !== intval($current_user_id)) {
    header("Location: cartpage.php");
    exit();
}

// If already paid, redirect straight to receipt
if (($booking['payment_status'] ?? '') === 'paid') {
    header("Location: receipt.php?booking_id=" . $booking_id);
    exit();
}

// Initiate payment via Khalti
$guest_name  = $booking['user_name'] ?? ($_SESSION['username'] ?? 'Guest');
$guest_email = $booking['user_email'] ?? ($_SESSION['email'] ?? 'guest@example.com');
$guest_phone = $booking['phone_number'] ?? '';
$amount      = floatval($booking['tprice']);
$room_label  = $booking['room_label'] ?? 'Hotel Room';

$init = khalti_initiate_payment($booking_id, $amount, $room_label, $guest_name, $guest_email, $guest_phone);

if ($init['success'] && !empty($init['payment_url'])) {
    // Save pidx
    $pidx = $init['pidx'];
    $stmtUp = mysqli_prepare($conn, "UPDATE booking SET pidx = ?, payment_method = 'khalti' WHERE booking_id = ?");
    mysqli_stmt_bind_param($stmtUp, "si", $pidx, $booking_id);
    mysqli_stmt_execute($stmtUp);
    mysqli_stmt_close($stmtUp);

    // Redirect to Khalti Payment Page
    header("Location: " . $init['payment_url']);
    exit();
}

// If failed
$error = $init['error'] ?? 'Failed to initiate Khalti payment.';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Khalti Payment Error - LOTUS</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="../css/style.css" />
</head>
<body class="bg-[#F7F4ED] min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-lg border border-gray-200 p-8 text-center">
        <h1 class="text-xl font-bold text-red-600 mb-2">Payment Gateway Error</h1>
        <p class="text-xs text-gray-600 mb-6"><?= htmlspecialchars($error) ?></p>
        <a href="receipt.php?booking_id=<?= $booking_id ?>" class="inline-block py-2.5 px-6 bg-gray-900 text-white rounded-xl text-sm font-semibold">
            Return to Receipt
        </a>
    </div>
</body>
</html>
