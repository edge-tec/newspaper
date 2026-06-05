<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/functions.php';

if (!isLoggedIn() || !in_array($_SESSION['user_role'], ['admin', 'editor', 'reporter'])) {
    redirect('/admin/login.php');
}

$id = $_GET['id'] ?? null;

if ($id) {
    $stmt = $pdo->prepare("SELECT author_id, image FROM posts WHERE id = ?");
    $stmt->execute([$id]);
    $post = $stmt->fetch();

    if ($post) {
        if ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'editor' || $_SESSION['user_id'] == $post['author_id']) {
            if ($post['image'] && file_exists(__DIR__ . '/../../' . $post['image'])) {
                unlink(__DIR__ . '/../../' . $post['image']);
            }

            $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
            if ($stmt->execute([$id])) {
                setFlash('সংবাদটি সফলভাবে ডিলিট হয়েছে।', 'success');
            } else {
                setFlash('সংবাদটি ডিলিট করা সম্ভব হয়নি।', 'danger');
            }
        } else {
            setFlash('আপনার এই সংবাদটি ডিলিট করার অনুমতি নেই।', 'danger');
        }
    }
}

// Redirect back to where they came from
if ($_SESSION['user_role'] === 'reporter') {
    redirect('/reporter/articles.php');
} else {
    redirect('/admin/posts/index.php');
}
