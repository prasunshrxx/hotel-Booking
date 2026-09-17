<?php
include_once __DIR__ . '/../conn.php';
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

// Fetch booking details with room and user information
$sql = "SELECT booking.*, 
               rooms.label AS room_label, 
               rooms.price AS room_price, 
               rooms.features AS room_features, 
               rooms.description AS room_description, 
               rooms.image AS room_image,
               users.username AS guest_name, 
               users.email AS guest_email
        FROM booking 
        LEFT JOIN rooms ON booking.room_id = rooms.room_id 
        LEFT JOIN users ON booking.user_id = users.id 
        WHERE booking.booking_id = ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $booking_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result || mysqli_num_rows($result) === 0) {
    header("Location: cartpage.php");
    exit();
}

$booking = mysqli_fetch_assoc($result);

// Access control: Guest can only view their own booking, unless user is admin
$current_user_id = $_SESSION['user_id'] ?? $_SESSION['id'] ?? 0;
$user_role = $_SESSION['role'] ?? 'user';

if ($user_role !== 'admin' && intval($booking['user_id']) !== intval($current_user_id)) {
    header("Location: cartpage.php");
    exit();
}

// Calculate night duration
$date1 = new DateTime($booking['checkin_date']);
$date2 = new DateTime($booking['checkout_date']);
$interval = $date1->diff($date2);
$nights = max(1, $interval->days);

$booking_date = !empty($booking['created_At']) ? date('M d, Y h:i A', strtotime($booking['created_At'])) : date('M d, Y');
$checkin_fmt = date('D, M d, Y', strtotime($booking['checkin_date']));
$checkout_fmt = date('D, M d, Y', strtotime($booking['checkout_date']));

$is_new = isset($_GET['new']) && $_GET['new'] == '1';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Booking Receipt #BK-<?= htmlspecialchars($booking['booking_id']) ?> - LOTUS</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" />
    <link rel="stylesheet" href="../css/style.css" />

    <style>
        @media print {
            body {
                background: #ffffff !important;
                color: #000000 !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            #receipt-card {
                box-shadow: none !important;
                border: none !important;
                width: 100% !important;
                max-width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
            }
        }
        .gold-border {
            border-color: #d4af37;
        }
    </style>
</head>

