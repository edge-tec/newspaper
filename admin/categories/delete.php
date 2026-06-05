<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';

requireAdminOrEditor();

$id = $_GET['id'] ?? null;
if ($id) {
    // Optionally check if posts are associated with this category and handle them (our DB has CASCADE ON DELETE)
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    if ($stmt->execute([$id])) {
        $_SESSION['success'] = 'Category deleted successfully.';
    } else {
        $_SESSION['error'] = 'Failed to delete category.';
    }
}

redirect('/admin/categories/index.php');
