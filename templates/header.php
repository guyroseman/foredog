<?php $currentPage = basename($_SERVER['PHP_SELF'], '.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Foredog - Find Your Perfect Dog' ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        
        :root { 
            /* The Modern Foredog + PuppiesNation Palette */
            --bg-main: #FDFCF8;
            --text-dark: #1A1311;
            --text-muted: #6B5E59;
            --pn-purple: #A26BFA;       /* Modernized PuppiesNation Purple */
            --pn-purple-dark: #8B4DF5;
            --pn-purple-light: #EBE1FF;
            --accent-yellow: #FBBF24;
            --surface: #FFFFFF;
            --border: #EFE9E2;
            --radius-lg: 24px;
            --radius-md: 16px;
            --shadow-soft: 0 12px 40px rgba(162, 107, 250, 0.08);
            --shadow-hover: 0 20px 50px rgba(162, 107, 250, 0.15);
        }

        html { scroll-behavior: smooth; overflow-x: hidden; }
        
        body { 
            font-family: 'DM Sans', sans-serif; 
            background: var(--bg-main); 
            color: var(--text-dark); 
            line-height: 1.6; 
            overflow-x: hidden;
        }

        /* Navigation */
        nav { 
            position: fixed; 
            top: 0; width: 100%; 
            z-index: 1000; 
            background: rgba(253, 252, 248, 0.85); 
            backdrop-filter: blur(16px); 
            border-bottom: 1px solid rgba(0,0,0,0.03); 
            transition: all 0.3s ease;
        }
        
        .nav-inner { 
            max-width: 1200px; 
            margin: 0 auto; 
            padding: 0 2rem;
            display: flex; 
            align-items: center; 
            justify-content: space-between; 
            height: 80px; 
        }
        
        .nav-logo { 
            font-family: 'Playfair Display', serif; 
            font-size: 1.75rem; 
            font-weight: 700; 
            color: var(--text-dark); 
            text-decoration: none; 
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .nav-logo span { color: var(--pn-purple); }

        .btn { 
            display: inline-flex; 
            align-items: center;
            justify-content: center;
            padding: 0.85rem 2rem; 
            border-radius: 50px; 
            font-family: 'DM Sans', sans-serif; 
            font-weight: 700; 
            font-size: 1rem; 
            cursor: pointer; 
            text-decoration: none; 
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); 
            border: none; 
        }
        
        .btn-primary { 
            background: var(--pn-purple); 
            color: var(--white); 
            box-shadow: 0 8px 24px rgba(162, 107, 250, 0.3);
        }
        
        .btn-primary:hover { 
            background: var(--pn-purple-dark); 
            transform: translateY(-3px); 
            box-shadow: 0 12px 32px rgba(162, 107, 250, 0.4); 
        }

        .container { max-width: 1200px; margin: 0 auto; padding: 0 2rem; }

        /* Animation Classes */
        .reveal { opacity: 0; transform: translateY(30px); transition: all 0.8s cubic-bezier(0.5, 0, 0, 1); }
        .reveal.active { opacity: 1; transform: translateY(0); }
        .delay-1 { transition-delay: 0.1s; }
        .delay-2 { transition-delay: 0.2s; }
        .delay-3 { transition-delay: 0.3s; }
    </style>
</head>
<body>
<nav id="navbar">
    <div class="nav-inner">
        <a class="nav-logo" href="/">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="var(--pn-purple)"><path d="M12 2C8.69 2 6 4.69 6 8c0 3.31 2.69 6 6 6s6-2.69 6-6c0-3.31-2.69-6-6-6zm0 10c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4zm-8.83 5.46C2.33 16.32 2 15.18 2 14c0-2.76 2.24-5 5-5h1c.55 0 1 .45 1 1s-.45 1-1 1H7c-1.65 0-3 1.35-3 3 0 .85.36 1.62.94 2.18L3.17 17.46zM22 14c0 1.18-.33 2.32-.94 3.18l-1.77-1.28c.58-.56.94-1.33.94-2.18 0-1.65-1.35-3-3-3h-1c-.55 0-1-.45-1-1s.45-1 1-1h1c2.76 0 5 2.24 5 5zM12 16c-3.31 0-6 2.69-6 6h12c0-3.31-2.69-6-6-6zm0 4c-1.1 0-2-.9-2-2h4c0 1.1-.9 2-2 2z"/></svg>
            Fore<span>dog</span>
        </a>
        <a class="btn btn-primary" href="/survey.php">Match Me With a Dog</a>
    </div>
</nav>
<main style="padding-top: 80px;">