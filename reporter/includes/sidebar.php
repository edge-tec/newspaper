<?php $current_page = basename($_SERVER['PHP_SELF']); ?>
<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-white sidebar collapse">
    <div class="sidebar-sticky pt-3">
        <ul class="nav flex-column mb-4">
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'index.php' ? 'active' : ''; ?>" href="index.php">
                    <i class="bi bi-speedometer2"></i> ড্যাশবোর্ড
                </a>
            </li>
        </ul>

        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted text-uppercase" style="font-size:12px;">
            <span>সংবাদ ব্যবস্থাপনা</span>
        </h6>
        <ul class="nav flex-column mb-2">
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'create.php' ? 'active' : ''; ?>" href="create.php">
                    <i class="bi bi-pencil-square"></i> নতুন খবর লিখুন
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'articles.php' && !isset($_GET['status'])) ? 'active' : ''; ?>" href="articles.php">
                    <i class="bi bi-journal-text"></i> আমার সকল খবর
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo (isset($_GET['status']) && $_GET['status'] == 'draft') ? 'active' : ''; ?>" href="articles.php?status=draft">
                    <i class="bi bi-file-earmark"></i> খসড়া সমূহ
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo (isset($_GET['status']) && $_GET['status'] == 'pending') ? 'active' : ''; ?>" href="articles.php?status=pending">
                    <i class="bi bi-hourglass-split"></i> অপেক্ষমান
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo (isset($_GET['status']) && $_GET['status'] == 'published') ? 'active' : ''; ?>" href="articles.php?status=published">
                    <i class="bi bi-check2-circle"></i> প্রকাশিত
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo (isset($_GET['status']) && $_GET['status'] == 'rejected') ? 'active' : ''; ?>" href="articles.php?status=rejected">
                    <i class="bi bi-x-circle text-danger"></i> বাতিলকৃত
                </a>
            </li>
        </ul>
    </div>
</nav>
