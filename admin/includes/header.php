<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/functions.php';

if (!isLoggedIn() || !in_array($_SESSION['user_role'], ['admin', 'editor', 'reporter'])) {
    redirect('/admin/login.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { font-size: .875rem; }
        .sidebar { position: fixed; top: 0; bottom: 0; left: 0; z-index: 100; padding: 48px 0 0; box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1); }
        .sidebar-sticky { position: relative; top: 0; height: calc(100vh - 48px); padding-top: .5rem; overflow-x: hidden; overflow-y: auto; }
        .sidebar .nav-link { font-weight: 500; color: #333; }
        .sidebar .nav-link .bi { margin-right: 4px; color: #727272; }
        .sidebar .nav-link.active { color: #2470dc; }
        .sidebar .nav-link:hover .bi, .sidebar .nav-link.active .bi { color: inherit; }
        .sidebar-heading { font-size: .75rem; text-transform: uppercase; }
        .navbar-brand { padding-top: .75rem; padding-bottom: .75rem; font-size: 1rem; background-color: rgba(0, 0, 0, .25); box-shadow: inset -1px 0 0 rgba(0, 0, 0, .25); }
        .navbar .navbar-toggler { top: .25rem; right: 1rem; }
        .navbar .form-control { padding: .75rem 1rem; border-width: 0; border-radius: 0; }
        .form-control-dark { color: #fff; background-color: rgba(255, 255, 255, .1); border-color: rgba(255, 255, 255, .1); }
        .form-control-dark:focus { border-color: transparent; box-shadow: 0 0 0 3px rgba(255, 255, 255, .25); }
    </style>
</head>
<body>
    <header class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0 shadow">
        <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3" href="<?php echo SITE_URL; ?>/admin/index.php"><?php echo SITE_NAME; ?> Admin</a>
        <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="navbar-nav w-100 d-flex flex-row justify-content-between px-3">
            <div class="nav-item text-nowrap d-flex align-items-center">
                <a class="nav-link px-3 text-white" href="<?php echo SITE_URL; ?>" target="_blank">View Site</a>
            </div>
            <div class="nav-item text-nowrap d-flex align-items-center">
                <span class="text-white me-3">Welcome, <?php echo h($_SESSION['user_name']); ?></span>
                <a class="nav-link px-3 text-white" href="<?php echo SITE_URL; ?>/admin/logout.php">Sign out</a>
            </div>
        </div>
    </header>
    <div class="container-fluid">
        <div class="row">
