<?php $currentPage = basename($_SERVER['PHP_SELF'], '.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Foredog - Find Your Perfect Dog' ?></title>
    <link rel="icon" href="data:;base64,iVBORw0KGgo=">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        
        :root { 
            --bg-main: #FFFFFF;
            --bg-alt: #F6F3EE;
            --text-dark: #111827;
            --text-muted: #6B7280;
            --pn-purple: #8B5CF6;
            --pn-purple-dark: #7C3AED; 
            --pn-purple-light: #EDE9FE; 
            --accent-yellow: #FBBF24;
            --surface: #FFFFFF;
            --border: #E5E7EB;
            --radius-lg: 32px;
            --radius-md: 16px;
            --shadow-soft: 0 10px 40px rgba(0, 0, 0, 0.04);
            --shadow-hover: 0 20px 50px rgba(139, 92, 246, 0.12);
        }

        html { scroll-behavior: smooth; overflow-x: hidden; }
        body { font-family: 'DM Sans', sans-serif; background: var(--bg-main); color: var(--text-dark); line-height: 1.6; overflow-x: hidden; }

        nav { position: fixed; top: 0; width: 100%; z-index: 1000; background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(12px); border-bottom: 1px solid transparent; transition: all 0.3s ease; }
        nav.scrolled { border-bottom: 1px solid var(--border); box-shadow: 0 4px 20px rgba(0,0,0,0.03); }
        
        .nav-inner { max-width: 1200px; margin: 0 auto; padding: 0 2rem; display: flex; align-items: center; justify-content: space-between; height: 80px; }
        .nav-logo { font-family: 'Playfair Display', serif; font-size: 1.8rem; font-weight: 700; color: var(--text-dark); text-decoration: none; display: flex; align-items: center; gap: 8px; }
        
        .nav-right { display: flex; align-items: center; gap: 2.5rem; }
        .nav-links { display: flex; gap: 2rem; align-items: center; }
        .nav-links a { text-decoration: none; color: var(--text-dark); font-weight: 500; font-size: 0.95rem; transition: color 0.2s; }
        .nav-links a:hover { color: var(--pn-purple); }

        .btn { display: inline-flex; align-items: center; justify-content: center; padding: 0.8rem 2rem; border-radius: 50px; font-family: 'DM Sans', sans-serif; font-weight: 700; font-size: 1rem; cursor: pointer; text-decoration: none; transition: all 0.3s ease; border: none; }
        .btn-primary { background: var(--pn-purple); color: var(--surface); box-shadow: 0 4px 15px rgba(139, 92, 246, 0.3); }
        .btn-primary:hover { background: var(--pn-purple-dark); transform: translateY(-2px); box-shadow: 0 8px 25px rgba(139, 92, 246, 0.4); }

        .container { max-width: 1200px; margin: 0 auto; padding: 0 2rem; }

        .reveal { opacity: 0; transform: translateY(30px); transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1); }
        .reveal.active { opacity: 1; transform: translateY(0); }
        .delay-1 { transition-delay: 0.1s; }
        .delay-2 { transition-delay: 0.2s; }
        .delay-3 { transition-delay: 0.3s; }
        
        @media (max-width: 768px) {
            .nav-links { display: none; }
            .nav-right { gap: 1rem; }
        }
    </style>
</head>
<body>
<nav id="navbar">
    <div class="nav-inner">
        <a class="nav-logo" href="/">
            <svg width="28" height="28" viewBox="0 0 32 32" fill="var(--pn-purple)" xmlns="http://www.w3.org/2000/svg">
                <circle cx="16" cy="19" r="6" />
                <circle cx="9" cy="9" r="3" />
                <circle cx="16" cy="6" r="3.5" />
                <circle cx="23" cy="9" r="3" />
            </svg>
            Foredog
        </a>
        <div class="nav-right">
            <div class="nav-links">
                <a href="/">Home</a>
                <a href="/breed.php">Browse Pets</a>
                <a href="/contact.php">Contact Us</a>
            </div>
            <a class="btn btn-primary" href="/survey.php" style="color:white;">Match Me</a>
        </div>
    </div>
</nav>
<main style="padding-top: 80px;">