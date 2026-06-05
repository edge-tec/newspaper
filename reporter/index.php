<?php
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

$uid = $_SESSION['user_id'];

$totalArticles = $pdo->prepare("SELECT COUNT(*) FROM posts WHERE author_id = ?"); $totalArticles->execute([$uid]); $totalArticles = $totalArticles->fetchColumn();
$draftCount    = $pdo->prepare("SELECT COUNT(*) FROM posts WHERE author_id = ? AND status = 'draft'"); $draftCount->execute([$uid]); $draftCount = $draftCount->fetchColumn();
$pendingCount  = $pdo->prepare("SELECT COUNT(*) FROM posts WHERE author_id = ? AND status = 'pending'"); $pendingCount->execute([$uid]); $pendingCount = $pendingCount->fetchColumn();
$publishedCount = $pdo->prepare("SELECT COUNT(*) FROM posts WHERE author_id = ? AND status = 'published'"); $publishedCount->execute([$uid]); $publishedCount = $publishedCount->fetchColumn();
$rejectedCount = $pdo->prepare("SELECT COUNT(*) FROM posts WHERE author_id = ? AND status = 'rejected'"); $rejectedCount->execute([$uid]); $rejectedCount = $rejectedCount->fetchColumn();
$totalViews    = $pdo->prepare("SELECT COALESCE(SUM(views),0) FROM posts WHERE author_id = ? AND status = 'published'"); $totalViews->execute([$uid]); $totalViews = $totalViews->fetchColumn();

// Recent articles
$recentStmt = $pdo->prepare("
    SELECT p.id, p.title, p.status, p.views, p.created_at, c.name as category_name
    FROM posts p LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.author_id = ? ORDER BY p.created_at DESC LIMIT 5
");
$recentStmt->execute([$uid]);
$recentArticles = $recentStmt->fetchAll();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Welcome, <?php echo h($_SESSION['user_name']); ?>!</h1>
    <a href="create.php" class="btn btn-danger"><i class="bi bi-pencil-square me-1"></i> Write New Article</a>
</div>

<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 bg-primary text-white shadow-sm h-100">
            <div class="card-body text-center py-3">
                <div class="fs-3 fw-bold"><?php echo $totalArticles; ?></div>
                <div class="small">Total</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 bg-secondary text-white shadow-sm h-100">
            <div class="card-body text-center py-3">
                <div class="fs-3 fw-bold"><?php echo $draftCount; ?></div>
                <div class="small">Drafts</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 bg-warning text-dark shadow-sm h-100">
            <div class="card-body text-center py-3">
                <div class="fs-3 fw-bold"><?php echo $pendingCount; ?></div>
                <div class="small">Pending</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 bg-success text-white shadow-sm h-100">
            <div class="card-body text-center py-3">
                <div class="fs-3 fw-bold"><?php echo $publishedCount; ?></div>
                <div class="small">Published</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 bg-danger text-white shadow-sm h-100">
            <div class="card-body text-center py-3">
                <div class="fs-3 fw-bold"><?php echo $rejectedCount; ?></div>
                <div class="small">Rejected</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 bg-info text-white shadow-sm h-100">
            <div class="card-body text-center py-3">
                <div class="fs-3 fw-bold"><?php echo number_format($totalViews); ?></div>
                <div class="small">Total Views</div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Articles -->
<h4 class="mb-3">Recent Articles</h4>
<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th>Title</th>
                <th>Category</th>
                <th>Status</th>
                <th>Views</th>
                <th>Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($recentArticles) === 0): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">No articles yet. <a href="create.php" class="text-danger">Write your first article!</a></td></tr>
            <?php endif; ?>
            <?php foreach ($recentArticles as $art): ?>
            <tr>
                <td class="fw-semibold"><?php echo h($art['title']); ?></td>
                <td><?php echo h($art['category_name']); ?></td>
                <td><?php echo getStatusBadge($art['status']); ?></td>
                <td><?php echo number_format($art['views']); ?></td>
                <td class="text-muted small"><?php echo timeAgo($art['created_at']); ?></td>
                <td>
                    <?php if (in_array($art['status'], ['draft', 'rejected'])): ?>
                        <a href="edit.php?id=<?php echo $art['id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once 'includes/footer.php'; ?>
