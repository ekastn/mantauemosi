<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/User.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Mahasiswa') {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit;
}

$userModel = new User();
$userId = $_SESSION['user_id'];
$user = $userModel->find($userId);
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate input
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Basic validation
    if (empty($name) || empty($email)) {
        $error = 'Nama dan email harus diisi';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid';
    } elseif (!empty($newPassword) && $newPassword !== $confirmPassword) {
        $error = 'Konfirmasi password tidak cocok';
    } else {
        // Check if email is already taken by another user
        $existingUser = $userModel->findByEmail($email);
        if ($existingUser && $existingUser['userID'] !== $userId) {
            $error = 'Email sudah digunakan oleh pengguna lain';
        } else {
            // Verify current password if changing password
            if (!empty($newPassword)) {
                if (empty($currentPassword) || !password_verify($currentPassword, $user['password'])) {
                    $error = 'Password saat ini tidak valid';
                } else {
                    // Update password
                    $updateData = [
                        'name' => $name,
                        'email' => $email,
                        'password' => password_hash($newPassword, PASSWORD_DEFAULT)
                    ];
                }
            } else {
                // Update without password
                $updateData = [
                    'name' => $name,
                    'email' => $email
                ];
            }
            
            if (empty($error)) {
                // Update user data
                if ($userModel->update($userId, $updateData)) {
                    $_SESSION['user_name'] = $name;
                    $user = array_merge($user, $updateData);
                    $success = 'Profil berhasil diperbarui';
                } else {
                    $error = 'Gagal memperbarui profil. Silakan coba lagi.';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - Aplikasi Pemantauan Emosi Harian</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <!-- Header -->
    <header class="bg-blue-600 text-white p-4 shadow">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center">
                <a href="dashboard.php" class="mr-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <h1 class="text-xl font-bold">Profil Saya</h1>
            </div>
            <div class="flex items-center space-x-4">
                <a href="notifications.php" class="relative">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <?php if ($_SESSION['unread_notifications'] > 0): ?>
                        <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-4 w-4 flex items-center justify-center">
                            <?php echo $_SESSION['unread_notifications']; ?>
                        </span>
                    <?php endif; ?>
                </a>
                <span><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                <a href="<?php echo BASE_URL; ?>auth/logout.php" class="text-blue-200 hover:text-white">Logout</a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto p-4 max-w-3xl">
        <div class="bg-white rounded-lg shadow p-6">
            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <form action="" method="POST" class="space-y-6">
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="name">
                        Nama Lengkap
                    </label>
                    <input type="text" id="name" name="name" required
                           value="<?php echo htmlspecialchars($user['name']); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="email">
                        Email
                    </label>
                    <input type="email" id="email" name="email" required
                           value="<?php echo htmlspecialchars($user['email']); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div class="border-t border-gray-200 pt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Ganti Password</h3>
                    <p class="text-sm text-gray-500 mb-4">Biarkan kosong jika tidak ingin mengubah password.</p>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="current_password">
                                Password Saat Ini
                            </label>
                            <input type="password" id="current_password" name="current_password"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="new_password">
                                Password Baru
                            </label>
                            <input type="password" id="new_password" name="new_password"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="confirm_password">
                                Konfirmasi Password Baru
                            </label>
                            <input type="password" id="confirm_password" name="confirm_password"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-4 pt-4">
                    <a href="dashboard.php" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Batal
                    </a>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
