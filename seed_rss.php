<?php
require_once __DIR__ . '/config/database.php';

$feeds = [
    ['Samakal', 'https://samakal.com/feed'],
    ['Bangladesh Pratidin', 'https://www.bd-pratidin.com/rss.xml'],
    ['Bangla Tribune', 'https://www.banglatribune.com/feed/'],
    ['BBC Bangla', 'https://www.bbc.com/bengali/index.xml'],
    ['The Daily Star', 'https://www.thedailystar.net/frontpage/rss.xml'],
    ['BSS News', 'https://www.bssnews.net/rss.xml'],
    ['Prothom Alo English', 'https://en.prothomalo.com/feed']
];

// Clear previous RSS feeds and aggregated news
$pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
$pdo->exec("TRUNCATE TABLE aggregated_news");
$pdo->exec("TRUNCATE TABLE rss_feeds");
$pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

$stmt = $pdo->prepare("INSERT INTO rss_feeds (source_name, feed_url, category_id, status) VALUES (?, ?, 1, 'active')");

echo "<!DOCTYPE html><html lang='bn'><head><meta charset='UTF-8'><title>RSS Seed</title></head><body>";
echo "<h2>Database Cleared! Added New Bangladeshi RSS Feeds</h2><ul>";

$addedCount = 0;
foreach ($feeds as $feed) {
    // check if exists
    $check = $pdo->prepare("SELECT id FROM rss_feeds WHERE feed_url = ?");
    $check->execute([$feed[1]]);
    if ($check->rowCount() == 0) {
        $stmt->execute([$feed[0], $feed[1]]);
        echo "<li><span style='color:green;'>Inserted:</span> {$feed[0]}</li>";
        $addedCount++;
    } else {
        echo "<li><span style='color:orange;'>Skipped (already exists):</span> {$feed[0]}</li>";
    }
}
echo "</ul><h3>Done! Added {$addedCount} new feeds.</h3>";
echo "<p>Please delete this file (seed_rss.php) from your server for security.</p>";
echo "</body></html>";
?>
