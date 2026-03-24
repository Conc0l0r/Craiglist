<?php
require_once 'config/config.php';

$page_title = 'Accounting - Craigslist';

$jobs = [];
$table = 'jobs_accounting';
if (isset($conn)) {
    $check = $conn->query("SHOW TABLES LIKE '$table'");
    if ($check && $check->num_rows > 0) {
        $result = $conn->query("SELECT *, 'Accounting' AS category FROM `$table` ORDER BY created_at DESC");
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $jobs[] = $row;
            }
        }
    }
}

include 'includes/header.php';
?>

<main>
    <div class="container">
        <h1 class="page-title">Accounting Jobs</h1>
        
        <?php if (empty($jobs)): ?>
            <div style="text-align: center; padding: 3rem;">
                <p style="font-size: 1.2rem; color: #666;">No Accounting jobs found. <a href="post.php">Post one now</a>!</p>
            </div>
        <?php else: ?>
            <div class="jobs-grid">
                <?php foreach ($jobs as $job): ?>
                    <?php
                        $isVacant = !isset($job['is_vacant']) || (int)$job['is_vacant'] === 1;
                        $isOwnJob = isLoggedIn() && isset($job['user_id']) && (int)$job['user_id'] === (int)$_SESSION['user_id'];
                    ?>
                    <div class="job-card" data-job-id="<?php echo $job['id']; ?>" data-table="<?php echo htmlspecialchars($table); ?>">
                        <div class="job-card-header">
                            <h3><?php echo htmlspecialchars($job['company']); ?></h3>
                            <div class="vacancy-badge <?php echo $isVacant ? 'vacancy-badge--available' : 'vacancy-badge--filled'; ?>">
                                <?php echo $isVacant ? '🟢 Vacancy available' : '🔴 No vacancy'; ?>
                            </div>
                        </div>
                        <div class="post">
                            <?php echo htmlspecialchars($job['post']); ?>
                        </div>
                        <div class="department">Department: <?php echo htmlspecialchars($job['department']); ?></div>
                        
                        <?php if (!$isVacant && !$isOwnJob): ?>
                            <div class="restricted-info">
                                <span style="color: #a00; font-weight: 500;">This position is no longer vacant.</span>
                            </div>
                        <?php else: ?>
                            <?php if (isLoggedIn()): ?>
                                <div class="full-info">
                                    <div class="info-item">
                                        <span class="info-label">Salary:</span>
                                        <span class="info-value"><?php echo htmlspecialchars($job['salary']); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Requirements:</span>
                                        <span class="info-value"><?php echo htmlspecialchars($job['requirements']); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Vacancy From:</span>
                                        <span class="info-value"><?php echo date('M d, Y', strtotime($job['vacancy_from'])); ?></span>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="restricted-info">
                                    <a href="login.php?redirect=<?php echo urlencode('ad-detail.php?id=' . $job['id'] . '&table=' . $table); ?>" style="color: #b492d4; text-decoration: none;">
                                        Login to view salary, requirements, and vacancy details
                                    </a>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
