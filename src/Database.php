<?php
declare(strict_types=1);
class Database {
    private static ?PDO $instance = null;
    private function __construct() {}
    public static function getInstance(): PDO {
        if (self::$instance === null) {
            $config = require __DIR__ . '/../config.php';
            $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $config['db_host'], $config['db_name']);
            $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false];
            try { self::$instance = new PDO($dsn, $config['db_user'], $config['db_pass'], $options); }
            catch (PDOException $e) { error_log('DB Connection failed: ' . $e->getMessage()); throw new RuntimeException('Database connection failed.'); }
        }
        return self::$instance;
    }
}
