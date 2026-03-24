<?php
require_once 'config/config.php';
requireLogin();

$page_title = 'My Profile - Craigslist';

$user = getCurrentUser();

// vacancy toggle 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_vacancy'], $_POST['job_id'], $_POST['table'])) {
    $jobId = (int)$_POST['job_id'];
    $table = $_POST['table'];
    $allowedTables = getAllJobTables();

    if ($jobId > 0 && in_array($table, $allowedTables, true)) {
        $safeTable = $conn->real_escape_string($table);
        // Only allow toggling own jobs
        $stmt = $conn->prepare("UPDATE `$safeTable` SET is_vacant = CASE WHEN is_vacant = 1 THEN 0 ELSE 1 END WHERE id = ? AND user_id = ?");
        if ($stmt) {
            $stmt->bind_param("ii", $jobId, $_SESSION['user_id']);
            $stmt->execute();
            $stmt->close();
        }
    }
}

// newest post first
$user_jobs = [];
$allTables = getAllJobTables();
foreach ($allTables as $table) {
    $category = getCategoryFromTable($table);
    $stmt = $conn->prepare("SELECT *, ? as category FROM `$table` WHERE user_id = ? ORDER BY created_at DESC");
    if (!$stmt) continue;
    $stmt->bind_param("si", $category, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if (!$result) { $stmt->close(); continue; }
    while ($row = $result->fetch_assoc()) {
        $user_jobs[] = $row;
    }
    $stmt->close();
}
// Sort all jobs 
usort($user_jobs, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});

include 'includes/header.php';
?>

<main>
    <div class="container">
        <div class="profile-header">
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
                <img src="<?php echo htmlspecialchars($pic_url); ?>" alt="Profile" class="profile-avatar">
            <?php endif; ?>
            <h1><?php echo htmlspecialchars(getDisplayName($user)); ?></h1>
            <p class="profile-email"><?php echo htmlspecialchars($user['email']); ?></p>
            <?php if (!empty($user['degree']) || !empty($user['university'])): ?>
                <p class="profile-education" style="color: #555; margin-top: 0.5rem;">
                    <?php
                    $parts = array_filter([$user['degree'] ?? '', $user['university'] ?? '']);
                    echo htmlspecialchars(implode(' · ', $parts));
                    ?>
                </p>
            <?php endif; ?>
            <p><a href="profile-edit.php" class="btn" style="margin-top: 0.5rem;">Edit profile</a></p>
        </div>

        <div id="my-posts">
            <h2 class="page-title">Your Posts</h2>
            
            <?php if (empty($user_jobs)): ?>
                <div style="text-align: center; padding: 3rem;">
                    <p style="font-size: 1.2rem; color: #666;">You haven't posted any jobs yet. <a href="post.php">Post your first job</a>!</p>
                </div>
            <?php else: ?>
                <div class="jobs-grid">
                    <?php foreach ($user_jobs as $job): ?>
                        <?php
                            $isVacant = !isset($job['is_vacant']) || (int)$job['is_vacant'] === 1;
                            $tableName = getTableName($job['category'] ?? '');
                        ?>
                        <div class="job-card" data-job-id="<?php echo $job['id']; ?>" data-table="<?php echo htmlspecialchars($tableName); ?>">
                            <div class="job-card-header">
                                <h3><?php echo htmlspecialchars($job['company']); ?></h3>
                                <div class="vacancy-badge <?php echo $isVacant ? 'vacancy-badge--available' : 'vacancy-badge--filled'; ?>">
                                    <?php echo $isVacant ? '🟢 Vacancy available' : '🔴 No vacancy'; ?>
                                </div>
                            </div>
                            <div class="post">
                                <?php echo htmlspecialchars($job['post']); ?>
                            </div>
                            <div class="full-info">
                                <div class="info-item">
                                    <span class="info-label">Category:</span>
                                    <span class="info-value"><?php echo htmlspecialchars($job['category']); ?></span>
                                </div>
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
                                <div class="info-item">
                                    <span class="info-label">Posted:</span>
                                    <span class="info-value"><?php echo date('M d, Y', strtotime($job['created_at'])); ?></span>
                                </div>
                                <?php if (!empty($job['contact_email'])): ?>
                                <div class="info-item">
                                    <span class="info-label">Contact Email:</span>
                                    <span class="info-value"><?php echo htmlspecialchars($job['contact_email']); ?></span>
                                </div>
                                <?php endif; ?>
                                <div class="info-item">
                                    <form method="POST" action="" style="margin-top: 0.5rem;">
                                        <input type="hidden" name="job_id" value="<?php echo (int)$job['id']; ?>">
                                        <input type="hidden" name="table" value="<?php echo htmlspecialchars($job['category'] ? getTableName($job['category']) : ''); ?>">
                                        <button type="submit" name="toggle_vacancy" class="btn" style="padding: 0.4rem 0.8rem; font-size: 0.85rem; margin-top: 0.3rem;">
                                            <?php echo $isVacant ? 'Mark as Filled' : 'Mark as Vacant'; ?>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
