<?php
// Security Functions
function sanitize_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token)
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Authentication Functions
function is_logged_in()
{
    return isset($_SESSION['user_id']);
}

function is_admin()
{
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function require_login()
{
    if (!is_logged_in()) {
        header('Location: ' . SITE_URL . '/login.php');
        exit;
    }
}

function require_admin()
{
    if (!is_admin()) {
        header('Location: ' . SITE_URL . '/index.php');
        exit;
    }
}

// File Upload Functions
function upload_image($file, $destination_folder)
{
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $max_size = 5 * 1024 * 1024; // 5MB

    if (!in_array($file['type'], $allowed_types)) {
        return ['success' => false, 'message' => 'نوع الملف غير مسموح'];
    }

    if ($file['size'] > $max_size) {
        return ['success' => false, 'message' => 'حجم الملف كبير جداً'];
    }

    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $extension;
    $filepath = $destination_folder . $filename;

    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => true, 'filename' => $filename, 'path' => $filepath];
    }

    return ['success' => false, 'message' => 'فشل رفع الملف'];
}

// Pagination Functions
function paginate($total_items, $current_page = 1, $items_per_page = ITEMS_PER_PAGE)
{
    $total_pages = ceil($total_items / $items_per_page);
    $current_page = max(1, min($current_page, $total_pages));
    $offset = ($current_page - 1) * $items_per_page;

    return [
        'total_items' => $total_items,
        'total_pages' => $total_pages,
        'current_page' => $current_page,
        'items_per_page' => $items_per_page,
        'offset' => $offset
    ];
}

// URL Functions
function redirect($url)
{
    header('Location: ' . $url);
    exit;
}

function current_url()
{
    return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http")
        . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
}

// Date Functions
function format_date($date, $format = 'd/m/Y')
{
    return date($format, strtotime($date));
}

function time_ago($datetime)
{
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;

    if ($diff < 60)
        return 'الآن';
    if ($diff < 3600)
        return floor($diff / 60) . ' دقيقة';
    if ($diff < 86400)
        return floor($diff / 3600) . ' ساعة';
    if ($diff < 604800)
        return floor($diff / 86400) . ' يوم';
    if ($diff < 2592000)
        return floor($diff / 604800) . ' أسبوع';
    if ($diff < 31536000)
        return floor($diff / 2592000) . ' شهر';
    return floor($diff / 31536000) . ' سنة';
}

// Flash Messages
function set_flash($type, $message)
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash()
{
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// Slug Generator
function generate_slug($text)
{
    $text = preg_replace('/[^\\p{Arabic}a-zA-Z0-9\s-]/u', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    $text = trim($text, '-');
    return strtolower($text);
}
