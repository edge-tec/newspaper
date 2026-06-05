<?php
// Output sanitation
function h($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

// Redirect utility
function redirect($url) {
    header("Location: " . SITE_URL . $url);
    exit;
}

// Generate a slug from a string
function createSlug($string) {
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string)));
    return trim($slug, '-');
}

// ── Role checks ──────────────────────────────────────────────
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function isAdminOrEditor() {
    return isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin', 'editor']);
}

function isReporter() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'reporter';
}

// ── Guard helpers ────────────────────────────────────────────
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['error'] = 'Please log in to continue.';
        redirect('/admin/login.php');
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        $_SESSION['error'] = 'Admin access required.';
        redirect('/admin/index.php');
    }
}

function requireAdminOrEditor() {
    requireLogin();
    if (!isAdminOrEditor()) {
        $_SESSION['error'] = 'Access denied.';
        redirect('/admin/login.php');
    }
}

function requireReporter() {
    requireLogin();
    if (!isReporter()) {
        $_SESSION['error'] = 'Reporter access required.';
        redirect('/admin/login.php');
    }
}

// ── CSRF ─────────────────────────────────────────────────────
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// ── Breaking News ────────────────────────────────────────────
function getBreakingNews($pdo) {
    $stmt = $pdo->query("SELECT id, title, slug FROM posts WHERE status = 'published' AND breaking_news = 1 ORDER BY created_at DESC LIMIT 5");
    return $stmt->fetchAll();
}

// ── Date helpers ─────────────────────────────────────────────
function formatDate($dateString) {
    $date = new DateTime($dateString);
    return $date->format('F j, Y, g:i a');
}

function timeAgo($datetime) {
    $now  = new DateTime();
    $past = new DateTime($datetime);
    $diff = $now->diff($past);

    if ($diff->y > 0) return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
    if ($diff->m > 0) return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
    if ($diff->d > 0) return $diff->d . ' day'   . ($diff->d > 1 ? 's' : '') . ' ago';
    if ($diff->h > 0) return $diff->h . ' hour'  . ($diff->h > 1 ? 's' : '') . ' ago';
    if ($diff->i > 0) return $diff->i . ' min'   . ($diff->i > 1 ? 's' : '') . ' ago';
    return 'Just now';
}

// ── Status badge ─────────────────────────────────────────────
function getStatusBadge($status) {
    $map = [
        'draft'     => '<span class="badge bg-secondary">Draft</span>',
        'pending'   => '<span class="badge bg-warning text-dark">Pending</span>',
        'published' => '<span class="badge bg-success">Published</span>',
        'rejected'  => '<span class="badge bg-danger">Rejected</span>',
        'active'    => '<span class="badge bg-success">Active</span>',
        'inactive'  => '<span class="badge bg-secondary">Inactive</span>',
    ];
    return $map[$status] ?? '<span class="badge bg-light text-dark">' . h($status) . '</span>';
}

// ── Flash messages ───────────────────────────────────────────
function flashSuccess() {
    if (isset($_SESSION['success'])) {
        echo '<div class="alert alert-success alert-dismissible fade show">' . h($_SESSION['success'])
           . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
        unset($_SESSION['success']);
    }
}

function flashError() {
    if (isset($_SESSION['error'])) {
        echo '<div class="alert alert-danger alert-dismissible fade show">' . h($_SESSION['error'])
           . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
        unset($_SESSION['error']);
    }
}

function flashMessages() {
    flashSuccess();
    flashError();
}
