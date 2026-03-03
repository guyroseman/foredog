<?php $currentPage = basename($_SERVER['PHP_SELF'], '.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Foredog - Find Your Perfect Dog' ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root { --cream:#FAF7F2; --bark:#2C1810; --amber:#C8732A; --amber-light:#E8934A; --sage:#7A8C6E; --sand:#E8DDD0; --white:#FFFFFF; --muted:#7A6A5A; --radius:12px; }
        html { scroll-behavior: smooth; }
        body { font-family:'DM Sans',sans-serif; background:var(--cream); color:var(--bark); font-weight:300; line-height:1.6; min-height:100vh; }
        nav { position:sticky; top:0; z-index:100; background:rgba(250,247,242,0.92); backdrop-filter:blur(12px); border-bottom:1px solid var(--sand); padding:0 2rem; }
        .nav-inner { max-width:1100px; margin:0 auto; display:flex; align-items:center; justify-content:space-between; height:64px; }
        .nav-logo { font-family:'Playfair Display',serif; font-size:1.5rem; font-weight:700; color:var(--bark); text-decoration:none; }
        .nav-logo span { color:var(--amber); }
        .nav-cta { background:var(--amber); color:var(--white); padding:.5rem 1.25rem; border-radius:50px; text-decoration:none; font-size:.875rem; font-weight:500; transition:background .2s; }
        .nav-cta:hover { background:var(--amber-light); }
        .container { max-width:1100px; margin:0 auto; padding:0 2rem; }
        .btn { display:inline-block; padding:.75rem 2rem; border-radius:50px; font-family:'DM Sans',sans-serif; font-weight:500; font-size:1rem; cursor:pointer; text-decoration:none; transition:all .2s; border:none; }
        .btn-primary { background:var(--amber); color:var(--white); }
        .btn-primary:hover { background:var(--amber-light); transform:translateY(-2px); box-shadow:0 8px 24px rgba(200,115,42,.3); }
        .btn-outline { background:transparent; color:var(--bark); border:2px solid var(--bark); }
        .btn-outline:hover { background:var(--bark); color:var(--white); }
    </style>
</head>
<body>
<nav>
    <div class="nav-inner">
        <a class="nav-logo" href="/">🐾 Fore<span>dog</span></a>
        <a class="nav-cta" href="/survey.php">Find My Dog</a>
    </div>
</nav>
