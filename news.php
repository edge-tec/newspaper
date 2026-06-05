<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/functions.php';

$slug = $_GET['slug'] ?? null;
if (!$slug) redirect('/');

$stmt = $pdo->prepare("
    SELECT p.*, c.name as category_name, c.slug as category_slug, u.name as author_name
    FROM posts p LEFT JOIN categories c ON p.category_id = c.id LEFT JOIN users u ON p.author_id = u.id
    WHERE p.slug = ? AND p.status = 'published'
");
$stmt->execute([$slug]);
$post = $stmt->fetch();

if (!$post) {
    header("HTTP/1.0 404 Not Found");
    echo "খবরটি পাওয়া যায়নি।";
    exit;
}

// Increment views
$pdo->prepare("UPDATE posts SET views = views + 1 WHERE id = ?")->execute([$post['id']]);

// Comments
$commStmt = $pdo->prepare("SELECT name, comment, created_at FROM comments WHERE post_id = ? ORDER BY created_at DESC");
$commStmt->execute([$post['id']]);
$comments = $commStmt->fetchAll();

// Handle new comment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_comment'])) {
    if (verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $cName    = trim($_POST['name'] ?? '');
        $cComment = trim($_POST['comment'] ?? '');
        if (!empty($cName) && !empty($cComment)) {
            $pdo->prepare("INSERT INTO comments (post_id, name, comment) VALUES (?,?,?)")->execute([$post['id'], $cName, $cComment]);
            redirect('/news/' . urlencode($slug));
        }
    }
}

// Related news
$relStmt = $pdo->prepare("SELECT title, slug, image, created_at FROM posts WHERE category_id = ? AND id != ? AND status = 'published' ORDER BY created_at DESC LIMIT 4");
$relStmt->execute([$post['category_id'], $post['id']]);
$relatedPosts = $relStmt->fetchAll();

$pageTitle = $post['title'];
$pageDesc  = $post['short_description'];
$pageImage = $post['image'];

require_once __DIR__ . '/includes/header.php';

// Format Bengali date for article
$bnDays = ['Sunday'=>'রবিবার', 'Monday'=>'সোমবার', 'Tuesday'=>'মঙ্গলবার', 'Wednesday'=>'বুধবার', 'Thursday'=>'বৃহস্পতিবার', 'Friday'=>'শুক্রবার', 'Saturday'=>'শনিবার'];
$bnMonths = ['January'=>'জানুয়ারি', 'February'=>'ফেব্রুয়ারি', 'March'=>'মার্চ', 'April'=>'এপ্রিল', 'May'=>'মে', 'June'=>'জুন', 'July'=>'জুলাই', 'August'=>'আগস্ট', 'September'=>'সেপ্টেম্বর', 'October'=>'অক্টোবর', 'November'=>'নভেম্বর', 'December'=>'ডিসেম্বর'];
$pubDate = strtotime($post['created_at']);
$articleDateBn = en2bn(date('j', $pubDate)) . ' ' . $bnMonths[date('F', $pubDate)] . ' ' . en2bn(date('Y', $pubDate));
?>

