<?php
include '../conn.php';
session_start();
if (!isset($_SESSION['isLogin'])) {
    header("location:http://localhost/hotelbooking/pages/login.php ");
}


?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Rooms</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="../Css/style.css" />
</head>

<body>
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
                        <a href="room.php" class="text-[#E5A819] transition-colors duration-150">Rooms</a>
                        <a href="garden.php" class="hover:text-[#E5A819] transition-colors duration-150">Garden &
                            Terrace</a>
                        <a href="cartpage.php" class="hover:text-[#E5A819] transition-colors duration-150">My Cart</a>
                    </div>
                </div>
                <div class="lg:flex gap-8 items-center hidden">
                    <!-- <h1 class="text-sm font-normal cursor-pointer">+977 9765406567</h1> -->
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
        <section class="relative pt-32 pb-20 flex items-center justify-center bg-[#F7F4ED]">
            <div class="text-center">
                <h1 class="text-sm tracking-widest text-black/60 font-medium">ACCOMODATION</h1>
                <p class="text-black/80 text-4xl md:text-6xl font-semibold text-playfair mt-4">Our Rooms</p>
                <p class="text-black/50 text-lg tracking-wide font-medium mt-4">From cozy singles to spacious suites,
                    find your perfect retreat in Kathmandu.</p>
            </div>
        </section>
        <section class="relative flex items-center justify-center bg-[#F9FAFB] py-12">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 p-6 max-w-7xl relative" id="roomContainer">


            </div>

        </section>
        <section class="pb-20 px-6  max-w-7xl mx-auto">
            <div class="max-w-7xl mx-auto rounded-lg bg-[#E3E6EB] p-10 text-center flex flex-col gap-4">
                <h1 class="text-black/90 text-playfair font-bold text-2xl md:text-4xl">Planning a Longer Stay?</h1>
                <p class="text-wrap text-black/60 max-w-2xl text-lg mx-auto">
                    We offer special rates for weekly and monthly stays. Many of our guests stay for weeks or even
                    months! Contact us for customized long-stay packages.
                </p>
                <span>
                    <button
                        class="bg-[#193366] transition-all duration-200 cursor-pointer text-white rounded-full py-2 px-4 hover:bg-[#304775] font-medium">Inquire
                        About Long Stay</button>
                </span>
            </div>
        </section>
    </main>

    <footer class="bg-[#1B2232] pt-10">
        <div class="p-6 max-w-7xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-4 md:grid-cols-2 gap-8 justify-between text-white">
                <span>
                    <div class="flex gap-2 text-white font-bold items-center">
                        <h2 class="bg-[#193366] p-2 rounded-full w-10 h-10 flex justify-center items-center text-white">
                            M</h2>
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