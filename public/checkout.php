<?php
session_start();
// TODO Phase 2: Replace with real Stripe Checkout session
// For now: simulate successful payment for local dev
$dogId = (int)($_GET['dog'] ?? $_SESSION['pending_dog_id'] ?? 0);
if (!$dogId) { header('Location: /'); exit; }
require_once __DIR__ . '/../src/Database.php';
$db = Database::getInstance();
if (!empty($_SESSION['user_id'])) {
    $db->prepare('UPDATE users SET is_subscribed = TRUE WHERE id = ?')->execute([$_SESSION['user_id']]);
}
header('Location: /reveal.php?dog=' . $dogId); exit;
