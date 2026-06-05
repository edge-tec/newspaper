<?php
$pageTitle = 'Home';
require_once __DIR__ . '/includes/header.php';

// ── Hero: Top 5 published internal posts ──
$heroPosts = $pdo->query("
    SELECT p.title, p.slug, p.image, p.created_at, c.name as category_name
    FROM posts p LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.status = 'published'
    ORDER BY p.created_at DESC LIMIT 5
")->fetchAll();
$mainHero  = $heroPosts[0] ?? null;
$subHeroes = array_slice($heroPosts, 1, 4);

// ── Trending (most viewed internal posts) ──
$trendingPosts = $pdo->query("
    SELECT title, slug, image, views, created_at
    FROM posts WHERE status = 'published'
    ORDER BY views DESC LIMIT 5
")->fetchAll();

// ── Latest internal news feed ──
$latestFeed = $pdo->query("
    SELECT p.title, p.slug, p.short_description, p.image, p.created_at, c.name as category_name
    FROM posts p LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.status = 'published'
    ORDER BY p.created_at DESC LIMIT 10 OFFSET 5
")->fetchAll();

// ── Category sections (internal + external) ──
$categorySections = $pdo->query("SELECT id, name, slug FROM categories WHERE status = 'active' LIMIT 6")->fetchAll();

// ── External headlines ──
$externalNews = $pdo->query("
    SELECT id, title, excerpt, image, source_name, original_url, published_at
    FROM aggregated_news
    ORDER BY published_at DESC LIMIT 8
")->fetchAll();
?>

<div class="container my-4">

    <!-- ═══ HERO SECTION ═══ -->
    <?php if ($mainHero): ?>
    <div class="row g-3 mb-5">
        <div class="col-lg-8">
            <div class="position-relative overflow-hidden rounded-3" style="height:460px;">
                <img src="<?php echo $mainHero['image'] ? SITE_URL . '/' . $mainHero['image'] : 'https://via.placeholder.com/800x460/333/fff?text=News'; ?>"
                     class="w-100 h-100" style="object-fit:cover;" alt="<?php echo h($mainHero['title']); ?>" loading="eager">
                <div class="position-absolute bottom-0 start-0 end-0 p-4" style="background:linear-gradient(to top, rgba(0,0,0,.85), transparent);">
                    <span class="badge bg-danger mb-2"><?php echo h($mainHero['category_name']); ?></span>
                    <h2 class="text-white fw-bold mb-1" style="font-family:'Playfair Display',serif;">
                        <a href="<?php echo SITE_URL; ?>/news/<?php echo h($mainHero['slug']); ?>" class="text-white text-decoration-none"><?php echo h($mainHero['title']); ?></a>
                    </h2>
                    <small class="text-white-50">
                        <?php echo en2bn(date('j', strtotime($mainHero['created_at']))) . ' ' . $bnMonths[date('F', strtotime($mainHero['created_at']))] . ' ' . en2bn(date('Y', strtotime($mainHero['created_at']))); ?>
                    </small>
                </div>
            </div>
        </div>
        <div class="col-lg-4 d-flex flex-column gap-3">
            <?php foreach ($subHeroes as $post): ?>
            <div class="d-flex bg-white rounded-2 overflow-hidden shadow-sm flex-grow-1" style="min-height:100px;">
                <img src="<?php echo $post['image'] ? SITE_URL . '/' . $post['image'] : 'https://via.placeholder.com/120x100/eee/999?text=N'; ?>"
                     style="width:120px; object-fit:cover;" alt="" loading="lazy">
                <div class="p-2 d-flex flex-column justify-content-center">
                    <h6 class="fw-bold mb-1 lh-sm" style="font-size:.85rem;">
                        <a href="<?php echo SITE_URL; ?>/news/<?php echo h($post['slug']); ?>" class="text-dark text-decoration-none"><?php echo h($post['title']); ?></a>
                    </h6>
                    <small class="text-danger" style="font-size:.75rem;"><?php echo h($post['category_name']); ?></small>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="row g-5">
        <!-- ═══ MAIN CONTENT ═══ -->
        <div class="col-lg-8">

            <!-- ═══ CATEGORY SECTIONS (Internal) ═══ -->
            <?php foreach ($categorySections as $cat): ?>
                <?php
                $catStmt = $pdo->prepare("SELECT title, slug, short_description, image, created_at FROM posts WHERE category_id = ? AND status = 'published' ORDER BY created_at DESC LIMIT 5");
                $catStmt->execute([$cat['id']]);
                $catPosts = $catStmt->fetchAll();

                // Get external news for this category
                $extStmt = $pdo->prepare("SELECT id, title, source_name, original_url, published_at FROM aggregated_news WHERE category_id = ? ORDER BY published_at DESC LIMIT 3");
                $extStmt->execute([$cat['id']]);
                $extPosts = $extStmt->fetchAll();

                if (count($catPosts) === 0 && count($extPosts) === 0) continue;
                $firstPost = $catPosts[0] ?? null;
                ?>
            <section class="mb-5">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom border-danger border-2">
                    <h3 class="m-0 fw-bold text-uppercase" style="font-size:1.1rem;">
                        <a href="<?php echo SITE_URL; ?>/category/<?php echo h($cat['slug']); ?>" class="text-dark text-decoration-none"><?php echo h($cat['name']); ?></a>
                    </h3>
                    <a href="<?php echo SITE_URL; ?>/category/<?php echo h($cat['slug']); ?>" class="text-danger text-decoration-none small fw-semibold">সবগুলো দেখুন <i class="bi bi-chevron-right"></i></a>
                </div>

                <div class="row g-4">
                    <?php if ($firstPost): ?>
                    <div class="col-md-6">
                        <div class="card border-0 h-100">
                            <img src="<?php echo $firstPost['image'] ? SITE_URL . '/' . $firstPost['image'] : 'https://via.placeholder.com/400x250/eee/999?text=News'; ?>"
                                 class="card-img-top" style="height:220px; object-fit:cover;" alt="" loading="lazy">
                            <div class="card-body px-0">
                                <h5 class="card-title fw-bold"><a href="<?php echo SITE_URL; ?>/news/<?php echo h($firstPost['slug']); ?>" class="text-dark text-decoration-none"><?php echo h($firstPost['title']); ?></a></h5>
                                <p class="card-text text-muted small"><?php echo h($firstPost['short_description']); ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="col-md-6">
                        <ul class="list-unstyled mb-0">
                            <?php for ($i = 1; $i < count($catPosts); $i++): $p = $catPosts[$i]; ?>
                            <li class="d-flex mb-3 pb-3 border-bottom">
                                <img src="<?php echo $p['image'] ? SITE_URL . '/' . $p['image'] : 'https://via.placeholder.com/80x80/eee/999?text=N'; ?>"
                                     class="me-3 rounded" style="width:80px; height:70px; object-fit:cover;" alt="" loading="lazy">
                                <div>
                                    <h6 class="fw-bold mb-1 lh-sm"><a href="<?php echo SITE_URL; ?>/news/<?php echo h($p['slug']); ?>" class="text-dark text-decoration-none"><?php echo h($p['title']); ?></a></h6>
                                    <small class="text-muted"><?php echo timeAgo($p['created_at']); ?></small>
                                </div>
                            </li>
                            <?php endfor; ?>

                            <!-- External headlines for this category -->
                            <?php foreach ($extPosts as $ep): ?>
                            <li class="d-flex mb-3 pb-3 border-bottom">
                                <div class="me-3 rounded bg-light d-flex align-items-center justify-content-center flex-shrink-0" style="width:80px; height:70px;">
                                    <i class="bi bi-globe2 text-secondary fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1 lh-sm">
                                        <a href="<?php echo SITE_URL; ?>/external-news.php?id=<?php echo $ep['id']; ?>" class="text-dark text-decoration-none"><?php echo h($ep['title']); ?></a>
                                    </h6>
                                    <small class="text-muted"><span class="badge bg-secondary bg-opacity-25 text-dark me-1"><i class="bi bi-link-45deg"></i> খবর</span></small>
                                </div>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </section>
            <?php endforeach; ?>

            <!-- ═══ LATEST NEWS FEED ═══ -->
            <section>
                <h3 class="fw-bold text-uppercase pb-2 mb-4 border-bottom border-danger border-2" style="font-size:1.1rem;">সর্বশেষ খবর</h3>
                <?php foreach ($latestFeed as $post): ?>
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
            </section>
        </div>

        <!-- ═══ SIDEBAR ═══ -->
        <div class="col-lg-4">
            <!-- Trending -->
            <div class="bg-white rounded-3 shadow-sm p-4 mb-4">
                <h4 class="fw-bold text-uppercase pb-2 mb-3 border-bottom border-danger border-2" style="font-size:1rem;">বর্তমানে জনপ্রিয়</h4>
                <ul class="list-unstyled mb-0">
                    <?php $rank = 1; foreach ($trendingPosts as $post): ?>
                    <li class="d-flex mb-3 align-items-start">
                        <span class="text-danger fw-bold fs-4 me-3 opacity-50" style="min-width:28px;"><?php echo en2bn(str_pad($rank++, 2, '0', STR_PAD_LEFT)); ?></span>
                        <div>
                            <h6 class="fw-bold mb-1 lh-sm"><a href="<?php echo SITE_URL; ?>/news/<?php echo h($post['slug']); ?>" class="text-dark text-decoration-none"><?php echo h($post['title']); ?></a></h6>
                            <small class="text-muted"><?php echo en2bn(number_format($post['views'])); ?> বার পড়া হয়েছে</small>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- External Headlines -->
            <?php if (count($externalNews) > 0): ?>
            <div class="bg-white rounded-3 shadow-sm p-4 mb-4">
                <h4 class="fw-bold text-uppercase pb-2 mb-3 border-bottom border-secondary border-2" style="font-size:1rem;">
                    <i class="bi bi-globe2 me-1"></i> অন্যান্য মাধ্যম থেকে
                </h4>
                <ul class="list-unstyled mb-0">
                    <?php foreach ($externalNews as $en): ?>
                    <li class="mb-3 pb-3 border-bottom">
                        <h6 class="fw-bold mb-1 lh-sm">
                            <a href="<?php echo SITE_URL; ?>/external-news.php?id=<?php echo $en['id']; ?>" class="text-dark text-decoration-none"><?php echo h($en['title']); ?></a>
                        </h6>
                        <small class="text-muted">
                            <span class="badge bg-light text-dark border"><i class="bi bi-link-45deg"></i> খবর</span>
                            · <?php echo $en['published_at'] ? timeAgo($en['published_at']) : ''; ?>
                        </small>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <!-- Ad Placeholder -->
            <div class="bg-secondary bg-opacity-10 rounded-3 text-center p-5 mb-4">
                <p class="text-muted mb-0">বিজ্ঞাপন</p>
                <small class="text-muted">৩০০×২৫০</small>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