<body class="bg-gray-100 min-h-screen text-gray-800">

    <!-- Top Action Bar (No-Print) -->
    <header class="no-print bg-white border-b border-gray-200 sticky top-0 z-30 shadow-xs">
        <div class="max-w-5xl mx-auto px-4 py-3 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <a href="<?= ($user_role === 'admin') ? 'adminpage.php?tab=orders' : 'cartpage.php' ?>" class="inline-flex items-center text-sm font-semibold text-gray-600 hover:text-black">
                    <i class="fa-solid fa-arrow-left mr-2"></i> <?= ($user_role === 'admin') ? 'Back to Admin Orders' : 'Back to My Bookings' ?>
                </a>
                <span class="text-gray-300">|</span>
                <span class="text-xs font-semibold px-2.5 py-1 bg-amber-50 text-amber-800 border border-amber-200 rounded-md">
                    Receipt #LOTUS-BK-<?= htmlspecialchars($booking['booking_id']) ?>
                </span>
            </div>

            <div class="flex items-center gap-2">
                <a href="download_receipt.php?booking_id=<?= $booking['booking_id'] ?>" class="inline-flex items-center gap-2 px-5 py-2 bg-[#193366] hover:bg-[#254685] text-white text-sm font-semibold rounded-lg shadow-sm transition-all cursor-pointer">
                    <i class="fa-solid fa-file-arrow-down text-amber-400"></i>
                    <span>Download PDF Receipt</span>
                </a>
            </div>
        </div>
    </header>

    <main class="max-w-5xl mx-auto px-4 py-8">

        <?php if ($is_new): ?>
        <!-- Booking Success Banner -->
        <div class="no-print mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-300 text-emerald-900 flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-emerald-500 text-white flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-check text-lg"></i>
                </div>
                <div>
                    <h3 class="font-bold text-base">Booking Successfully Confirmed!</h3>
                    <p class="text-xs text-emerald-800">Your reservation has been recorded. Your booking receipt is ready below for your records.</p>
                </div>
            </div>
            <a href="download_receipt.php?booking_id=<?= $booking['booking_id'] ?>" class="hidden sm:inline-flex items-center gap-2 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg transition-colors cursor-pointer">
                <i class="fa-solid fa-download"></i> Download PDF
            </a>
        </div>
        <?php endif; ?>

        <!-- Printable / Exportable Receipt Container -->
        <div id="receipt-card" class="bg-white rounded-2xl shadow-md border border-gray-200 p-8 md:p-12 transition-all">
            
            <!-- Receipt Header -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center border-b border-gray-200 pb-6 gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-[#193366] text-white flex items-center justify-center font-bold text-xl shadow-xs border-2 border-[#d4af37]">
                        L
                    </div>
                    <div>
                        <h1 class="text-2xl font-black tracking-wide text-gray-900">LOTUS</h1>
                        <p class="text-xs font-medium text-amber-700 uppercase tracking-widest">Luxury Boutique Hotel</p>
                    </div>
                </div>

                <div class="text-left md:text-right">
                    <div class="flex items-center md:justify-end gap-2 mb-1">
                        <span class="text-xs font-bold uppercase tracking-wider text-gray-500">Booking Receipt</span>
                        <?php
                            $stat = strtolower(trim($booking['status']));
                            $badgeBg = 'bg-amber-100 text-amber-800 border-amber-200';
                            if ($stat === 'confirmed') $badgeBg = 'bg-emerald-100 text-emerald-800 border-emerald-200';
                            elseif ($stat === 'checked out') $badgeBg = 'bg-blue-100 text-blue-800 border-blue-200';
                            elseif ($stat === 'cancelled') $badgeBg = 'bg-red-100 text-red-800 border-red-200';
                        ?>
                        <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wide border <?= $badgeBg ?>">
                            <?= htmlspecialchars($booking['status']) ?>
                        </span>
                    </div>
                    <h2 class="text-xl font-extrabold text-[#193366]">#LOTUS-BK-<?= htmlspecialchars($booking['booking_id']) ?></h2>
                    <p class="text-xs text-gray-500 mt-0.5">Date: <?= $booking_date ?></p>
                </div>
            </div>

            <!-- Reservation Overview -->
            <div class="my-6 bg-[#F8FAFC] border border-gray-200 rounded-xl p-6">
                <h3 class="text-xs font-bold text-[#193366] uppercase tracking-wider mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-calendar-check"></i> Reservation Overview
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm mb-4">
                    <div>
                        <span class="block text-xs text-gray-400 font-semibold uppercase">Guest Name</span>
                        <span class="font-bold text-gray-900 text-base"><?= htmlspecialchars($booking['guest_name'] ?? 'Guest') ?></span>
                        <span class="block text-xs text-gray-600 mt-0.5"><?= htmlspecialchars($booking['guest_email'] ?? '') ?></span>
                        <?php if (!empty($booking['phone_number'])): ?>
                            <span class="block text-xs text-gray-600 mt-0.5"><i class="fa-solid fa-phone text-[10px] mr-1 text-gray-400"></i><?= htmlspecialchars($booking['phone_number']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div>
                        <span class="block text-xs text-gray-400 font-semibold uppercase">Room Reserved</span>
                        <span class="font-bold text-gray-900 text-base"><?= htmlspecialchars($booking['room_label'] ?? 'Hotel Room') ?></span>
                        <span class="block text-xs text-gray-600 mt-0.5"><?= htmlspecialchars($booking['no_of_guests'] ?? 1) ?> Guest(s)</span>
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 pt-4 border-t border-gray-200 text-sm">
                    <div>
                        <span class="block text-xs text-gray-400 font-semibold uppercase">Check-in</span>
                        <span class="font-bold text-gray-900 block mt-0.5"><?= $checkin_fmt ?></span>
                        <span class="text-[11px] text-gray-500">From 02:00 PM</span>
                    </div>
                    <div>
                        <span class="block text-xs text-gray-400 font-semibold uppercase">Check-out</span>
                        <span class="font-bold text-gray-900 block mt-0.5"><?= $checkout_fmt ?></span>
                        <span class="text-[11px] text-gray-500">Until 12:00 PM</span>
                    </div>
                    <div>
                        <span class="block text-xs text-gray-400 font-semibold uppercase">Total Stay</span>
                        <span class="font-bold text-gray-900 block mt-0.5"><?= $nights ?> <?= ($nights == 1) ? 'Night' : 'Nights' ?></span>
                        <span class="text-[11px] text-gray-500">Duration</span>
                    </div>
                </div>

                <?php if (!empty($booking['special_request'])): ?>
                <div class="mt-4 pt-3 border-t border-gray-200 text-xs">
                    <span class="font-bold text-gray-700">Special Request:</span>
                    <span class="text-gray-600 ml-1"><?= htmlspecialchars($booking['special_request']) ?></span>
                </div>
                <?php endif; ?>
            </div>

            <!-- Item & Description -->
            <div class="mb-6">
                <h3 class="text-xs font-bold text-[#193366] uppercase tracking-wider mb-3 flex items-center gap-2">
                    <i class="fa-solid fa-list-check"></i> Item & Description
                </h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse border border-gray-200 rounded-lg overflow-hidden">
                        <thead>
                            <tr class="bg-[#F7F8F9] border-b border-gray-200 text-xs font-bold text-gray-700 uppercase tracking-wider">
                                <th class="py-3 px-4">Item & Description</th>
                                <th class="py-3 px-4 text-center">Nights</th>
                                <th class="py-3 px-4 text-right">Rate / Night</th>
                                <th class="py-3 px-4 text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            <tr>
                                <td class="py-4 px-4">
                                    <div class="font-bold text-gray-900 text-base"><?= htmlspecialchars($booking['room_label'] ?? 'Hotel Room') ?></div>
                                    <div class="text-xs text-gray-500 mt-0.5">
                                        <?= !empty($booking['room_features']) ? htmlspecialchars($booking['room_features']) : 'Room accommodation with amenities' ?>
                                    </div>
                                </td>
                                <td class="py-4 px-4 text-center font-medium text-gray-700"><?= $nights ?></td>
                                <td class="py-4 px-4 text-right font-medium text-gray-700">Rs. <?= number_format($booking['room_price']) ?></td>
                                <td class="py-4 px-4 text-right font-bold text-gray-900">Rs. <?= number_format($booking['tprice']) ?></td>
                            </tr>
                        </tbody>
                        <tfoot class="bg-gray-50 border-t border-gray-200">
                            <tr class="text-base font-bold">
                                <td colspan="3" class="py-3 px-4 text-right text-gray-900 uppercase">Total Amount</td>
                                <td class="py-3 px-4 text-right text-xl text-[#193366]">Rs. <?= number_format($booking['tprice']) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Simple Footer -->
            <div class="pt-6 border-t border-gray-200 flex flex-col sm:flex-row justify-between items-center text-xs text-gray-500 gap-2">
                <div>
                    <span class="font-semibold text-gray-700">Payment:</span> Pay at Hotel / Check-in
                </div>
                <div class="text-center sm:text-right">
                    Thank you for choosing LOTUS Luxury Boutique Hotel!
                </div>
            </div>

        </div>

        <!-- Secondary Navigation (No-Print) -->
        <div class="no-print mt-8 flex flex-wrap justify-between items-center gap-4 text-sm">
            <a href="../index.php" class="text-gray-600 hover:text-black font-semibold flex items-center gap-2">
                <i class="fa-solid fa-house"></i> Home
            </a>
            <div class="flex gap-4">
                <a href="room.php" class="text-[#193366] hover:underline font-semibold">
                    Browse Other Rooms
                </a>
                <a href="cartpage.php" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-lg transition-colors">
                    Go to My Bookings
                </a>
            </div>
        </div>

    </main>
</body>
</html>
