<?php
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

// Stats
$postsCount      = $pdo->query("SELECT COUNT(*) FROM posts")->fetchColumn();
$publishedCount  = $pdo->query("SELECT COUNT(*) FROM posts WHERE status='published'")->fetchColumn();
$pendingCount    = $pdo->query("SELECT COUNT(*) FROM posts WHERE status='pending'")->fetchColumn();
$categoriesCount = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$usersCount      = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalViews      = $pdo->query("SELECT COALESCE(SUM(views),0) FROM posts")->fetchColumn();
$rssCount        = $pdo->query("SELECT COUNT(*) FROM rss_feeds")->fetchColumn();
$aggregatedCount = $pdo->query("SELECT COUNT(*) FROM aggregated_news")->fetchColumn();

// Recent posts
$recentPosts = $pdo->query("
    SELECT p.id, p.title, p.status, p.created_at, c.name as category_name, u.name as author_name
    FROM posts p LEFT JOIN categories c ON p.category_id = c.id LEFT JOIN users u ON p.author_id = u.id
    ORDER BY p.created_at DESC LIMIT 8
")->fetchAll();

// Pending articles
$pendingPosts = $pdo->query("
    SELECT p.id, p.title, p.created_at, u.name as author_name
    FROM posts p LEFT JOIN users u ON p.author_id = u.id
    WHERE p.status = 'pending' ORDER BY p.created_at ASC LIMIT 5
")->fetchAll();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Dashboard</h1>
</div>

<?php flashMessages(); ?>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card text-white bg-primary border-0 shadow-sm"><div class="card-body py-3">
            <div class="d-flex justify-content-between"><span>Total Posts</span><i class="bi bi-file-text fs-4"></i></div>
            <h3 class="fw-bold mt-1"><?php echo number_format($postsCount); ?></h3>
        </div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-dark bg-warning border-0 shadow-sm"><div class="card-body py-3">
            <div class="d-flex justify-content-between"><span>Pending Review</span><i class="bi bi-hourglass-split fs-4"></i></div>
            <h3 class="fw-bold mt-1"><?php echo number_format($pendingCount); ?></h3>
        </div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-white bg-success border-0 shadow-sm"><div class="card-body py-3">
            <div class="d-flex justify-content-between"><span>Published</span><i class="bi bi-check-circle fs-4"></i></div>
            <h3 class="fw-bold mt-1"><?php echo number_format($publishedCount); ?></h3>
        </div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-white bg-info border-0 shadow-sm"><div class="card-body py-3">
            <div class="d-flex justify-content-between"><span>Total Views</span><i class="bi bi-eye fs-4"></i></div>
            <h3 class="fw-bold mt-1"><?php echo number_format($totalViews); ?></h3>
        </div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card bg-light border-0 shadow-sm"><div class="card-body py-3">
            <div class="d-flex justify-content-between"><span>Categories</span><i class="bi bi-tags fs-4"></i></div>
            <h3 class="fw-bold mt-1"><?php echo $categoriesCount; ?></h3>
        </div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card bg-light border-0 shadow-sm"><div class="card-body py-3">
            <div class="d-flex justify-content-between"><span>Users</span><i class="bi bi-people fs-4"></i></div>
            <h3 class="fw-bold mt-1"><?php echo $usersCount; ?></h3>
        </div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card bg-light border-0 shadow-sm"><div class="card-body py-3">
            <div class="d-flex justify-content-between"><span>RSS Feeds</span><i class="bi bi-rss fs-4"></i></div>
            <h3 class="fw-bold mt-1"><?php echo $rssCount; ?></h3>
        </div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card bg-light border-0 shadow-sm"><div class="card-body py-3">
            <div class="d-flex justify-content-between"><span>External News</span><i class="bi bi-globe fs-4"></i></div>
            <h3 class="fw-bold mt-1"><?php echo number_format($aggregatedCount); ?></h3>
        </div></div>
    </div>
</div>

<div class="row">
    <!-- Pending Approval -->
    <div class="col-lg-5 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-warning text-dark fw-semibold d-flex justify-content-between">
                <span><i class="bi bi-hourglass-split me-1"></i> Pending Approval</span>
                <a href="posts/approve.php" class="text-dark">View All</a>
            </div>
            <div class="list-group list-group-flush">
                <?php if (count($pendingPosts) === 0): ?>
                    <div class="list-group-item text-muted text-center py-3">No pending articles</div>
                <?php endif; ?>
                <?php foreach ($pendingPosts as $pp): ?>
                <a href="posts/approve.php?id=<?php echo $pp['id']; ?>" class="list-group-item list-group-item-action">
                    <div class="fw-semibold"><?php echo h($pp['title']); ?></div>
                    <small class="text-muted">by <?php echo h($pp['author_name']); ?> · <?php echo timeAgo($pp['created_at']); ?></small>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Recent Posts -->
    <div class="col-lg-7 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Recent Posts</div>
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead><tr><th>Title</th><th>Author</th><th>Status</th><th>Date</th></tr></thead>
                    <tbody>
                    <?php foreach ($recentPosts as $rp): ?>
                    <tr>
                        <td><?php echo h($rp['title']); ?></td>
                        <td class="text-muted small"><?php echo h($rp['author_name']); ?></td>
                        <td><?php echo getStatusBadge($rp['status']); ?></td>
                        <td class="text-muted small"><?php echo timeAgo($rp['created_at']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
