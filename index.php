<?php
/**
 * Main entry point for the Emotion Monitoring App
 * This file handles basic routing for the application
 */

// Define application path and URL
$basePath = __DIR__;
$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];

// Load configuration
require_once $basePath . '/config/config.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get the requested URI
$requestUri = strtok($_SERVER['REQUEST_URI'], '?');
$requestUri = rtrim($requestUri, '/');

// Remove base path from request URI if needed
$basePath = str_replace('index.php', '', $_SERVER['SCRIPT_NAME']);
$requestPath = str_replace($basePath, '', $requestUri);

// Define routes
$routes = [
    '' => 'auth/login.php',
    'auth/login' => 'auth/login.php',
    'auth/register' => 'auth/register.php',
    'auth/logout' => 'auth/logout.php',
    'auth/process_login' => 'auth/process_login.php',
    'auth/process_register' => 'auth/process_register.php',
    'dosen' => 'dosen/dashboard.php',
    'dosen/dashboard' => 'dosen/dashboard.php',
    'dosen/students' => 'dosen/students.php',
    'dosen/student_detail' => 'dosen/student_detail.php',
    'dosen/recommendations' => 'dosen/recommendations.php',
    'dosen/notifications' => 'dosen/notifications.php',
    'dosen/profile' => 'dosen/profile.php',
    'mahasiswa' => 'mahasiswa/dashboard.php',
    'mahasiswa/dashboard' => 'mahasiswa/dashboard.php',
    'mahasiswa/emotion' => 'mahasiswa/emotion.php',
    'mahasiswa/history' => 'mahasiswa/history.php',
    'mahasiswa/recommendations' => 'mahasiswa/recommendations.php',
    'mahasiswa/profile' => 'mahasiswa/profile.php',
];

// Check if the requested route exists
if (array_key_exists($requestPath, $routes)) {
    $includeFile = $basePath . '/' . $routes[$requestPath];
    if (file_exists($includeFile)) {
        // Check authentication for protected routes
        $isAuthPage = strpos($requestPath, 'auth/') === 0;
        $isApi = strpos($requestPath, 'api/') === 0;
        
        if (!$isAuthPage && !$isApi) {
            if (!isset($_SESSION['user_id'])) {
                header('Location: ' . BASE_URL . 'auth/login.php');
                exit;
            }
            
            // Check role-based access
            $allowedRoles = [
                'dosen/' => ['Dosen'],
                'mahasiswa/' => ['Mahasiswa'],
            ];
            
            foreach ($allowedRoles as $prefix => $roles) {
                if (strpos($requestPath, $prefix) === 0 && !in_array($_SESSION['user_role'], $roles)) {
                    header('HTTP/1.0 403 Forbidden');
                    echo 'You do not have permission to access this page.';
                    exit;
                }
            }
        }
        
        // Include the requested file
        require_once $includeFile;
    } else {
        // File not found
        header('HTTP/1.0 404 Not Found');
        echo 'Page not found';
    }
} else {
    // Route not found
    header('HTTP/1.0 404 Not Found');
    echo 'Page not found';
}
