<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/functions.php';

requireReporter();
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>রিপোর্টার প্যানেল — <?php echo SITE_NAME; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Noto+Serif+Bengali:wght@500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Load custom styles from main site to match aesthetics, or define here -->
    <style>
        body { font-family: 'Inter', 'Noto Serif Bengali', sans-serif; background-color: #f4f5f7; }
        .sidebar { position: fixed; top: 0; bottom: 0; left: 0; z-index: 100; padding: 56px 0 0; box-shadow: inset -1px 0 0 rgba(0,0,0,.1); }
        .sidebar-sticky { position: relative; top: 0; height: calc(100vh - 56px); padding-top: .5rem; overflow-x: hidden; overflow-y: auto; }
        .sidebar .nav-link { font-weight: 500; color: #333; }
        .sidebar .nav-link .bi { margin-right: 8px; color: #999; }
        .sidebar .nav-link.active, .sidebar .nav-link:hover { color: #dc3545; }
        .sidebar .nav-link.active .bi, .sidebar .nav-link:hover .bi { color: #dc3545; }
    </style>
</head>
<body>

<header class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0 shadow">
    <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3 fs-6 fw-bold" href="<?php echo SITE_URL; ?>" target="_blank">
        <?php echo SITE_NAME; ?> <small class="fw-normal opacity-75">(সাইট দেখুন)</small>
    </a>
    <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="w-100"></div>
    <div class="navbar-nav">
        <div class="nav-item text-nowrap d-flex align-items-center">
            <span class="text-light me-3 small">স্বাগতম, <?php echo h($_SESSION['user_name']); ?></span>
            <a class="nav-link px-3 bg-danger text-white" href="<?php echo SITE_URL; ?>/admin/logout.php">লগআউট</a>
        </div>
    </div>
</header>

<div class="container-fluid">
    <div class="row">
