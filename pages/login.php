
<?php
include '../conn.php';
session_start();

if (isset($_SESSION['isLogin'])) {
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        header("Location: adminpage.php");
    } else {
        header("Location: ../index.php");
    }
    exit();
}

if (isset($_POST['signup'])) {
    $semail = trim($_POST['remail']);
    $sname = trim($_POST['rname']);
    $spassword = $_POST['rpass'];
    $scpassword = $_POST['rcpass'];

    $allowed_domains = ['gmail.com', 'outlook.com'];
    $email_domain = strtolower(substr(strrchr($semail, '@'), 1));

    if (!filter_var($semail, FILTER_VALIDATE_EMAIL)) {
        $signup_error = "Please enter a valid email address";
    } elseif (!in_array($email_domain, $allowed_domains)) {
        $signup_error = "Only Gmail (gmail.com) and Outlook (outlook.com) addresses are accepted";
    } elseif (!preg_match("/^[a-zA-Z\s]+$/", $sname)) {
        $signup_error = "Username should only consist of alphabets";
    } elseif (strlen($spassword) < 6) {
        $signup_error = "Password must be at least 6 characters long";
    } elseif ($spassword !== $scpassword) {
        $signup_error = "Password doesn't match";
    } else {
        $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $semail);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            $signup_error = "Email Already exists";
        } else {
            $hashedpass = password_hash($spassword, PASSWORD_DEFAULT);
            
            $insert_stmt = mysqli_prepare($conn, "INSERT INTO users (username, email, password, role, status) VALUES (?, ?, ?, 'user', 'ACTIVE')");
            mysqli_stmt_bind_param($insert_stmt, "sss", $sname, $semail, $hashedpass);

            if (mysqli_stmt_execute($insert_stmt)) {
                header("Location: login.php");
                exit();
            } else {
                $signup_error = "Something went wrong: " . mysqli_error($conn);
            }
            mysqli_stmt_close($insert_stmt);
        }
        mysqli_stmt_close($stmt);
    }
}

if (isset($_POST['signin'])) {
    $email = trim($_POST['lemail']);
    $password = $_POST['lpass'];

    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($res)) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['isLogin'] = true;
            $_SESSION['email'] = $email;
            $_SESSION['username'] = $row['username'];
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['role'] = $row['role'];

            if ($row['role'] === 'admin') {
                header("Location: adminpage.php");
            } else {
                header("Location: ../index.php");
            }
            exit();
        } else {
            $login_error = "Wrong password";
        }
    } else {
        $login_error = "Login using correct Email";
    }
    mysqli_stmt_close($stmt);
}
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login / Register</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="../css/style.css" />
</head>

