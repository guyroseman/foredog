<?php
session_start();
require_once __DIR__ . '/../src/Database.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: /'); exit; }
$name  = trim($_POST['name']  ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$dogId = (int)($_POST['dog_id'] ?? 0);
if (!$email || !$phone || !$dogId) { header('Location: /'); exit; }
$db = Database::getInstance();
$db->prepare('INSERT INTO users (name, email, phone) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE phone=VALUES(phone), name=VALUES(name)')->execute([$name, $email, $phone]);
$userId = $db->lastInsertId() ?: $db->query('SELECT id FROM users WHERE email = ' . $db->quote($email))->fetchColumn();
$_SESSION['user_id'] = $userId;
$_SESSION['user_email'] = $email;
$_SESSION['pending_dog_id'] = $dogId;
if (!empty($_SESSION['recommended_breed'])) {
    $db->prepare('UPDATE survey_sessions SET user_id = ? WHERE session_id = ?')->execute([$userId, session_id()]);
}
header('Location: /paywall.php?dog=' . $dogId); exit;
