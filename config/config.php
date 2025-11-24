<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'go_cards');  // Change this
define('DB_PASS', 'Fares19911952');      // Change this
define('DB_NAME', 'go_cards');  // Change this

// Site Configuration
define('SITE_NAME', 'منصة كروت المعايدة');
define('SITE_URL', 'https://go.demaghfull.com');
define('ADMIN_EMAIL', 'faressallam@gmail.com');

// Paths
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('TEMPLATE_PATH', UPLOAD_PATH . 'templates/');
define('EMOJI_PATH', UPLOAD_PATH . 'emojis/');

// Security
define('SESSION_NAME', 'greeting_cards_session');
define('HASH_ALGO', PASSWORD_BCRYPT);

// Pagination
define('ITEMS_PER_PAGE', 12);

// Timezone
date_default_timezone_set('Africa/Cairo');

// Error Reporting (Set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
