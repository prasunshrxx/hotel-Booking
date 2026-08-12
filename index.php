<?php
include 'conn.php';
session_start();
/* if (!isset($_SESSION['isLogin'])) {
    header("location:http://localhost/hotelbooking/pages/login.php");
} else {
    if ($_SESSION['role'] == "admin") {
        header("location:http://localhost/hotelbooking/pages/adminpage.php");
    }
} */
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>LOTUS - Hotel Management System</title>
    
    <!-- Tailwind v4 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" />
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css" />
</head>

<body class="">
    <nav class="container mx-auto">
        <div class="fixed top-0 left-0 right-0 z-40 lg:py-4 text-white transition-all duration-300" id="nav-cont">
            <div class="flex justify-between w-full items-center max-w-7xl ml-auto mr-auto px-6 py-2 lg:py-0">
                <a class="flex gap-2 items-center cursor-pointer" href="index.php">
                    <div
                        class="w-10 h-10 text-white font-semibold bg-primary text-2xl rounded-full flex justify-center items-center">
                        L</div>
                    <div class="font-bold text-xl flex flex-col">LOTUS</div>
                </a>
                <div class="lg:block hidden">
                    <div class="flex gap-8 cursor-pointer ml-2 text-sm font-medium">
                        <a href="index.php" class="text-[#E5A819] transition-colors duration-150">Home</a>
                        <a href="pages/room.php" class="hover:text-[#E5A819] transition-colors duration-150">Rooms</a>
                        <a href="pages/room.php" class="hover:text-[#E5A819] transition-colors duration-150">Garden & Terrace</a>
                        <a href="pages/cartpage.php" class="hover:text-[#E5A819] transition-colors duration-150">My Cart</a>
                    </div>
                </div>
                <div class="lg:flex gap-8 items-center hidden">
                    <?php
                    if (!isset($_SESSION['isLogin']) || !$_SESSION['isLogin']) {
                        echo ' <a href="pages/login.php" class="text-base font-medium text-white transition-all duration-300 bg-primary py-2 px-6 rounded-full">Login</a>';
                    } else {
                        echo '<div class="flex gap-2 items-center bg-gray-900/10 py-2 px-4 rounded-full text-gray-900">
                            <div class="p-2 h-9 rounded-full text-white bg-primary text-2xl flex justify-center items-center">' . $_SESSION['username'][0] . '</div>
                            <h1 class="text-base font-medium text-white transition-all duration-300" id="userName">' . $_SESSION['username'] . '</h1>
                        </div><a href="pages/logout.php" class="text-base font-medium text-white transition-all duration-300 bg-primary py-2 px-6 rounded-full">Logout</a>';
                    }
                    ?>
                </div>
                <div class="lg:hidden flex"><i class="fa-solid fa-bars text-2xl"></i></div>
            </div>
        </div>
    </nav>
    <main class="">
        <section class="relative h-screen flex items-center justify-center">
            <div class="absolute inset-0 bg-cover bg-center bg-fixed"
                style="background-image: url(https://images.unsplash.com/photo-1566073771259-6a8506099945?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80)">
                <div class="absolute inset-0 w-full bg-black opacity-50"></div>
            </div>
            <div class="w-full relative ml-auto mr-auto max-w-7xl p-6 mt-6">
                <div class="flex flex-col justify-center items-center gap-4">
                    <span
                        class="text-white text-sm flex gap-2 lg:gap-4 px-5 py-2 backdrop-blur-lg bg-white/10 rounded-full">
                        <div>
                            <i class="fa-regular fa-star text-white"></i>
                            <i class="fa-regular fa-star text-white"></i>
                            <i class="fa-regular fa-star text-white"></i>
                            <i class="fa-regular fa-star text-white"></i>
                            <i class="fa-regular fa-star text-white"></i>
                        </div>
                        Rated Excellent on TripAdvisor
                    </span>
                    <span class="text-white text-4xl md:text-5xl lg:text-7xl text-playfair font-bold text-center">
                        <h1>Your Quiet Home</h1>
                        <h1>In Kathmandu</h1>
                    </span>
                    <span class="text-gray-100 font-base text-xl text-center"> Clean. Cozy. Friendly. Perfect for
                        Travelers, Trekkers & Long Stays. </span>
                    <span class="flex flex-col md:flex-row gap-6">
                        <a class="py-2.5 px-8 bg-primary cursor-pointer text-xl font-medium text-white rounded-full">Book
                            Your Stay</a>
                        <a href="pages/room.php"
                            class="py-2.5 px-8 border border-gray-200/70 cursor-pointer hover:bg-gray-50/30 text-xl font-medium text-white rounded-full">View
                            Rooms</a>
                    </span>
                    <div class="flex flex-wrap justify-center gap-5 text-base font-medium text-white/80 mt-10">
                        <span>10 min to Thamel</span><span>Garden Terrace</span><span>Free WiFi</span><span>Airport
                            Pickup</span>
                    </div>
                </div>
            </div>
            <div class="absolute bottom-5 mr-auto ml-auto cursor-pointer animate-bounce h-12 w-12 flex justify-center items-center"
                id="godown">
                <i class="fa-solid fa-chevron-down text-2xl text-white" id="section2"></i>
            </div>
        </section>
        <section class="bg-[#F7F4ED] relative py-10">
            <div class="text-center py-4 flex flex-col gap-4">
                <h2 class="text-black/70 text-lg font-medium">WHY CHOOSE US</h2>
                <h1 class="text-3xl md:text-5xl font-bold text-playfair">Why Guests Love Us</h1>
                <p class="text-black/60 text-lg font-medium">
                    Discover what makes Hotel BnB Mhepi the perfect home for travelers seeking authentic <br />
                    Nepali hospitality.
                </p>
            </div>
            <div class="mt-4 container max-w-7xl mx-auto grid lg:grid-cols-3 md:grid-cols-2 grid-cols-1 gap-4 p-6"
                id="whyGuestLoveUs"></div>
        </section>
        <section class="bg-[#F9FAFB] relative py-10">
            <div class="max-w-7xl grid md:grid-cols-2 grid-cols-1 gap-16 mx-auto mt-4 p-6">
                <div class="grid grid-cols-2 gap-4 relative md:my-20">
                    <div class="space-y-4">
                        <img src="https://images.unsplash.com/photo-1590490360182-c33d57733427?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
                            alt="" class="w-full h-48 object-cover rounded-lg" />
                        <img src="https://images.unsplash.com/photo-1551882547-ff40c63fe5fa?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
                            alt="" class="w-full h-64 object-cover rounded-lg" />
                    </div>
                    <div class="space-y-4 py-8">
                        <img src="https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
                            alt="" class="w-full h-64 object-cover rounded-lg" />
                        <img src="https://images.unsplash.com/photo-1596394516093-501ba68a0ba6?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
                            alt="" class="w-full h-48 object-cover rounded-lg" />
                    </div>
                    <div class="absolute bg-[#193366] bottom-1 -right-10 text-white rounded-lg p-4 text-center">
                        <h1 class="text-2xl">10+</h1>
                        <p class="text-base">
                            years of <br />
                            Excellence
                        </p>
                    </div>
                </div>
                <div>
                    <h2 class="text-black/70 text-xs font-medium">ABOUT US</h2>
                    <h1 class="text-3xl md:text-4xl font-semibold mb-4 text-playfair">A Home Away From Home in the Heart
                        of Nepal</h1>
                    <div class="text-black/60 font-medium">
                        <p>
                            Nestled in the peaceful neighborhood of Chalnakhel, Hotel BnB Mhepi offers an authentic
                            Nepali experience combined with modern comfort. For over a decade, we've been
                            welcoming travelers, trekkers, and adventurers from every corner of the globe. <br /><br />

                            Whether you're preparing for an epic Himalayan trek, visiting for business, or simply
                            seeking a tranquil escape from the bustling city, our family will make you feel
                            right at home.
                        </p>
                        <div class="mt-10 flex gap-4 justify-center flex-col" id="featuresHome"></div>
                        <a href="pages/room.php"
                            class="inline-block py-3 px-6 bg-[#193366] text-white/90 rounded-full hover:bg-[#314b7d] cursor-pointer mt-8 transition-colors duration-200">Explore
                            Our Rooms</a>
                    </div>
                </div>
            </div>
        </section>
        <section class="bg-[#1B2232]">
            <div class="px-6 py-12 max-w-7xl text-center mx-auto text-white">
                <h2 class="text-[#1A3366] text-base font-medium">ABOUT US</h2>
                <h1 class="text-3xl md:text-4xl font-semibold p-10 text-playfair">What Our Guests Say</h1>
                <div class="text-[#1B2742]">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="lucide lucide-quote w-16 h-16 text-primary/30 mx-auto mb-8">
                        <path
                            d="M16 3a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2 1 1 0 0 1 1 1v1a2 2 0 0 1-2 2 1 1 0 0 0-1 1v2a1 1 0 0 0 1 1 6 6 0 0 0 6-6V5a2 2 0 0 0-2-2z">
                        </path>
                        <path
                            d="M5 3a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2 1 1 0 0 1 1 1v1a2 2 0 0 1-2 2 1 1 0 0 0-1 1v2a1 1 0 0 0 1 1 6 6 0 0 0 6-6V5a2 2 0 0 0-2-2z">
                        </path>
                    </svg>
                </div>
                <span id="guestMessage">

                </span>
            </div>
        </section>
    </main>

    <div class="relative h-full w-full">
        <div class="inset-0 absolute bg-center bg-cover"
            style="background-image: url(&quot;https://images.unsplash.com/photo-1571896349842-33c89424de2d?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80&quot;)">
        </div>
        <div class="inset-0 absolute h-full w-full bg-[#1b2232]/60"></div>
        <div class="p-15 relative text-white text-center flex flex-col gap-8">
            <h1 class="text-2xl md:text-4xl font-bold text-playfair">Ready to Experience True</h1>
            <h1 class="text-2xl md:text-4xl font-semibold text-playfair ">Nepal Hospitality?</h1>
            <p class="text-lg max-w-3xl mx-auto text-white/90">Book your stay today and discover why guests keep coming
                back. We can't wait to welcome you to our home.</p>
            <div class="flex gap-3 flex-wrap justify-center">
                <button class="py-2.5 px-8 bg-primary cursor-pointer text-xl font-medium text-white rounded-full">Book
                    Now</button>
                <button
                    class="py-2.5 px-8 border border-gray-200/70 cursor-pointer hover:bg-gray-50/30 text-xl font-medium text-white rounded-full">WhatsApp</button>
            </div>
        </div>
    </div>

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
                            <a href="index.php" class="hover:text-white transition-colors">Home</a>
                            <a href="pages/room.php" class="hover:text-white transition-colors">Rooms</a>
                            <a href="pages/room.php" class="hover:text-white transition-colors">Garden & Terrace</a>
                            <a href="#" class="hover:text-white transition-colors">Restaurant</a>
                            <a href="#" class="hover:text-white transition-colors">Contact</a>
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
    <script type="module" src="js/script.js"></script>
</body>

</html>