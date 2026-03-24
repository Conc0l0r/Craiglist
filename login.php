<?php
require_once 'config/config.php';

$page_title = 'Login - Craigslist';

// If already logged in, redirect to index
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

include 'includes/header.php';
?>

<main>
    <div class="container">
        <div class="login-container">
            <h2>Login to Continue</h2>
            <p style="margin-bottom: 2rem; color: #666;">Please sign in with your Google account to view full job details and post jobs.</p>
            <div id="google-signin-button"></div>
            <p style="margin-top: 2rem; font-size: 0.9rem; color: #666;">
                After logging in, you'll be redirected back to where you left off.
            </p>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
