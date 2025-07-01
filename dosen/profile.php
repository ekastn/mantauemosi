<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/User.php';

// Check if user is logged in and is a lecturer
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Dosen') {
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
    <title>Profil Dosen - Aplikasi Pemantauan Emosi Harian</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
                    <i class="fas fa-bell text-xl"></i>
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
            <h2 class="text-2xl font-bold mb-6">Profil Dosen</h2>
            
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
                <div class="flex items-center space-x-6">
                    <div class="flex-shrink-0">
                        <div class="h-20 w-20 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 text-2xl font-bold">
                            <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-lg font-medium"><?php echo htmlspecialchars($user['name']); ?></h3>
                        <p class="text-gray-500">Dosen</p>
                    </div>
                </div>
                
                <div class="border-t border-gray-200 pt-6">
                    <h3 class="text-lg font-medium mb-4">Informasi Akun</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                            <input type="text" name="name" required
                                   value="<?php echo htmlspecialchars($user['name']); ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" name="email" required
                                   value="<?php echo htmlspecialchars($user['email']); ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                </div>
                
                <div class="border-t border-gray-200 pt-6">
                    <h3 class="text-lg font-medium mb-4">Ubah Password</h3>
                    <p class="text-sm text-gray-500 mb-4">Biarkan kosong jika tidak ingin mengubah password.</p>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Password Saat Ini</label>
                            <input type="password" name="current_password"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Password Baru</label>
                                <input type="password" name="new_password"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password</label>
                                <input type="password" name="confirm_password"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end pt-6 border-t border-gray-200">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
