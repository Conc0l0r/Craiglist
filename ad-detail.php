<?php
require_once 'config/config.php';
requireLogin();

$page_title = 'Job Details - Craigslist';

// Handle vacancy toggle from detail view
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_vacancy'], $_POST['job_id'], $_POST['table'])) {
    $jobId = (int)$_POST['job_id'];
    $table = $_POST['table'];
    $allowedTables = getAllJobTables();

    if ($jobId > 0 && in_array($table, $allowedTables, true)) {
        $safeTable = $conn->real_escape_string($table);
        $stmt = $conn->prepare("UPDATE `$safeTable` SET is_vacant = CASE WHEN is_vacant = 1 THEN 0 ELSE 1 END WHERE id = ? AND user_id = ?");
        if ($stmt) {
            $stmt->bind_param("ii", $jobId, $_SESSION['user_id']);
            $stmt->execute();
            $stmt->close();
        }
    }

    header('Location: ad-detail.php?id=' . $jobId . '&table=' . urlencode($table));
    exit;
}

$job_id = (int)($_GET['id'] ?? 0);
$job = null;
$category = null;
$jobTable = null;


$requestedTable = $_GET['table'] ?? null;
$allTables = getAllJobTables();
$tablesToSearch = [];

if ($requestedTable && in_array($requestedTable, $allTables, true)) {
    $tablesToSearch = [$requestedTable];
} else {
    $tablesToSearch = $allTables;
}

foreach ($tablesToSearch as $table) {
    $stmt = $conn->prepare("SELECT j.*, u.name as user_name, u.username as user_username, u.picture as user_picture, u.id as user_id FROM `$table` j JOIN users u ON j.user_id = u.id WHERE j.id = ?");
    if (!$stmt) continue;
    $stmt->bind_param("i", $job_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if (!$result) { $stmt->close(); continue; }
    $foundJob = $result->fetch_assoc();
    $stmt->close();
    if ($foundJob) {
        $job = $foundJob;
        $job['category'] = getCategoryFromTable($table);
        $jobTable = $table;
        break;
    }
}

if (!$job) {
    header('Location: index.php');
    exit;
}

include 'includes/header.php';

$isVacant = !isset($job['is_vacant']) || (int)$job['is_vacant'] === 1;
$isOwnJob = isLoggedIn() && isset($_SESSION['user_id']) && (int)$job['user_id'] === (int)$_SESSION['user_id'];
?>

<main>
    <div class="container">
        <div class="form-container" style="max-width: 800px;">
            <div class="detail-header">
                <h1 class="page-title">
                    <?php echo htmlspecialchars($job['post']); ?>
                </h1>
                <div class="detail-vacancy-controls">
                    <div class="vacancy-badge <?php echo $isVacant ? 'vacancy-badge--available' : 'vacancy-badge--filled'; ?>">
                        <?php echo $isVacant ? '🟢 Vacancy available' : '🔴 No vacancy'; ?>
                    </div>
                    <?php if ($isOwnJob && $jobTable): ?>
                        <form method="POST" action="">
                            <input type="hidden" name="job_id" value="<?php echo (int)$job_id; ?>">
                            <input type="hidden" name="table" value="<?php echo htmlspecialchars($jobTable); ?>">
                            <button type="submit" name="toggle_vacancy" class="btn btn-vacancy-toggle">
                                <?php echo $isVacant ? 'Mark as Filled' : 'Mark as Vacant'; ?>
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
            
            <div style="margin-bottom: 2rem;">
                <h2 style="color: #b492d4; margin-bottom: 1rem;"><?php echo htmlspecialchars($job['company']); ?></h2>
                
                <div class="info-item" style="margin-bottom: 1rem;">
                    <span class="info-label">Category:</span>
                    <span class="info-value"><?php echo htmlspecialchars($job['category']); ?></span>
                </div>
                
                <?php if (!$isVacant && !$isOwnJob): ?>
                    <div style="margin-top: 1.5rem; padding: 1rem; background-color: #f8d7da; border-radius: 5px; color: #721c24;">
                        This position is no longer vacant. Contact details and vacancy information are hidden.
                    </div>
                <?php else: ?>
                    <div class="info-item" style="margin-bottom: 1rem;">
                        <span class="info-label">Salary:</span>
                        <span class="info-value" style="font-size: 1.1rem; font-weight: 600;"><?php echo htmlspecialchars($job['salary']); ?></span>
                    </div>
                    
                    <div class="info-item" style="margin-bottom: 1rem;">
                        <span class="info-label">Vacancy From:</span>
                        <span class="info-value"><?php echo date('M d, Y', strtotime($job['vacancy_from'])); ?></span>
                    </div>

                    <?php if (!empty($job['contact_email'])): ?>
                    <div class="info-item" style="margin-bottom: 1rem;">
                        <span class="info-label">Contact Email:</span>
                        <span class="info-value">
                            <a href="mailto:<?php echo htmlspecialchars($job['contact_email']); ?>">
                                <?php echo htmlspecialchars($job['contact_email']); ?>
                            </a>
                        </span>
                    </div>
                    <?php endif; ?>
                    
                    <div style="margin-top: 2rem;">
                        <h3 style="margin-bottom: 0.5rem;">Requirements:</h3>
                        <div style="background-color: #f8f9fa; padding: 1rem; border-radius: 5px; white-space: pre-wrap;"><?php echo htmlspecialchars($job['requirements']); ?></div>
                    </div>
                <?php endif; ?>
                
                <div style="margin-top: 2rem; padding-top: 1rem; border-top: 1px solid #e0e0e0;">
                    <p style="color: #666; font-size: 0.9rem;">
                        Posted by: <a href="user-profile.php?id=<?php echo (int)$job['user_id']; ?>"><?php echo htmlspecialchars(getDisplayName(['name' => $job['user_name'], 'username' => $job['user_username'] ?? ''])); ?></a> on <?php echo date('M d, Y', strtotime($job['created_at'])); ?>
                    </p>
                </div>
            </div>
            
            <div>
                <a href="index.php" class="btn btn-secondary">Back to Jobs</a>
                <?php if ($job['category'] === 'IT'): ?>
                    <a href="it.php" class="btn">View More IT Jobs</a>
                <?php elseif ($job['category'] === 'Marketing'): ?>
                    <a href="marketing.php" class="btn">View More Marketing Jobs</a>
                <?php elseif ($job['category'] === 'Accounting'): ?>
                    <a href="accounting.php" class="btn">View More Accounting Jobs</a>
                <?php elseif ($job['category'] === 'Medical'): ?>
                    <a href="medical.php" class="btn">View More Medical Jobs</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
