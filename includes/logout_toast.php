<?php if (isset($_GET['logout']) && $_GET['logout'] === 'success'): ?>
<!-- Logged Out Popout Toast Notification -->
<style>
@keyframes toastSlideDown {
    0% {
        opacity: 0;
        transform: translateY(-20px) scale(0.95);
    }
    100% {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}
.animate-toast-pop {
    animation: toastSlideDown 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
</style>
<div id="logout-toast" class="fixed top-6 right-6 z-50 flex items-center gap-3.5 bg-white/95 backdrop-blur-md text-gray-800 px-5 py-4 rounded-2xl shadow-2xl border border-emerald-100 animate-toast-pop">
    <div class="w-9 h-9 rounded-full bg-emerald-500 text-white flex items-center justify-center font-bold shrink-0 shadow-md shadow-emerald-200">
        <i class="fa-solid fa-check text-base"></i>
    </div>
    <div class="pr-2">
        <h4 class="font-bold text-sm text-gray-900 leading-snug">Logged Out</h4>
        <p class="text-xs text-gray-500 font-medium">Logged out successfully!</p>
    </div>
    <button onclick="dismissLogoutToast()" class="ml-2 text-gray-400 hover:text-gray-600 transition-colors p-1.5 rounded-lg hover:bg-gray-100 cursor-pointer">
        <i class="fa-solid fa-xmark text-sm"></i>
    </button>
</div>

<script>
function dismissLogoutToast() {
    const toast = document.getElementById('logout-toast');
    if (toast) {
        toast.style.transition = 'all 0.3s ease';
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(-20px) scale(0.95)';
        setTimeout(() => toast.remove(), 300);
    }
}

// Auto dismiss after 4 seconds
setTimeout(() => {
    dismissLogoutToast();
}, 4000);

// Remove 'logout' parameter from URL without page refresh
if (window.history.replaceState) {
    const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
    window.history.replaceState({path: cleanUrl}, '', cleanUrl);
}
</script>
<?php endif; ?>
