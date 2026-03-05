<?php
/**
 * Foredog Database Installer / Resetter
 */
require_once __DIR__ . '/src/Database.php';

echo "1. Connecting to Foredog database using config.php...\n";
try {
    $db = Database::getInstance();
} catch (Exception $e) {
    die("❌ Connection failed! Make sure your password in config.php is correct.\nError: " . $e->getMessage() . "\n");
}

echo "2. Reading database/schema.sql...\n";
$sql = file_get_contents(__DIR__ . '/database/schema.sql');

if (!$sql) {
    die("❌ Could not find database/schema.sql file!\n");
}

echo "3. Executing complete database wipe and rebuild...\n";
try {
    // This executes all the SQL commands in the file at once
    $db->exec($sql);
    echo "✅ SUCCESS: The database has been perfectly reset with the new columns!\n\n";
} catch (Exception $e) {
    echo "❌ Error running SQL: " . $e->getMessage() . "\n";
}