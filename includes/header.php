<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Craigslist'; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <script>
        // Google OAuth Client ID 
        window.GOOGLE_CLIENT_ID = '<?php echo defined("GOOGLE_CLIENT_ID") ? GOOGLE_CLIENT_ID : "273035518472-d7pcp71l8jqn0fulf12ul4lp9h2lvd4e.apps.googleusercontent.com"; ?>';
    </script>
</head>
<body>
    <header class="header" style="background-color: #b492d4;">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <h1>
                        <a href="index.php">
                            <img src="logo.png" alt="Craigslist logo" width="24" height="24">
                            CRAIGSLIST
                        </a>
                    </h1>
                </div>
                <nav class="nav">
                    <a href="index.php">Home</a>
                    <a href="categories.php">Categories</a>
                    <a href="search-users.php">Find users</a>
                    <?php if (isLoggedIn()): ?>
                        <a href="post.php">Post Job</a>
                        <a href="profile.php">Profile</a>
                        <a href="logout.php" id="logout-link" class="nav-logout">Logout</a>
                    <?php else: ?>
                        <a href="login.php" style="padding: 0.5rem 1rem; background-color: rgba(255,255,255,0.2); border-radius: 4px; transition: background-color 0.3s;">Login</a>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    </header>

<?php if (isLoggedIn()): ?>
<div class="dashboard" style="background-color: #b492d4;">
    <div class="container">
        <div class="dashboard-content">
            <?php
                $user = getCurrentUser();

                
                $picture = $user['picture'] ?? '';
                $googlePic = $user['google_picture'] ?? '';
                $finalPic = '';

                // If user uploaded a local file
                if (!empty($picture) && strpos($picture, 'uploads/avatars/') === 0) {
                    $finalPic = rtrim(BASE_URL, '/') . '/' . ltrim($picture, '/');
                }
                // EGoogle avatar if available
                elseif (!empty($googlePic)) {
                    $finalPic = preg_replace('/=s\d+-c$/', '=s200-c', $googlePic);
                }
                //  remote URL (non-Google)
                elseif (!empty($picture) && strpos($picture, 'http') === 0) {
                    $finalPic = preg_replace('/=s\d+-c$/', '=s200-c', $picture);
                } else {
                    $finalPic = rtrim(BASE_URL, '/') . '/logo.png';
                }
            ?>
            
            <div class="user-info">
                <img src="<?php echo htmlspecialchars($finalPic); ?>"
                     width="100"
                     height="100"
                     alt="Profile"
                     class="user-avatar"
                     style="border-radius:50%; object-fit:cover;">

                <?php if (isLoggedIn()): ?>
                    
                    <div style="display:none;" id="avatar-debug">
                        <?php
                        echo "<!-- finalPic:" . htmlspecialchars($finalPic) . " -->\n";
                        echo "<!-- picture:" . htmlspecialchars($user['picture'] ?? '') . " -->\n";
                        echo "<!-- google_picture:" . htmlspecialchars($user['google_picture'] ?? '') . " -->\n";
                        ?>
                    </div>
                <?php endif; ?>

                <span>
                    Welcome, <?php echo htmlspecialchars(getDisplayName($user)); ?>!
                </span>
            </div>

            <div class="dashboard-links">
                <a href="profile.php">My Profile</a>
                <a href="profile-edit.php">Edit Profile</a>
                <a href="search-users.php">Find Users</a>
                <a href="post.php">Post a Job</a>
            </div>

        </div>
    </div>
</div>
<?php endif; ?>

    <?php if (isLoggedIn()): ?>
    <!-- Logout confirmation -->
    <div id="logout-modal" class="logout-modal" style="display: none;" aria-hidden="true">
        <div class="logout-modal-backdrop"></div>
        <div class="logout-modal-box">
            <p class="logout-modal-text">Are you sure you want to log out?</p>
            <div class="logout-modal-actions">
                <button type="button" class="btn logout-modal-cancel">Cancel</button>
                <a href="logout.php" class="btn logout-modal-confirm">Log out</a>
            </div>
        </div>
    </div>
    <script>
    (function() {
        var link = document.getElementById('logout-link');
        var modal = document.getElementById('logout-modal');
        if (link && modal) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                modal.classList.add('logout-modal-open');
                modal.style.display = 'flex';
                modal.style.alignItems = 'center';
                modal.style.justifyContent = 'center';
                modal.setAttribute('aria-hidden', 'false');
            });
            function closeLogoutModal() {
                modal.classList.remove('logout-modal-open');
                modal.style.display = 'none';
                modal.setAttribute('aria-hidden', 'true');
            }
            modal.querySelector('.logout-modal-backdrop').addEventListener('click', closeLogoutModal);
            modal.querySelector('.logout-modal-cancel').addEventListener('click', closeLogoutModal);
        }
    })();
    </script>
    <?php endif; ?>
