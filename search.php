<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/functions.php';

$query = trim($_GET['q'] ?? '');
$pageTitle = '"' . h($query) . '" এর জন্য অনুসন্ধানের ফলাফল';

$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;
$offset  = ($page - 1) * $perPage;

$posts      = [];
$totalPages = 0;
$total      = 0;

// Also search external news
$externalResults = [];

if (!empty($query)) {
    $searchTerm = '%' . $query . '%';

    // Internal posts
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM posts WHERE status = 'published' AND (title LIKE ? OR content LIKE ?)");
    $countStmt->execute([$searchTerm, $searchTerm]);
    $total      = $countStmt->fetchColumn();
    $totalPages = ceil($total / $perPage);

    $stmt = $pdo->prepare("
        SELECT p.title, p.slug, p.short_description, p.image, p.created_at, c.name as category_name
        FROM posts p LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.status = 'published' AND (p.title LIKE ? OR p.content LIKE ?)
        ORDER BY p.created_at DESC LIMIT $perPage OFFSET $offset
    ");
    $stmt->execute([$searchTerm, $searchTerm]);
    $posts = $stmt->fetchAll();

    // External news
    $extStmt = $pdo->prepare("SELECT id, title, excerpt, source_name, published_at FROM aggregated_news WHERE title LIKE ? ORDER BY published_at DESC LIMIT 5");
    $extStmt->execute([$searchTerm]);
    $externalResults = $extStmt->fetchAll();
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-8">
            <h2 class="fw-bold text-uppercase pb-2 mb-4 border-bottom border-danger border-2">
                অনুসন্ধান: <?php echo h($query); ?>
            </h2>

            <?php if (!empty($query) && count($posts) > 0): ?>
                <p class="text-muted mb-4"><?php echo en2bn($total); ?> টি খবর পাওয়া গেছে</p>
                <?php foreach ($posts as $post): ?>
                <div class="row g-0 mb-4 pb-4 border-bottom">
                    <div class="col-4 col-md-3">
                        <img src="<?php echo $post['image'] ? SITE_URL . '/' . $post['image'] : 'https://via.placeholder.com/300x200/eee/999?text=News'; ?>"
                             class="w-100 h-100 rounded" style="object-fit:cover; max-height:150px;" alt="" loading="lazy">
                    </div>
                    <div class="col-8 col-md-9 ps-3">
                        <span class="badge bg-danger mb-2" style="font-size:.65rem;"><?php echo h($post['category_name']); ?></span>
                        <h5 class="fw-bold lh-sm"><a href="<?php echo SITE_URL; ?>/news/<?php echo h($post['slug']); ?>" class="text-dark text-decoration-none"><?php echo h($post['title']); ?></a></h5>
                        <p class="text-muted small mb-1 d-none d-md-block"><?php echo h($post['short_description']); ?></p>
                        <small class="text-muted"><?php echo timeAgo($post['created_at']); ?></small>
                    </div>
                </div>
                <?php endforeach; ?>

                <?php if ($totalPages > 1): ?>
                <nav><ul class="pagination justify-content-center">
                    <li class="page-item <?php echo $page<=1?'disabled':''; ?>"><a class="page-link" href="?q=<?php echo urlencode($query); ?>&page=<?php echo $page-1; ?>">পূর্ববর্তী</a></li>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?php echo $page==$i?'active':''; ?>"><a class="page-link" href="?q=<?php echo urlencode($query); ?>&page=<?php echo $i; ?>"><?php echo en2bn($i); ?></a></li>
                    <?php endfor; ?>
                    <li class="page-item <?php echo $page>=$totalPages?'disabled':''; ?>"><a class="page-link" href="?q=<?php echo urlencode($query); ?>&page=<?php echo $page+1; ?>">পরবর্তী</a></li>
                </ul></nav>
                <?php endif; ?>

            <?php elseif (!empty($query)): ?>
                <p class="text-muted">"<?php echo h($query); ?>" এর জন্য কোনো খবর পাওয়া যায়নি।</p>
            <?php else: ?>
                <p class="text-muted">উপরে সার্চ বক্সে কিছু লিখে অনুসন্ধান করুন।</p>
            <?php endif; ?>
        </div>

        <div class="col-lg-4">
            <!-- External search results -->
            <?php if (count($externalResults) > 0): ?>
            <div class="bg-white rounded-3 shadow-sm p-4 mb-4">
                <h5 class="fw-bold text-uppercase pb-2 mb-3 border-bottom border-secondary border-2" style="font-size:.9rem;">
                    <i class="bi bi-globe2 me-1"></i> অন্যান্য মাধ্যম থেকে
                </h5>
                <ul class="list-unstyled mb-0">
                    <?php foreach ($externalResults as $er): ?>
                    <li class="mb-3 pb-3 border-bottom">
                        <h6 class="fw-bold lh-sm mb-1"><a href="<?php echo SITE_URL; ?>/external-news.php?id=<?php echo $er['id']; ?>" class="text-dark text-decoration-none"><?php echo h($er['title']); ?></a></h6>
                        <small class="text-muted"><span class="badge bg-light text-dark border">খবর</span></small>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <div class="bg-white rounded-3 shadow-sm p-4 mb-4">
                <h5 class="fw-bold text-uppercase pb-2 mb-3 border-bottom border-danger border-2" style="font-size:.9rem;">ক্যাটাগরি</h5>
                <ul class="list-unstyled mb-0">
                    <?php foreach ($navCategories as $cat): ?>
                    <li class="mb-2"><a href="<?php echo SITE_URL; ?>/category/<?php echo h($cat['slug']); ?>" class="text-dark text-decoration-none fw-semibold"><i class="bi bi-chevron-right text-danger small"></i> <?php echo h($cat['name']); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
