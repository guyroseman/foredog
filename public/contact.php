<?php
$pageTitle = 'Contact Us - Foredog';
require __DIR__ . '/../templates/header.php';
?>
<style>
    /* Dark Theme Wrapper matching mockup */
    .contact-wrapper { background: #1C1C1C; color: #FFFFFF; padding: 6rem 2rem 14rem; position: relative; min-height: 85vh; overflow: hidden; }
    
    .contact-title { text-align: center; font-family: 'DM Sans', sans-serif; font-size: 3rem; letter-spacing: 0.15em; text-transform: uppercase; margin-bottom: 5rem; font-weight: 800; }
    
    .contact-grid { max-width: 1000px; margin: 0 auto; display: grid; grid-template-columns: 1fr 1fr; gap: 4rem; align-items: center; position: relative; z-index: 10; }
    @media (max-width: 768px) { .contact-grid { grid-template-columns: 1fr; padding-bottom: 12rem; } }
    
    .contact-info { display: flex; flex-direction: column; gap: 2.5rem; padding-left: 2rem; }
    .info-row { display: flex; align-items: flex-start; gap: 1.5rem; font-size: 1.1rem; line-height: 1.5; font-weight: 500; }
    .info-icon { flex-shrink: 0; width: 32px; height: 32px; }
    
    /* The Light Grey Form Box */
    .contact-form { background: #E5E5E5; padding: 3rem; border-radius: var(--radius-lg); }
    .contact-form input, .contact-form textarea { width: 100%; background: transparent; border: 1px solid #9CA3AF; border-radius: 50px; padding: 1rem 1.5rem; margin-bottom: 1.5rem; font-family: 'DM Sans', sans-serif; font-size: 1rem; color: #111827; outline: none; transition: border-color 0.2s; }
    .contact-form input:focus, .contact-form textarea:focus { border-color: var(--pn-purple-dark); }
    .contact-form textarea { border-radius: 20px; resize: none; min-height: 140px; }
    .contact-form input::placeholder, .contact-form textarea::placeholder { color: #6B7280; font-weight: 500; }
    
    .btn-submit { background: #111827; color: #FFFFFF; padding: 1rem 3rem; border-radius: 50px; border: none; font-size: 1.1rem; font-weight: 700; cursor: pointer; transition: transform 0.2s; }
    .btn-submit:hover { transform: translateY(-3px); background: #000000; }

    /* The Overlapping Decorative Image */
    .contact-deco { position: absolute; bottom: 8px; left: 10%; width: 380px; height: 260px; object-fit: cover; border-radius: 24px 24px 0 0; z-index: 5; }
    @media (max-width: 768px) { .contact-deco { left: 50%; transform: translateX(-50%); width: 300px; height: 220px; bottom: 8px; } }
    
    /* The specific blue border line from the mockup */
    .blue-line { position: absolute; bottom: 0; left: 0; width: 100%; height: 8px; background: #2B88D8; z-index: 6; }
</style>

<div class="contact-wrapper reveal">
    <h1 class="contact-title">Contact Us</h1>
    
    <div class="contact-grid">
        <div class="contact-info">
            <div class="info-row">
                <svg class="info-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                <span>123 Pawprint Street, Petville District,<br>Bangkok 10200, Thailand</span>
            </div>
            <div class="info-row">
                <svg class="info-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                <span>+66 98 123 4567</span>
            </div>
        </div>
        
        <div class="contact-form">
            <form action="#" method="POST">
                <input type="text" name="name" placeholder="Name" required>
                <input type="email" name="email" placeholder="Email" required>
                <textarea name="message" placeholder="Message" required></textarea>
                <button type="submit" class="btn-submit">Submit</button>
            </form>
        </div>
    </div>

    <img src="https://images.unsplash.com/photo-1513360371669-4adf3dd7dff8?w=600&auto=format&fit=crop&q=80" class="contact-deco" alt="Pets">
    <div class="blue-line"></div>
</div>

<?php require __DIR__ . '/../templates/footer.php'; ?>