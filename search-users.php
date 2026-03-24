<?php
require_once 'config/config.php';

$page_title = 'Find users - Job Board';
$q = trim($_GET['q'] ?? '');
$users = [];

if ($q !== '') {
    $search = '%' . $conn->real_escape_string($q) . '%';
    $stmt = $conn->prepare("SELECT id, name, username, picture, email FROM users WHERE name LIKE ? OR username LIKE ? OR email LIKE ? ORDER BY name ASC LIMIT 50");
    $stmt->bind_param("sss", $search, $search, $search);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    $stmt->close();
}

include 'includes/header.php';
?>

<main>
    <div class="container">
        <h1 class="page-title">Find users</h1>
        <form method="get" action="search-users.php" style="margin-bottom: 2rem; display: flex; gap: 0.5rem; max-width: 400px;">
            <input type="search" name="q" value="<?php echo htmlspecialchars($q); ?>" placeholder="Search by name, username or email..." style="flex: 1; padding: 0.6rem; border: 1px solid #ccc; border-radius: 6px;">
            <button type="submit" class="btn">Search</button>
        </form>

        <?php if ($q === ''): ?>
            <p style="color: #666;">Enter a name, username or email to search.</p>
        <?php elseif (empty($users)): ?>
            <p style="color: #666;">No users found for "<?php echo htmlspecialchars($q); ?>".</p>
        <?php else: ?>
            <div class="users-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 1rem;">
                <?php foreach ($users as $u): ?>
                    <a href="user-profile.php?id=<?php echo (int)$u['id']; ?>" class="user-card" style="display: flex; align-items: center; gap: 1rem; padding: 1rem; background: #f8f8f8; border-radius: 10px; text-decoration: none; color: inherit; border: 1px solid #eee; transition: box-shadow 0.2s;">
                        <?php
                        $pic_url = getPictureUrl($u['picture'] ?? '');
                        if ($pic_url): ?>
                            <img src="<?php echo htmlspecialchars($pic_url); ?>" alt="" style="width: 56px; height: 56px; border-radius: 50%; object-fit: cover;">
                        <?php else: ?>
                            <div style="width: 56px; height: 56px; border-radius: 50%; background: #b492d4; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: bold;"><?php echo strtoupper(substr(getDisplayName($u), 0, 1)); ?></div>
                        <?php endif; ?>
                        <div>
                            <strong style="font-size: 1.05rem;"><?php echo htmlspecialchars(getDisplayName($u)); ?></strong>
                            <?php if (!empty($u['username'])): ?>
                                <span style="color: #666; font-size: 0.9rem;">@<?php echo htmlspecialchars($u['username']); ?></span>
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
