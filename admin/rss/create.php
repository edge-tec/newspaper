<?php
require_once '../includes/header.php';
require_once '../includes/sidebar.php';
requireAdmin();

$categories = $pdo->query("SELECT id, name FROM categories WHERE status = 'active' ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    $source_name = trim($_POST['source_name'] ?? '');
    $feed_url    = trim($_POST['feed_url'] ?? '');
    $category_id = $_POST['category_id'] ?? null;
    $status      = $_POST['status'] ?? 'active';

    if (empty($source_name) || empty($feed_url)) {
        $_SESSION['error'] = 'Source name and feed URL are required.';
    } elseif (!filter_var($feed_url, FILTER_VALIDATE_URL)) {
        $_SESSION['error'] = 'Please enter a valid URL.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO rss_feeds (source_name, feed_url, category_id, status) VALUES (?,?,?,?)");
        if ($stmt->execute([$source_name, $feed_url, $category_id ?: null, $status])) {
            $_SESSION['success'] = 'RSS feed added successfully.';
            redirect('/admin/rss/index.php');
        } else {
            $_SESSION['error'] = 'Failed to add feed.';
        }
    }
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Add RSS Feed</h1>
    <a href="index.php" class="btn btn-sm btn-outline-secondary">Back</a>
</div>

<?php flashMessages(); ?>

<div class="row"><div class="col-md-6">
<form method="POST">
    <input type="hidden" name="csrf_token" value="<?php echo h(generateCsrfToken()); ?>">

    <div class="mb-3">
        <label class="form-label">Source Name <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="source_name" placeholder="e.g. Prothom Alo" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Feed URL <span class="text-danger">*</span></label>
        <input type="url" class="form-control" name="feed_url" placeholder="https://example.com/rss.xml" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Category (optional)</label>
        <select class="form-select" name="category_id">
            <option value="">— General —</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?php echo $cat['id']; ?>"><?php echo h($cat['name']); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">Status</label>
        <select class="form-select" name="status">
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Save Feed</button>
</form>
</div></div>

<?php require_once '../includes/footer.php'; ?>
