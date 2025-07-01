<?php
/**
 * Main entry point for the Emotion Monitoring App
 * Handles basic routing and authentication
 */

// Start session
session_start();

// Define base path
$basePath = __DIR__;

// Load configuration
require_once $basePath . '/config/config.php';
require_once $basePath . '/config/database.php';

// Set default timezone
date_default_timezone_set('Asia/Jakarta');

// Get the request URI and remove the base path
$requestUri = str_replace(BASE_PATH, '', $_SERVER['REQUEST_URI']);
$requestUri = strtok($requestUri, '?'); // Remove query string

// Remove trailing slash
$requestUri = rtrim($requestUri, '/');

// Define routes
$routes = [
    '' => 'auth/login.php',
    'login' => 'auth/login.php',
    'register' => 'auth/register.php',
    'logout' => 'auth/logout.php',
    'dosen' => 'dosen/dashboard.php',
    'dosen/dashboard' => 'dosen/dashboard.php',
    'dosen/students' => 'dosen/students.php',
    'dosen/student' => 'dosen/student_detail.php',
    'dosen/recommendations' => 'dosen/recommendations.php',
    'dosen/notifications' => 'dosen/notifications.php',
    'dosen/profile' => 'dosen/profile.php',
    'mahasiswa' => 'mahasiswa/dashboard.php',
    'mahasiswa/dashboard' => 'mahasiswa/dashboard.php',
];

// Check if the requested route exists
if (array_key_exists($requestUri, $routes)) {
    $includeFile = $routes[$requestUri];
    
    // Check if the file exists
    if (file_exists($basePath . '/' . $includeFile)) {
        // Check authentication for protected routes
        $protectedRoutes = [
            'dosen/dashboard.php',
            'dosen/students.php',
            'dosen/student_detail.php',
            'dosen/recommendations.php',
            'dosen/notifications.php',
            'dosen/profile.php',
            'mahasiswa/dashboard.php'
        ];
        
        if (in_array($includeFile, $protectedRoutes)) {
            if (!isset($_SESSION['user_id'])) {
                header('Location: ' . BASE_URL . 'login');
                exit;
            }
            
            // Check role-based access
            $allowedRoles = [
                'dosen/dashboard.php' => ['Dosen'],
                'dosen/students.php' => ['Dosen'],
                'dosen/student_detail.php' => ['Dosen'],
                'dosen/recommendations.php' => ['Dosen'],
                'dosen/notifications.php' => ['Dosen'],
                'dosen/profile.php' => ['Dosen'],
                'mahasiswa/dashboard.php' => ['Mahasiswa']
            ];
            
            if (isset($allowedRoles[$includeFile]) && !in_array($_SESSION['user_role'], $allowedRoles[$includeFile])) {
                header('HTTP/1.0 403 Forbidden');
                die('Anda tidak memiliki akses ke halaman ini.');
            }
        }
        
        // Include the requested file
        require_once $basePath . '/' . $includeFile;
    } else {
        // 404 Not Found
        header('HTTP/1.0 404 Not Found');
        require_once $basePath . '/views/errors/404.php';
    }
} else {
    // Try to find a matching route with parameters (e.g., dosen/student/123)
    $found = false;
    foreach ($routes as $route => $file) {
        $pattern = '#^' . preg_replace('/:([^/]+)/', '([^/]+)', $route) . '$#';
        if (preg_match($pattern, $requestUri, $matches)) {
            $found = true;
            require_once $basePath . '/' . $file;
            break;
        }
    }
    
    if (!$found) {
        // 404 Not Found
        header('HTTP/1.0 404 Not Found');
        require_once $basePath . '/views/errors/404.php';
    }
}
