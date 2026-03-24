<?php

mysqli_report(MYSQLI_REPORT_OFF);

$database_url = getenv('DATABASE_URL');

if ($database_url) {
    // Railway environment - parse the DATABASE_URL
    $url = parse_url($database_url);
    $db_host = $url['host'];
    $db_port = $url['port'];
    $db_user = $url['user'];
    $db_pass = $url['pass'];
    $db_name = ltrim($url['path'], '/');
} else {
    // Local XAMPP environment
    $db_host = 'localhost:3307';
    $db_port = 3306;
    $db_user = 'root';
    $db_pass = '';
    $db_name = 'jobfinding';
}

// Connect to database
$conn = @new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port);

if (!$conn || $conn->connect_error) {
    $error = $conn ? $conn->connect_error : 'Could not connect to database';
    die("<h2>Database Connection Failed</h2>" .
        "<p>Error: " . htmlspecialchars($error) . "</p>");
}

$conn->set_charset("utf8mb4");
?>