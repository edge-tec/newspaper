<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

// Only Admin & Editor can access admin panel
requireAdminOrEditor();

// Basic statistics
$stats = [
    'users' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'categories' => $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn(),
    'posts_published' => $pdo->query("SELECT COUNT(*) FROM posts WHERE status = 'published'")->fetchColumn(),
    'posts_pending' => $pdo->query("SELECT COUNT(*) FROM posts WHERE status = 'pending'")->fetchColumn(),
    'rss_sources' => $pdo->query("SELECT COUNT(*) FROM rss_sources")->fetchColumn(),
    'rss_news' => $pdo->query("SELECT COUNT(*) FROM aggregated_news")->fetchColumn(),
];

// Recent Posts
$recentPosts = $pdo->query("
    SELECT p.title, p.created_at, p.status, u.name as author_name 
    FROM posts p LEFT JOIN users u ON p.author_id = u.id 
    ORDER BY p.created_at DESC LIMIT 5
")->fetchAll();

// Recent External News
$recentExternal = $pdo->query("
    SELECT title, source_name, published_at 
    FROM aggregated_news 
    ORDER BY published_at DESC LIMIT 5
")->fetchAll();
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center mb-4">
        <h1 class="h3 fw-bold">ড্যাশবোর্ড</h1>
        <?php if ($stats['posts_pending'] > 0): ?>
            <a href="posts/index.php?status=pending" class="btn btn-warning fw-bold">
                <i class="bi bi-bell-fill"></i> <?php echo en2bn($stats['posts_pending']); ?> টি সংবাদ অপেক্ষমান
            </a>
        <?php endif; ?>
    </div>

    <?php displayFlash(); ?>

    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase mb-2">প্রকাশিত সংবাদ</h6>
                    <h3 class="fw-bold mb-0"><?php echo en2bn($stats['posts_published']); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase mb-2">RSS সংবাদ</h6>
                    <h3 class="fw-bold mb-0"><?php echo en2bn($stats['rss_news']); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase mb-2">ক্যাটাগরি</h6>
                    <h3 class="fw-bold mb-0"><?php echo en2bn($stats['categories']); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase mb-2">ব্যবহারকারী</h6>
                    <h3 class="fw-bold mb-0"><?php echo en2bn($stats['users']); ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold">সাম্প্রতিক নিজস্ব সংবাদ</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>শিরোনাম</th>
                                    <th>অবস্থা</th>
                                    <th>লেখক</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentPosts as $post): ?>
                                <tr>
                                    <td><?php echo h($post['title']); ?><br><small class="text-muted"><?php echo timeAgo($post['created_at']); ?></small></td>
                                    <td><?php echo getStatusBadge($post['status']); ?></td>
                                    <td class="small"><?php echo h($post['author_name']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold">সাম্প্রতিক RSS সংবাদ</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>শিরোনাম</th>
                                    <th>উৎস</th>
                                    <th>সময়</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentExternal as $post): ?>
                                <tr>
                                    <td>
                                        <div class="text-truncate" style="max-width: 200px;" title="<?php echo h($post['title']); ?>">
                                            <?php echo h($post['title']); ?>
                                        </div>
                                    </td>
                                    <td><span class="badge bg-secondary"><?php echo h($post['source_name']); ?></span></td>
                                    <td class="small text-muted"><?php echo $post['published_at'] ? timeAgo($post['published_at']) : '-'; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
