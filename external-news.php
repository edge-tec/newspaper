<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/functions.php';

$id = $_GET['id'] ?? null;
if (!$id) redirect('/');

$stmt = $pdo->prepare("SELECT a.*, c.name as category_name FROM aggregated_news a LEFT JOIN categories c ON a.category_id = c.id WHERE a.id = ?");
$stmt->execute([$id]);
$article = $stmt->fetch();

if (!$article) {
    header("HTTP/1.0 404 Not Found");
    echo "খবরটি পাওয়া যায়নি।";
    exit;
}

$pageTitle = $article['title'];
$pageDesc  = $article['excerpt'];

require_once __DIR__ . '/includes/header.php';

// Related external articles from the same source
$relatedStmt = $pdo->prepare("SELECT id, title, source_name, published_at FROM aggregated_news WHERE source_name = ? AND id != ? ORDER BY published_at DESC LIMIT 5");
$relatedStmt->execute([$article['source_name'], $article['id']]);
$relatedArticles = $relatedStmt->fetchAll();

// Format date
$bnDays = ['Sunday'=>'রবিবার', 'Monday'=>'সোমবার', 'Tuesday'=>'মঙ্গলবার', 'Wednesday'=>'বুধবার', 'Thursday'=>'বৃহস্পতিবার', 'Friday'=>'শুক্রবার', 'Saturday'=>'শনিবার'];
$bnMonths = ['January'=>'জানুয়ারি', 'February'=>'ফেব্রুয়ারি', 'March'=>'মার্চ', 'April'=>'এপ্রিল', 'May'=>'মে', 'June'=>'জুন', 'July'=>'জুলাই', 'August'=>'আগস্ট', 'September'=>'সেপ্টেম্বর', 'October'=>'অক্টোবর', 'November'=>'নভেম্বর', 'December'=>'ডিসেম্বর'];
$pubDate = strtotime($article['published_at']);
$articleDateBn = en2bn(date('j', $pubDate)) . ' ' . $bnMonths[date('F', $pubDate)] . ' ' . en2bn(date('Y, g:i a', $pubDate));
?>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-8">
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>" class="text-danger text-decoration-none">হোম</a></li>
                <?php if ($article['category_name']): ?>
                <li class="breadcrumb-item text-muted"><?php echo h($article['category_name']); ?></li>
                <?php endif; ?>
                <li class="breadcrumb-item active">বাহ্যিক উৎস</li>
              </ol>
            </nav>

            <!-- External Source Badge -->
            <div class="alert alert-light border d-flex align-items-center mb-4">
                <i class="bi bi-globe2 text-secondary fs-4 me-3"></i>
                <div>
                    <strong>উৎস:</strong> <?php echo h($article['source_name']); ?>
                    <br><small class="text-muted">এই খবরটি অন্য একটি ওয়েবসাইট থেকে সংগ্রহ করা হয়েছে। নিচে শুধুমাত্র খবরের সারসংক্ষেপ দেওয়া হলো।</small>
                </div>
            </div>

            <h1 class="fw-bold mb-3" style="font-family:'Playfair Display',serif;"><?php echo h($article['title']); ?></h1>

            <div class="text-muted mb-4">
                <span class="me-3"><i class="bi bi-newspaper me-1"></i> <?php echo h($article['source_name']); ?></span>
                <?php if ($article['published_at']): ?>
                <span><i class="bi bi-calendar3 me-1"></i> <?php echo str_replace(['am', 'pm'], ['এএম', 'পিএম'], $articleDateBn); ?></span>
                <?php endif; ?>
            </div>

            <?php if ($article['image']): ?>
                <img src="<?php echo h($article['image']); ?>" class="img-fluid w-100 rounded mb-4" alt="<?php echo h($article['title']); ?>" loading="lazy">
            <?php endif; ?>

            <!-- Excerpt Only -->
            <div class="fs-5 mb-4" style="line-height:1.8;">
                <?php echo nl2br(h($article['excerpt'])); ?>
            </div>

            <!-- Read Full Article Button -->
            <div class="bg-light rounded-3 p-4 text-center mb-4">
                <p class="mb-3 text-muted">খবরটির বিস্তারিত পড়তে মূল ওয়েবসাইটে যান:</p>
                <a href="<?php echo h($article['original_url']); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-danger btn-lg">
                    <i class="bi bi-box-arrow-up-right me-2"></i> <?php echo h($article['source_name']); ?> এ বিস্তারিত পড়ুন
                </a>
            </div>

            <!-- Share -->
            <div class="d-flex gap-2 mb-4">
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($article['original_url']); ?>" target="_blank" class="btn btn-sm btn-primary"><i class="bi bi-facebook me-1"></i> শেয়ার করুন</a>
                <a href="https://api.whatsapp.com/send?text=<?php echo urlencode($article['title'] . ' ' . $article['original_url']); ?>" target="_blank" class="btn btn-sm btn-success"><i class="bi bi-whatsapp me-1"></i> হোয়াটসঅ্যাপ</a>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="bg-white rounded-3 shadow-sm p-4">
                <h5 class="fw-bold mb-3 border-bottom border-danger border-2 pb-2 text-uppercase" style="font-size:.9rem;"><?php echo h($article['source_name']); ?> থেকে আরও খবর</h5>
                <ul class="list-unstyled mb-0">
                    <?php foreach ($relatedArticles as $ra): ?>
                    <li class="mb-3 pb-3 border-bottom">
                        <h6 class="fw-bold lh-sm mb-1">
                            <a href="<?php echo SITE_URL; ?>/external-news.php?id=<?php echo $ra['id']; ?>" class="text-dark text-decoration-none"><?php echo h($ra['title']); ?></a>
                        </h6>
                        <small class="text-muted"><?php echo $ra['published_at'] ? timeAgo($ra['published_at']) : ''; ?></small>
                    </li>
                    <?php endforeach; ?>
                    <?php if (count($relatedArticles) === 0): ?>
                        <li class="text-muted small">এই উৎসের আর কোনো খবর নেই।</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
