<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/functions.php';

if ($_SESSION['user_role'] !== 'admin') {
    setFlash('অ্যাক্সেস ডিনাইড।', 'danger');
    redirect('/admin/index.php');
}

$id = $_GET['id'] ?? null;

if ($id && $id != $_SESSION['user_id']) {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    if ($stmt->execute([$id])) {
        setFlash('ব্যবহারকারী সফলভাবে ডিলিট হয়েছে।', 'success');
    } else {
        setFlash('ব্যবহারকারী ডিলিট করা সম্ভব হয়নি।', 'danger');
    }
} else {
    setFlash('আপনি আপনার নিজের একাউন্ট ডিলিট করতে পারবেন না।', 'danger');
}

redirect('/admin/users/index.php');
