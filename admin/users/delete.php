<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';

if ($_SESSION['user_role'] !== 'admin') {
    $_SESSION['error'] = 'Access denied.';
    redirect('/admin/index.php');
}

$id = $_GET['id'] ?? null;

if ($id && $id != $_SESSION['user_id']) {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    if ($stmt->execute([$id])) {
        $_SESSION['success'] = 'User deleted successfully.';
    } else {
        $_SESSION['error'] = 'Failed to delete user.';
    }
} else {
    $_SESSION['error'] = 'Cannot delete your own account.';
}

redirect('/admin/users/index.php');
