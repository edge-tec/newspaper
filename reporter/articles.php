<?php
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

$statusFilter = $_GET['status'] ?? 'all';
$uid = $_SESSION['user_id'];

$validStatuses = ['all', 'draft', 'pending', 'published', 'rejected'];
if (!in_array($statusFilter, $validStatuses)) $statusFilter = 'all';

if ($statusFilter === 'all') {
    $stmt = $pdo->prepare("
        SELECT p.id, p.title, p.slug, p.status, p.views, p.rejection_reason, p.created_at, c.name as category_name
        FROM posts p LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.author_id = ? ORDER BY p.created_at DESC
    ");
    $stmt->execute([$uid]);
} else {
    $stmt = $pdo->prepare("
        SELECT p.id, p.title, p.slug, p.status, p.views, p.rejection_reason, p.created_at, c.name as category_name
        FROM posts p LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.author_id = ? AND p.status = ? ORDER BY p.created_at DESC
    ");
    $stmt->execute([$uid, $statusFilter]);
}
$articles = $stmt->fetchAll();

$statusLabels = [
    'all'       => 'All Articles',
    'draft'     => 'Drafts',
    'pending'   => 'Pending Approval',
    'published' => 'Published',
    'rejected'  => 'Rejected',
];
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2"><?php echo $statusLabels[$statusFilter]; ?></h1>
    <a href="create.php" class="btn btn-danger btn-sm"><i class="bi bi-pencil-square me-1"></i> Write New</a>
</div>

<?php flashMessages(); ?>

<!-- Status tabs -->
<ul class="nav nav-pills mb-4">
    <?php foreach ($statusLabels as $key => $label): ?>
    <li class="nav-item">
        <a class="nav-link <?php echo $statusFilter === $key ? 'active bg-danger border-danger' : 'text-dark'; ?>"
           href="?status=<?php echo $key; ?>"><?php echo $label; ?></a>
    </li>
    <?php endforeach; ?>
</ul>

<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th>Title</th>
                <th>Category</th>
                <th>Status</th>
                <th>Views</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($articles) === 0): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">No articles found.</td></tr>
            <?php endif; ?>
            <?php foreach ($articles as $art): ?>
            <tr>
                <td>
                    <span class="fw-semibold"><?php echo h($art['title']); ?></span>
                    <?php if ($art['status'] === 'rejected' && $art['rejection_reason']): ?>
                        <br><small class="text-danger"><i class="bi bi-exclamation-circle"></i> <?php echo h($art['rejection_reason']); ?></small>
                    <?php endif; ?>
                </td>
                <td><?php echo h($art['category_name']); ?></td>
                <td><?php echo getStatusBadge($art['status']); ?></td>
                <td><?php echo number_format($art['views']); ?></td>
                <td class="small text-muted"><?php echo timeAgo($art['created_at']); ?></td>
                <td>
                    <?php if (in_array($art['status'], ['draft', 'rejected'])): ?>
                        <a href="edit.php?id=<?php echo $art['id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                    <?php endif; ?>
                    <?php if ($art['status'] === 'published'): ?>
                        <a href="<?php echo SITE_URL; ?>/news/<?php echo h($art['slug']); ?>" class="btn btn-sm btn-outline-info" target="_blank">View</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once 'includes/footer.php'; ?>
