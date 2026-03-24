<?php

require_once __DIR__ . '/config/config.php';

if (!isset($conn) || !$conn) {
    die("<h2>Database connection failed</h2><p>Fix credentials in config/database.php first, then run this again.</p>");
}

$sql = file_get_contents(__DIR__ . '/database/init.sql');

// Run only CREATE TABLE and USE 
$statements = array_filter(array_map('trim', explode(';', $sql)));

foreach ($statements as $statement) {
    if (empty($statement) || preg_match('/^--/', $statement)) {
        continue;
    }
    if (preg_match('/^CREATE DATABASE/i', $statement)) {
        continue; // already done by config
    }
    if (preg_match('/^USE /i', $statement)) {
        continue; // already using the DB
    }
    if (!$conn->query($statement)) {
        $err = $conn->error;
        if (strpos($err, 'already exists') === false && strpos($err, '1050') === false) {
            die("<h2>Error initializing database</h2><p>" . htmlspecialchars($err) . "</p>");
        }
    }
}

// Add username column to users
$check = $conn->query("SHOW COLUMNS FROM users LIKE 'username'");
if ($check && $check->num_rows === 0) {
    $conn->query("ALTER TABLE users ADD COLUMN username VARCHAR(100) UNIQUE NULL AFTER name");
}
$check = $conn->query("SHOW COLUMNS FROM users LIKE 'degree'");
if ($check && $check->num_rows === 0) {
    $conn->query("ALTER TABLE users ADD COLUMN degree VARCHAR(100) NULL AFTER picture");
}
$check = $conn->query("SHOW COLUMNS FROM users LIKE 'university'");
if ($check && $check->num_rows === 0) {
    $conn->query("ALTER TABLE users ADD COLUMN university VARCHAR(255) NULL AFTER degree");
}

echo "<h2>Database initialized successfully!</h2>";
echo "<p>You can now <a href='login.php'>log in</a> or <a href='index.php'>go to the home page</a>.</p>";
?>
