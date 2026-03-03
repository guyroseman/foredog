</main>
<footer style="background: var(--surface); border-top: 1px solid var(--border); padding: 4rem 2rem 2rem; margin-top: auto;">
    <div class="container" style="display: flex; flex-direction: column; align-items: center; text-align: center;">
        
        <a href="/" style="font-family: 'Playfair Display', serif; font-size: 1.5rem; font-weight: 700; color: var(--text-dark); text-decoration: none; display: flex; align-items: center; gap: 8px; margin-bottom: 1rem;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="var(--pn-purple)"><path d="M12 2C8.69 2 6 4.69 6 8c0 3.31 2.69 6 6 6s6-2.69 6-6c0-3.31-2.69-6-6-6zm0 10c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4zm-8.83 5.46C2.33 16.32 2 15.18 2 14c0-2.76 2.24-5 5-5h1c.55 0 1 .45 1 1s-.45 1-1 1H7c-1.65 0-3 1.35-3 3 0 .85.36 1.62.94 2.18L3.17 17.46zM22 14c0 1.18-.33 2.32-.94 3.18l-1.77-1.28c.58-.56.94-1.33.94-2.18 0-1.65-1.35-3-3-3h-1c-.55 0-1-.45-1-1s.45-1 1-1h1c2.76 0 5 2.24 5 5zM12 16c-3.31 0-6 2.69-6 6h12c0-3.31-2.69-6-6-6zm0 4c-1.1 0-2-.9-2-2h4c0 1.1-.9 2-2 2z"/></svg>
            Fore<span>dog</span>
        </a>
        
        <p style="color: var(--text-muted); font-size: 0.95rem; max-width: 400px; margin-bottom: 2rem;">Connecting loving homes with dogs who need them through intelligent lifestyle matchmaking.</p>
        
        <div style="display: flex; gap: 2rem; margin-bottom: 3rem;">
            <a href="/breed.php" style="color: var(--text-dark); text-decoration: none; font-weight: 700; font-size: 0.9rem;">Browse Dogs</a>
            <a href="/survey.php" style="color: var(--text-dark); text-decoration: none; font-weight: 700; font-size: 0.9rem;">Match Quiz</a>
            <a href="#" style="color: var(--text-dark); text-decoration: none; font-weight: 700; font-size: 0.9rem;">Contact</a>
        </div>

        <div style="border-top: 1px solid var(--border); width: 100%; padding-top: 2rem; color: var(--text-muted); font-size: 0.85rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <p>&copy; <?= date('Y') ?> Foredog. All rights reserved.</p>
            <p style="opacity: 0.7;">Secure Stripe Checkout 🔒</p>
        </div>
    </div>
</footer>

<script>
document.addEventListener("DOMContentLoaded", () => {
    // Reveal animations on scroll
    const reveals = document.querySelectorAll(".reveal");
    const revealOptions = { threshold: 0.15, rootMargin: "0px 0px -50px 0px" };
    
    const revealOnScroll = new IntersectionObserver(function(entries, observer) {
        entries.forEach(entry => {
            if (!entry.isIntersecting) return;
            entry.target.classList.add("active");
            observer.unobserve(entry.target); // Only animate once
        });
    }, revealOptions);

    reveals.forEach(reveal => { revealOnScroll.observe(reveal); });

    // Dynamic Navbar Shadow on Scroll
    const nav = document.getElementById("navbar");
    window.addEventListener("scroll", () => {
        if (window.scrollY > 20) {
            nav.style.boxShadow = "0 10px 30px rgba(0,0,0,0.05)";
            nav.style.borderBottom = "1px solid transparent";
        } else {
            nav.style.boxShadow = "none";
            nav.style.borderBottom = "1px solid rgba(0,0,0,0.03)";
        }
    });
});
</script>
</body>
</html>