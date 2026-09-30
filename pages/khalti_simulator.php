<?php
/**
 * Khalti Sandbox Payment Simulator
 *
 * Simulates the official Khalti Sandbox Payment Gateway for local testing
 * when merchant credentials are in sandbox test mode.
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

$pidx = trim($_GET['pidx'] ?? '');
$booking_id = intval($_GET['booking_id'] ?? 0);
$amount_paisa = intval($_GET['amount'] ?? 0);
$amount_npr = $amount_paisa / 100;

// Verify booking details
$booking = null;
if ($booking_id > 0) {
    $stmt = mysqli_prepare($conn, "SELECT b.*, r.label AS room_label FROM booking b LEFT JOIN rooms r ON b.room_id = r.room_id WHERE b.booking_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $booking_id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $booking = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
}

if (!$booking) {
    echo "Invalid booking reference.";
    exit();
}

$room_label = $booking['room_label'] ?? 'Hotel Room';
if ($amount_npr <= 0) {
    $amount_npr = floatval($booking['tprice']);
    $amount_paisa = intval(round($amount_npr * 100));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Khalti Payment Gateway (Sandbox) - LOTUS</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" />
    <link rel="stylesheet" href="../css/style.css" />
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl border border-gray-200 overflow-hidden">
        
        <!-- Khalti Purple Header -->
        <div class="bg-[#5c2d91] p-6 text-white text-center relative">
            <div class="inline-flex items-center justify-center w-14 h-14 bg-white/10 rounded-2xl mb-3 border border-white/20">
                <i class="fa-solid fa-wallet text-2xl text-amber-300"></i>
            </div>
            <div class="inline-block px-2.5 py-0.5 rounded-full bg-amber-400 text-gray-900 text-[10px] font-black uppercase tracking-wider mb-1">
                Sandbox Test Mode
            </div>
            <h1 class="text-xl font-bold tracking-wide">Khalti ePayment</h1>
            <p class="text-xs text-white/70 mt-1">LOTUS Luxury Hotel & Sanctuary</p>
        </div>

        <!-- Order Summary -->
        <div class="p-6">
            <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 mb-5">
                <div class="flex justify-between items-center text-xs text-gray-500 mb-1">
                    <span>Order Reference</span>
                    <span class="font-mono font-bold text-gray-800">#LOTUS-<?= $booking_id ?></span>
                </div>
                <div class="flex justify-between items-center text-xs text-gray-500 mb-2">
                    <span>Room</span>
                    <span class="font-semibold text-gray-800"><?= htmlspecialchars($room_label) ?></span>
                </div>
                <div class="flex justify-between items-center text-sm font-bold text-gray-900 pt-2 border-t border-gray-200">
                    <span>Total Amount</span>
                    <span class="text-[#5c2d91] text-lg font-black">Rs. <?= number_format($amount_npr, 2) ?></span>
                </div>
            </div>

            <!-- Sandbox Test Credentials Helper -->
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-3.5 mb-5 text-xs text-amber-900">
                <div class="font-bold flex items-center gap-1.5 mb-1 text-amber-800">
                    <i class="fa-solid fa-flask"></i> Sandbox Test Credentials:
                </div>
                <div class="grid grid-cols-3 gap-2 mt-1.5 font-mono text-[11px]">
                    <div class="bg-white/80 p-1.5 rounded border border-amber-200 text-center">
                        <span class="block text-[9px] text-gray-500 uppercase">Khalti ID</span>
                        <strong>9800000000</strong>
                    </div>
                    <div class="bg-white/80 p-1.5 rounded border border-amber-200 text-center">
                        <span class="block text-[9px] text-gray-500 uppercase">MPIN</span>
                        <strong>1111</strong>
                    </div>
                    <div class="bg-white/80 p-1.5 rounded border border-amber-200 text-center">
                        <span class="block text-[9px] text-gray-500 uppercase">OTP</span>
                        <strong>987654</strong>
                    </div>
                </div>
            </div>

            <!-- Interactive Simulation Form -->
            <form action="khalti_callback.php" method="GET" class="space-y-4">
                <input type="hidden" name="pidx" value="<?= htmlspecialchars($pidx) ?>" />
                <input type="hidden" name="amount" value="<?= htmlspecialchars($amount_paisa) ?>" />
                <input type="hidden" name="purchase_order_id" value="LOTUS-<?= htmlspecialchars($booking_id) ?>" />

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Khalti Mobile Number</label>
                    <input type="tel" name="mobile" value="9800000000" class="w-full p-2.5 border border-gray-300 rounded-lg text-sm font-mono focus:border-[#5c2d91] outline-none" required />
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Khalti MPIN</label>
                        <input type="password" value="1111" class="w-full p-2.5 border border-gray-300 rounded-lg text-sm font-mono focus:border-[#5c2d91] outline-none" required />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Payment OTP</label>
                        <input type="text" value="987654" class="w-full p-2.5 border border-gray-300 rounded-lg text-sm font-mono focus:border-[#5c2d91] outline-none" required />
                    </div>
                </div>

                <div class="pt-2 flex flex-col gap-2">
                    <button type="submit" name="status" value="Completed" class="w-full py-3 bg-[#5c2d91] hover:bg-[#4a2275] text-white font-bold text-sm rounded-xl transition-all shadow-md cursor-pointer flex items-center justify-center gap-2">
                        <i class="fa-solid fa-lock"></i> Pay Rs. <?= number_format($amount_npr) ?> with Khalti
                    </button>

                    <button type="submit" name="status" value="User canceled" class="w-full py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold text-xs rounded-xl transition-colors cursor-pointer">
                        Cancel &amp; Return to Hotel
                    </button>
                </div>
            </form>
        </div>

        <!-- Footer Notice -->
        <div class="px-6 py-3 bg-gray-50 border-t border-gray-200 text-center text-[11px] text-gray-400">
            Khalti Sandbox &bull; LOTUS Luxury Hotel Booking
        </div>
    </div>

</body>
</html>
