<?php
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir  = basename(dirname($_SERVER['PHP_SELF']));
?>
<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block sidebar collapse">
    <div class="sidebar-sticky pt-3">
        
        <ul class="nav flex-column mb-3">
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'index.php' && $current_dir == 'admin' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/admin/index.php">
                    <i class="bi bi-speedometer2"></i> ড্যাশবোর্ড
                </a>
            </li>
        </ul>

        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-4 mt-3 mb-2 text-uppercase" style="font-size:0.75rem;">
            <span>কন্টেন্ট ব্যবস্থাপনা</span>
        </h6>
        <ul class="nav flex-column mb-3">
            <li class="nav-item">
                <a class="nav-link <?php echo $current_dir == 'posts' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/admin/posts/index.php">
                    <i class="bi bi-file-earmark-text"></i> সংবাদ সমূহ
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_dir == 'categories' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/admin/categories/index.php">
                    <i class="bi bi-tags"></i> ক্যাটাগরি
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_dir == 'rss' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/admin/rss/index.php">
                    <i class="bi bi-rss"></i> RSS সংবাদ
                </a>
            </li>
        </ul>

        <?php if (isAdmin()): ?>
        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-4 mt-3 mb-2 text-uppercase" style="font-size:0.75rem;">
            <span>সিস্টেম</span>
        </h6>
        <ul class="nav flex-column mb-3">
            <li class="nav-item">
                <a class="nav-link <?php echo $current_dir == 'users' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/admin/users/index.php">
                    <i class="bi bi-people"></i> ব্যবহারকারী
                </a>
            </li>
        </ul>
        <?php endif; ?>

    </div>
</nav>
