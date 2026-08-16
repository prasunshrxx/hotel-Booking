<?php
include '../conn.php';
session_start();

if (!isset($_SESSION['isLogin']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$toastMsg = "";
$toastType = "success";

if (isset($_POST['UpdateStatus']) && isset($_POST['status_change']) && isset($_GET['booking_id'])) {
    $status_change = $_POST['status_change'];
    $booking_id = intval($_GET['booking_id']);

    // Whitelist allowed statuses to prevent arbitrary writes
    $allowedStatuses = ['pending', 'confirmed', 'checked out', 'cancelled'];
    if (!in_array($status_change, $allowedStatuses)) {
        header("Location: adminpage.php?tab=orders&msg=invalid_status");
        exit();
    }

    $stmtStatus = mysqli_prepare($conn, "UPDATE booking SET status = ? WHERE booking_id = ?");
    mysqli_stmt_bind_param($stmtStatus, "si", $status_change, $booking_id);
    
    if (mysqli_stmt_execute($stmtStatus)) {
        // Rebuild filter params so we return to the same filtered view
        $redirectParams = 'tab=orders&msg=status_updated';
        if (!empty($_GET['room_id']))  $redirectParams .= '&room_id='  . intval($_GET['room_id']);
        if (!empty($_GET['status']))   $redirectParams .= '&status='   . urlencode($_GET['status']);
        if (!empty($_GET['sort']))     $redirectParams .= '&sort='     . urlencode($_GET['sort']);
        if (!empty($_GET['search']))   $redirectParams .= '&search='   . urlencode($_GET['search']);
        header("Location: adminpage.php?" . $redirectParams);
        exit();
    } else {
        echo "<script>alert('Failed to change status');</script>";
    }
}


$defaultRoomImages = [
    1 => 'https://images.unsplash.com/photo-1590490360182-c33d57733427?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
    2 => 'https://images.unsplash.com/photo-1566665797739-1674de7a421a?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
    3 => 'https://images.unsplash.com/photo-1596394516093-501ba68a0ba6?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
    4 => 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
    5 => 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
    6 => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
];
foreach ($defaultRoomImages as $id => $img) {
    mysqli_query($conn, "UPDATE rooms SET image = '$img' WHERE room_id = $id AND (image NOT LIKE '%?%' OR image IS NULL OR image = '')");
}

if (!function_exists('getRoomImageUrl')) {
    function getRoomImageUrl($img) {
        $img = trim($img ?? '');
        if (empty($img)) {
            return 'https://images.unsplash.com/photo-1590490360182-c33d57733427?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80';
        }
        if (strpos($img, 'images.unsplash.com/photo-') !== false && strpos($img, '?') === false) {
            $img .= '?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80';
        }
        return $img;
    }
}

if (isset($_POST['AddRoom'])) {
    $label = trim($_POST['label']);
    $price = intval($_POST['price']);
    $no_of_guests = intval($_POST['no_of_guests']);
    $description = trim($_POST['description']);
    $image = getRoomImageUrl($_POST['image']);
    $features = trim($_POST['features']);
    $available = isset($_POST['available']) ? intval($_POST['available']) : 1;

    $stmtAdd = mysqli_prepare($conn, "INSERT INTO rooms (label, price, no_of_guests, description, image, features, available) VALUES (?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmtAdd, "siisssi", $label, $price, $no_of_guests, $description, $image, $features, $available);
    
    if (mysqli_stmt_execute($stmtAdd)) {
        header("Location: adminpage.php?tab=rooms&msg=room_added");
        exit();
    } else {
        $toastMsg = "Failed to add room: " . mysqli_error($conn);
        $toastType = "error";
    }
}

if (isset($_POST['UpdateRoom']) && isset($_POST['room_id'])) {
    $room_id = intval($_POST['room_id']);
    $label = trim($_POST['label']);
    $price = intval($_POST['price']);
    $no_of_guests = intval($_POST['no_of_guests']);
    $description = trim($_POST['description']);
    $image = getRoomImageUrl($_POST['image']);
    $features = trim($_POST['features']);
    $available = intval($_POST['available']);

    $stmtEdit = mysqli_prepare($conn, "UPDATE rooms SET label = ?, price = ?, no_of_guests = ?, description = ?, image = ?, features = ?, available = ? WHERE room_id = ?");
    mysqli_stmt_bind_param($stmtEdit, "siisssii", $label, $price, $no_of_guests, $description, $image, $features, $available, $room_id);
    
    if (mysqli_stmt_execute($stmtEdit)) {
        header("Location: adminpage.php?tab=rooms&msg=room_updated");
        exit();
    } else {
        $toastMsg = "Failed to update room: " . mysqli_error($conn);
        $toastType = "error";
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'delete_room' && isset($_GET['room_id'])) {
    $delete_id = intval($_GET['room_id']);
    
    $stmtDel = mysqli_prepare($conn, "DELETE FROM rooms WHERE room_id = ?");
    mysqli_stmt_bind_param($stmtDel, "i", $delete_id);
    
    try {
        if (mysqli_stmt_execute($stmtDel)) {
            header("Location: adminpage.php?tab=rooms&msg=room_deleted");
            exit();
        } else {
            header("Location: adminpage.php?tab=rooms&msg=has_bookings");
            exit();
        }
    } catch (Exception $e) {
        header("Location: adminpage.php?tab=rooms&msg=has_bookings");
        exit();
    }
}

$activeTab = $_GET['tab'] ?? 'orders';

if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'room_added') {
        $toastMsg = "Room added successfully!";
    } elseif ($_GET['msg'] === 'room_updated') {
        $toastMsg = "Room updated successfully!";
    } elseif ($_GET['msg'] === 'room_deleted') {
        $toastMsg = "Room deleted successfully!";
    } elseif ($_GET['msg'] === 'status_updated') {
        $toastMsg = "Booking status updated successfully!";
    } elseif ($_GET['msg'] === 'has_bookings') {
        $toastMsg = "Cannot delete room with active bookings. Edit availability instead.";
        $toastType = "error";
    }
}

// Room, status, search, and sorting parameters for Orders view
$filterRoom   = isset($_GET['room_id']) ? intval($_GET['room_id']) : 0;
$filterStatus = isset($_GET['status']) ? trim($_GET['status']) : '';
$searchQuery  = isset($_GET['search']) ? trim($_GET['search']) : '';
$sortBy       = isset($_GET['sort']) ? trim($_GET['sort']) : 'id_desc';

// Build SQL query dynamically
$whereClauses = ["1=1"];
$params = [];
$paramTypes = "";

if ($filterRoom > 0) {
    $whereClauses[] = "booking.room_id = ?";
    $params[] = $filterRoom;
    $paramTypes .= "i";
}

if (!empty($filterStatus)) {
    $whereClauses[] = "booking.status = ?";
    $params[] = $filterStatus;
    $paramTypes .= "s";
}

if (!empty($searchQuery)) {
    $whereClauses[] = "(users.username LIKE ? OR booking.booking_id LIKE ? OR rooms.label LIKE ?)";
    $likeSearch = "%" . $searchQuery . "%";
    $params[] = $likeSearch;
    $params[] = $likeSearch;
    $params[] = $likeSearch;
    $paramTypes .= "sss";
}

$orderByClause = "booking.booking_id DESC";
switch ($sortBy) {
    case 'id_asc':
        $orderByClause = "booking.booking_id ASC";
        break;
    case 'checkin_asc':
        $orderByClause = "booking.checkin_date ASC";
        break;
    case 'checkin_desc':
        $orderByClause = "booking.checkin_date DESC";
        break;
    case 'checkout_asc':
        $orderByClause = "booking.checkout_date ASC";
        break;
    case 'checkout_desc':
        $orderByClause = "booking.checkout_date DESC";
        break;
    case 'room_asc':
        $orderByClause = "rooms.label ASC";
        break;
    case 'room_desc':
        $orderByClause = "rooms.label DESC";
        break;
    case 'price_desc':
        $orderByClause = "booking.tprice DESC";
        break;
    case 'price_asc':
        $orderByClause = "booking.tprice ASC";
        break;
    case 'customer_asc':
        $orderByClause = "users.username ASC";
        break;
}

$sql = "SELECT booking.*, users.username, rooms.label 
        FROM booking 
        LEFT JOIN users ON booking.user_id = users.id 
        LEFT JOIN rooms ON booking.room_id = rooms.room_id 
        WHERE " . implode(" AND ", $whereClauses) . " 
        ORDER BY " . $orderByClause;

if (!empty($params)) {
    $stmtBookings = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmtBookings, $paramTypes, ...$params);
    mysqli_stmt_execute($stmtBookings);
    $res = mysqli_stmt_get_result($stmtBookings);
} else {
    $res = mysqli_query($conn, $sql);
}

// Fetch all rooms for filter dropdown and room-wise summary stats
$sqlRooms = "SELECT * FROM rooms ORDER BY label ASC";
$resRooms = mysqli_query($conn, $sqlRooms);
$allRoomsList = [];
if ($resRooms) {
    while ($rRow = mysqli_fetch_assoc($resRooms)) {
        $allRoomsList[] = $rRow;
    }
}

// Fetch room-wise booking counts for stats cards
$sqlRoomStats = "SELECT room_id, COUNT(*) as booking_count, SUM(tprice) as total_revenue FROM booking WHERE status != 'cancelled' GROUP BY room_id";
$resRoomStats = mysqli_query($conn, $sqlRoomStats);
$roomStats = [];
if ($resRoomStats) {
    while ($st = mysqli_fetch_assoc($resRoomStats)) {
        $roomStats[$st['room_id']] = $st;
    }
}

$dataDialoug = null;
if (isset($_GET['booking_id'])) {
    $id = intval($_GET['booking_id']);
    $stmt = mysqli_prepare($conn, "SELECT booking.*, users.username, users.email, rooms.label, rooms.price 
                                   FROM booking 
                                   JOIN users ON booking.user_id = users.id 
                                   JOIN rooms ON booking.room_id = rooms.room_id 
                                   WHERE booking.booking_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $ress = mysqli_stmt_get_result($stmt);
    if ($ress && mysqli_num_rows($ress) > 0) {
        $dataDialoug = mysqli_fetch_assoc($ress);
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Panel - LOTUS Hotel</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="../css/style.css" />
</head>

<body class="relative bg-gray-50 min-h-screen">
    <?php include '../includes/logout_toast.php'; ?>

    <?php if (!empty($toastMsg)): ?>
        <div id="admin-alert" class="fixed top-20 right-6 z-50 flex items-center gap-3 px-5 py-3.5 rounded-xl shadow-2xl text-white <?= $toastType === 'error' ? 'bg-red-600' : 'bg-emerald-600' ?> transition-all duration-300 animate-bounce-once">
            <i class="fa-solid <?= $toastType === 'error' ? 'fa-triangle-exclamation' : 'fa-circle-check' ?> text-lg"></i>
            <span class="text-sm font-medium"><?= htmlspecialchars($toastMsg) ?></span>
            <button onclick="document.getElementById('admin-alert').remove()" class="ml-3 text-white/80 hover:text-white cursor-pointer">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <script>
            setTimeout(() => {
                const el = document.getElementById('admin-alert');
                if (el) el.remove();
            }, 4000);
        </script>
    <?php endif; ?>

    <nav class="container mx-auto">
        <div class="fixed top-0 left-0 right-0 z-40 lg:py-4 text-black shadow-sm bg-white transition-all duration-300" id="nav-cont">
            <div class="flex justify-between w-full items-center max-w-7xl ml-auto mr-auto px-6 py-2 lg:py-0">
                <a href="../index.php" class="flex gap-2 items-center">
                    <div class="w-10 h-10 text-white font-semibold bg-primary text-2xl rounded-full flex justify-center items-center">L</div>
                    <div class="font-bold text-xl flex flex-col">LOTUS</div>
                </a>
                <div class="lg:block hidden">
                    <div class="flex gap-8 cursor-pointer ml-2 text-sm font-medium">
                        <a href="adminpage.php?tab=orders" class="<?= $activeTab === 'orders' ? 'text-[#E5A819]' : 'hover:text-[#E5A819]' ?> transition-colors duration-150">Bookings</a>
                        <a href="adminpage.php?tab=rooms" class="<?= $activeTab === 'rooms' ? 'text-[#E5A819]' : 'hover:text-[#E5A819]' ?> transition-colors duration-150">Rooms</a>
                    </div>
                </div>
                <div class="lg:flex gap-8 items-center hidden">
                    <div class="flex gap-2 items-center bg-gray-900/10 py-2 px-4 rounded-full text-gray-900">
                        <div class="p-2 h-9 rounded-full text-white bg-primary text-2xl flex justify-center items-center"><?= htmlspecialchars($_SESSION['username'][0]) ?></div>
                        <h1 class="text-base font-medium text-black" id="userName"><?= htmlspecialchars($_SESSION['username']) ?></h1>
                    </div>
                    <a href="logout.php" class="text-base font-medium text-white transition-all duration-300 bg-primary py-2 px-6 rounded-full">Logout</a>
                </div>
                <div class="lg:hidden flex"><i class="fa-solid fa-bars text-2xl"></i></div>
            </div>
        </div>
    </nav>

    <main>
        <!-- Header Banner & Tabs -->
        <section class="relative pt-28 pb-10 flex flex-col items-center justify-center bg-[#F7F4ED]">
            <div class="text-center">
                <h1 class="text-xs tracking-widest text-black/60 font-semibold uppercase">Admin Panel</h1>
                <p class="text-black/80 text-3xl md:text-5xl font-semibold text-playfair mt-2">
                    <?= $activeTab === 'rooms' ? 'Room Inventory' : 'Guest Bookings & Orders' ?>
                </p>
            </div>

            <!-- Tab Buttons -->
            <div class="flex justify-center gap-3 mt-6">
                <a href="adminpage.php?tab=orders" class="px-6 py-2.5 rounded-full font-medium text-sm transition-all duration-200 flex items-center gap-2 <?= ($activeTab === 'orders') ? 'bg-[#193366] text-white shadow-md' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200' ?>">
                    <i class="fa-solid fa-list-check"></i> Orders & Bookings
                </a>
                <a href="adminpage.php?tab=rooms" class="px-6 py-2.5 rounded-full font-medium text-sm transition-all duration-200 flex items-center gap-2 <?= ($activeTab === 'rooms') ? 'bg-[#193366] text-white shadow-md' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200' ?>">
                    <i class="fa-solid fa-bed"></i> Manage Rooms
                </a>
            </div>
        </section>

        
        <?php if ($activeTab === 'orders'): ?>
        <section class="relative pt-8 pb-20 bg-white px-4">
            <div class="w-full max-w-7xl mx-auto space-y-6">

                <!-- Filters & Search Toolbar -->
                <form method="GET" action="adminpage.php" class="bg-[#F8FAFC] border border-gray-200 rounded-xl p-4 flex flex-wrap gap-4 items-center justify-between shadow-xs">
                    <input type="hidden" name="tab" value="orders" />
                    
                    <div class="flex flex-wrap gap-3 items-center w-full lg:w-auto">
                        <!-- Room Filter -->
                        <div class="flex items-center gap-2">
                            <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide flex items-center gap-1">
                                <i class="fa-solid fa-bed text-[#193366]"></i> Room:
                            </label>
                            <select name="room_id" onchange="this.form.submit()" class="p-2 border border-gray-300 rounded-lg text-xs bg-white focus:outline-none focus:ring-2 focus:ring-[#193366] text-gray-800 font-medium">
                                <option value="0">All Rooms</option>
                                <?php foreach ($allRoomsList as $rm): ?>
                                    <option value="<?= $rm['room_id'] ?>" <?= ($filterRoom === intval($rm['room_id'])) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($rm['label']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Status Filter -->
                        <div class="flex items-center gap-2">
                            <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide flex items-center gap-1">
                                <i class="fa-solid fa-filter text-[#193366]"></i> Status:
                            </label>
                            <select name="status" onchange="this.form.submit()" class="p-2 border border-gray-300 rounded-lg text-xs bg-white focus:outline-none focus:ring-2 focus:ring-[#193366] text-gray-800 font-medium">
                                <option value="">All Statuses</option>
                                <option value="pending" <?= ($filterStatus === 'pending') ? 'selected' : '' ?>>Pending</option>
                                <option value="checked out" <?= ($filterStatus === 'checked out') ? 'selected' : '' ?>>Checked Out</option>
                                <option value="cancelled" <?= ($filterStatus === 'cancelled') ? 'selected' : '' ?>>Cancelled</option>
                            </select>
                        </div>

                        <!-- Sort By Filter -->
                        <div class="flex items-center gap-2">
                            <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide flex items-center gap-1">
                                <i class="fa-solid fa-arrow-down-short-wide text-[#193366]"></i> Sort By:
                            </label>
                            <select name="sort" onchange="this.form.submit()" class="p-2 border border-gray-300 rounded-lg text-xs bg-white focus:outline-none focus:ring-2 focus:ring-[#193366] text-gray-800 font-medium">
                                <option value="id_desc" <?= ($sortBy === 'id_desc') ? 'selected' : '' ?>>Booking ID (Newest First)</option>
                                <option value="id_asc" <?= ($sortBy === 'id_asc') ? 'selected' : '' ?>>Booking ID (Oldest First)</option>
                                <option value="checkin_asc" <?= ($sortBy === 'checkin_asc') ? 'selected' : '' ?>>Check-in Date (Earliest First)</option>
                                <option value="checkin_desc" <?= ($sortBy === 'checkin_desc') ? 'selected' : '' ?>>Check-in Date (Latest First)</option>
                                <option value="room_asc" <?= ($sortBy === 'room_asc') ? 'selected' : '' ?>>Room Name (A - Z)</option>
                                <option value="price_desc" <?= ($sortBy === 'price_desc') ? 'selected' : '' ?>>Total Amount (High - Low)</option>
                                <option value="price_asc" <?= ($sortBy === 'price_asc') ? 'selected' : '' ?>>Total Amount (Low - High)</option>
                                <option value="customer_asc" <?= ($sortBy === 'customer_asc') ? 'selected' : '' ?>>Guest Name (A - Z)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Search Input -->
                    <div class="flex items-center gap-2 w-full lg:w-auto">
                        <div class="relative w-full lg:w-64">
                            <input type="text" name="search" value="<?= htmlspecialchars($searchQuery) ?>" placeholder="Search guest or ID..." class="w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg text-xs bg-white focus:outline-none focus:ring-2 focus:ring-[#193366]" />
                            <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-gray-400 text-xs"></i>
                        </div>
                        <button type="submit" class="px-4 py-2 bg-[#193366] text-white text-xs font-semibold rounded-lg hover:bg-[#254685] transition-colors cursor-pointer">
                            Filter
                        </button>
                        <?php if ($filterRoom > 0 || !empty($filterStatus) || !empty($searchQuery) || $sortBy !== 'id_desc'): ?>
                            <a href="adminpage.php?tab=orders" class="px-3 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 text-xs font-semibold rounded-lg transition-colors" title="Reset Filters">
                                Reset
                            </a>
                        <?php endif; ?>
                    </div>
                </form>

                <!-- Bookings Table Card -->
                <div class="rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <table class="border-collapse w-full text-left">
                        <thead>
                            <tr class="bg-[#F7F8F9] border-b border-gray-200 text-gray-700 text-sm font-semibold">
                                <th class="p-4 text-center">ID</th>
                                <th class="p-4">Customer Name</th>
                                <th class="p-4">Room</th>
                                <th class="p-4">Check-in</th>
                                <th class="p-4">Check-Out</th>

                                <th class="p-4">Amount</th>
                                <th class="p-4 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($res && mysqli_num_rows($res) > 0) {
                                while ($row = mysqli_fetch_assoc($res)) {


                                    echo "
                                    <tr class='border-b border-gray-100 hover:bg-gray-50/80 transition-colors text-sm text-gray-700'>
                                        <td class='p-4 text-center font-medium'>" . htmlspecialchars($row['booking_id']) . "</td>
                                        <td class='p-4 font-semibold text-gray-900'>" . htmlspecialchars($row['username'] ?? 'N/A') . "</td>
                                        <td class='p-4 font-medium text-blue-900'>" . htmlspecialchars($row['label'] ?? 'N/A') . "</td>
                                        <td class='p-4'>" . htmlspecialchars($row['checkin_date']) . "</td>
                                        <td class='p-4'>" . htmlspecialchars($row['checkout_date']) . "</td>
                                        <td class='p-4 font-semibold text-gray-900'>Rs. " . htmlspecialchars(number_format($row['tprice'])) . "</td>
                                        <td class='p-4'>
                                             <div class='flex items-center justify-between gap-2 min-w-[140px]'>";

                                    $isTerminal = ($row['status'] === 'cancelled' || $row['status'] === 'checked out');

                                    if ($isTerminal) {
                                        $termClass = $row['status'] === 'cancelled' ? 'bg-red-100 text-red-600' : 'bg-emerald-100 text-emerald-700';
                                        echo "<span class='inline-flex items-center gap-1 text-xs font-semibold capitalize py-1 px-2.5 rounded-full $termClass whitespace-nowrap'>
                                                  <i class='fa-solid fa-lock text-[10px]'></i>
                                                  " . htmlspecialchars($row['status']) . "
                                              </span>";
                                    } else {
                                        echo "<form method='POST' action='adminpage.php?tab=orders&booking_id=" . $row['booking_id'] . "&room_id=" . $filterRoom . "&status=" . urlencode($filterStatus) . "&sort=" . urlencode($sortBy) . "' class='flex items-center gap-1.5'>
                                                  <select name='status_change' class='py-1 px-2 border border-gray-300 rounded-lg text-xs outline-none focus:border-[#193366] bg-white cursor-pointer'>
                                                      <option value='pending'" . ($row['status'] === 'pending' ? ' selected' : '') . ">Pending</option>
                                                      <option value='confirmed'" . ($row['status'] === 'confirmed' ? ' selected' : '') . ">Confirmed</option>
                                                      <option value='checked out'" . ($row['status'] === 'checked out' ? ' selected' : '') . ">Checked Out</option>
                                                      <option value='cancelled'" . ($row['status'] === 'cancelled' ? ' selected' : '') . ">Cancelled</option>
                                                  </select>
                                                  <button type='submit' name='UpdateStatus' title='Save status' class='inline-flex items-center justify-center w-7 h-7 rounded-full bg-[#193366] hover:bg-[#254685] text-white transition-colors cursor-pointer flex-shrink-0'>
                                                      <i class='fa-solid fa-check text-[10px]'></i>
                                                  </button>
                                              </form>";
                                    }

                                    echo "
                                             <a href='?tab=orders&booking_id=" . $row['booking_id'] . "&room_id=" . $filterRoom . "&status=" . urlencode($filterStatus) . "&sort=" . urlencode($sortBy) . "' class='inline-flex items-center justify-center w-7 h-7 rounded-full bg-gray-100 hover:bg-[#193366] hover:text-white transition-colors text-gray-500 flex-shrink-0 ml-auto' title='View booking details'>
                                                 <i class='fa-solid fa-chevron-right text-[10px]'></i>
                                             </a>
                                         </div>
                                         </td>
                                    </tr>
                                    ";
                                }
                            } else {
                                echo "<tr><td colspan='7' class='p-12 text-center text-gray-500 font-medium'>No bookings found matching your filter criteria.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
        <?php endif; ?>

    
        <?php if ($activeTab === 'rooms'): ?>
        <section class="relative pt-8 pb-20 bg-white px-4">
            <div class="max-w-7xl mx-auto">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">Rooms List</h2>
                        <p class="text-xs text-gray-500 mt-1">Add, update or remove room listings visible on the website</p>
                    </div>
                    <button onclick="openAddRoomModal()" class="px-5 py-2.5 bg-[#193366] hover:bg-[#304775] text-white text-sm font-semibold rounded-full shadow-md transition-all flex items-center gap-2 cursor-pointer">
                        <i class="fa-solid fa-plus text-xs"></i> Add New Room
                    </button>
                </div>

                <div class="w-full rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <table class="border-collapse w-full text-left">
                        <thead>
                            <tr class="bg-[#F7F8F9] border-b border-gray-200 text-gray-700 text-sm font-semibold">
                                <th class="p-4 text-center">ID</th>
                                <th class="p-4">Image</th>
                                <th class="p-4">Room Title</th>
                                <th class="p-4">Price / Night</th>
                                <th class="p-4 text-center">Guests</th>
                                <th class="p-4">Features</th>
                                <th class="p-4 text-center">Available</th>
                                <th class="p-4 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (!empty($allRoomsList)) {
                                foreach ($allRoomsList as $room) {
                                    $roomJson = htmlspecialchars(json_encode($room), ENT_QUOTES, 'UTF-8');
                                    ?>
                                    <tr class="border-b border-gray-100 hover:bg-gray-50/80 transition-colors text-sm text-gray-700">
                                        <td class="p-4 text-center font-medium"><?= htmlspecialchars($room['room_id']) ?></td>
                                        <td class="p-4">
                                            <img src="<?= htmlspecialchars(getRoomImageUrl($room['image'])) ?>" 
                                                 onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=800&q=80';" 
                                                 alt="<?= htmlspecialchars($room['label']) ?>" 
                                                 class="w-16 h-12 object-cover rounded-lg shadow-sm border border-gray-200" />
                                        </td>
                                        <td class="p-4 font-bold text-gray-900"><?= htmlspecialchars($room['label']) ?></td>
                                        <td class="p-4 font-semibold text-emerald-700">Rs. <?= htmlspecialchars(number_format($room['price'])) ?></td>
                                        <td class="p-4 text-center"><?= htmlspecialchars($room['no_of_guests']) ?> Person(s)</td>
                                        <td class="p-4 max-w-xs text-xs text-gray-500 truncate"><?= htmlspecialchars($room['features']) ?></td>
                                        <td class="p-4 text-center">
                                            <span class="py-1 px-3 rounded-full text-xs font-semibold <?= $room['available'] > 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' ?>">
                                                <?= htmlspecialchars($room['available']) ?> Available
                                            </span>
                                        </td>
                                        <td class="p-4 text-center">
                                            <div class="flex justify-center gap-2">
                                                <button onclick="openEditRoomModal(<?= $roomJson ?>)" class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-colors flex items-center justify-center cursor-pointer" title="Edit Room">
                                                    <i class="fa-solid fa-pen text-xs"></i>
                                                </button>
                                                <a href="adminpage.php?tab=rooms&action=delete_room&room_id=<?= $room['room_id'] ?>" onclick="return confirm('Are you sure you want to delete \'<?= htmlspecialchars(addslashes($room['label'])) ?>\'?')" class="w-8 h-8 rounded-full bg-red-50 text-red-600 hover:bg-red-600 hover:text-white transition-colors flex items-center justify-center" title="Delete Room">
                                                    <i class="fa-solid fa-trash text-xs"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                echo "<tr><td colspan='8' class='p-8 text-center text-gray-500 font-medium'>No rooms in inventory. Click 'Add New Room' to create one.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
        <?php endif; ?>
    </main>

    <!-- Modal Dialog: View Booking Details -->
    <div class="inset-0 fixed z-50 <?= isset($_GET['booking_id']) ? '' : 'hidden' ?>" id="dialog_admin">
        <div class="absolute inset-0 h-screen w-full bg-black/40 backdrop-blur-xs flex justify-center items-center p-4">
            <form class="relative max-w-lg w-full rounded-2xl bg-white p-6 shadow-2xl" method="POST" action="adminpage.php?tab=orders&booking_id=<?= htmlspecialchars($_GET['booking_id'] ?? '') ?>">
                <div class="flex w-full justify-between items-center mb-4 pb-3 border-b border-gray-100">
                    <h3 class="font-bold text-lg text-gray-800">Booking Details #<?= htmlspecialchars($dataDialoug['booking_id'] ?? '') ?></h3>
                    <span class="hover:bg-gray-100 p-2 cursor-pointer text-gray-500 rounded-full flex justify-center items-center transition-colors" onclick="closeMenu()">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </span>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <div class="mb-3">
                            <h4 class="text-xs text-gray-500 font-medium">Guest Name</h4>
                            <p class="text-sm text-gray-800 font-semibold"><?= htmlspecialchars($dataDialoug['username'] ?? '') ?></p>
                        </div>
                        <div class="mb-3">
                            <h4 class="text-xs text-gray-500 font-medium">Room</h4>
                            <p class="text-sm text-gray-800 font-semibold"><?= htmlspecialchars($dataDialoug['label'] ?? '') ?></p>
                        </div>
                        <div class="mb-3">
                            <h4 class="text-xs text-gray-500 font-medium">Check-in</h4>
                            <p class="text-sm text-gray-800 font-semibold"><?= htmlspecialchars($dataDialoug['checkin_date'] ?? '') ?></p>
                        </div>
                        <div class="mb-3">
                            <h4 class="text-xs text-gray-500 font-medium">Total Amount</h4>
                            <p class="text-sm text-emerald-700 font-bold">Rs. <?= htmlspecialchars($dataDialoug['tprice'] ?? '') ?></p>
                        </div>
                    </div>
                    <div>
                        <div class="mb-3">
                            <h4 class="text-xs text-gray-500 font-medium">Email</h4>
                            <p class="text-sm text-gray-800 font-semibold truncate"><?= htmlspecialchars($dataDialoug['email'] ?? '') ?></p>
                        </div>
                        <div class="mb-3">
                            <h4 class="text-xs text-gray-500 font-medium">Guests Count</h4>
                            <p class="text-sm text-gray-800 font-semibold"><?= htmlspecialchars($dataDialoug['no_of_guests'] ?? '') ?></p>
                        </div>
                        <div class="mb-3">
                            <h4 class="text-xs text-gray-500 font-medium">Check-out</h4>
                            <p class="text-sm text-gray-800 font-semibold"><?= htmlspecialchars($dataDialoug['checkout_date'] ?? '') ?></p>
                        </div>
                        <div class="mb-3">
                            <h4 class="text-xs text-gray-500 font-medium">Update Status</h4>
                            <?php
                            if (isset($dataDialoug)) {
                                echo ($dataDialoug['status'] == 'cancelled' || $dataDialoug['status'] == 'checked out') 
                                    ? "<span class='text-xs font-bold capitalize py-1 px-3 rounded-full inline-block bg-gray-100 text-gray-700'>" . htmlspecialchars($dataDialoug['status']) . "</span>" 
                                    : "<select name='status_change' class='mt-1 p-1.5 border border-gray-300 rounded-lg text-xs outline-none focus:border-[#193366] w-full'> 
                                        <option value='pending'" . ($dataDialoug['status'] == 'pending' ? 'selected' : '') . ">Pending</option>
                                        <option value='cancelled'" . ($dataDialoug['status'] == 'cancelled' ? 'selected' : '') . ">Cancelled</option>
                                        <option value='checked out'" . ($dataDialoug['status'] == 'checked out' ? 'selected' : '') . ">Checked Out</option>
                                    </select>";
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <?php
                if (isset($dataDialoug)) {
                    echo ($dataDialoug['status'] == 'cancelled' || $dataDialoug['status'] == 'checked out') 
                        ? ""
                        : '<button type="submit" class="mt-6 w-full py-2.5 rounded-full text-white font-medium bg-[#193366] hover:bg-[#304775] transition-all cursor-pointer shadow-md text-sm" name="UpdateStatus">Update Status</button>';
                }
                ?>
            </form>
        </div>
    </div>

    <!-- Modal Dialog: Add New Room -->
    <div class="inset-0 fixed z-50 hidden" id="addRoomModal">
        <div class="absolute inset-0 h-screen w-full bg-black/50 backdrop-blur-xs flex justify-center items-center p-4">
            <form class="relative max-w-xl w-full rounded-2xl bg-white p-6 shadow-2xl max-h-[90vh] overflow-y-auto" method="POST" action="adminpage.php?tab=rooms">
                <div class="flex w-full justify-between items-center pb-4 mb-4 border-b border-gray-100">
                    <h3 class="font-bold text-xl text-gray-800"><i class="fa-solid fa-plus-circle mr-2 text-[#193366]"></i>Add New Room</h3>
                    <span class="hover:bg-gray-100 p-2 cursor-pointer text-gray-500 rounded-full transition-colors" onclick="closeAddRoomModal()">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </span>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Room Title / Name *</label>
                        <input type="text" name="label" required placeholder="e.g. Deluxe Garden View Room" class="w-full p-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#193366]" />
                    </div>
                    
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Price per Night (Rs.) *</label>
                        <input type="number" name="price" required placeholder="e.g. 50" class="w-full p-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#193366]" />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Max Guests *</label>
                        <input type="number" name="no_of_guests" required min="1" placeholder="e.g. 2" class="w-full p-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#193366]" />
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Available Quantity *</label>
                        <input type="number" name="available" value="1" min="0" required class="w-full p-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#193366]" />
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Image URL *</label>
                        <input type="text" name="image" required placeholder="https://images.unsplash.com/..." class="w-full p-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#193366]" />
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Features (comma separated)</label>
                        <input type="text" name="features" placeholder="Free WiFi, Air Conditioning, Private Bathroom, Terrace" class="w-full p-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#193366]" />
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Room Description</label>
                        <textarea name="description" rows="3" placeholder="Write a brief description of the room..." class="w-full p-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#193366]"></textarea>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-gray-100">
                    <button type="button" onclick="closeAddRoomModal()" class="px-5 py-2 rounded-full border border-gray-300 text-gray-600 hover:bg-gray-100 text-sm font-medium cursor-pointer">Cancel</button>
                    <button type="submit" name="AddRoom" class="px-6 py-2 rounded-full bg-[#193366] hover:bg-[#304775] text-white text-sm font-medium shadow-md cursor-pointer transition-all">Add Room</button>
                </div>
            </form>
        </div>
    </div>

    <div class="inset-0 fixed z-50 hidden" id="editRoomModal">
        <div class="absolute inset-0 h-screen w-full bg-black/50 backdrop-blur-xs flex justify-center items-center p-4">
            <form class="relative max-w-xl w-full rounded-2xl bg-white p-6 shadow-2xl max-h-[90vh] overflow-y-auto" method="POST" action="adminpage.php?tab=rooms">
                <input type="hidden" name="room_id" id="edit_room_id" />
                <div class="flex w-full justify-between items-center pb-4 mb-4 border-b border-gray-100">
                    <h3 class="font-bold text-xl text-gray-800"><i class="fa-solid fa-pen-to-square mr-2 text-[#193366]"></i>Edit Room</h3>
                    <span class="hover:bg-gray-100 p-2 cursor-pointer text-gray-500 rounded-full transition-colors" onclick="closeEditRoomModal()">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </span>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Room Title / Name *</label>
                        <input type="text" name="label" id="edit_label" required class="w-full p-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#193366]" />
                    </div>
                    
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Price per Night (Rs.) *</label>
                        <input type="number" name="price" id="edit_price" required class="w-full p-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#193366]" />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Max Guests *</label>
                        <input type="number" name="no_of_guests" id="edit_no_of_guests" required min="1" class="w-full p-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#193366]" />
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Available Quantity *</label>
                        <input type="number" name="available" id="edit_available" min="0" required class="w-full p-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#193366]" />
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Image URL *</label>
                        <input type="text" name="image" id="edit_image" required class="w-full p-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#193366]" />
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Features (comma separated)</label>
                        <input type="text" name="features" id="edit_features" class="w-full p-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#193366]" />
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Room Description</label>
                        <textarea name="description" id="edit_description" rows="3" class="w-full p-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#193366]"></textarea>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-gray-100">
                    <button type="button" onclick="closeEditRoomModal()" class="px-5 py-2 rounded-full border border-gray-300 text-gray-600 hover:bg-gray-100 text-sm font-medium cursor-pointer">Cancel</button>
                    <button type="submit" name="UpdateRoom" class="px-6 py-2 rounded-full bg-[#193366] hover:bg-[#304775] text-white text-sm font-medium shadow-md cursor-pointer transition-all">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <footer class="bg-[#1B2232] pt-10">
        <div class="p-6 max-w-7xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-4 md:grid-cols-2 gap-8 justify-between text-white">
                <span>
                    <div class="flex gap-2 text-white font-bold items-center">
                        <h2 class="bg-[#193366] p-2 rounded-full w-10 h-10 flex justify-center items-center text-white">L</h2>
                        <div>
                            <h1>LOTUS</h1>
                        </div>
                    </div>
                    <p class="text-white/60 mt-4">Your quiet home in Kathmandu. Over 10 years of welcoming travelers, trekkers, and adventurers with warm Nepali hospitality.</p>
                </span>
                <span>
                    <h1 class="text-lg font-semibold">Quick Links</h1>
                    <div class="flex gap-2 mt-4 text-white/60 flex-col">
                        <p>Home</p>
                        <p>Rooms</p>
                        <p>Garden & Terrace</p>
                    </div>
                </span>
                <span>
                    <h1 class="text-lg font-semibold">Contact Us</h1>
                    <div class="mt-4 flex flex-col text-white/60 gap-4 [&_span]:flex [&_span]:items-center [&_span]:gap-2">
                        <span><i class="fa-solid fa-phone"></i>+977 9765406567</span>
                        <span><i class="fa-regular fa-envelope"></i>ahenstha@gmail.com</span>
                        <span><i class="fa-solid fa-location-dot"></i>Kathmandu, Nepal</span>
                    </div>
                </span>
                <span>
                    <h1 class="text-lg font-semibold">Stay Updated</h1>
                    <p class="text-white/60 mt-4">Subscribe for exclusive offers and travel tips.</p>
                    <input type="text" placeholder="Your Email" class="w-full focus:outline-0 p-3 bg-[#313846] border-gray-500 placeholder:text-base mt-2 border rounded-lg" />
                    <button class="w-full py-2 px-4 font-normal bg-[#193366] hover:bg-[#1A3161] transition-all duration-200 text-white text-lg mt-2 cursor-pointer rounded-lg">Subscribe</button>
                </span>
            </div>
            <div class="h-px mt-10 bg-gray-400/30"></div>
            <div class="justify-between flex flex-col md:flex-row text-center text-white/60 py-6">
                <h1>© 2026 LOTUS. All rights reserved.</h1>
                <h1>Created by <a href="https://www.instagram.com/_shrestha__prasun_/" class="hover:text-blue-500 cursor-pointer transition-colors duration-200">Prasun Shrestha</a></h1>
            </div>
        </div>
    </footer>
    
    <script>
        window.closeMenu = () => {
            window.location.href = 'adminpage.php?tab=orders';
        };

        function openAddRoomModal() {
            document.getElementById('addRoomModal').classList.remove('hidden');
        }

        function closeAddRoomModal() {
            document.getElementById('addRoomModal').classList.add('hidden');
        }

        function openEditRoomModal(room) {
            document.getElementById('edit_room_id').value = room.room_id;
            document.getElementById('edit_label').value = room.label;
            document.getElementById('edit_price').value = room.price;
            document.getElementById('edit_no_of_guests').value = room.no_of_guests;
            document.getElementById('edit_available').value = room.available;
            document.getElementById('edit_image').value = room.image;
            document.getElementById('edit_features').value = room.features || '';
            document.getElementById('edit_description').value = room.description || '';
            document.getElementById('editRoomModal').classList.remove('hidden');
        }

        function closeEditRoomModal() {
            document.getElementById('editRoomModal').classList.add('hidden');
        }
    </script>
</body>

</html>