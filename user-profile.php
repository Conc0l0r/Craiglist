<?php
require_once 'config/config.php';

$page_title = 'User profile - Craigslist';
$profile_id = (int)($_GET['id'] ?? 0);
$profile_user = null;

if ($profile_id > 0) {
    $stmt = $conn->prepare("SELECT id, name, username, picture, email, degree, university FROM users WHERE id = ?");
    $stmt->bind_param("i", $profile_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $profile_user = $result->fetch_assoc();
    $stmt->close();
}

if (!$profile_user) {
    header('Location: index.php');
    exit;
}

// Get this user's job posts from all tables
$user_jobs = [];
$allTables = getAllJobTables();
foreach ($allTables as $table) {
    $category = getCategoryFromTable($table);
    $stmt = $conn->prepare("SELECT *, ? as category FROM `$table` WHERE user_id = ? ORDER BY created_at DESC");
    if (!$stmt) continue;
    $stmt->bind_param("si", $category, $profile_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if (!$result) { $stmt->close(); continue; }
    while ($row = $result->fetch_assoc()) {
        $user_jobs[] = $row;
    }
    $stmt->close();
}
usort($user_jobs, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});

$is_own_profile = isLoggedIn() && $_SESSION['user_id'] == $profile_id;

include 'includes/header.php';
?>

<main>
    <div class="container">
        <div class="profile-header" style="text-align: center; padding: 2rem 0;">
            <?php
            $pic_url = getPictureUrl($profile_user['picture'] ?? '');
            if ($pic_url): ?>
                <img src="<?php echo htmlspecialchars($pic_url); ?>" alt="Profile" class="profile-avatar" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover;">
            <?php else: ?>
                <div style="width: 100px; height: 100px; border-radius: 50%; background: #b492d4; margin: 0 auto; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 2.5rem;"><?php echo strtoupper(substr(getDisplayName($profile_user), 0, 1)); ?></div>
            <?php endif; ?>
            <h1><?php echo htmlspecialchars(getDisplayName($profile_user)); ?></h1>
            <?php if (!empty($profile_user['username'])): ?>
                <p style="color: #666;">@<?php echo htmlspecialchars($profile_user['username']); ?></p>
            <?php endif; ?>
            <?php if (!empty($profile_user['degree']) || !empty($profile_user['university'])): ?>
                <p style="color: #555; margin-top: 0.5rem;">
                    <?php
                    $parts = array_filter([$profile_user['degree'] ?? '', $profile_user['university'] ?? '']);
                    echo htmlspecialchars(implode(' · ', $parts));
                    ?>
                </p>
            <?php endif; ?>
            <?php if ($is_own_profile): ?>
                <p><a href="profile-edit.php" class="btn" style="margin-top: 0.5rem;">Edit profile</a></p>
            <?php endif; ?>
        </div>

        <h2 class="page-title">Posted jobs</h2>
        <?php if (empty($user_jobs)): ?>
            <p style="text-align: center; color: #666; padding: 2rem;">No job posts yet.</p>
        <?php else: ?>
            <div class="jobs-grid">
                <?php foreach ($user_jobs as $job): ?>
                    <?php
                        $tableName = getTableName($job['category']);
                        $isVacant = !isset($job['is_vacant']) || (int)$job['is_vacant'] === 1;
                    ?>
                    <a href="ad-detail.php?id=<?php echo (int)$job['id']; ?>&table=<?php echo urlencode($tableName); ?>" class="job-card" style="text-decoration: none; color: inherit;" data-job-id="<?php echo $job['id']; ?>" data-table="<?php echo htmlspecialchars($tableName); ?>">
                        <div class="job-card-header">
                            <h3><?php echo htmlspecialchars($job['company']); ?></h3>
                            <div class="vacancy-badge <?php echo $isVacant ? 'vacancy-badge--available' : 'vacancy-badge--filled'; ?>">
                                <?php echo $isVacant ? '🟢 Vacancy available' : '🔴 No vacancy'; ?>
                            </div>
                        </div>
                        <div class="post"><?php echo htmlspecialchars($job['post']); ?></div>
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
                                <span class="info-label">Vacancy From:</span>
                                <span class="info-value"><?php echo date('M d, Y', strtotime($job['vacancy_from'])); ?></span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
