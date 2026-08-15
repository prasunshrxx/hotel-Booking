<?php
include '../conn.php';
session_start();
if (!isset($_SESSION['isLogin'])) {
    header("location:http://localhost/hotelbooking/pages/login.php ");
}


if (isset($_SESSION['user_id'])) {
    $user_id      = $_SESSION['user_id'];
    $filterRoom   = isset($_GET['room_id']) ? intval($_GET['room_id']) : 0;
    $filterStatus = isset($_GET['status']) ? trim($_GET['status']) : 'active'; // default show active (non-cancelled)
    $sortBy       = isset($_GET['sort']) ? trim($_GET['sort']) : 'id_desc';

    $whereClauses = ["booking.user_id = ?"];
    $params = [$user_id];
    $paramTypes = "i";

    if ($filterRoom > 0) {
        $whereClauses[] = "booking.room_id = ?";
        $params[] = $filterRoom;
        $paramTypes .= "i";
    }

    if ($filterStatus === 'active') {
        $whereClauses[] = "booking.status != 'cancelled'";
    } elseif (!empty($filterStatus) && $filterStatus !== 'all') {
        $whereClauses[] = "booking.status = ?";
        $params[] = $filterStatus;
        $paramTypes .= "s";
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
        case 'price_desc':
            $orderByClause = "booking.tprice DESC";
            break;
        case 'price_asc':
            $orderByClause = "booking.tprice ASC";
            break;
    }

    $sql = "SELECT booking.*, rooms.label AS room_label 
            FROM booking 
            LEFT JOIN rooms ON booking.room_id = rooms.room_id 
            WHERE " . implode(" AND ", $whereClauses) . " 
            ORDER BY " . $orderByClause;

    $stmtUserBookings = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmtUserBookings, $paramTypes, ...$params);
    mysqli_stmt_execute($stmtUserBookings);
    $res = mysqli_stmt_get_result($stmtUserBookings);

    // Fetch user's distinct rooms for room-wise dropdown filter
    $sqlUserRooms = "SELECT DISTINCT rooms.room_id, rooms.label 
                     FROM booking 
                     JOIN rooms ON booking.room_id = rooms.room_id 
                     WHERE booking.user_id = '$user_id'";
    $resUserRooms = mysqli_query($conn, $sqlUserRooms);
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>My Cart & Bookings - LOTUS</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="../Css/style.css" />
</head>

