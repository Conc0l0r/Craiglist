<?php

header('Content-Type: application/json; charset=utf-8');

// Catch output or fatal errors 
try {
    require_once __DIR__ . '/../config/config.php';
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => 'Config error: ' . $e->getMessage()]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$credential = $data['credential'] ?? '';

if (empty($credential)) {
    echo json_encode(['success' => false, 'message' => 'No credential provided']);
    exit;
}

$parts = explode('.', $credential);
if (count($parts) !== 3) {
    echo json_encode(['success' => false, 'message' => 'Invalid token format']);
    exit;
}

$payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[1])), true);
if (!$payload) {
    echo json_encode(['success' => false, 'message' => 'Failed to decode token']);
    exit;
}

$google_id = $payload['sub'] ?? '';
$email = $payload['email'] ?? '';
$name = $payload['name'] ?? '';
$picture = $payload['picture'] ?? '';

if (empty($google_id) || empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Missing user information']);
    exit;
}

try {
    if (!isset($conn) || !$conn) {
        echo json_encode(['success' => false, 'message' => 'Database not connected']);
        exit;
    }

    // Check user table exists 
    $check = $conn->query("SHOW TABLES LIKE 'users'");
    if (!$check || $check->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Database not set up. Please run init_database.php first.']);
        exit;
    }

        // google pfp URL
        $colCheck = $conn->query("SHOW COLUMNS FROM users LIKE 'google_picture'");
        if ($colCheck && $colCheck->num_rows === 0) {
            $conn->query("ALTER TABLE users ADD COLUMN google_picture VARCHAR(512) NULL AFTER picture");
        }

    $stmt = $conn->prepare("SELECT * FROM users WHERE google_id = ?");
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        exit;
    }
    $stmt->bind_param("s", $google_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if ($user) {
        $stmt = $conn->prepare("UPDATE users SET email = ?, name = ?, picture = ?, google_picture = ? WHERE google_id = ?");
        if (!$stmt) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
            exit;
        }
        $stmt->bind_param("sssss", $email, $name, $picture, $picture, $google_id);
        $stmt->execute();
        $stmt->close();
        $user_id = $user['id'];
    } else {
        $stmt = $conn->prepare("INSERT INTO users (google_id, email, name, picture, google_picture) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
            exit;
        }
        $stmt->bind_param("sssss", $google_id, $email, $name, $picture, $picture);
        $stmt->execute();
        $user_id = $conn->insert_id;
        $stmt->close();
    }

   
    if (!empty($picture) && strpos($picture, 'http') === 0) {
        $allowed_types = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp'
        ];
        $upload_dir = __DIR__ . '/../uploads/avatars/';
        if (!is_dir($upload_dir)) {
            @mkdir($upload_dir, 0755, true);
        }

        $imageData = false;
        
        if (function_exists('curl_version')) {
            $ch = curl_init($picture);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            $imageData = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($imageData === false || $httpCode >= 400) {
                $imageData = false;
            }
        } else {
            $imageData = @file_get_contents($picture);
            if ($imageData === false) $imageData = false;
        }

        if ($imageData !== false) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->buffer($imageData);
            if (isset($allowed_types[$mime])) {
                $ext = $allowed_types[$mime];
                $filename = (int)$user_id . '_' . time() . '.' . $ext;
                $path = $upload_dir . $filename;
                if (@file_put_contents($path, $imageData) !== false) {

                    // Update picture

                    $localPath = 'uploads/avatars/' . $filename;
                    $u = $conn->prepare("UPDATE users SET picture = ? WHERE id = ?");
                    if ($u) {
                        $u->bind_param('si', $localPath, $user_id);
                        $u->execute();
                        $u->close();
                        $picture = $localPath;
                    }
                }
            }
        }
    }

    $_SESSION['user_id'] = (int) $user_id;
    $_SESSION['google_id'] = $google_id;

    echo json_encode(['success' => true, 'message' => 'Login successful']);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
