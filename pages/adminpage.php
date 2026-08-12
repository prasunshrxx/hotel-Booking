<?php
include '../conn.php';
session_start();

// 1. Check if user is logged in AND is an admin
if (!isset($_SESSION['isLogin']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// 2. Handle Status Updates (Processed before fetching data so updates reflect immediately)
if (isset($_POST['UpdateStatus']) && isset($_POST['status_change']) && isset($_GET['booking_id'])) {
    $status_change = $_POST['status_change'];
    $booking_id = intval($_GET['booking_id']);

    $stmtStatus = mysqli_prepare($conn, "UPDATE booking SET status = ? WHERE booking_id = ?");
    mysqli_stmt_bind_param($stmtStatus, "si", $status_change, $booking_id);
    
    if (mysqli_stmt_execute($stmtStatus)) {
        header("Location: adminpage.php");
        exit();
    } else {
        echo "<script>alert('Failed to change status');</script>";
    }
}

// 3. Fetch All Bookings with JOIN (Optimized single query)
$sql = "SELECT booking.*, users.username, rooms.label 
        FROM booking 
        LEFT JOIN users ON booking.user_id = users.id 
        LEFT JOIN rooms ON booking.room_id = rooms.room_id 
        ORDER BY booking.booking_id DESC";
$res = mysqli_query($conn, $sql);

if (!$res) {
    header("Location: errorpage.php");
    exit();
}

// 4. Handle View Modal Details
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
    <title>Admin - LOTUS - Hotel Management System</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="../css/style.css" />
</head>

<body class="relative">
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
                        <a href="adminpage.php" class="hover:text-[#E5A819] transition-colors duration-150">Admin Panel</a>
                    </div>
                </div>
                <div class="lg:flex gap-8 items-center hidden">
                    <?php
                    if (!isset($_SESSION['isLogin'])) {
                        echo '<a href="login.php" class="text-base font-medium text-white transition-all duration-300 bg-primary py-2 px-6 rounded-full">Login</a>';
                    } else {
                        echo '<div class="flex gap-2 items-center bg-gray-900/10 py-2 px-4 rounded-full text-gray-900">
                            <div class="p-2 h-9 rounded-full text-white bg-primary text-2xl flex justify-center items-center">' . htmlspecialchars($_SESSION['username'][0]) . '</div>
                            <h1 class="text-base font-medium text-black transition-all duration-300" id="userName">' . htmlspecialchars($_SESSION['username']) . '</h1>
                        </div><a href="logout.php" class="text-base font-medium text-white transition-all duration-300 bg-primary py-2 px-6 rounded-full">Logout</a>';
                    }
                    ?>
                </div>
                <div class="lg:hidden flex"><i class="fa-solid fa-bars text-2xl"></i></div>
            </div>
        </div>
    </nav>
    <main>
        <section class="relative pt-32 pb-20 flex items-center justify-center bg-[#F7F4ED]">
            <div class="text-center">
                <h1 class="text-sm tracking-widest text-black/70 font-medium"><i class="text-6xl fa-solid fa-cart-plus"></i></h1>
                <p class="text-black/80 text-4xl md:text-6xl font-semibold text-playfair mt-4">Orders</p>
            </div>
        </section>
        <section class="relative pt-8 pb-20 flex items-center justify-center bg-white">
            <div class="w-7xl max-w-7xl mx-auto rounded-lg">
                <table class="border-collapse w-full">
                    <tr class="bg-[#F7F8F9] [&>th]:p-3 border border-gray-300 rounded-lg">
                        <th>ID</th>
                        <th>Customer Name</th>
                        <th>Room</th>
                        <th>Check-in</th>
                        <th>Check-Out</th>
                        <th>Status</th>
                        <th>Amount</th>
                        <th>Action</th>
                    </tr>
                    <?php
                    if (mysqli_num_rows($res) > 0) {
                        while ($row = mysqli_fetch_assoc($res)) {
                            $statClas = $row['status'] == 'cancelled' ? "bg-red-300 text-red-800" : ($row['status'] == 'pending' ? "bg-yellow-300 text-yellow-800" : ($row['status'] == 'checked out' ? "bg-green-300 text-green-800" : ""));

                            echo "
                            <tr class='text-center border border-gray-300 hover:bg-[#FAFBFC] transition-colors duration-200'>
                                <td class='p-3'>" . htmlspecialchars($row['booking_id']) . "</td>
                                <td class='p-3'>" . htmlspecialchars($row['username'] ?? 'N/A') . "</td>
                                <td class='p-3'>" . htmlspecialchars($row['label'] ?? 'N/A') . "</td>
                                <td class='p-3'>" . htmlspecialchars($row['checkin_date']) . "</td>
                                <td class='p-3'>" . htmlspecialchars($row['checkout_date']) . "</td>
                                <td class='p-3'> <button class='py-1 px-4 w-30 rounded-full " . $statClas . "'>" . htmlspecialchars($row['status']) . " </button> </td>
                                <td class='p-3'> Rs. " . htmlspecialchars($row['tprice']) . "</td>
                                <td class='p-3'><div>
                                <a href='?booking_id=" . $row['booking_id'] . "'>
                                <button class='bg-[#c3c1c1] py-1 px-4 rounded-full cursor-pointer hover:bg-gray-200 text-gray-500'><i class='fa-solid fa-eye'></i></button>
                                </a>
                                </div></td>
                            </tr>
                            ";
                        }
                    } else {
                        echo "<tr><td colspan='8' class='p-6 text-center text-gray-500'>No bookings found.</td></tr>";
                    }
                    ?>
                </table>
            </div>
        </section>
    </main>

    <!-- Modal Dialog -->
    <div class="inset-0 fixed z-50 <?php echo isset($_GET['booking_id']) ? '' : 'hidden'; ?>" id="dialog_admin">
        <div class="absolute inset-0 h-screen w-full bg-black/40 flex justify-center items-center">
            <form class="absolute max-w-lg mx-auto w-lg rounded-lg bg-white p-6" method="POST" action="adminpage.php?booking_id=<?php echo htmlspecialchars($_GET['booking_id'] ?? ''); ?>">
                <div class="flex w-full justify-between mb-2">
                    <h1 class="font-medium text-lg">Booking Id. <?php echo htmlspecialchars($dataDialoug['booking_id'] ?? '') ?></h1>
                    <span class="hover:bg-gray-200 p-2 cursor-pointer text-black/60 flex justify-center items-center rounded-full"
                        onclick="closeMenu()">
                        <i class="fa-solid fa-x"></i>
                    </span>
                </div>
                <div class="flex w-full">
                    <div class="w-[50%]">
                        <div class="mb-2">
                            <h1 class="text-sm text-black/60 font-medium">Guest</h1>
                            <p class="text-sm text-black/80 font-medium"><?php echo htmlspecialchars($dataDialoug['username'] ?? '') ?></p>
                        </div>
                        <div class="mb-2">
                            <h1 class="text-sm text-black/60 font-medium">Room</h1>
                            <p class="text-sm text-black/80 font-medium"><?php echo htmlspecialchars($dataDialoug['label'] ?? '') ?></p>
                        </div>
                        <div class="mb-2">
                            <h1 class="text-sm text-black/60 font-medium">Check-in</h1>
                            <p class="text-sm text-black/80 font-medium"><?php echo htmlspecialchars($dataDialoug['checkin_date'] ?? '') ?></p>
                        </div>
                        <div class="mb-2">
                            <h1 class="text-sm text-black/60 font-medium">Amount</h1>
                            <p class="text-sm text-black/80 font-medium">Rs. <?php echo htmlspecialchars($dataDialoug['tprice'] ?? '') ?></p>
                        </div>
                    </div>
                    <div class="w-[50%]">
                        <div class="mb-2">
                            <h1 class="text-sm text-black/60 font-medium">Email</h1>
                            <p class="text-sm text-black/80 font-medium"><?php echo htmlspecialchars($dataDialoug['email'] ?? '') ?></p>
                        </div>
                        <div class="mb-2">
                            <h1 class="text-sm text-black/60 font-medium">Guests</h1>
                            <p class="text-sm text-black/80 font-medium"><?php echo htmlspecialchars($dataDialoug['no_of_guests'] ?? '') ?></p>
                        </div>
                        <div class="mb-2">
                            <h1 class="text-sm text-black/60 font-medium">Check-out</h1>
                            <p class="text-sm text-black/80 font-medium"><?php echo htmlspecialchars($dataDialoug['checkout_date'] ?? '') ?></p>
                        </div>
                        <div class="mb-2">
                            <h1 class="text-sm text-black/60 font-medium">Status</h1>
                            <?php
                            if (isset($dataDialoug)) {
                                echo ($dataDialoug['status'] == 'cancelled' || $dataDialoug['status'] == 'checked out') 
                                    ? "<h1 class='text-sm text-black/80 font-medium'>" . htmlspecialchars($dataDialoug['status']) . "</h1>" 
                                    : "<select name='status_change' class='p-1 border border-gray-300 rounded outline-0'> 
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
                        : '<button type="submit" class="mt-4 w-full p-2 flex rounded-full text-white justify-center bg-[#193366] hover:bg-[#304775] transition-all duration-200 cursor-pointer" name="UpdateStatus">Update</button>';
                }
                ?>
            </form>
        </div>
    </div>

    <footer class="bg-[#1B2232] pt-10">
        <div class="p-6 max-w-7xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-4 md:grid-cols-2 gap-8 justify-between text-white">
                <span>
                    <div class="flex gap-2 text-white font-bold items-center">
                        <h2 class="bg-[#193366] p-2 rounded-full w-10 h-10 flex justify-center items-center text-white">M</h2>
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
    <script type="module" src="../js/script.js"></script>
    <script>
        window.closeMenu = () => {
            window.location.href = 'adminpage.php';
        }
    </script>
</body>

</html>