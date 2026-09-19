<?php
include '../conn.php';
require_once '../includes/mailer.php';
session_start();

if (isset($_SESSION['isLogin'])) {
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        header("Location: adminpage.php");
    } else {
        header("Location: ../index.php");
    }
    exit();
}

$otp_error = null;
$otp_success = null;

// Handle cancellation of OTP / going back to signup
if (isset($_POST['cancel_otp'])) {
    unset($_SESSION['pending_registration']);
    header("Location: login.php");
    exit();
}

// Handle Resending OTP
if (isset($_POST['resend_otp'])) {
    if (!isset($_SESSION['pending_registration'])) {
        $signup_error = "No pending registration found. Please register again.";
    } else {
        $pending = &$_SESSION['pending_registration'];
        if (time() < ($pending['resend_at'] ?? 0)) {
            $wait_secs = ($pending['resend_at'] - time());
            $otp_error = "Please wait {$wait_secs} seconds before requesting a new code.";
        } else {
            $new_otp = sprintf('%06d', random_int(100000, 999999));
            $pending['otp'] = $new_otp;
            $pending['expires_at'] = time() + 600; // 10 minutes
            $pending['resend_at'] = time() + 60;   // 60-second cooldown
            $pending['attempts'] = 0;

            $mail_res = send_otp_email($pending['email'], $pending['username'], $new_otp);
            if ($mail_res['success']) {
                $otp_success = "A new verification code has been sent to your email.";
            } else {
                $otp_error = "Could not deliver email: " . ($mail_res['error'] ?? 'Please try again.');
            }
        }
    }
}

// Handle OTP Verification
if (isset($_POST['verify_otp'])) {
    if (!isset($_SESSION['pending_registration'])) {
        $signup_error = "No pending registration found. Please register again.";
    } else {
        $pending = &$_SESSION['pending_registration'];

        // Gather submitted OTP code
        $submitted_otp = trim($_POST['otp_code'] ?? '');
        if (empty($submitted_otp) && isset($_POST['otp_digit']) && is_array($_POST['otp_digit'])) {
            $submitted_otp = implode('', array_map('trim', $_POST['otp_digit']));
        }

        if (empty($submitted_otp)) {
            $otp_error = "Please enter the 6-digit verification code.";
        } elseif (time() > ($pending['expires_at'] ?? 0)) {
            $otp_error = "Verification code has expired. Please request a new one.";
        } elseif (($pending['attempts'] ?? 0) >= 5) {
            unset($_SESSION['pending_registration']);
            $signup_error = "Too many failed attempts. Please register again.";
        } elseif ($submitted_otp !== (string)$pending['otp']) {
            $pending['attempts'] = ($pending['attempts'] ?? 0) + 1;
            $remaining = 5 - $pending['attempts'];
            $otp_error = "Incorrect code. {$remaining} attempts remaining.";
        } else {
            // Code is valid! Ensure email is not taken in database
            $check_stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
            mysqli_stmt_bind_param($check_stmt, "s", $pending['email']);
            mysqli_stmt_execute($check_stmt);
            mysqli_stmt_store_result($check_stmt);

            if (mysqli_stmt_num_rows($check_stmt) > 0) {
                unset($_SESSION['pending_registration']);
                $signup_error = "This email was already registered. Please sign in.";
            } else {
                $insert_stmt = mysqli_prepare($conn, "INSERT INTO users (username, email, password, role, status) VALUES (?, ?, ?, 'user', 'ACTIVE')");
                mysqli_stmt_bind_param($insert_stmt, "sss", $pending['username'], $pending['email'], $pending['password']);

                if (mysqli_stmt_execute($insert_stmt)) {
                    $new_id = mysqli_insert_id($conn);
                    // Automatically log user in
                    $_SESSION['isLogin'] = true;
                    $_SESSION['email'] = $pending['email'];
                    $_SESSION['username'] = $pending['username'];
                    $_SESSION['user_id'] = $new_id;
                    $_SESSION['role'] = 'user';
                    $_SESSION['login_success'] = true;

                    unset($_SESSION['pending_registration']);
                    mysqli_stmt_close($insert_stmt);
                    mysqli_stmt_close($check_stmt);
                    header("Location: ../index.php");
                    exit();
                } else {
                    $otp_error = "Something went wrong while creating your account: " . mysqli_error($conn);
                }
                mysqli_stmt_close($insert_stmt);
            }
            mysqli_stmt_close($check_stmt);
        }
    }
}

