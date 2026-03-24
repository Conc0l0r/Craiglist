<?php
require_once 'config/config.php';
requireLogin();

$page_title = 'Post a Job - Craigslist';

// Create job tables if not exist yet
ensureJobTablesExist();

$error = '';
$success = '';

$currentUser = getCurrentUser();
$defaultEmail = $currentUser['email'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $company = trim($_POST['company'] ?? '');
    $post = trim($_POST['post'] ?? '');
    $category = $_POST['category'] ?? '';
    $salary = trim($_POST['salary'] ?? '');
    $requirements = trim($_POST['requirements'] ?? '');
    $vacancy_from = $_POST['vacancy_from'] ?? '';
    $contact_email = trim($_POST['contact_email'] ?? $defaultEmail);

    // Validation
    if (empty($company) || empty($post) || empty($category) || 
        empty($salary) || empty($requirements) || empty($vacancy_from) || empty($contact_email)) {
        $error = 'Please fill in all fields.';
    } elseif (!filter_var($contact_email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid contact email address.';
    } else {
        $tableName = getTableName($category);
        if (!$tableName) {
            $error = 'Invalid category selected.';
        } else {
            $stmt = $conn->prepare("INSERT INTO `$tableName` (user_id, company, post, department, salary, requirements, vacancy_from, contact_email, is_vacant) VALUES (?, ?, ?, '', ?, ?, ?, ?, 1)");
            if (!$stmt) {
                $error = 'Error posting job: ' . $conn->error;
            } else {
                $stmt->bind_param("issssss", $_SESSION['user_id'], $company, $post, $salary, $requirements, $vacancy_from, $contact_email);
                if ($stmt->execute()) {
                    $success = 'Job posted successfully!';
                    $_POST = [];
                } else {
                    $error = 'Error posting job: ' . $conn->error;
                }
                $stmt->close();
            }
        }
    }
}

include 'includes/header.php';
?>

<main>
    <div class="container">
        <h1 class="page-title">Post a Job</h1>
        
        <?php if ($error): ?>
            <div style="background-color: #f8d7da; color: #721c24; padding: 1rem; border-radius: 5px; margin-bottom: 1rem;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div style="background-color: #d4edda; color: #155724; padding: 1rem; border-radius: 5px; margin-bottom: 1rem;">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST" action="">
                <div class="form-group">
                    <label for="company">Company Name *</label>
                    <input type="text" id="company" name="company" required value="<?php echo htmlspecialchars($_POST['company'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="post">Job Post/Title *</label>
                    <input type="text" id="post" name="post" required value="<?php echo htmlspecialchars($_POST['post'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="category">Category *</label>
                    <select id="category" name="category" required>
                        <option value="">Select Category</option>
                        <option value="IT" <?php echo (isset($_POST['category']) && $_POST['category'] === 'IT') ? 'selected' : ''; ?>>IT</option>
                        <option value="Marketing" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Marketing') ? 'selected' : ''; ?>>Marketing</option>
                        <option value="Accounting" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Accounting') ? 'selected' : ''; ?>>Accounting</option>
                        <option value="Medical" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Medical') ? 'selected' : ''; ?>>Medical</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="salary">Salary *</label>
                    <input type="text" id="salary" name="salary" required placeholder="e.g.70KWD - 100KWD" value="<?php echo htmlspecialchars($_POST['salary'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="contact_email">Contact Email *</label>
                    <input
                        type="email"
                        id="contact_email"
                        name="contact_email"
                        required
                        value="<?php echo htmlspecialchars($_POST['contact_email'] ?? $defaultEmail); ?>">
                </div>

                <div class="form-group">
                    <label for="requirements">Requirements *</label>
                    <textarea id="requirements" name="requirements" required><?php echo htmlspecialchars($_POST['requirements'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="vacancy_from">Vacancy From Date *</label>
                    <input type="date" id="vacancy_from" name="vacancy_from" required value="<?php echo htmlspecialchars($_POST['vacancy_from'] ?? ''); ?>">
                </div>

                <button type="submit" class="btn">Post Job</button>
                <a href="index.php" class="btn btn-secondary" style="margin-left: 1rem;">Cancel</a>
            </form>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
