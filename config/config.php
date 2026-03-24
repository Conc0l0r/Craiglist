<?php
session_start();


define('GOOGLE_CLIENT_ID', getenv('GOOGLE_CLIENT_ID'));
define('GOOGLE_CLIENT_SECRET', getenv('GOOGLE_CLIENT_SECRET'));
define('GOOGLE_REDIRECT_URI', 'http://localhost/jobfinding/auth/google_callback.php');


define('BASE_URL', 'http://localhost/craiglist');

// database connection
require_once __DIR__ . '/database.php';

// check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// get current user
function getCurrentUser() {
    global $conn;
    if (!isLoggedIn()) {
        return null;
    }
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Display username 
function getDisplayName($user) {
    if (!$user) return '';
    $u = trim($user['username'] ?? '');
    return $u !== '' ? $u : trim($user['name'] ?? '');
}

// profile picture 
function getPictureUrl($picture) {
    if (empty($picture)) return '';
    if (strpos($picture, 'http') === 0) return $picture;
    return rtrim(BASE_URL, '/') . '/' . ltrim($picture, '/');
}

//  require login
function requireLogin() {
    if (!isLoggedIn()) {
        $redirect = $_SERVER['REQUEST_URI'];
        header("Location: login.php?redirect=" . urlencode($redirect));
        exit;
    }
}

//  table name from category
function getTableName($category) {
    $tables = [
        'IT' => 'jobs_it',
        'Marketing' => 'jobs_marketing',
        'Accounting' => 'jobs_accounting',
        'Medical' => 'jobs_medical'
    ];
    return $tables[$category] ?? null;
}

// H get all job tables
function getAllJobTables() {
    return ['jobs_it', 'jobs_marketing', 'jobs_accounting', 'jobs_medical'];
}

//  get category from table name
function getCategoryFromTable($tableName) {
    $categories = [
        'jobs_it' => 'IT',
        'jobs_marketing' => 'Marketing',
        'jobs_accounting' => 'Accounting',
        'jobs_medical' => 'Medical'
    ];
    return $categories[$tableName] ?? null;
}

// Create a job table if it doesn't exist 
function ensureJobTableExists($conn, $tableName) {
    $safe = $conn->real_escape_string($tableName);
    $check = $conn->query("SHOW TABLES LIKE '$safe'");
    if ($check && $check->num_rows > 0) return true;
    $sql = "CREATE TABLE IF NOT EXISTS `$tableName` (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        company VARCHAR(255) NOT NULL,
        post VARCHAR(255) NOT NULL,
        department VARCHAR(255) NOT NULL,
        salary VARCHAR(255) NOT NULL,
        requirements TEXT NOT NULL,
        vacancy_from DATE NOT NULL,
        contact_email VARCHAR(255) NOT NULL,
        is_vacant TINYINT(1) NOT NULL DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX idx_user (user_id),
        INDEX idx_created (created_at DESC)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    return (bool) $conn->query($sql);
}

// all job tables exist
function ensureJobTablesExist() {
    global $conn;
    foreach (getAllJobTables() as $table) {
        $safeTable = $conn->real_escape_string($table);

        // Create table if missing
        $check = $conn->query("SHOW TABLES LIKE '$safeTable'");
        if (!$check || $check->num_rows === 0) {
            ensureJobTableExists($conn, $table);
        }

        // new columns exist 
        $colCheck = $conn->query("SHOW COLUMNS FROM `$safeTable` LIKE 'contact_email'");
        if ($colCheck && $colCheck->num_rows === 0) {
            $conn->query("ALTER TABLE `$safeTable` ADD COLUMN contact_email VARCHAR(255) NOT NULL DEFAULT '' AFTER vacancy_from");
        }

        $vacancyCheck = $conn->query("SHOW COLUMNS FROM `$safeTable` LIKE 'is_vacant'");
        if ($vacancyCheck && $vacancyCheck->num_rows === 0) {
            $conn->query("ALTER TABLE `$safeTable` ADD COLUMN is_vacant TINYINT(1) NOT NULL DEFAULT 1 AFTER contact_email");
        }
    }
}
?>
