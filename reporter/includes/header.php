<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/functions.php';

requireLogin();
if (!isReporter()) {
    redirect('/admin/index.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporter Dashboard — <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <style>
        .sidebar { position: fixed; top: 0; bottom: 0; left: 0; z-index: 100; padding: 56px 0 0; box-shadow: inset -1px 0 0 rgba(0,0,0,.1); }
        .sidebar-sticky { position: relative; top: 0; height: calc(100vh - 56px); padding-top: .5rem; overflow-x: hidden; overflow-y: auto; }
        .sidebar .nav-link { font-weight: 500; color: #333; padding: .5rem 1rem; }
        .sidebar .nav-link .bi { margin-right: 6px; color: #727272; }
        .sidebar .nav-link.active, .sidebar .nav-link:hover { color: #dc3545; }
        .sidebar .nav-link.active .bi, .sidebar .nav-link:hover .bi { color: #dc3545; }
    </style>
</head>
<body>
    <header class="navbar navbar-dark sticky-top bg-danger flex-md-nowrap p-0 shadow">
        <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3 fs-6" href="<?php echo SITE_URL; ?>/reporter/index.php">
            <i class="bi bi-newspaper me-1"></i> Reporter Panel
        </a>
        <button class="navbar-toggler position-absolute d-md-none collapsed" type="button"
                data-bs-toggle="collapse" data-bs-target="#sidebarMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="navbar-nav w-100 d-flex flex-row justify-content-end px-3">
            <div class="nav-item text-nowrap d-flex align-items-center gap-3">
                <a class="nav-link px-0 text-white-50" href="<?php echo SITE_URL; ?>" target="_blank"><i class="bi bi-box-arrow-up-right"></i> View Site</a>
                <span class="text-white-50 small">Hi, <?php echo h($_SESSION['user_name']); ?></span>
                <a class="nav-link px-0 text-white" href="<?php echo SITE_URL; ?>/admin/logout.php">Sign out</a>
            </div>
        </div>
    </header>

    <div class="container-fluid">
        <div class="row">
