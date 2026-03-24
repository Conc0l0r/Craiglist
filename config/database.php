<?php

$db_host = 'localhost:3307';
$db_user = 'root';
$db_pass = ''; 
$db_name = 'jobfinding';


mysqli_report(MYSQLI_REPORT_OFF);


function testConnection($host, $user, $pass) {
    $test = @mysqli_connect($host, $user, $pass);
    if ($test) {
        mysqli_close($test);
        return true;
    }
    return false;
}


if (!testConnection($db_host, $db_user, '')) {
    
    if (testConnection($db_host, $db_user, 'root')) {
        $db_pass = '';
    } else {
        die("<h2>MySQL Connection Error</h2>" .
            "<p>Could not connect to MySQL. Please check:</p>" .
            "<ol>" .
            "<li><strong>MySQL service is running</strong> - Open XAMPP Control Panel and make sure MySQL is started</li>" .
            "<li><strong>Password is correct</strong> - If your MySQL root user has a password, update <code>\$db_pass</code> in <code>config/database.php</code></li>" .
            "<li><strong>Common XAMPP passwords:</strong> '' (empty) or 'root'</li>" .
            "</ol>" .
            "<p>Current settings: User='$db_user', Password='" . (empty($db_pass) ? '(empty)' : '(set)') . "'</p>");
    }
}


$temp_conn = @new mysqli($db_host, $db_user, $db_pass);

if (!$temp_conn || $temp_conn->connect_error) {
    $error = $temp_conn ? $temp_conn->connect_error : 'Could not connect to MySQL server';
    die("<h2>Database Connection Failed</h2>" .
        "<p>Error: " . htmlspecialchars($error) . "</p>" .
        "<p>Please check your MySQL credentials in <code>config/database.php</code></p>");
}

// Create database if it doesn't exist
$temp_conn->query("CREATE DATABASE IF NOT EXISTS $db_name CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$temp_conn->close();

// connect to the specific database
$conn = @new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if (!$conn || $conn->connect_error) {
    $error = $conn ? $conn->connect_error : 'Could not connect to database';
    die("<h2>Database Connection Failed</h2>" .
        "<p>Error: " . htmlspecialchars($error) . "</p>" .
        "<p>Could not connect to database '$db_name'. Please check your MySQL settings.</p>");
}

// Set charset to utf8
$conn->set_charset("utf8mb4");
?>
