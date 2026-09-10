<?php
include '../conn.php';
session_start();

if (!isset($_SESSION['isLogin'])) {
    header("Location: login.php");
    exit();
}

$room_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$room = null;

if ($room_id > 0) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM rooms WHERE room_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $room_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($result && mysqli_num_rows($result) > 0) {
        $room = mysqli_fetch_assoc($result);
    }
}

if (!$room) {
    header("Location: rooms.php");
    exit();
}

$errorMsg = "";
if (isset($_POST['book_now'])) {

    $user_id = $_SESSION['user_id'] ?? $_SESSION['id'] ?? null;
    
    if (!$user_id) {
        $errorMsg = "Session expired or user ID not found. Please log in again.";
    } else {
        $checkin_date = $_POST['checkin_date'];
        $checkout_date = $_POST['checkout_date'];
        $no_of_guests = intval($_POST['no_of_guests']);

        // Calculate total days & total price
        $date1 = new DateTime($checkin_date);
        $date2 = new DateTime($checkout_date);
        $interval = $date1->diff($date2);
        $days = $interval->days;

        if ($days <= 0 || $date2 <= $date1) {
            $errorMsg = "Check-out date must be after check-in date.";
        } else {
            $tprice = $days * floatval($room['price']);
            $status = 'pending';

            $insertStmt = mysqli_prepare($conn, "INSERT INTO booking (user_id, room_id, checkin_date, checkout_date, no_of_guests, tprice, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($insertStmt, "iissids", $user_id, $room_id, $checkin_date, $checkout_date, $no_of_guests, $tprice, $status);

            if (mysqli_stmt_execute($insertStmt)) {
                header("Location: cartpage.php");
                exit();
            } else {
                $errorMsg = "Failed to process booking. Error: " . mysqli_error($conn);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Checkout - LOTUS</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" />
    <link rel="stylesheet" href="../css/style.css" />
</head>

<body class="bg-[#F7F4ED] min-h-screen">
    <?php include '../includes/logout_toast.php'; ?>

    <!-- Header Navigation -->
    <nav class="container mx-auto">
        <div class="fixed top-0 left-0 right-0 z-40 lg:py-4 text-black shadow-sm bg-white transition-all duration-300">
            <div class="flex justify-between w-full items-center max-w-7xl ml-auto mr-auto px-6 py-2 lg:py-0">
                <a href="../index.php" class="flex gap-2 items-center">
                    <div class="w-10 h-10 text-white font-semibold bg-primary text-2xl rounded-full flex justify-center items-center">L</div>
                    <div class="font-bold text-xl flex flex-col">LOTUS</div>
                </a>
                <div class="lg:flex gap-8 items-center hidden">
                    <a href="../index.php" class="hover:text-[#E5A819]">Home</a>
                    <a href="rooms.php" class="hover:text-[#E5A819]">Rooms</a>
                    <a href="mycart.php" class="hover:text-[#E5A819]">My Cart</a>
                </div>
                <div class="lg:flex gap-4 items-center hidden">
                    <div class="flex gap-2 items-center bg-gray-900/10 py-2 px-4 rounded-full text-gray-900">
                        <div class="p-2 h-8 w-8 rounded-full text-white bg-primary flex justify-center items-center">
                            <?php echo htmlspecialchars($_SESSION['username'][0] ?? 'U'); ?>
                        </div>
                        <h1 class="text-base font-medium"><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></h1>
                    </div>
                    <a href="logout.php" class="text-base font-medium text-white bg-primary py-2 px-6 rounded-full">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="pt-32 pb-20 max-w-7xl mx-auto px-6">
        <a href="rooms.php" class="inline-flex items-center text-gray-600 hover:text-black mb-6">
            <i class="fa-solid fa-arrow-left mr-2"></i> Back to Rooms
        </a>

        <div class="text-center mb-10">
            <h1 class="text-sm tracking-widest text-amber-600 uppercase font-semibold">Book Your Stay</h1>
            <h2 class="text-4xl md:text-5xl font-bold mt-2 text-gray-800"><?php echo htmlspecialchars($room['label']); ?></h2>
        </div>

        <?php if (!empty($errorMsg)): ?>
            <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg text-center">
                <?php echo htmlspecialchars($errorMsg); ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Left Room Info Card -->
            <div class="lg:col-span-1 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden h-fit">
                <div class="relative overflow-hidden">
                    <?php 
                        $rawImg = trim($room['image'] ?? '');
                        if (strpos($rawImg, 'http://') === 0 || strpos($rawImg, 'https://') === 0) {
                            $imgPath = $rawImg;
                            if (strpos($imgPath, 'images.unsplash.com/photo-') !== false && strpos($imgPath, '?') === false) {
                                $imgPath .= '?auto=format&fit=crop&w=800&q=80';
                            }
                        } else {
                            $imgPath = $rawImg;
                            if (!empty($imgPath) && strpos($imgPath, '../') === false && strpos($imgPath, '/') !== 0) {
                                $imgPath = '../' . $imgPath;
                            }
                        }
                        if (empty($imgPath)) {
                            $imgPath = 'https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=800&q=80';
                        }
                    ?>
                    <img src="<?php echo htmlspecialchars($imgPath); ?>" 
                         alt="<?php echo htmlspecialchars($room['label'] ?? 'Room Image'); ?>" 
                         class="w-full h-60 object-cover"
                         onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=600&q=80';" />

                    <span class="absolute top-4 right-4 bg-[#193366] text-white text-sm font-medium px-4 py-1.5 rounded-full shadow-md">
                        Rs. <?php echo htmlspecialchars($room['price']); ?>/night
                    </span>
                </div>
                
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-900 mb-2"><?php echo htmlspecialchars($room['label']); ?></h3>
                    <p class="text-gray-600 text-sm mb-4"><?php echo htmlspecialchars($room['description'] ?? 'Comfortable room with modern amenities.'); ?></p>
                    
                    <div class="flex items-center gap-2 text-gray-700 text-sm border-t border-gray-100 pt-4">
                        <i class="fa-solid fa-user-group text-amber-600"></i>
                        <span>Max Occupancy: <strong>Up to 2 guests</strong></span>
                    </div>
                </div>
            </div>

            <!-- Right Booking Form Card -->
            <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200 p-6 md:p-8">
                <!-- Explicit form action targeting the current page with room ID -->
                <form action="./checkout.php?id=<?php echo $room_id; ?>" method="POST" class="space-y-6">
                    
                    <div>
                        <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Guest Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                                <input type="text" value="<?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?>" readonly class="w-full p-3 bg-gray-100 border border-gray-300 rounded-lg text-gray-600 cursor-not-allowed outline-none" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input type="email" value="<?php echo htmlspecialchars($_SESSION['email'] ?? 'guest@example.com'); ?>" readonly class="w-full p-3 bg-gray-100 border border-gray-300 rounded-lg text-gray-600 cursor-not-allowed outline-none" />
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Booking Details</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Check-in Date *</label>
                                <input type="date" name="checkin_date" required min="<?php echo date('Y-m-d'); ?>" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#193366] outline-none" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Check-out Date *</label>
                                <input type="date" name="checkout_date" required min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#193366] outline-none" />
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Number of Guests *</label>
                                <input type="number" name="no_of_guests" min="1" max="4" value="1" required class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#193366] outline-none" />
                            </div>
                        </div>
                    </div>

                    <button type="submit" name="book_now" class="w-full py-4 bg-[#193366] hover:bg-[#254685] text-white font-semibold rounded-lg shadow-md transition-all duration-200 text-lg cursor-pointer">
                        Confirm & Reserve Room
                    </button>
                </form>
            </div>

        </div>
    </main>

</body>
</html>