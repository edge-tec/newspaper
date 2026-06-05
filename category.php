<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/functions.php';

$slug = $_GET['slug'] ?? null;
if (!$slug) redirect('/');

$stmt = $pdo->prepare("SELECT id, name FROM categories WHERE slug = ? AND status = 'active'");
$stmt->execute([$slug]);
$category = $stmt->fetch();
if (!$category) { header("HTTP/1.0 404 Not Found"); echo "Category not found."; exit; }

$pageTitle = $category['name'];

// Pagination for internal posts
$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;
$offset  = ($page - 1) * $perPage;

$totalStmt = $pdo->prepare("SELECT COUNT(*) FROM posts WHERE category_id = ? AND status = 'published'");
$totalStmt->execute([$category['id']]);
$total      = $totalStmt->fetchColumn();
$totalPages = ceil($total / $perPage);

$stmt = $pdo->prepare("SELECT title, slug, short_description, image, created_at FROM posts WHERE category_id = ? AND status = 'published' ORDER BY created_at DESC LIMIT $perPage OFFSET $offset");
$stmt->execute([$category['id']]);
$posts = $stmt->fetchAll();

// External news for this category
$extStmt = $pdo->prepare("SELECT id, title, excerpt, source_name, original_url, published_at FROM aggregated_news WHERE category_id = ? ORDER BY published_at DESC LIMIT 5");
$extStmt->execute([$category['id']]);
$externalPosts = $extStmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-8">
            <h2 class="fw-bold text-uppercase pb-2 mb-4 border-bottom border-danger border-2"><?php echo h($category['name']); ?></h2>

            <?php if (count($posts) > 0): ?>
                <?php foreach ($posts as $post): ?>
                <div class="row g-0 mb-4 pb-4 border-bottom">
                    <div class="col-4 col-md-3">
                        <img src="<?php echo $post['image'] ? SITE_URL . '/' . $post['image'] : 'https://via.placeholder.com/300x200/eee/999?text=News'; ?>"
                             class="w-100 h-100 rounded" style="object-fit:cover; max-height:150px;" alt="" loading="lazy">
                    </div>
                    <div class="col-8 col-md-9 ps-3">
                        <h5 class="fw-bold lh-sm"><a href="<?php echo SITE_URL; ?>/news/<?php echo h($post['slug']); ?>" class="text-dark text-decoration-none"><?php echo h($post['title']); ?></a></h5>
                        <p class="text-muted small mb-1 d-none d-md-block"><?php echo h($post['short_description']); ?></p>
                        <small class="text-muted"><?php echo timeAgo($post['created_at']); ?></small>
                    </div>
                </div>
                <?php endforeach; ?>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                <nav><ul class="pagination justify-content-center">
                    <li class="page-item <?php echo $page<=1?'disabled':''; ?>"><a class="page-link" href="?slug=<?php echo h($slug); ?>&page=<?php echo $page-1; ?>">Prev</a></li>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?php echo $page==$i?'active':''; ?>"><a class="page-link" href="?slug=<?php echo h($slug); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a></li>
                    <?php endfor; ?>
                    <li class="page-item <?php echo $page>=$totalPages?'disabled':''; ?>"><a class="page-link" href="?slug=<?php echo h($slug); ?>&page=<?php echo $page+1; ?>">Next</a></li>
                </ul></nav>
                <?php endif; ?>
            <?php else: ?>
                <p class="text-muted">No internal news found in this category yet.</p>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- External Headlines for this category -->
            <?php if (count($externalPosts) > 0): ?>
            <div class="bg-white rounded-3 shadow-sm p-4 mb-4">
                <h5 class="fw-bold text-uppercase pb-2 mb-3 border-bottom border-secondary border-2" style="font-size:.9rem;">
                    <i class="bi bi-globe2 me-1"></i> External Headlines
                </h5>
                <ul class="list-unstyled mb-0">
                    <?php foreach ($externalPosts as $ep): ?>
                    <li class="mb-3 pb-3 border-bottom">
                        <h6 class="fw-bold lh-sm mb-1"><a href="<?php echo SITE_URL; ?>/external-news.php?id=<?php echo $ep['id']; ?>" class="text-dark text-decoration-none"><?php echo h($ep['title']); ?></a></h6>
                        <small class="text-muted"><span class="badge bg-light text-dark border"><?php echo h($ep['source_name']); ?></span></small>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <!-- More Categories -->
            <div class="bg-white rounded-3 shadow-sm p-4 mb-4">
                <h5 class="fw-bold text-uppercase pb-2 mb-3 border-bottom border-danger border-2" style="font-size:.9rem;">More Categories</h5>
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
