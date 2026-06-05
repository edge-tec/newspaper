<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

$userId = $_SESSION['user_id'];

// Get counts
$stmt = $pdo->prepare("SELECT status, COUNT(*) as count FROM posts WHERE author_id = ? GROUP BY status");
$stmt->execute([$userId]);
$counts = ['published' => 0, 'pending' => 0, 'draft' => 0, 'rejected' => 0];
$totalPosts = 0;
while ($row = $stmt->fetch()) {
    $counts[$row['status']] = $row['count'];
    $totalPosts += $row['count'];
}

// Get total views for published posts
$stmt = $pdo->prepare("SELECT SUM(views) FROM posts WHERE author_id = ? AND status = 'published'");
$stmt->execute([$userId]);
$totalViews = $stmt->fetchColumn() ?: 0;

// Recent posts
$stmt = $pdo->prepare("
    SELECT title, slug, status, created_at, views 
    FROM posts WHERE author_id = ? 
    ORDER BY created_at DESC LIMIT 5
");
$stmt->execute([$userId]);
$recentPosts = $stmt->fetchAll();
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pb-5">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-4 pb-2 mb-4 border-bottom">
        <h1 class="h3 fw-bold">আমার ড্যাশবোর্ড</h1>
        <div>
            <a href="create.php" class="btn btn-danger"><i class="bi bi-pencil-square"></i> নতুন খবর লিখুন</a>
        </div>
    </div>

    <?php displayFlash(); ?>

    <div class="row g-4 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm bg-white h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="text-muted fw-normal mb-0">সর্বমোট খবর</h6>
                        <div class="bg-primary bg-opacity-10 text-primary rounded p-2"><i class="bi bi-journal-text fs-5"></i></div>
                    </div>
                    <h3 class="fw-bold mb-0"><?php echo en2bn($totalPosts); ?></h3>
                </div>
            </div>
        </div>
        
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm bg-white h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="text-muted fw-normal mb-0">প্রকাশিত</h6>
                        <div class="bg-success bg-opacity-10 text-success rounded p-2"><i class="bi bi-check2-circle fs-5"></i></div>
                    </div>
                    <h3 class="fw-bold mb-0"><?php echo en2bn($counts['published']); ?></h3>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm bg-white h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="text-muted fw-normal mb-0">অপেক্ষমান</h6>
                        <div class="bg-warning bg-opacity-10 text-warning rounded p-2"><i class="bi bi-hourglass-split fs-5"></i></div>
                    </div>
                    <h3 class="fw-bold mb-0"><?php echo en2bn($counts['pending']); ?></h3>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm bg-white h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="text-muted fw-normal mb-0">মোট পঠিত (Views)</h6>
                        <div class="bg-info bg-opacity-10 text-info rounded p-2"><i class="bi bi-eye fs-5"></i></div>
                    </div>
                    <h3 class="fw-bold mb-0"><?php echo en2bn($totalViews); ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Latest Articles -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">সাম্প্রতিক খবর</h5>
            <a href="articles.php" class="btn btn-sm btn-outline-secondary">সবগুলো দেখুন</a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="px-4">শিরোনাম</th>
                        <th>অবস্থা</th>
                        <th>পঠিত</th>
                        <th>তারিখ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($recentPosts) > 0): ?>
                        <?php foreach ($recentPosts as $post): ?>
                        <tr>
                            <td class="px-4">
                                <?php if ($post['status'] == 'published'): ?>
                                    <a href="<?php echo SITE_URL; ?>/news/<?php echo h($post['slug']); ?>" target="_blank" class="text-dark fw-medium text-decoration-none"><?php echo h($post['title']); ?></a>
                                <?php else: ?>
                                    <span class="text-dark fw-medium"><?php echo h($post['title']); ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo getStatusBadge($post['status']); ?></td>
                            <td><?php echo en2bn($post['views']); ?></td>
                            <td class="text-muted small"><?php echo timeAgo($post['created_at']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">আপনি এখনও কোনো খবর লেখেননি।</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
