<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/functions.php';

requireAdminOrEditor();
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>অ্যাডমিন প্যানেল — <?php echo SITE_NAME; ?></title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Noto+Serif+Bengali:wght@500;700&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            font-size: .875rem;
            font-family: 'Inter', 'Noto Serif Bengali', sans-serif;
            background-color: #f8f9fa;
        }
        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            padding: 48px 0 0;
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
            background-color: #212529;
        }
        .sidebar-sticky {
            position: relative;
            top: 0;
            height: calc(100vh - 48px);
            padding-top: .5rem;
            overflow-x: hidden;
            overflow-y: auto;
        }
        .sidebar .nav-link {
            font-weight: 500;
            color: #adb5bd;
            padding: 0.5rem 1rem;
            margin: 0.2rem 1rem;
            border-radius: 0.25rem;
        }
        .sidebar .nav-link .bi {
            margin-right: 10px;
            font-size: 1.1rem;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: #fff;
            background-color: rgba(255,255,255,0.1);
        }
        .sidebar-heading {
            color: #6c757d;
        }
        /* Top Navbar */
        .navbar {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, .075);
        }
        .navbar-brand {
            padding-top: .75rem;
            padding-bottom: .75rem;
            font-size: 1rem;
            background-color: rgba(0, 0, 0, .25);
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .25);
        }
    </style>
</head>
<body>

<header class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0">
  <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3 fs-6" href="<?php echo SITE_URL; ?>" target="_blank">
      <i class="bi bi-box-arrow-up-right me-1"></i> ওয়েবসাইট দেখুন
  </a>
  <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>
  <div class="navbar-nav w-100 d-flex flex-row justify-content-end px-3 py-2">
      <div class="nav-item text-nowrap d-flex align-items-center">
          <span class="text-light me-3">স্বাগতম, <?php echo h($_SESSION['user_name']); ?></span>
          <a class="nav-link px-3 bg-danger text-white rounded" href="<?php echo SITE_URL; ?>/admin/logout.php">লগআউট</a>
      </div>
  </div>
</header>

<div class="container-fluid">
  <div class="row">
