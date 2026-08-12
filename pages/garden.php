<?php
include '../conn.php';
session_start();
if (!isset($_SESSION['isLogin'])) {
    header("location:http://localhost/hotelbooking/pages/login.php ");
}


?><!doctype html>
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
                        <a href="garden.php" class="text-[#E5A819] transition-colors duration-150">Garden & Terrace</a>
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
                <h1 class="text-sm tracking-widest text-black/60 font-medium">OUR SPACE</h1>
                <p class="text-black/80 text-4xl md:text-6xl font-semibold text-playfair mt-4">Garden & Terrace</p>
                <p class="text-black/50 text-lg tracking-wide font-medium mt-4">A peaceful oasis in the heart of
                    Kathmandu where guests love to unwind and connect.</p>
            </div>
        </section>
        <section class="relative flex items-center justify-center bg-[#F9FAFB] py-24">
            <!-- <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 p-6 max-w-7xl relative"
                id="gardenCOntainer">

            </div> -->

            <div class="mx-auto container space-y-10">
                <div class="max-w-2xl mx-auto flex flex-col justify-center items-center space-y-6">

                    <h1 class="text-4xl text-playfair text-black font-bold">Your Personal Retreat</h1>
                    <p class="text-lg text-center font-normal text-black/60">Our garden and terrace have become
                        legendary among guests. It's where stories are shared,
                        friendships are formed, and memories are made.</p>
                </div>
                <!-- <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 items-center justify-center"> -->
                <div class="flex flex-wrap gap-5 items-center justify-center" id="garden-header-data">
                    <span
                        class="border bg-white border-gray-200 py-4 gap-4 px-6 rounded-lg flex justify-center items-center">
                        <i class="fa-solid fa-tree"></i>
                        <span>
                            <h2 class="text-lg text-black/80 font-medium">Lust Greenery</h2>
                            <p class="text-xs text-black/60">Surrounded by carefully maintained plants and flowers</p>
                        </span>
                    </span>
                </div>
                <div class="container px-[6%]">
                    <span class="grid grid-cols-2 md:grid-cols-3 gap-5">
                        <div class="overflow-hidden rounded-lg md:row-span-2 md:col-span-2  row-span-1 col-span-2">

                            <img src="https://images.unsplash.com/photo-1585320806297-9794b3e4eeae?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
                                class="w-full rounded-lg object-cover  transition-all duration-200 hover:scale-110 cursor-pointer  h-full min-h-100"
                                alt="">
                        </div>

                        <div class="overflow-hidden rounded-lg">

                            <img src="https://images.unsplash.com/photo-1600585154340-be6161a56a0c?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
                                class="w-full rounded-lg object-cover transition-all duration-200 hover:scale-110 cursor-pointer h-48 md:h-56"
                                alt="">
                        </div>
                        <div class="overflow-hidden rounded-lg">

                            <img src="https://images.unsplash.com/photo-1558618666-fcd25c85cd64?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
                                class="w-full rounded-lg object-cover transition-all duration-200 hover:scale-110 cursor-pointer h-48 md:h-56"
                                alt="">
                        </div>

                        <div class="overflow-hidden rounded-lg">

                            <img src="https://images.unsplash.com/photo-1509315811345-672d83ef2fbc?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
                                class="w-full rounded-lg object-cover transition-all duration-200 hover:scale-110 cursor-pointer h-48 md:h-56"
                                alt="">
                        </div>
                        <div class="overflow-hidden rounded-lg">

                            <img src="https://images.unsplash.com/photo-1551882547-ff40c63fe5fa?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
                                class="w-full rounded-lg object-cover transition-all duration-200 hover:scale-110 cursor-pointer h-48 md:h-56"
                                alt="">
                        </div>
                        <div class="overflow-hidden rounded-lg">

                            <img src="https://images.unsplash.com/photo-1564501049412-61c2a3083791?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
                                class="w-full rounded-lg object-cover transition-all duration-200 hover:scale-110 cursor-pointer h-48 md:h-56"
                                alt="">
                        </div>
                </div>
            </div>
        </section>
        <section class="pb-20">
            <div class="max-w-7xl mx-auto rounded-lg bg-[#E3E6EB] p-10 text-center flex flex-col gap-4">
                <p class="text-wrap italic text-black/70 text-xl text-playfair ">
                    "The garden was absolutely magical. I spent hours there with my morning tea, watching the world go
                    by. It felt like my own private sanctuary."
                </p>
                <p class="text-black/60 text-base ">
                    <i class="fa-solid fa-minus "></i> Prasun Shrestha, Guest From Nepal
                </p>
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