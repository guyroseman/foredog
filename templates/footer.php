</main>
<footer style="background: var(--bg-main); padding: 4rem 2rem 2rem; margin-top: auto;">
    <div class="container" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 2rem;">
        
        <div style="display: flex; align-items: center; gap: 12px;">
            <svg width="32" height="32" viewBox="0 0 32 32" fill="var(--text-dark)" xmlns="http://www.w3.org/2000/svg">
                <circle cx="16" cy="19" r="6" />
                <circle cx="9" cy="9" r="3" />
                <circle cx="16" cy="6" r="3.5" />
                <circle cx="23" cy="9" r="3" />
            </svg>
            <span style="font-family: 'Playfair Display', serif; font-size: 2rem; font-weight: 700; color: var(--text-dark);">Foredog</span>
        </div>
        
        <div style="display: flex; flex-direction: column; gap: 1.2rem; font-weight: 700; font-size: 0.95rem; text-align: right;">
            <a href="/" style="color: var(--text-dark); text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='var(--pn-purple)'" onmouseout="this.style.color='var(--text-dark)'">Home</a>
            <a href="/breed.php" style="color: var(--text-dark); text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='var(--pn-purple)'" onmouseout="this.style.color='var(--text-dark)'">Browse</a>
            <a href="/survey.php" style="color: var(--text-dark); text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='var(--pn-purple)'" onmouseout="this.style.color='var(--text-dark)'">Quiz</a>
            <a href="/contact.php" style="color: var(--text-dark); text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='var(--pn-purple)'" onmouseout="this.style.color='var(--text-dark)'">Contact</a>
        </div>
    </div>
    
    <div class="container" style="border-top: 1px solid var(--border); margin-top: 3rem; padding-top: 2rem; color: var(--text-muted); font-size: 0.85rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <p>&copy; <?= date('Y') ?> Foredog. All rights reserved.</p>
        <p>Built for a loving future.</p>
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
            observer.unobserve(entry.target); 
        });
    }, revealOptions);

    reveals.forEach(reveal => { revealOnScroll.observe(reveal); });

    // Dynamic Navbar Shadow
    const nav = document.getElementById("navbar");
    window.addEventListener("scroll", () => {
        if (window.scrollY > 20) {
            nav.classList.add("scrolled");
        } else {
            nav.classList.remove("scrolled");
        }
    });
});
</script>
</body>
</html>