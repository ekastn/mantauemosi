<?php
/**
 * Application Configuration
 * 
 * This file contains configuration settings for the Emotion Monitoring App.
 */

// Determine if we're running on localhost or production
$isLocal = in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']);

// Application URL - Update this with your DomCloud URL
if ($isLocal) {
    define('BASE_URL', 'http://localhost/rpl2e/');
} else {
    // Replace 'your-app-name' with your actual DomCloud app name
    define('BASE_URL', 'https://your-app-name.domcloud.io/');
}

// Database configuration
define('DB_HOST', $isLocal ? 'localhost' : 'localhost');
define('DB_NAME', 'emotion_tracker');
define('DB_USER', $isLocal ? 'root' : 'your_db_username');
define('DB_PASS', $isLocal ? '' : 'your_db_password');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_lifetime' => 86400, // 24 hours
        'cookie_secure' => !$isLocal, // Only send cookie over HTTPS in production
        'cookie_httponly' => true, // Prevent JavaScript access to session cookie
        'use_strict_mode' => true // Enable strict session ID mode
    ]);
}

// Autoload classes
spl_autoload_register(function ($class) {
    $paths = [
        'models/',
        'controllers/',
        'middleware/'
    ];
    
    foreach ($paths as $path) {
        $file = __DIR__ . '/../' . $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Error reporting
if ($isLocal) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../storage/logs/error.log');
}

// Set default timezone
date_default_timezone_set('Asia/Jakarta');

// Security headers
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 1; mode=block');
    
    if (!$isLocal) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}

// Set timezone
date_default_timezone_set('Asia/Jakarta');

// Include database connection
require_once __DIR__ . '/database.php';
?>
