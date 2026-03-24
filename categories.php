<?php
require_once 'config/config.php';

$page_title = 'Categories - Craigslist';

include 'includes/header.php';
?>

<main>
    <div class="container">
        <h1 class="page-title">Browse by Category</h1>
        
        <div class="jobs-grid" style="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));">
            <a href="it.php" class="job-card" style="text-decoration: none; display: block;">
                <h3 style="color: #b492d4;">IT & Technology</h3>
                <p>Find jobs in software development, IT support, and technology roles.</p>
            </a>
            
            <a href="marketing.php" class="job-card" style="text-decoration: none; display: block;">
                <h3 style="color: #b492d4;">Marketing</h3>
                <p>Explore marketing, advertising, and communications positions.</p>
            </a>
            
            <a href="accounting.php" class="job-card" style="text-decoration: none; display: block;">
                <h3 style="color: #b492d4;">Accounting</h3>
                <p>Discover finance, accounting, and auditing opportunities.</p>
            </a>
            
            <a href="medical.php" class="job-card" style="text-decoration: none; display: block;">
                <h3 style="color: #b492d4;">Medical & Healthcare</h3>
                <p>Browse healthcare, nursing, and medical professional jobs.</p>
            </a>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