<!-- Schema.org Article markup -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "NewsArticle",
  "headline": "<?php echo h($post['title']); ?>",
  "description": "<?php echo h($post['short_description']); ?>",
  "author": { "@type": "Person", "name": "<?php echo h($post['author_name']); ?>" },
  "datePublished": "<?php echo date('c', strtotime($post['created_at'])); ?>",
  <?php if ($post['image']): ?>"image": "<?php echo SITE_URL . '/' . $post['image']; ?>",<?php endif; ?>
  "publisher": { "@type": "Organization", "name": "<?php echo SITE_NAME; ?>" }
}
</script>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-8">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb small">
                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>" class="text-danger text-decoration-none">হোম</a></li>
                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/category/<?php echo h($post['category_slug']); ?>" class="text-danger text-decoration-none"><?php echo h($post['category_name']); ?></a></li>
                <li class="breadcrumb-item active">বিস্তারিত খবর</li>
              </ol>
            </nav>

            <h1 class="fw-bold mb-3" style="font-family:'Playfair Display',serif; line-height:1.3;"><?php echo h($post['title']); ?></h1>

            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-3 border-bottom text-muted small">
                <div>
                    <span class="me-3"><i class="bi bi-person-circle me-1"></i> <?php echo h($post['author_name']); ?></span>
                    <span class="me-3"><i class="bi bi-calendar3 me-1"></i> <?php echo $articleDateBn; ?></span>
                    <span><i class="bi bi-eye me-1"></i> <?php echo en2bn(number_format($post['views'])); ?> বার পড়া হয়েছে</span>
                </div>
                <div class="mt-2 mt-md-0">
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(SITE_URL . '/news/' . $post['slug']); ?>" target="_blank" class="btn btn-sm btn-primary"><i class="bi bi-facebook"></i></a>
                    <a href="https://api.whatsapp.com/send?text=<?php echo urlencode($post['title'] . ' ' . SITE_URL . '/news/' . $post['slug']); ?>" target="_blank" class="btn btn-sm btn-success"><i class="bi bi-whatsapp"></i></a>
                    <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode(SITE_URL . '/news/' . $post['slug']); ?>&text=<?php echo urlencode($post['title']); ?>" target="_blank" class="btn btn-sm btn-dark"><i class="bi bi-twitter-x"></i></a>
                </div>
            </div>

            <?php if ($post['image']): ?>
                <img src="<?php echo SITE_URL . '/' . $post['image']; ?>" class="img-fluid w-100 rounded mb-4" alt="<?php echo h($post['title']); ?>">
            <?php endif; ?>

            <div class="article-content">
                <?php echo nl2br($post['content']); ?>
            </div>

            <!-- Comments -->
            <div class="mt-5 pt-4 border-top">
                <h4 class="fw-bold mb-4">মতামত (<?php echo en2bn(count($comments)); ?>)</h4>

                <div class="card bg-light border-0 mb-4">
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo h(generateCsrfToken()); ?>">
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <input type="text" class="form-control" name="name" placeholder="আপনার নাম" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <textarea class="form-control" name="comment" rows="3" placeholder="মতামত লিখুন..." required></textarea>
                            </div>
                            <button type="submit" name="submit_comment" class="btn btn-danger">মতামত প্রকাশ করুন</button>
                        </form>
                    </div>
                </div>

                <?php foreach ($comments as $c): ?>
                <div class="d-flex mb-4">
                    <div class="flex-shrink-0"><i class="bi bi-person-circle fs-2 text-secondary"></i></div>
                    <div class="ms-3">
                        <h6 class="fw-bold mb-0"><?php echo h($c['name']); ?> <small class="text-muted fw-normal ms-2"><?php echo timeAgo($c['created_at']); ?></small></h6>
                        <p class="mb-0 mt-1"><?php echo nl2br(h($c['comment'])); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="bg-white rounded-3 shadow-sm p-4">
                <h5 class="fw-bold text-uppercase pb-2 mb-3 border-bottom border-danger border-2" style="font-size:.9rem;">সম্পর্কিত খবর</h5>
                <?php foreach ($relatedPosts as $rp): ?>
                <div class="mb-3 pb-3 border-bottom">
                    <img src="<?php echo $rp['image'] ? SITE_URL . '/' . $rp['image'] : 'https://via.placeholder.com/300x150/eee/999?text=N'; ?>"
                         class="w-100 rounded mb-2" style="height:140px; object-fit:cover;" alt="" loading="lazy">
                    <h6 class="fw-bold lh-sm"><a href="<?php echo SITE_URL; ?>/news/<?php echo h($rp['slug']); ?>" class="text-dark text-decoration-none"><?php echo h($rp['title']); ?></a></h6>
                    <small class="text-muted"><?php echo timeAgo($rp['created_at']); ?></small>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
