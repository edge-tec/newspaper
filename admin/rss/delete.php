<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/functions.php';

requireAdmin();

$id = $_GET['id'] ?? null;
if ($id) {
    $stmt = $pdo->prepare("DELETE FROM rss_sources WHERE id = ?");
    if ($stmt->execute([$id])) {
        setFlash('RSS উৎস সফলভাবে ডিলিট হয়েছে।', 'success');
    } else {
        setFlash('RSS উৎস ডিলিট করা সম্ভব হয়নি।', 'danger');
    }
}
redirect('/admin/rss/index.php');
