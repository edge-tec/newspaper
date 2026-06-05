<?php
/**
 * RSS Feed Fetcher — Cron Job Script
 * 
 * Run via cron every 30-60 minutes:
 *   php /path/to/cron/fetch_rss.php
 * 
 * Or via command line for testing:
 *   cd /path/to/project && php cron/fetch_rss.php
 */

// Suppress HTML errors — this is a CLI script
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/database.php';
// We don't need session/functions for the cron, just config + database

$logPrefix = "[" . date('Y-m-d H:i:s') . "] ";

echo $logPrefix . "Starting RSS fetch...\n";

// Fetch all active RSS feeds
$feeds = $pdo->query("SELECT * FROM rss_feeds WHERE status = 'active'")->fetchAll();

if (count($feeds) === 0) {
    echo $logPrefix . "No active feeds found.\n";
    exit;
}

$totalInserted = 0;

foreach ($feeds as $feed) {
    echo $logPrefix . "Fetching: {$feed['source_name']} ({$feed['feed_url']})\n";

    try {
        // Suppress warnings from malformed XML
        $context = stream_context_create([
            'http' => [
                'timeout'    => 15,
                'user_agent' => 'ModernNews-RSS-Fetcher/1.0',
            ],
        ]);

        $content = @file_get_contents($feed['feed_url'], false, $context);

        if ($content === false) {
            echo $logPrefix . "  ERROR: Could not fetch URL.\n";
            continue;
        }

        // Suppress XML parsing warnings
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($content);
        libxml_clear_errors();

        if ($xml === false) {
            echo $logPrefix . "  ERROR: Invalid XML.\n";
            continue;
        }

        // Determine feed type: RSS 2.0 or Atom
        $items = [];

        if (isset($xml->channel->item)) {
            // RSS 2.0
            foreach ($xml->channel->item as $item) {
                $title       = (string) $item->title;
                $link        = (string) $item->link;
                $description = (string) $item->description;
                $pubDate     = (string) ($item->pubDate ?? '');
                $image       = null;

                // Try to get image from media:content or enclosure
                $namespaces = $item->getNameSpaces(true);
                if (isset($namespaces['media'])) {
                    $media = $item->children($namespaces['media']);
                    if (isset($media->content)) {
                        $image = (string) $media->content->attributes()->url;
                    } elseif (isset($media->thumbnail)) {
                        $image = (string) $media->thumbnail->attributes()->url;
                    }
                }
                if (!$image && isset($item->enclosure)) {
                    $encType = (string) $item->enclosure->attributes()->type;
                    if (str_starts_with($encType, 'image/')) {
                        $image = (string) $item->enclosure->attributes()->url;
                    }
                }
                
                // Fallback: Extract image from description or content:encoded
                if (!$image) {
                    $contentNs = $item->children('http://purl.org/rss/1.0/modules/content/');
                    $contentEncoded = isset($contentNs->encoded) ? (string)$contentNs->encoded : '';
                    $searchContent = $contentEncoded ? $contentEncoded : $description;
                    
                    if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $searchContent, $matches)) {
                        $image = $matches[1];
                    }
                }

                // Replace original newspaper name with our site name
                $title = str_ireplace($feed['source_name'], SITE_NAME, $title);
                
                // Preserve paragraph spacing
                $descWithNewlines = str_ireplace(['</p>', '<br>', '<br/>', '<br />', '</div>'], "\n", $description);
                $excerpt = strip_tags($descWithNewlines);
                $excerpt = preg_replace("/\n\s*\n+/", "\n\n", trim($excerpt));
                $excerpt = str_ireplace($feed['source_name'], SITE_NAME, $excerpt);

                $items[] = [
                    'title'   => $title,
                    'link'    => $link,
                    'excerpt' => $excerpt,
                    'image'   => $image,
                    'pubDate' => $pubDate,
                ];
            }
        } elseif (isset($xml->entry)) {
            // Atom
            foreach ($xml->entry as $entry) {
                $title = (string) $entry->title;
                $link  = '';
                foreach ($entry->link as $l) {
                    if ((string) $l->attributes()->rel === 'alternate' || empty($link)) {
                        $link = (string) $l->attributes()->href;
                    }
                }
                $description = (string) ($entry->summary ?? $entry->content ?? '');
                $pubDate     = (string) ($entry->published ?? $entry->updated ?? '');
                
                $image = null;
                if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $description, $matches)) {
                    $image = $matches[1];
                }

                // Replace original newspaper name with our site name
                $title = str_ireplace($feed['source_name'], SITE_NAME, $title);

                // Preserve paragraph spacing
                $descWithNewlines = str_ireplace(['</p>', '<br>', '<br/>', '<br />', '</div>'], "\n", $description);
                $excerpt = strip_tags($descWithNewlines);
                $excerpt = preg_replace("/\n\s*\n+/", "\n\n", trim($excerpt));
                $excerpt = str_ireplace($feed['source_name'], SITE_NAME, $excerpt);

                $items[] = [
                    'title'   => $title,
                    'link'    => $link,
                    'excerpt' => $excerpt,
                    'image'   => $image,
                    'pubDate' => $pubDate,
                ];
            }
        }

        echo $logPrefix . "  Found " . count($items) . " items.\n";

        // Insert items, skip duplicates (unique constraint on original_url)
        $insertStmt = $pdo->prepare("
            INSERT IGNORE INTO aggregated_news (title, excerpt, image, source_name, original_url, category_id, published_at)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $inserted = 0;
        foreach ($items as $item) {
            if (empty($item['title']) || empty($item['link'])) continue;

            // No longer truncating to 500 chars to show full news
            $excerpt = $item['excerpt'];

            // Parse date
            $pubDate = null;
            if (!empty($item['pubDate'])) {
                try {
                    $dt = new DateTime($item['pubDate']);
                    $pubDate = $dt->format('Y-m-d H:i:s');
                } catch (Exception $e) {
                    $pubDate = date('Y-m-d H:i:s');
                }
            } else {
                $pubDate = date('Y-m-d H:i:s');
            }

            $result = $insertStmt->execute([
                $item['title'],
                $excerpt,
                $item['image'],
                $feed['source_name'],
                $item['link'],
                $feed['category_id'],
                $pubDate,
            ]);

            if ($insertStmt->rowCount() > 0) $inserted++;
        }

        echo $logPrefix . "  Inserted $inserted new items.\n";
        $totalInserted += $inserted;

        // Update last_fetched timestamp
        $pdo->prepare("UPDATE rss_feeds SET last_fetched = NOW() WHERE id = ?")->execute([$feed['id']]);

    } catch (Exception $e) {
        echo $logPrefix . "  EXCEPTION: " . $e->getMessage() . "\n";
    }
}

echo $logPrefix . "Done. Total new items: $totalInserted\n";
