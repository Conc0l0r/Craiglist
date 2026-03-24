    <footer class="footer" style="background-color: #b492d4;">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Craigslist</h3>
                    <p>Find your dream job today!</p>
                </div>
                <div class="footer-section">
                    <h4>Categories</h4>
                    <ul>
                        <li><a href="it.php">IT</a></li>
                        <li><a href="marketing.php">Marketing</a></li>
                        <li><a href="accounting.php">Accounting</a></li>
                        <li><a href="medical.php">Medical</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="categories.php">All Categories</a></li>
                        <?php if (isLoggedIn()): ?>
                            <li><a href="post.php">Post Job</a></li>
                            <li><a href="profile.php">Profile</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> Craigslist. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>
