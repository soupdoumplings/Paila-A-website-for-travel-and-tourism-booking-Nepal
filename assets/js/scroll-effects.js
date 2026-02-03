// Scroll effects

// Navbar
window.addEventListener('scroll', function () {
    const navbar = document.querySelector('.navbar');
    if (!navbar || navbar.classList.contains('nav-no-scroll')) return;

    const scrollPosition = window.scrollY;

    // Toggle class
    if (scrollPosition > 100) {
        navbar.classList.add('scrolled');
    } else {
        navbar.classList.remove('scrolled');
    }
});

// Parallax
window.addEventListener('scroll', function () {
    const scrollPosition = window.scrollY;
    const windowHeight = window.innerHeight;

    const heroContent = document.getElementById('hero-content');
    const aboutContent = document.getElementById('about-content');
    const parallaxBg = document.getElementById('parallax-bg');

    if (!heroContent || !aboutContent || !parallaxBg) return;

    // Progress
    const factor = 0.85;
    const progress = scrollPosition / (windowHeight * factor);

    // Hero transition
    const heroOpacity = Math.max(0, 1 - (progress / 0.4));
    heroContent.style.opacity = heroOpacity;

    // About transition
    const aboutOpacity = Math.max(0, Math.min(1, (progress - 0.4) / 0.4));
    aboutContent.style.opacity = aboutOpacity;

    // Hide bg
    if (scrollPosition > windowHeight * 2) {
        parallaxBg.style.opacity = '0';
    } else {
        parallaxBg.style.opacity = '1';
    }
});

// Sidebar
document.addEventListener('DOMContentLoaded', function () {
    const userIcon = document.getElementById('user-icon');
    const sidebar = document.getElementById('user-sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    const closeBtn = document.getElementById('close-sidebar');

    // Open
    if (userIcon) {
        userIcon.addEventListener('click', function () {
            sidebar.style.transform = 'translateX(0)';
            overlay.style.opacity = '1';
            overlay.style.visibility = 'visible';
        });
    }

    // Close
    function closeSidebar() {
        sidebar.style.transform = 'translateX(100%)';
        overlay.style.opacity = '0';
        overlay.style.visibility = 'hidden';
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', closeSidebar);
    }

    if (overlay) {
        overlay.addEventListener('click', closeSidebar);
    }
});