// Handle Sign Up
if (isset($_POST['signup'])) {
    $semail = trim($_POST['remail'] ?? '');
    $sname = trim($_POST['rname'] ?? '');
    $spassword = $_POST['rpass'] ?? '';
    $scpassword = $_POST['rcpass'] ?? '';

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
            $otp = sprintf('%06d', random_int(100000, 999999));

            $_SESSION['pending_registration'] = [
                'username'   => $sname,
                'email'      => $semail,
                'password'   => $hashedpass,
                'otp'        => $otp,
                'expires_at' => time() + 600, // 10 minutes
                'resend_at'  => time() + 60,  // 60-second cooldown
                'attempts'   => 0
            ];

            $mail_res = send_otp_email($semail, $sname, $otp);
            if (!$mail_res['success']) {
                $otp_error = "Could not send verification email: " . ($mail_res['error'] ?? 'Please try again.');
            }
        }
        mysqli_stmt_close($stmt);
    }
}

// Handle Sign In
if (isset($_POST['signin'])) {
    $email = trim($_POST['lemail'] ?? '');
    $password = $_POST['lpass'] ?? '';

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

            $_SESSION['login_success'] = true;
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

$has_pending_otp = isset($_SESSION['pending_registration']);
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login / Register - LOTUS Luxury Hotel</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="../css/style.css" />
    <style>
        .otp-input:focus {
            box-shadow: 0 0 15px rgba(251, 191, 36, 0.4);
            border-color: #fbbf24;
        }
        @keyframes otpPulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.05); opacity: 0.9; }
        }
        .animate-otp-pulse {
            animation: otpPulse 3s ease-in-out infinite;
        }
    </style>
</head>