<body class="text-poppins min-h-screen relative flex items-center justify-center" style="background: #0a0f1e;">
    <!-- Full-screen luxury hotel background -->
    <div class="fixed inset-0 z-0" style="background-image: url('https://dwarikas.com/media/site/1e1069b432-1780289916/dwarikas_elisehassey_7512copy-1920x-q85.webp'); background-size: cover; background-position: center; filter: blur(3px) brightness(0.92); transform: scale(1.05);"></div>
    <!-- Gradient overlay for depth -->
    <div class="fixed inset-0 z-0" style="background: linear-gradient(135deg, rgba(0,0,0,0.25) 0%, rgba(0,0,0,0.10) 50%, rgba(0,0,0,0.25) 100%);"></div>
    <?php include '../includes/logout_toast.php'; ?>
    <main class="relative z-10 max-w-4xl w-full mx-auto my-10 px-4">
        <div class="relative flex p-3 w-full h-150 rounded-2xl overflow-hidden" style="background: rgba(255,255,255,0.05); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.15); box-shadow: 0 32px 80px rgba(0,0,0,0.35);">
            <div class="absolute inset-0 rounded-xl overflow-hidden z-3 transition-all duration-500 w-[50%] h-100%"
                id="slider-nav">
                <div class="w-full h-full relative">
                    <img src="https://www.firstclassmagazine.co/wp-content/uploads/2024/07/room-details-2-pic-supplied-800x1067.jpeg"
                        class="object-cover h-full w-full" alt="Villa Image">
                    <div class="inset-0 absolute w-full h-full z-4 bg-black/10"></div>
                </div>
            </div>
            
            <div class="w-[50%] h-100% text-white" id="login">
                <div class="text-right pr-8 py-4 text-xs text-white/70">Already a member? <span class="text-amber-300 cursor-pointer font-semibold"
                        id="login-nav">Login Now</span></div>
                <div class="flex flex-col p-8 px-14">
                    <h1 class="text-2xl font-bold text-white text-center">Welcome to LOTUS</h1>
                    <p class="text-white/60 text-sm mt-2 text-center">Create your account to start booking amazing stays.</p>
                    <form action="" method="POST">
                        <input type="email"
                            class="mt-4 w-full p-2.5 rounded-lg focus:outline-none bg-white/10 border border-white/20 text-white placeholder:text-white/50 placeholder:text-sm focus:border-amber-300 focus:bg-white/15 transition-all"
                            placeholder="Enter email" name="remail" pattern="[a-zA-Z0-9._%+\-]+@(gmail\.com|outlook\.com)" title="Only Gmail (gmail.com) or Outlook (outlook.com) addresses are accepted" value="<?php echo isset($_POST['remail']) ? htmlspecialchars($_POST['remail']) : ''; ?>" required />
                        <input type="text"
                            class="mt-4 w-full p-2.5 rounded-lg focus:outline-none bg-white/10 border border-white/20 text-white placeholder:text-white/50 placeholder:text-sm focus:border-amber-300 focus:bg-white/15 transition-all"
                            placeholder="Enter username" name="rname" pattern="[A-Za-z\s]+" title="Username should only consist of alphabets" value="<?php echo isset($_POST['rname']) ? htmlspecialchars($_POST['rname']) : ''; ?>" required />
                        <input type="password"
                            class="mt-4 w-full p-2.5 rounded-lg focus:outline-none bg-white/10 border border-white/20 text-white placeholder:text-white/50 placeholder:text-sm focus:border-amber-300 focus:bg-white/15 transition-all"
                            placeholder="Password" name="rpass" minlength="6" required />
                        <input type="password"
                            class="mt-4 w-full p-2.5 rounded-lg focus:outline-none bg-white/10 border border-white/20 text-white placeholder:text-white/50 placeholder:text-sm focus:border-amber-300 focus:bg-white/15 transition-all"
                            placeholder="Confirm Password" name="rcpass" minlength="6" required />
                        <button type="submit"
                            class="text-sm mt-5 w-full bg-amber-400 hover:bg-amber-300 p-2.5 rounded-lg text-gray-900 font-semibold transition-all duration-200 cursor-pointer shadow-lg"
                            name="signup">Create Account</button>
                    </form>
                    <?php if (isset($signup_error)) {
                        echo "<span class='text-sm mt-2 text-red-500 text-center'>$signup_error</span>";
                    } ?>
                </div>
            </div>

            <div class="w-[50%] text-white" id="signin">
                <div class="text-right pr-8 py-4 text-xs text-white/70">Not a member? <span class="text-amber-300 cursor-pointer font-semibold"
                        id="register-nav">Register Now</span></div>
                <div class="flex flex-col justify-center h-120 px-14">
                    <h1 class="text-2xl font-bold text-white text-center">Hello Again</h1>
                    <p class="text-white/60 text-sm mt-2 text-center">Welcome back, you've been missed!</p>
                    <form method="POST" class="flex flex-col justify-center">
                        <input type="email"
                            class="mt-4 w-full p-2.5 rounded-lg focus:outline-none bg-white/10 border border-white/20 text-white placeholder:text-white/50 placeholder:text-sm focus:border-amber-300 focus:bg-white/15 transition-all"
                            placeholder="Enter email" name="lemail" required />
                        <input type="password"
                            class="mt-4 w-full p-2.5 rounded-lg focus:outline-none bg-white/10 border border-white/20 text-white placeholder:text-white/50 placeholder:text-sm focus:border-amber-300 focus:bg-white/15 transition-all"
                            placeholder="Password" name="lpass" required />
                        <p class="text-xs mt-4 text-right text-amber-300/80 cursor-pointer hover:text-amber-300 transition-colors">Reset Password</p>
                        <button type="submit"
                            class="text-sm mt-5 bg-amber-400 hover:bg-amber-300 p-2.5 rounded-lg text-gray-900 font-semibold transition-all duration-200 cursor-pointer shadow-lg"
                            name="signin">Sign In</button>
                    </form>
                    <?php if (isset($login_error)) {
                        echo "<span class='text-sm mt-2 text-red-400 text-center block'>$login_error</span>";
                    } ?>
                </div>
            </div>
        </div>
    </main>

    <script>
        window.addEventListener("DOMContentLoaded", () => {
            const RegisterBtn = document.querySelector("#register-nav");
            const LoginBtn = document.querySelector("#login-nav");
            const SliderElement = document.querySelector("#slider-nav");

            RegisterBtn.addEventListener("click", () => {
                SliderElement.classList.add("translate-x-full");
            });
            LoginBtn.addEventListener("click", () => {
                SliderElement.classList.remove("translate-x-full");
            });

            <?php if (isset($signup_error)) : ?>
            SliderElement.classList.add("translate-x-full");
            <?php endif; ?>
        });
    </script>
</body>

</html>