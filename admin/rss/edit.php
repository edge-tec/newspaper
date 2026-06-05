<?php
require_once '../includes/header.php';
require_once '../includes/sidebar.php';
requireAdmin();

$id = $_GET['id'] ?? null;
if (!$id) redirect('/admin/rss/index.php');

$stmt = $pdo->prepare("SELECT * FROM rss_feeds WHERE id = ?");
$stmt->execute([$id]);
$feed = $stmt->fetch();
if (!$feed) redirect('/admin/rss/index.php');

$categories = $pdo->query("SELECT id, name FROM categories WHERE status = 'active' ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    $source_name = trim($_POST['source_name'] ?? '');
    $feed_url    = trim($_POST['feed_url'] ?? '');
    $category_id = $_POST['category_id'] ?? null;
    $status      = $_POST['status'] ?? 'active';

    if (empty($source_name) || empty($feed_url)) {
        $_SESSION['error'] = 'Source name and feed URL are required.';
    } else {
        $stmt = $pdo->prepare("UPDATE rss_feeds SET source_name=?, feed_url=?, category_id=?, status=? WHERE id=?");
        if ($stmt->execute([$source_name, $feed_url, $category_id ?: null, $status, $id])) {
            $_SESSION['success'] = 'RSS feed updated.';
            redirect('/admin/rss/index.php');
        }
    }
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Edit RSS Feed</h1>
    <a href="index.php" class="btn btn-sm btn-outline-secondary">Back</a>
</div>

<?php flashMessages(); ?>

<div class="row"><div class="col-md-6">
<form method="POST">
    <input type="hidden" name="csrf_token" value="<?php echo h(generateCsrfToken()); ?>">

    <div class="mb-3">
        <label class="form-label">Source Name</label>
        <input type="text" class="form-control" name="source_name" value="<?php echo h($feed['source_name']); ?>" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Feed URL</label>
        <input type="url" class="form-control" name="feed_url" value="<?php echo h($feed['feed_url']); ?>" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Category</label>
        <select class="form-select" name="category_id">
            <option value="">— General —</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?php echo $cat['id']; ?>" <?php echo $feed['category_id'] == $cat['id'] ? 'selected' : ''; ?>><?php echo h($cat['name']); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">Status</label>
        <select class="form-select" name="status">
            <option value="active" <?php echo $feed['status']==='active'?'selected':''; ?>>Active</option>
            <option value="inactive" <?php echo $feed['status']==='inactive'?'selected':''; ?>>Inactive</option>
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Update Feed</button>
</form>
</div></div>

<?php require_once '../includes/footer.php'; ?>
