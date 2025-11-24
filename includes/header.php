<?php
// Load config first before using constants
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

// Now we can use SESSION_NAME constant
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}

$db = Database::getInstance()->getConnection();
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?><?php echo SITE_NAME; ?></title>
    <meta name="description" content="منصة كروت المعايدة - صمم كروت معايدة احترافية للمناسبات العربية">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    
    <!-- Fabric.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.0/fabric.min.js"></script>
    
    <!-- Main CSS -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    
    <?php if (isset($extra_css)): ?>
        <?php foreach ($extra_css as $css): ?>
            <link rel="stylesheet" href="<?php echo SITE_URL . '/assets/css/' . $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <!-- Flash Messages -->
    <?php if ($flash): ?>
        <div class="flash-message flash-<?php echo $flash['type']; ?>" id="flash-message">
            <?php echo $flash['message']; ?>
        </div>
    <?php endif; ?>
    
    <!-- Header -->
    <header class="header">
        <nav class="navbar">
            <div class="container">
                <div class="navbar-brand">
                    <a href="<?php echo SITE_URL; ?>" class="logo">
                        🎨 <?php echo SITE_NAME; ?>
                    </a>
                    <button class="hamburger" id="hamburger" aria-label="Toggle menu">
                        <span></span>
                        <span></span>
                        <span></span>
                    </button>
                </div>
                
                <div class="navbar-menu" id="navbar-menu">
                    <a href="<?php echo SITE_URL; ?>" class="nav-link">الرئيسية</a>
                    <a href="<?php echo SITE_URL; ?>/templates.php" class="nav-link">القوالب</a>
                    <a href="<?php echo SITE_URL; ?>/greetings.php" class="nav-link">رسائل نصية</a>
                    <a href="<?php echo SITE_URL; ?>/blog.php" class="nav-link">المدونة</a>
                    
                    <?php if (is_logged_in()): ?>
                        <a href="<?php echo SITE_URL; ?>/dashboard.php" class="nav-link">لوحة التحكم</a>
                        <?php if (is_admin()): ?>
                            <a href="<?php echo SITE_URL; ?>/admin/" class="nav-link admin-link">الإدارة</a>
                        <?php endif; ?>
                        <a href="<?php echo SITE_URL; ?>/logout.php" class="nav-link">تسجيل خروج</a>
                    <?php else: ?>
                        <a href="<?php echo SITE_URL; ?>/login.php" class="nav-link">تسجيل دخول</a>
                        <a href="<?php echo SITE_URL; ?>/register.php" class="btn btn-primary">حساب جديد</a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header>
    
    <script>
        // Hamburger menu toggle
        document.addEventListener('DOMContentLoaded', function() {
            const hamburger = document.getElementById('hamburger');
            const navbarMenu = document.getElementById('navbar-menu');
            
            if (hamburger) {
                hamburger.addEventListener('click', function(e) {
                    e.stopPropagation();
                    this.classList.toggle('active');
                    navbarMenu.classList.toggle('active');
                });

                // Close menu when clicking outside
                document.addEventListener('click', function(event) {
                    if (!event.target.closest('.navbar')) {
                        hamburger.classList.remove('active');
                        navbarMenu.classList.remove('active');
                    }
                });

                // Close menu when clicking a link
                navbarMenu.querySelectorAll('.nav-link, .btn').forEach(link => {
                    link.addEventListener('click', function() {
                        hamburger.classList.remove('active');
                        navbarMenu.classList.remove('active');
                    });
                });
            }
        });
    </script>
    
    <!-- Main Content -->
    <main class="main-content">