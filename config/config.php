<?php
// Application configuration
define('BASE_URL', getenv('BASE_URL') ?: 'http://localhost/');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
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
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set timezone
date_default_timezone_set('Asia/Jakarta');

// Include database connection
require_once __DIR__ . '/database.php';
?>
