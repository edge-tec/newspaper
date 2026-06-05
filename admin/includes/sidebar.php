            <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
                <div class="position-sticky sidebar-sticky">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo SITE_URL; ?>/admin/index.php">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        <?php if (isAdminOrEditor()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo SITE_URL; ?>/admin/posts/approve.php">
                                <i class="bi bi-check2-square"></i> Approve Articles
                                <?php
                                $pc = $pdo->query("SELECT COUNT(*) FROM posts WHERE status='pending'")->fetchColumn();
                                if ($pc > 0): ?>
                                <span class="badge bg-danger ms-1"><?php echo $pc; ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo SITE_URL; ?>/admin/posts/index.php">
                                <i class="bi bi-file-earmark-text"></i> All Posts
                            </a>
                        </li>
                        <?php if (isAdminOrEditor()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo SITE_URL; ?>/admin/categories/index.php">
                                <i class="bi bi-tags"></i> Categories
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if (isAdmin()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo SITE_URL; ?>/admin/rss/index.php">
                                <i class="bi bi-rss"></i> RSS Feeds
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo SITE_URL; ?>/admin/users/index.php">
                                <i class="bi bi-people"></i> Users
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </nav>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
