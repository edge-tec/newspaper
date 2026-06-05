<?php
require_once __DIR__ . '/config/database.php';

$categories = [
    'Bangladesh' => 'বাংলাদেশ',
    'Business' => 'ব্যবসা-বাণিজ্য',
    'Entertainment' => 'বিনোদন',
    'International' => 'আন্তর্জাতিক',
    'Politics' => 'রাজনীতি',
    'Sports' => 'খেলাধুলা',
    'Technology' => 'প্রযুক্তি'
];

try {
    foreach ($categories as $eng => $bng) {
        $stmt = $pdo->prepare("UPDATE categories SET name = ? WHERE name = ?");
        $stmt->execute([$bng, $eng]);
    }
    echo "<h1>Categories have been successfully updated to Bengali!</h1>";
    echo "<p>Please delete this file (lang_update.php) for security.</p>";
} catch (Exception $e) {
    echo "Error updating categories: " . $e->getMessage();
}
