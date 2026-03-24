<?php
require_once 'config/config.php';

// Create job tables
ensureJobTablesExist();


$category_filter = isset($_GET['category']) ? $_GET['category'] : 'all';


if ($category_filter === 'all') {
    $page_title = 'Home - Craigslist';
} else {
    $page_title = htmlspecialchars($category_filter) . ' Jobs - Craigslist';
}

$jobs = [];
$db_ready = true;
$db_error = null;

    if ($category_filter === 'all') {
    //  all tables 
    $allTables = getAllJobTables();

    // Ensure tables exist 
    foreach ($allTables as $table) {
        $safe_table = $conn->real_escape_string($table);
        $check = $conn->query("SHOW TABLES LIKE '$safe_table'");
        if (!$check || $check->num_rows === 0) {
            $db_ready = false;
            $db_error = "Database tables are missing. Please run init_database.php once.";
            break;
        }
    }

    if ($db_ready) {
        foreach ($allTables as $table) {
            $category = getCategoryFromTable($table);
            $safe_category = $conn->real_escape_string($category);
            $safe_table = $conn->real_escape_string($table);
            $query = "SELECT *, '$safe_category' AS category, '$safe_table' AS table_name FROM `$table` ORDER BY created_at DESC";
            $result = $conn->query($query);
            if (!$result) {
                $db_error = $conn->error;
                break;
            }
            while ($row = $result->fetch_assoc()) {
                $jobs[] = $row;
            }
        }
    }

    // Sort all jobs 
    usort($jobs, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
} else {
    // Query specific table
    $tableName = getTableName($category_filter);
    if ($tableName) {
        $safe_table = $conn->real_escape_string($tableName);
        $check = $conn->query("SHOW TABLES LIKE '$safe_table'");
        if (!$check || $check->num_rows === 0) {
            $db_ready = false;
            $db_error = "Database tables are missing. Please run init_database.php once.";
        } else {
            $safe_category = $conn->real_escape_string($category_filter);
            $query = "SELECT *, '$safe_category' AS category, '$safe_table' AS table_name FROM `$tableName` ORDER BY created_at DESC";
            $result = $conn->query($query);
            if (!$result) {
                $db_error = $conn->error;
            } else {
                $jobs = [];
                while ($row = $result->fetch_assoc()) {
                    $jobs[] = $row;
                }
            }
        }
    }
}

include 'includes/header.php';
?>

<main>
    <div class="container">
        <h1 class="page-title">Find Your Dream Job</h1>
  
      <div class="filter-section">
            <h3 style="margin-bottom: 1rem;">Filter by :</h3>
            <div class="filter-buttons">
                <a href="index.php" class="filter-btn <?php echo $category_filter === 'all' ? 'active' : ''; ?>">All</a>
                <a href="index.php?category=IT" class="filter-btn <?php echo $category_filter === 'IT' ? 'active' : ''; ?>">IT</a>
               <a href="index.php?category=Marketing" class="filter-btn <?php echo $category_filter === 'Marketing' ? 'active' : ''; ?>">Marketing</a>
                <a href="index.php?category=Accounting" class="filter-btn <?php echo $category_filter === 'Accounting' ? 'active' : ''; ?>">Accounting</a>
                    <a href="index.php?category=Medical" class="filter-btn <?php echo $category_filter === 'Medical' ? 'active' : ''; ?>">Medical</a>
            </div>
        </div> 

        <?php if (!$db_ready): ?>
            <div style="text-align: center; padding: 3rem;">
                <p style="font-size: 1.2rem; color: #666;">
                    <?php echo htmlspecialchars($db_error ?: 'Database is not ready yet.'); ?>
                </p>
                <p style="margin-top: 1rem;">
                    <a class="btn" href="init_database.php">Initialize Database</a>
                </p>
            </div>
        <?php elseif (empty($jobs)): ?>
            <div style="text-align: center; padding: 3rem;">
                <p style="font-size: 1.2rem; color: #666;">No jobs found. Be the first to <a href="post.php">post a job</a>!</p>
            </div>
        <?php else: ?>
            <div class="jobs-grid">
                <?php foreach ($jobs as $job): ?>
                    <?php
                        $isVacant = !isset($job['is_vacant']) || (int)$job['is_vacant'] === 1;
                        $isOwnJob = isLoggedIn() && isset($job['user_id']) && (int)$job['user_id'] === (int)$_SESSION['user_id'];
                        $tableName = isset($job['table_name']) ? $job['table_name'] : getTableName($job['category'] ?? '');
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
                                    <a href="login.php?redirect=<?php echo urlencode('ad-detail.php?id=' . $job['id'] . '&table=' . $tableName); ?>" style="color: #b492d4; text-decoration: none;">
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
