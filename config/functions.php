<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Output escaping
 */
function h($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Flash messaging
 */
function setFlash($message, $type = 'success') {
    $_SESSION['flash'] = [
        'message' => $message,
        'type'    => $type
    ];
}

function displayFlash() {
    if (isset($_SESSION['flash'])) {
        $msg  = $_SESSION['flash']['message'];
        $type = $_SESSION['flash']['type'];
        echo "<div class='alert alert-{$type} alert-dismissible fade show mb-4'>
                {$msg}
                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
              </div>";
        unset($_SESSION['flash']);
    }
}

/**
 * CSRF Protection
 */
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken($token) {
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        die('CSRF token validation failed.');
    }
    return true;
}

/**
 * Redirection
 */
function redirect($url) {
    header("Location: " . SITE_URL . $url);
    exit;
}

/**
 * Authentication Helpers
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        redirect('/admin/login.php');
    }
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        die("অ্যাক্সেস ডিনাইড (Access Denied). আপনি অ্যাডমিন নন।");
    }
}

function isEditor() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'editor';
}

function requireAdminOrEditor() {
    requireLogin();
    if (!isAdmin() && !isEditor()) {
        die("অ্যাক্সেস ডিনাইড (Access Denied). আপনি অ্যাডমিন বা এডিটর নন।");
    }
}

function isReporter() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'reporter';
}

function requireReporter() {
    requireLogin();
    if (!isReporter()) {
        die("অ্যাক্সেস ডিনাইড (Access Denied). শুধুমাত্র রিপোর্টারদের জন্য।");
    }
}

/**
 * Format timestamp as "Time Ago" in Bengali
 */
function timeAgo($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $w = floor($diff->d / 7);
    $d = $diff->d - ($w * 7);

    $parts = [
        'y' => $diff->y,
        'm' => $diff->m,
        'w' => $w,
        'd' => $d,
        'h' => $diff->h,
        'i' => $diff->i,
        's' => $diff->s,
    ];

    $string = array(
        'y' => 'বছর',
        'm' => 'মাস',
        'w' => 'সপ্তাহ',
        'd' => 'দিন',
        'h' => 'ঘণ্টা',
        'i' => 'মিনিট',
        's' => 'সেকেন্ড',
    );
    foreach ($string as $k => &$v) {
        if ($parts[$k]) {
            $v = en2bn($parts[$k]) . ' ' . $v;
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' আগে' : 'এইমাত্র';
}

/**
 * Status Badge Formatter (Bengali)
 */
function getStatusBadge($status) {
    switch ($status) {
        case 'published':
            return '<span class="badge bg-success">প্রকাশিত</span>';
        case 'pending':
            return '<span class="badge bg-warning text-dark">অপেক্ষমান</span>';
        case 'draft':
            return '<span class="badge bg-secondary">খসড়া</span>';
        case 'rejected':
            return '<span class="badge bg-danger">বাতিল</span>';
        case 'active':
            return '<span class="badge bg-success">সক্রিয়</span>';
        case 'inactive':
            return '<span class="badge bg-secondary">নিষ্ক্রিয়</span>';
        default:
            return '<span class="badge bg-light text-dark">' . h($status) . '</span>';
    }
}

/**
 * English to Bengali numbers converter
 */
function en2bn($number) {
    $eng = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    $bng = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
    return str_replace($eng, $bng, $number);
}

/**
 * Helper to fetch breaking news
 */
function getBreakingNews($pdo) {
    $stmt = $pdo->query("SELECT title, slug FROM posts WHERE breaking_news = 1 AND status = 'published' ORDER BY created_at DESC LIMIT 5");
    return $stmt->fetchAll();
}
