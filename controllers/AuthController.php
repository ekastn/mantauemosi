<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Notification.php';

class AuthController {
    private $userModel;
    private $notificationModel;

    public function __construct() {
        $this->userModel = new User();
        $this->notificationModel = new Notification();
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            error_log('Login attempt started');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            
            error_log('Email: ' . $email);
            
            if (empty($email) || empty($password)) {
                error_log('Email or password empty');
                $_SESSION['error'] = 'Email dan password harus diisi';
                header('Location: ' . BASE_URL . 'auth/login.php');
                exit;
            }

            $user = $this->userModel->findByEmail($email);
            
            if (!$user) {
                error_log('User not found: ' . $email);
                $_SESSION['error'] = 'Email atau password salah';
                header('Location: ' . BASE_URL . 'auth/login.php');
                exit;
            }
            
            error_log('User found: ' . print_r($user, true));
            error_log('Input password: ' . $password);
            error_log('Stored hash: ' . $user['password']);
            
            $isValid = password_verify($password, $user['password']);
            error_log('Password verify result: ' . ($isValid ? 'true' : 'false'));
            
            if ($isValid) {
                $_SESSION['user_id'] = $user['userID'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];
                
                // Check for unread notifications
                $unreadCount = $this->notificationModel->getUnreadCount($user['userID']);
                $_SESSION['unread_notifications'] = $unreadCount;
                
                // Redirect based on role
                if ($user['role'] === 'Dosen') {
                    header('Location: ' . BASE_URL . 'dosen/dashboard.php');
                } else {
                    header('Location: ' . BASE_URL . 'mahasiswa/dashboard.php');
                }
                exit;
            } else {
                $_SESSION['error'] = 'Email atau password salah';
                header('Location: ' . BASE_URL . 'auth/login.php');
                exit;
            }
        }
    }

    public function logout() {
        // Unset all session variables
        $_SESSION = array();
        
        // Destroy the session
        session_destroy();
        
        // Redirect to login page
        header('Location: ' . BASE_URL . 'auth/login.php');
        exit;
    }
}
?>
