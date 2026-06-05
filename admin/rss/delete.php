<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';
requireAdmin();

$id = $_GET['id'] ?? null;
if ($id) {
    $stmt = $pdo->prepare("DELETE FROM rss_feeds WHERE id = ?");
    $stmt->execute([$id]);
    $_SESSION['success'] = 'RSS feed deleted.';
}
redirect('/admin/rss/index.php');
