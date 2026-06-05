<?php
require_once '../includes/header.php';
require_once '../includes/sidebar.php';
requireAdminOrEditor();

$singleId = $_GET['id'] ?? null;

// Handle approve / reject
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    $postId = $_POST['post_id'] ?? null;
    $action = $_POST['action'] ?? '';

    if ($postId) {
        if ($action === 'approve') {
            $pdo->prepare("UPDATE posts SET status = 'published', rejection_reason = NULL WHERE id = ? AND status = 'pending'")->execute([$postId]);
            $_SESSION['success'] = 'Article published!';
        } elseif ($action === 'reject') {
            $reason = trim($_POST['rejection_reason'] ?? 'No reason provided.');
            $pdo->prepare("UPDATE posts SET status = 'rejected', rejection_reason = ? WHERE id = ? AND status = 'pending'")->execute([$reason, $postId]);
            $_SESSION['success'] = 'Article rejected.';
        }
    }
    redirect('/admin/posts/approve.php');
}

// Single article review
if ($singleId) {
    $stmt = $pdo->prepare("
        SELECT p.*, u.name as author_name, c.name as category_name
        FROM posts p LEFT JOIN users u ON p.author_id = u.id LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.id = ? AND p.status = 'pending'
    ");
    $stmt->execute([$singleId]);
    $article = $stmt->fetch();

    if (!$article) {
        $_SESSION['error'] = 'Article not found or already reviewed.';
        redirect('/admin/posts/approve.php');
    }
}

// List all pending
$pendingPosts = $pdo->query("
    SELECT p.id, p.title, p.created_at, u.name as author_name, c.name as category_name
    FROM posts p LEFT JOIN users u ON p.author_id = u.id LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.status = 'pending' ORDER BY p.created_at ASC
")->fetchAll();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Article Approval</h1>
</div>

<?php flashMessages(); ?>

<?php if ($singleId && isset($article)): ?>
<!-- Single Article Review -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <h3 class="fw-bold"><?php echo h($article['title']); ?></h3>
        <p class="text-muted">
            by <strong><?php echo h($article['author_name']); ?></strong>
            in <strong><?php echo h($article['category_name']); ?></strong>
            · <?php echo timeAgo($article['created_at']); ?>
        </p>

        <?php if ($article['image']): ?>
            <img src="<?php echo SITE_URL . '/' . $article['image']; ?>" class="img-fluid mb-3 rounded" style="max-height:300px; object-fit:cover;" alt="">
        <?php endif; ?>

        <?php if ($article['short_description']): ?>
            <p class="lead"><?php echo h($article['short_description']); ?></p>
        <?php endif; ?>

        <div class="border rounded p-3 bg-light mb-4" style="max-height:400px; overflow-y:auto;">
            <?php echo nl2br(h($article['content'])); ?>
        </div>

        <div class="row g-3">
            <!-- Approve -->
            <div class="col-auto">
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo h(generateCsrfToken()); ?>">
                    <input type="hidden" name="post_id" value="<?php echo $article['id']; ?>">
                    <button type="submit" name="action" value="approve" class="btn btn-success btn-lg">
                        <i class="bi bi-check-lg me-1"></i> Approve & Publish
                    </button>
                </form>
            </div>
            <!-- Reject -->
            <div class="col">
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo h(generateCsrfToken()); ?>">
                    <input type="hidden" name="post_id" value="<?php echo $article['id']; ?>">
                    <div class="input-group">
                        <input type="text" class="form-control" name="rejection_reason" placeholder="Reason for rejection (optional)">
                        <button type="submit" name="action" value="reject" class="btn btn-danger">
                            <i class="bi bi-x-lg me-1"></i> Reject
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<a href="approve.php" class="btn btn-outline-secondary">&larr; Back to Queue</a>

<?php else: ?>
<!-- Pending Queue -->
<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead class="table-light"><tr><th>Title</th><th>Author</th><th>Category</th><th>Submitted</th><th>Actions</th></tr></thead>
        <tbody>
        <?php if (count($pendingPosts) === 0): ?>
            <tr><td colspan="5" class="text-center text-muted py-4">No articles pending review. 🎉</td></tr>
        <?php endif; ?>
        <?php foreach ($pendingPosts as $pp): ?>
        <tr>
            <td class="fw-semibold"><?php echo h($pp['title']); ?></td>
            <td><?php echo h($pp['author_name']); ?></td>
            <td><?php echo h($pp['category_name']); ?></td>
            <td class="text-muted small"><?php echo timeAgo($pp['created_at']); ?></td>
            <td>
                <a href="approve.php?id=<?php echo $pp['id']; ?>" class="btn btn-sm btn-outline-primary">Review</a>
                <form method="POST" action="" class="d-inline">
                    <input type="hidden" name="csrf_token" value="<?php echo h(generateCsrfToken()); ?>">
                    <input type="hidden" name="post_id" value="<?php echo $pp['id']; ?>">
                    <button type="submit" name="action" value="approve" class="btn btn-sm btn-success">Quick Approve</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>
