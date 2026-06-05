<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/functions.php';

requireAdminOrEditor();

$id = $_GET['id'] ?? null;
if ($id) {
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    if ($stmt->execute([$id])) {
        setFlash('ক্যাটাগরি সফলভাবে ডিলিট হয়েছে।', 'success');
    } else {
        setFlash('ক্যাটাগরি ডিলিট করা সম্ভব হয়নি।', 'danger');
    }
}

redirect('/admin/categories/index.php');
