<?php if (isset($_SESSION['login_success']) && $_SESSION['login_success'] === true):
    unset($_SESSION['login_success']);
?>
<!-- Logged In Popup Toast Notification -->
<style>
@keyframes loginToastSlideDown {
    0% {
        opacity: 0;
        transform: translateY(-20px) scale(0.95);
    }
    100% {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}
.animate-login-toast-pop {
    animation: loginToastSlideDown 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
</style>
<div id="login-toast" class="fixed top-6 right-6 z-50 flex items-center gap-3.5 bg-white/95 backdrop-blur-md text-gray-800 px-5 py-4 rounded-2xl shadow-2xl border border-amber-100 animate-login-toast-pop">
    <div class="w-9 h-9 rounded-full bg-amber-400 text-white flex items-center justify-center font-bold shrink-0 shadow-md shadow-amber-200">
        <i class="fa-solid fa-check text-base"></i>
    </div>
    <div class="pr-2">
        <h4 class="font-bold text-sm text-gray-900 leading-snug">Successfully Logged In</h4>
        <p class="text-xs text-gray-500 font-medium">Welcome back, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Guest'); ?>!</p>
    </div>
    <button onclick="dismissLoginToast()" class="ml-2 text-gray-400 hover:text-gray-600 transition-colors p-1.5 rounded-lg hover:bg-gray-100 cursor-pointer">
        <i class="fa-solid fa-xmark text-sm"></i>
    </button>
</div>

<script>
function dismissLoginToast() {
    const toast = document.getElementById('login-toast');
    if (toast) {
        toast.style.transition = 'all 0.3s ease';
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(-20px) scale(0.95)';
        setTimeout(() => toast.remove(), 300);
    }
}

// Auto dismiss after 4 seconds
setTimeout(() => {
    dismissLoginToast();
}, 4000);
</script>
<?php endif; ?>
