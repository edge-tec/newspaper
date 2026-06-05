<?php
require_once '../includes/header.php';
require_once '../includes/sidebar.php';
requireAdmin();

$feeds = $pdo->query("
    SELECT r.*, c.name as category_name
    FROM rss_feeds r LEFT JOIN categories c ON r.category_id = c.id
    ORDER BY r.source_name ASC
")->fetchAll();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">RSS Feed Sources</h1>
    <a href="create.php" class="btn btn-sm btn-outline-primary"><i class="bi bi-plus-lg me-1"></i> Add Feed</a>
</div>

<?php flashMessages(); ?>

<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead class="table-light"><tr><th>Source</th><th>Feed URL</th><th>Category</th><th>Status</th><th>Last Fetched</th><th>Actions</th></tr></thead>
        <tbody>
        <?php if (count($feeds) === 0): ?>
            <tr><td colspan="6" class="text-center text-muted py-4">No RSS feeds configured yet.</td></tr>
        <?php endif; ?>
        <?php foreach ($feeds as $feed): ?>
        <tr>
            <td class="fw-semibold"><?php echo h($feed['source_name']); ?></td>
            <td class="small text-break" style="max-width:250px;"><?php echo h($feed['feed_url']); ?></td>
            <td><?php echo h($feed['category_name'] ?? '—'); ?></td>
            <td><?php echo getStatusBadge($feed['status']); ?></td>
            <td class="small text-muted"><?php echo $feed['last_fetched'] ? timeAgo($feed['last_fetched']) : 'Never'; ?></td>
            <td>
                <a href="edit.php?id=<?php echo $feed['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                <a href="delete.php?id=<?php echo $feed['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this feed?');">Delete</a>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>
