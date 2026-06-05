<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';

if (!isLoggedIn() || !in_array($_SESSION['user_role'], ['admin', 'editor', 'reporter'])) {
    redirect('/admin/login.php');
}

$id = $_GET['id'] ?? null;

if ($id) {
    // Check if post exists and user has permission (reporters can only delete their own posts, admin/editor can delete any)
    $stmt = $pdo->prepare("SELECT author_id, image FROM posts WHERE id = ?");
    $stmt->execute([$id]);
    $post = $stmt->fetch();

    if ($post) {
        if ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'editor' || $_SESSION['user_id'] == $post['author_id']) {
            // Delete image file if exists
            if ($post['image'] && file_exists(__DIR__ . '/../../' . $post['image'])) {
                unlink(__DIR__ . '/../../' . $post['image']);
            }

            $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
            if ($stmt->execute([$id])) {
                $_SESSION['success'] = 'Post deleted successfully.';
            } else {
                $_SESSION['error'] = 'Failed to delete post.';
            }
        } else {
            $_SESSION['error'] = 'You do not have permission to delete this post.';
        }
    }
}

redirect('/admin/posts/index.php');
