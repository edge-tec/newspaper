<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/functions.php';

// Fetch active categories for navigation
$navCategories = $pdo->query("SELECT name, slug FROM categories WHERE status = 'active' ORDER BY name ASC")->fetchAll();
$breakingNews  = getBreakingNews($pdo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? h($pageTitle) . ' — ' . SITE_NAME : SITE_NAME . ' — ' . SITE_DESC; ?></title>
    <meta name="description" content="<?php echo isset($pageDesc) ? h($pageDesc) : SITE_DESC; ?>">
    <!-- Open Graph -->
    <meta property="og:title" content="<?php echo isset($pageTitle) ? h($pageTitle) : SITE_NAME; ?>">
    <meta property="og:description" content="<?php echo isset($pageDesc) ? h($pageDesc) : SITE_DESC; ?>">
    <meta property="og:type" content="article">
    <?php if (isset($pageImage) && $pageImage): ?>
    <meta property="og:image" content="<?php echo SITE_URL . '/' . $pageImage; ?>">
    <?php endif; ?>
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo isset($pageTitle) ? h($pageTitle) : SITE_NAME; ?>">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head>
<body>

<!-- Top Bar -->
<div class="bg-dark text-white-50 py-1 d-none d-md-block" style="font-size:13px;">
    <div class="container d-flex justify-content-between">
        <span><i class="bi bi-calendar3 me-1"></i> <?php echo date('l, F j, Y'); ?></span>
        <div>
            <?php if (isLoggedIn()): ?>
                <?php if (isReporter()): ?>
                    <a href="<?php echo SITE_URL; ?>/reporter/index.php" class="text-white-50 text-decoration-none me-3"><i class="bi bi-speedometer2 me-1"></i>My Dashboard</a>
                <?php else: ?>
                    <a href="<?php echo SITE_URL; ?>/admin/index.php" class="text-white-50 text-decoration-none me-3"><i class="bi bi-gear me-1"></i>Admin</a>
                <?php endif; ?>
                <a href="<?php echo SITE_URL; ?>/admin/logout.php" class="text-white-50 text-decoration-none">Sign Out</a>
            <?php else: ?>
                <a href="<?php echo SITE_URL; ?>/admin/login.php" class="text-white-50 text-decoration-none me-3"><i class="bi bi-box-arrow-in-right me-1"></i>Login</a>
                <a href="<?php echo SITE_URL; ?>/register.php" class="text-white-50 text-decoration-none"><i class="bi bi-person-plus me-1"></i>Register</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Header -->
<header class="sticky-top bg-white border-bottom shadow-sm">
    <div class="container py-3">
        <div class="row align-items-center">
            <div class="col-8 col-md-4">
                <a class="text-dark text-decoration-none" href="<?php echo SITE_URL; ?>">
                    <span class="fs-2 fw-bold" style="font-family:'Playfair Display',serif;"><span class="text-danger">M</span>odern<span class="text-danger">N</span>ews</span>
                </a>
            </div>
            <div class="col-4 col-md-8 d-flex justify-content-end align-items-center">
                <a class="link-secondary" href="#" data-bs-toggle="modal" data-bs-target="#searchModal" aria-label="Search">
                    <i class="bi bi-search fs-5"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <div class="border-top">
        <div class="container">
            <nav class="nav d-flex flex-wrap justify-content-center text-uppercase fw-semibold" style="font-size:13px;">
                <a class="p-2 px-3 link-secondary text-decoration-none" href="<?php echo SITE_URL; ?>">Home</a>
                <?php foreach ($navCategories as $cat): ?>
                    <a class="p-2 px-3 link-secondary text-decoration-none" href="<?php echo SITE_URL; ?>/category/<?php echo h($cat['slug']); ?>"><?php echo h($cat['name']); ?></a>
                <?php endforeach; ?>
            </nav>
        </div>
    </div>
</header>

<!-- Breaking News Ticker -->
<?php if (!empty($breakingNews)): ?>
<div class="bg-danger text-white py-2">
    <div class="container d-flex align-items-center">
        <span class="fw-bold me-3 text-uppercase flex-shrink-0" style="font-size:13px; letter-spacing:1px;">Breaking</span>
        <div class="w-100 overflow-hidden">
            <marquee behavior="scroll" direction="left" scrollamount="4" onmouseover="this.stop();" onmouseout="this.start();">
                <?php foreach ($breakingNews as $bn): ?>
                    <a href="<?php echo SITE_URL; ?>/news/<?php echo h($bn['slug']); ?>" class="text-white text-decoration-none me-5">
                        <i class="bi bi-circle-fill me-1" style="font-size:6px; vertical-align:middle;"></i> <?php echo h($bn['title']); ?>
                    </a>
                <?php endforeach; ?>
            </marquee>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Search Modal -->
<div class="modal fade" id="searchModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold">Search News</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form action="<?php echo SITE_URL; ?>/search.php" method="GET">
            <div class="input-group input-group-lg">
                <input type="text" name="q" class="form-control" placeholder="Search keywords..." required>
                <button class="btn btn-danger" type="submit"><i class="bi bi-search"></i></button>
            </div>
        </form>
      </div>
    </div>
  </div>
</div>
