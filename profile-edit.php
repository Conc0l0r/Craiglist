<?php
require_once 'config/config.php';
requireLogin();

$page_title = 'Edit Profile - Job Board';
$user = getCurrentUser();
$error = '';
$success = '';

// Ensure username column exists 
$check = $conn->query("SHOW COLUMNS FROM users LIKE 'username'");
if ($check && $check->num_rows === 0) {
    $conn->query("ALTER TABLE users ADD COLUMN username VARCHAR(100) UNIQUE NULL AFTER name");
}
// Ensure degree and university columns exist
foreach (['degree' => 'VARCHAR(100) NULL AFTER picture', 'university' => 'VARCHAR(255) NULL AFTER degree'] as $col => $def) {
    $c = $conn->query("SHOW COLUMNS FROM users LIKE '$col'");
    if ($c && $c->num_rows === 0) {
        $conn->query("ALTER TABLE users ADD COLUMN $col $def");
    }
}

$allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$upload_dir = __DIR__ . '/uploads/avatars/';
if (!is_dir($upload_dir)) {
    @mkdir($upload_dir, 0755, true);
}

$degree_options = ['', 'High school', 'Diploma', "Bachelor's", "Master's", 'PhD'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $degree = trim($_POST['degree'] ?? '');
    $university = trim($_POST['university'] ?? '');
    $picture_url = trim($_POST['picture_url'] ?? '');
    $picture = $user['picture']; // keep current by default

    // Validate username: optional; if provided: 2–50 chars, letters, numbers, underscore   unique    
    if ($username !== '') {
        if (strlen($username) < 2 || strlen($username) > 50) {
            $error = 'Username must be between 2 and 50 characters.';
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $error = 'Username can only contain letters, numbers and underscores.';
        } else {
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
            $stmt->bind_param("si", $username, $_SESSION['user_id']);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $error = 'That username is already taken.';
            }
            $stmt->close();
        }
    }

    // Picture
    if ($error === '' && !empty($_FILES['picture_upload']['name']) && $_FILES['picture_upload']['error'] === UPLOAD_ERR_OK) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($_FILES['picture_upload']['tmp_name']);
        if (!in_array($mime, $allowed_types)) {
            $error = 'Please upload a JPG, PNG, GIF or WebP image.';
        } else {
            $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'][$mime];
            $filename = (int)$_SESSION['user_id'] . '_' . time() . '.' . $ext;
            $path = $upload_dir . $filename;
            if (move_uploaded_file($_FILES['picture_upload']['tmp_name'], $path)) {
                $picture = 'uploads/avatars/' . $filename;
            } else {
                $error = 'Could not save uploaded image.';
            }
        }
    } elseif ($error === '' && $picture_url !== '') {
        if (filter_var($picture_url, FILTER_VALIDATE_URL) && (preg_match('/\.(jpe?g|png|gif|webp)(\?|$)/i', $picture_url) || strpos($picture_url, 'googleusercontent') !== false || strpos($picture_url, 'gravatar') !== false)) {
            $picture = $picture_url;
        } else {
            $error = 'Please enter a valid image URL.';
        }
    }

    if ($error === '') {
        $stmt = $conn->prepare("UPDATE users SET username = ?, picture = ?, degree = ?, university = ? WHERE id = ?");
        if (!$stmt) {
            $error = 'Database error. Please run init_database.php once, then try again.';
        } else {
            $username_db = $username === '' ? null : $username;
            $degree_db = $degree === '' ? null : $degree;
            $university_db = $university === '' ? null : $university;
            $stmt->bind_param("ssssi", $username_db, $picture, $degree_db, $university_db, $_SESSION['user_id']);
            if ($stmt->execute()) {
                $success = 'Profile updated successfully.';
                $user = getCurrentUser();
            } else {
                $error = 'Could not update profile. Try a different username.';
            }
            $stmt->close();
        }
    }
}

include 'includes/header.php';
?>

<main>
    <div class="container">
        <div class="profile-edit-card" style="max-width: 500px; margin: 2rem auto; padding: 2rem; background: #f8f8f8; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.08);">
            <h1 class="page-title" style="margin-bottom: 1.5rem;">Edit Profile</h1>
            <?php if ($error): ?>
                <p class="error-msg" style="color: #c00; margin-bottom: 1rem;"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
            <?php if ($success): ?>
                <p class="success-msg" style="color: #0a0; margin-bottom: 1rem;"><?php echo htmlspecialchars($success); ?></p>
            <?php endif; ?>

            <div class="profile-preview" style="text-align: center; margin-bottom: 1.5rem;">
                <?php
                $picture = $user['picture'] ?? '';
                $googlePic = $user['google_picture'] ?? '';
                $pic_url = '';

                if (!empty($picture) && strpos($picture, 'uploads/avatars/') === 0) {
                    $pic_url = rtrim(BASE_URL, '/') . '/' . ltrim($picture, '/');
                } elseif (!empty($googlePic)) {
                    $pic_url = preg_replace('/=s\d+-c$/', '=s200-c', $googlePic);
                } elseif (!empty($picture) && strpos($picture, 'http') === 0) {
                    $pic_url = preg_replace('/=s\d+-c$/', '=s200-c', $picture);
                }

                if ($pic_url): ?>
                    <img src="<?php echo htmlspecialchars($pic_url); ?>" alt="Profile" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover;">
                <?php else: ?>
                    <div style="width: 80px; height: 80px; border-radius: 50%; background: #b492d4; margin: 0 auto; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 2rem;"><?php echo strtoupper(substr(getDisplayName($user), 0, 1)); ?></div>
                <?php endif; ?>
            </div>

            <form method="post" enctype="multipart/form-data">
                <div style="margin-bottom: 1rem;">
                    <label for="username" style="display: block; margin-bottom: 0.3rem; font-weight: 600;">Username (display name)</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" placeholder="e.g. johndoe" maxlength="50" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px;">
                    <small style="color: #666;">Letters, numbers, underscores. Leave blank to use your Google name.</small>
                </div>
                <div style="margin-bottom: 1rem;">
                    <label for="degree" style="display: block; margin-bottom: 0.3rem; font-weight: 600;">Degree (optional)</label>
                    <select id="degree" name="degree" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px;">
                        <option value="">— Select if applicable —</option>
                        <?php foreach (array_slice($degree_options, 1) as $opt): ?>
                            <option value="<?php echo htmlspecialchars($opt); ?>" <?php echo (isset($user['degree']) && $user['degree'] === $opt) ? 'selected' : ''; ?>><?php echo htmlspecialchars($opt); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="margin-bottom: 1rem;">
                    <label for="university" style="display: block; margin-bottom: 0.3rem; font-weight: 600;">University / Institution (optional)</label>
                    <input type="text" id="university" name="university" value="<?php echo htmlspecialchars($user['university'] ?? ''); ?>" placeholder="e.g. State University" maxlength="255" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px;">
                </div>
                <div style="margin-bottom: 1.5rem;">
                    <label for="picture_upload" style="display: block; margin-bottom: 0.3rem; font-weight: 600;">Or upload a photo</label>
                    <input type="file" id="picture_upload" name="picture_upload" accept=".jpg,.jpeg,.png,.gif,.webp">
                </div>
                <div style="display: flex; gap: 0.5rem;">
                    <button type="submit" class="btn" style="padding: 0.6rem 1.2rem;">Save changes</button>
                    <a href="profile.php" class="btn" style="padding: 0.6rem 1.2rem; background: #666; text-decoration: none; color: #fff;">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>