<body>
    <?php include '../includes/logout_toast.php'; ?>
    <nav class="container mx-auto">
        <div class="fixed top-0 left-0 right-0 z-40 lg:py-4 text-black shadow-sm bg-white transition-all duration-300"
            id="nav-cont">
            <div class="flex justify-between w-full items-center max-w-7xl ml-auto mr-auto px-6 py-2 lg:py-0">
                <a href="../index.php" class="flex gap-2 items-center">
                    <div
                        class="w-10 h-10 text-white font-semibold bg-primary text-2xl rounded-full flex justify-center items-center">
                        L</div>
                    <div class="font-bold text-xl flex flex-col">LOTUS</div>
                </a>
                <div class="lg:block hidden">
                    <div class="flex gap-8 cursor-pointer ml-2 text-sm font-medium">
                        <a href="../index.php" class="hover:text-[#E5A819] transition-colors duration-150">Home</a>
                        <a href="room.php" class="hover:text-[#E5A819] transition-colors duration-150">Rooms</a>
                        <a href="garden.php" class="hover:text-[#E5A819] transition-colors duration-150">Garden &
                            Terrace</a>
                        <a href="cartpage.php" class="text-[#E5A819] transition-colors duration-150">My Cart</a>

                    </div>
                </div>
                <div class="lg:flex gap-8 items-center hidden">
                    <?php
                    if (!$_SESSION['isLogin']) {
                        echo ' <a href="login.php" class=" text-base font-medium text-white transition-all duration-300 bg-primary py-2 px-6 rounded-full">Login</a>';
                    } else {
                        echo '<div class=" flex gap-2 items-center bg-gray-900/10 py-2 px-4 rounded-full text-gray-900  ">
                            <div class=" p-2 h-9 rounded-full text-white bg-primary text-2xl  flex justify-center items-center">' . $_SESSION['username'][0] . '</div>
                            <h1 class="text-base font-medium text-black transition-all duration-300" id="userName">' . $_SESSION['username'] . '</h1>
                        </div><a href="logout.php" class=" text-base font-medium text-white transition-all duration-300 bg-primary py-2 px-6 rounded-full">Logout</a>';
                    }

                    ?>
                </div>
                <div class="lg:hidden flex"><i class="fa-solid fa-bars text-2xl"></i></div>
            </div>
        </div>
    </nav>

    <main class="">
        <section class="relative pt-32 pb-16 flex items-center justify-center bg-[#F7F4ED]">
            <div class="text-center">
                <h1 class="text-sm tracking-widest text-black/70 font-medium"><i class="text-5xl fa-solid fa-cart-plus"></i></h1>
                <p class="text-black/80 text-4xl md:text-5xl font-semibold text-playfair mt-3">My Bookings & Cart</p>
            </div>
        </section>

        <section class="relative pt-8 pb-20 flex items-center justify-center bg-white px-4">
            <div class="w-full max-w-7xl mx-auto space-y-6">

                <!-- Filter and Sort Control Bar -->
                <form method="GET" action="cartpage.php" class="bg-[#F8FAFC] border border-gray-200 rounded-xl p-4 flex flex-wrap gap-4 items-center justify-between shadow-xs">
                    <div class="flex flex-wrap gap-3 items-center w-full md:w-auto">
                        
                        <!-- Room Filter -->
                        <div class="flex items-center gap-2">
                            <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide flex items-center gap-1">
                                <i class="fa-solid fa-bed text-[#193366]"></i> Room:
                            </label>
                            <select name="room_id" onchange="this.form.submit()" class="p-2 border border-gray-300 rounded-lg text-xs bg-white focus:outline-none focus:ring-2 focus:ring-[#193366] text-gray-800 font-medium">
                                <option value="0">All Booked Rooms</option>
                                <?php if ($resUserRooms && mysqli_num_rows($resUserRooms) > 0): ?>
                                    <?php while ($urm = mysqli_fetch_assoc($resUserRooms)): ?>
                                        <option value="<?= $urm['room_id'] ?>" <?= ($filterRoom === intval($urm['room_id'])) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($urm['label']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- Status Filter -->
                        <div class="flex items-center gap-2">
                            <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide flex items-center gap-1">
                                <i class="fa-solid fa-filter text-[#193366]"></i> Status:
                            </label>
                            <select name="status" onchange="this.form.submit()" class="p-2 border border-gray-300 rounded-lg text-xs bg-white focus:outline-none focus:ring-2 focus:ring-[#193366] text-gray-800 font-medium">
                                <option value="active" <?= ($filterStatus === 'active') ? 'selected' : '' ?>>Active Bookings</option>
                                <option value="all" <?= ($filterStatus === 'all') ? 'selected' : '' ?>>All (Inc. Cancelled)</option>
                                <option value="pending" <?= ($filterStatus === 'pending') ? 'selected' : '' ?>>Pending Only</option>
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
                                <option value="id_desc" <?= ($sortBy === 'id_desc') ? 'selected' : '' ?>>Newest First</option>
                                <option value="id_asc" <?= ($sortBy === 'id_asc') ? 'selected' : '' ?>>Oldest First</option>
                                <option value="checkin_asc" <?= ($sortBy === 'checkin_asc') ? 'selected' : '' ?>>Check-in Date (Earliest)</option>
                                <option value="checkin_desc" <?= ($sortBy === 'checkin_desc') ? 'selected' : '' ?>>Check-in Date (Latest)</option>
                                <option value="price_desc" <?= ($sortBy === 'price_desc') ? 'selected' : '' ?>>Amount (High - Low)</option>
                                <option value="price_asc" <?= ($sortBy === 'price_asc') ? 'selected' : '' ?>>Amount (Low - High)</option>
                            </select>
                        </div>
                    </div>

                    <?php if ($filterRoom > 0 || $filterStatus !== 'active' || $sortBy !== 'id_desc'): ?>
                        <a href="cartpage.php" class="px-3 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 text-xs font-semibold rounded-lg transition-colors flex items-center gap-1">
                            <i class="fa-solid fa-rotate-left"></i> Reset Filters
                        </a>
                    <?php endif; ?>
                </form>

                <!-- Bookings Table -->
                <div class="rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <table class="border-collapse w-full text-left">
                        <thead>
                            <tr class="bg-[#F7F8F9] border-b border-gray-200 text-gray-700 text-sm font-semibold">
                                <th class="p-4 text-center">ID</th>
                                <th class="p-4">Room</th>
                                <th class="p-4">Check-in</th>
                                <th class="p-4">Check-Out</th>
                                <th class="p-4 text-center">Status</th>
                                <th class="p-4">Amount</th>
                                <th class="p-4 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($res && mysqli_num_rows($res) > 0) {
                                while ($row = mysqli_fetch_assoc($res)) {
                                    $statClas = $row['status'] == 'cancelled' 
                                        ? "bg-red-100 text-red-700 border border-red-200" 
                                        : ($row['status'] == 'pending' 
                                            ? "bg-amber-100 text-amber-700 border border-amber-200" 
                                            : ($row['status'] == 'checked out' 
                                                ? "bg-emerald-100 text-emerald-700 border border-emerald-200" 
                                                : "bg-blue-100 text-blue-700"));
                                    $room = $row['room_id'];
                                    $booking_id = $row['booking_id'];
                                    ?>
                                    <tr class="border-b border-gray-100 hover:bg-gray-50/80 transition-colors text-sm text-gray-700">
                                        <td class="p-4 text-center font-medium">#BK-<?= htmlspecialchars($row['booking_id']) ?></td>
                                        <td class="p-4 font-semibold text-gray-900"><?= htmlspecialchars($row['room_label'] ?? 'Room') ?></td>
                                        <td class="p-4"><?= htmlspecialchars($row['checkin_date']) ?></td>
                                        <td class="p-4"><?= htmlspecialchars($row['checkout_date']) ?></td>
                                        <td class="p-4 text-center">
                                            <span class="py-1 px-3.5 rounded-full text-xs font-semibold capitalize inline-block <?= $statClas ?>">
                                                <?= htmlspecialchars($row['status']) ?>
                                            </span>
                                        </td>
                                        <td class="p-4 font-bold text-gray-900">Rs. <?= number_format($row['tprice']) ?></td>
                                        <td class="p-4 text-center">
                                            <div>
                                                <?php if ($row['status'] === 'cancelled'): ?>
                                                    <span class="bg-gray-100 py-1 px-3.5 rounded-full text-xs font-medium text-gray-400">Cancelled</span>
                                                <?php elseif ($row['status'] === 'checked out'): ?>
                                                    <span class="bg-gray-100 py-1 px-3.5 rounded-full text-xs font-medium text-gray-600">Completed</span>
                                                <?php else: ?>
                                                    <a href="cancle.php?roomId=<?= $room ?>&bookingId=<?= $booking_id ?>" 
                                                       onclick="return confirm('Are you sure you want to cancel this booking?')" 
                                                       class="bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 py-1 px-3.5 rounded-full text-xs font-semibold transition-all">
                                                        Cancel Booking
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                echo "<tr><td colspan='7' class='p-12 text-center text-gray-500 font-medium'>No bookings found matching your selected filters.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>

    <footer class="bg-[#1B2232] pt-10">
        <div class="p-6 max-w-7xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-4 md:grid-cols-2 gap-8 justify-between text-white">
                <span>
                    <div class="flex gap-2 text-white font-bold items-center">
                        <h2 class="bg-[#193366] p-2 rounded-full w-10 h-10 flex justify-center items-center text-white">
                            L</h2>
                        <div>
                            <h1>LOTUS</h1>
                        </div>
                    </div>
                    <p class="text-white/60 mt-4">Your quiet home in Kathmandu. Over 10 years of welcoming travelers,
                        trekkers, and adventurers with warm Nepali hospitality.</p>
                    <div class="flex gap-2 mt-4">
                        <span
                            class="cursor-pointer hover:bg-[#1A3161] transition-all duration-200 bg-[#313846] flex justify-center items-center rounded-full h-10 w-10"><i
                                class="text-lg fa-brands fa-facebook-f"></i></span>
                        <span
                            class="cursor-pointer hover:bg-[#1A3161] transition-all duration-200 bg-[#313846] flex justify-center items-center rounded-full h-10 w-10"><i
                                class="text-lg fa-brands fa-instagram"></i></span>
                        <span
                            class="cursor-pointer hover:bg-[#1A3161] transition-all duration-200 bg-[#313846] flex justify-center items-center rounded-full h-10 w-10"><i
                                class="text-lg fa-brands fa-viber"></i></span>
                    </div>
                </span>
                <span>
                    <h1 class="text-lg font-semibold">Quick Links</h1>
                    <div class="flex gap-2 mt-4">
                        <span class="text-white/60 flex flex-col gap-2">
                            <p class="">Home</p>
                            <p class="">Rooms</p>
                            <p class="">Garden & Terrace</p>
                            <p class="">Resturant</p>
                            <p class="">Contact</p>
                        </span>
                    </div>
                </span>
                <span>
                    <h1 class="text-lg font-semibold">Contact Us</h1>
                    <div
                        class="mt-4 flex flex-col text-white/60 gap-4 [&_span]:flex [&_span]:items-center [&_span]:gap-2">
                        <span><i class="fa-solid fa-phone"></i>+977 9765406567</span>
                        <span><i class="fa-regular fa-envelope"></i>ahenstha@gmail.com</span>
                        <span><i class="fa-solid fa-location-dot"></i>Kathmandu, Nepal</span>
                    </div>
                </span>
                <span>
                    <h1 class="text-lg font-semibold">Stay Updated</h1>
                    <p class="text-white/60 mt-4">Subscribe for exclusive offers and travel tips.</p>
                    <input type="text" placeholder="Your Email"
                        class="w-full focus:outline-0 p-3 bg-[#313846] border-gray-500 placeholder:text-base mt-2 border rounded-lg" />
                    <button
                        class="w-full py-2 px-4 font-normal bg-[#193366] hover:bg-[#1A3161] transition-all duration-200 text-white text-lg mt-2 cursor-pointer rounded-lg">Subscribe</button>
                </span>
            </div>
            <div class="h-px mt-10 bg-gray-400/30"></div>
            <div class="justify-between flex flex-col md:flex-row text-center text-white/60 py-6">
                <h1>© 2026 LOTUS. All rights reserved.</h1>
                <h1>Created by <a href="https://www.instagram.com/_shrestha__prasun_/"
                        class="hover:text-blue-500 cursor-pointer transition-colors duration-200">Prasun Shrestha</a></h1>
            </div>
        </div>
    </footer>
    <script type="module" src="../Js/script.js"></script>
</body>

</html>