<body class="text-poppins min-h-screen relative flex items-center justify-center" style="background: #0a0f1e;">
    <!-- Full-screen luxury hotel background -->
    <div class="fixed inset-0 z-0" style="background-image: url('https://dwarikas.com/media/site/1e1069b432-1780289916/dwarikas_elisehassey_7512copy-1920x-q85.webp'); background-size: cover; background-position: center; filter: blur(3px) brightness(0.92); transform: scale(1.05);"></div>
    <!-- Gradient overlay for depth -->
    <div class="fixed inset-0 z-0" style="background: linear-gradient(135deg, rgba(0,0,0,0.30) 0%, rgba(0,0,0,0.15) 50%, rgba(0,0,0,0.30) 100%);"></div>
    
    <?php include '../includes/logout_toast.php'; ?>

    <main class="relative z-10 max-w-4xl w-full mx-auto my-10 px-4">
        <div class="relative flex p-3 w-full h-150 rounded-2xl overflow-hidden" style="background: rgba(255,255,255,0.05); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.15); box-shadow: 0 32px 80px rgba(0,0,0,0.45);">
            <!-- Sliding Cover Panel -->
            <div class="absolute inset-0 rounded-xl overflow-hidden z-3 transition-all duration-500 w-[50%] h-100%"
                id="slider-nav">
                <div class="w-full h-full relative">
                    <img src="https://www.firstclassmagazine.co/wp-content/uploads/2024/07/room-details-2-pic-supplied-800x1067.jpeg"
                        class="object-cover h-full w-full" alt="Villa Image">
                    <div class="inset-0 absolute w-full h-full z-4 bg-black/10"></div>
                </div>
            </div>
            
            <!-- Registration Form Panel -->
            <div class="w-[50%] h-100% text-white" id="login">
                <div class="text-right pr-8 py-4 text-xs text-white/70">Already a member? <span class="text-amber-300 cursor-pointer font-semibold hover:underline"
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
                            class="text-sm mt-5 w-full bg-amber-400 hover:bg-amber-300 p-2.5 rounded-lg text-gray-900 font-semibold transition-all duration-200 cursor-pointer shadow-lg active:scale-[0.99]"
                            name="signup">Create Account</button>
                    </form>
                    <?php if (isset($signup_error)) {
                        echo "<span class='text-sm mt-2 text-red-400 text-center'>$signup_error</span>";
                    } ?>
                </div>
            </div>

            <!-- Login Form Panel -->
            <div class="w-[50%] text-white" id="signin">
                <div class="text-right pr-8 py-4 text-xs text-white/70">Not a member? <span class="text-amber-300 cursor-pointer font-semibold hover:underline"
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
                        <button type="submit"
                            class="text-sm mt-6 bg-amber-400 hover:bg-amber-300 p-2.5 rounded-lg text-gray-900 font-semibold transition-all duration-200 cursor-pointer shadow-lg active:scale-[0.99]"
                            name="signin">Sign In</button>
                    </form>
                    <?php if (isset($login_error)) {
                        echo "<span class='text-sm mt-2 text-red-400 text-center block'>$login_error</span>";
                    } ?>
                </div>
            </div>
        </div>
    </main>

    <!-- OTP Verification Modal -->
    <?php if ($has_pending_otp): 
        $pending_email = $_SESSION['pending_registration']['email'] ?? '';
        $expires_at = $_SESSION['pending_registration']['expires_at'] ?? (time() + 600);
        $resend_at  = $_SESSION['pending_registration']['resend_at'] ?? (time() + 60);
        $now = time();
        $remaining_seconds = max(0, $expires_at - $now);
        $resend_cooldown = max(0, $resend_at - $now);
    ?>
    <div id="otp-modal" class="fixed inset-0 z-50 flex items-center justify-center px-4 bg-black/75 backdrop-blur-md transition-opacity">
        <div class="relative w-full max-w-md bg-[#0d1527] border border-amber-400/30 rounded-3xl p-7 shadow-2xl shadow-black/80 text-white overflow-hidden">
            <!-- Ambient gold glow accent -->
            <div class="absolute -top-16 -left-16 w-36 h-36 bg-amber-400/20 rounded-full blur-2xl pointer-events-none"></div>
            <div class="absolute -bottom-16 -right-16 w-36 h-36 bg-amber-400/10 rounded-full blur-2xl pointer-events-none"></div>

            <div class="text-center relative z-10">
                <!-- Glowing Icon -->
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-amber-500/20 to-amber-300/10 border border-amber-400/40 text-amber-400 flex items-center justify-center mx-auto mb-4 text-2xl shadow-lg shadow-amber-500/10 animate-otp-pulse">
                    <i class="fa-solid fa-envelope-circle-check"></i>
                </div>

                <h2 class="text-2xl font-bold tracking-wide text-white">Verify Your Email</h2>
                <p class="text-xs text-white/60 mt-1.5 px-2">
                    We sent a 6-digit verification code to
                </p>
                <div class="mt-1 font-semibold text-amber-300 text-sm tracking-wide break-all">
                    <?php echo htmlspecialchars($pending_email); ?>
                </div>

                <?php 
                $active_mail_cfg = get_mail_config();
                if (($active_mail_cfg['driver'] ?? 'mail') === 'mail'): 
                ?>
                    <div class="mt-2.5">
                        <a href="http://localhost:8025" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-400/10 hover:bg-amber-400/20 text-amber-300 text-[11px] font-medium border border-amber-400/30 transition-all shadow-sm">
                            <i class="fa-solid fa-inbox"></i> Laragon Mailpit: Click here to view OTP <i class="fa-solid fa-arrow-up-right-from-square text-[9px]"></i>
                        </a>
                    </div>
                <?php endif; ?>

                <!-- Status Messages -->
                <?php if (!empty($otp_error)): ?>
                    <div class="mt-4 p-2.5 rounded-xl bg-red-500/15 border border-red-500/30 text-red-300 text-xs font-medium text-center">
                        <i class="fa-solid fa-circle-exclamation mr-1.5"></i><?php echo htmlspecialchars($otp_error); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($otp_success)): ?>
                    <div class="mt-4 p-2.5 rounded-xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-300 text-xs font-medium text-center">
                        <i class="fa-solid fa-circle-check mr-1.5"></i><?php echo htmlspecialchars($otp_success); ?>
                    </div>
                <?php endif; ?>

                <!-- OTP Form -->
                <form id="otp-form" method="POST" action="" class="mt-6">
                    <input type="hidden" name="otp_code" id="otp_full_code" value="" />

                    <!-- 6 Digit Input Group -->
                    <div class="flex justify-center items-center gap-2 sm:gap-3" id="otp-inputs">
                        <?php for ($i = 0; $i < 6; $i++): ?>
                            <input type="text"
                                maxlength="1"
                                inputmode="numeric"
                                autocomplete="off"
                                data-index="<?php echo $i; ?>"
                                class="otp-input w-11 h-13 sm:w-12 sm:h-14 text-center text-xl sm:text-2xl font-bold font-mono bg-white/5 border border-white/20 rounded-xl text-amber-300 placeholder-white/20 focus:outline-none focus:bg-white/10 transition-all shadow-inner"
                                required />
                        <?php endfor; ?>
                    </div>

                    <!-- Expiry Countdown -->
                    <div class="flex items-center justify-center gap-1.5 mt-4 text-xs text-white/60">
                        <i class="fa-regular fa-clock text-amber-400"></i>
                        <span>Code expires in:</span>
                        <span id="countdown-timer" class="font-mono font-semibold text-amber-300">--:--</span>
                    </div>

                    <!-- Verify Button -->
                    <button type="submit"
                        name="verify_otp"
                        id="verify-btn"
                        class="w-full mt-5 py-3 rounded-xl bg-gradient-to-r from-amber-400 to-amber-500 hover:from-amber-300 hover:to-amber-400 text-gray-950 font-bold text-sm tracking-wide transition-all shadow-lg shadow-amber-500/20 active:scale-[0.99] cursor-pointer">
                        Verify &amp; Create Account
                    </button>
                </form>

                <!-- Resend & Cancel Options -->
                <div class="mt-4 pt-4 border-t border-white/10 flex flex-col gap-2.5 text-xs text-white/70">
                    <form method="POST" action="" id="resend-form">
                        <span>Didn't receive the code?</span>
                        <button type="submit"
                            name="resend_otp"
                            id="resend-btn"
                            class="text-amber-400 hover:text-amber-300 font-semibold underline underline-offset-2 ml-1 cursor-pointer transition-colors disabled:opacity-50 disabled:cursor-not-allowed disabled:no-underline">
                            Resend Code <span id="resend-timer"></span>
                        </button>
                    </form>

                    <form method="POST" action="">
                        <button type="submit"
                            name="cancel_otp"
                            class="text-white/50 hover:text-white/80 transition-colors text-xs inline-flex items-center gap-1.5 cursor-pointer mt-1">
                            <i class="fa-solid fa-arrow-left text-[10px]"></i> Change email / Back to registration
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script>
        window.addEventListener("DOMContentLoaded", () => {
            const RegisterBtn = document.querySelector("#register-nav");
            const LoginBtn = document.querySelector("#login-nav");
            const SliderElement = document.querySelector("#slider-nav");

            RegisterBtn?.addEventListener("click", () => {
                SliderElement.classList.add("translate-x-full");
            });
            LoginBtn?.addEventListener("click", () => {
                SliderElement.classList.remove("translate-x-full");
            });

            <?php if (isset($signup_error) && !$has_pending_otp) : ?>
            SliderElement.classList.add("translate-x-full");
            <?php endif; ?>

            // OTP Input Management & Auto-Tab
            const otpInputs = document.querySelectorAll(".otp-input");
            const fullCodeInput = document.querySelector("#otp_full_code");
            const otpForm = document.querySelector("#otp-form");

            if (otpInputs.length > 0) {
                // Auto focus first input
                setTimeout(() => otpInputs[0].focus(), 150);

                const updateFullCode = () => {
                    let code = "";
                    otpInputs.forEach(input => code += input.value);
                    if (fullCodeInput) fullCodeInput.value = code;
                };

                otpInputs.forEach((input, idx) => {
                    input.addEventListener("input", (e) => {
                        const val = e.target.value.replace(/[^0-9]/g, "");
                        e.target.value = val ? val.slice(-1) : "";
                        updateFullCode();

                        if (e.target.value && idx < otpInputs.length - 1) {
                            otpInputs[idx + 1].focus();
                        }
                    });

                    input.addEventListener("keydown", (e) => {
                        if (e.key === "Backspace") {
                            if (!e.target.value && idx > 0) {
                                otpInputs[idx - 1].focus();
                                otpInputs[idx - 1].value = "";
                                updateFullCode();
                            } else {
                                e.target.value = "";
                                updateFullCode();
                            }
                        } else if (e.key === "ArrowLeft" && idx > 0) {
                            otpInputs[idx - 1].focus();
                        } else if (e.key === "ArrowRight" && idx < otpInputs.length - 1) {
                            otpInputs[idx + 1].focus();
                        }
                    });

                    // Handle full paste
                    input.addEventListener("paste", (e) => {
                        e.preventDefault();
                        const pasteData = (e.clipboardData || window.clipboardData).getData("text").trim().replace(/[^0-9]/g, "");
                        if (!pasteData) return;

                        const digits = pasteData.slice(0, 6).split("");
                        digits.forEach((digit, i) => {
                            if (otpInputs[i]) {
                                otpInputs[i].value = digit;
                            }
                        });
                        updateFullCode();

                        const nextFocusIdx = Math.min(digits.length, otpInputs.length - 1);
                        otpInputs[nextFocusIdx].focus();
                    });
                });

                otpForm?.addEventListener("submit", (e) => {
                    updateFullCode();
                    if (!fullCodeInput.value || fullCodeInput.value.length !== 6) {
                        e.preventDefault();
                        alert("Please enter all 6 digits of the verification code.");
                    }
                });
            }

            // Expiry countdown timer & Resend Cooldown
            <?php if ($has_pending_otp): ?>
            let remainingSecs = <?php echo (int)$remaining_seconds; ?>;
            let resendCooldown = <?php echo (int)$resend_cooldown; ?>;

            const countdownElem = document.getElementById("countdown-timer");
            const resendBtn = document.getElementById("resend-btn");
            const resendTimerElem = document.getElementById("resend-timer");

            const updateTimers = () => {
                // Code expiry
                if (countdownElem) {
                    const mins = Math.floor(remainingSecs / 60);
                    const secs = remainingSecs % 60;
                    countdownElem.textContent = `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
                    if (remainingSecs <= 0) {
                        countdownElem.textContent = "Expired";
                        countdownElem.classList.add("text-red-400");
                    }
                }

                // Resend cooldown
                if (resendBtn && resendTimerElem) {
                    if (resendCooldown > 0) {
                        resendBtn.disabled = true;
                        resendTimerElem.textContent = `(${resendCooldown}s)`;
                    } else {
                        resendBtn.disabled = false;
                        resendTimerElem.textContent = "";
                    }
                }

                if (remainingSecs > 0) remainingSecs--;
                if (resendCooldown > 0) resendCooldown--;
            };

            updateTimers();
            const timerInterval = setInterval(updateTimers, 1000);
            <?php endif; ?>
        });
    </script>
</body>

</html>