<?php
require_once __DIR__ . '/../config/config.php';

// Redirect jika sudah login
if (isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . ($_SESSION['user_role'] === 'Dosen' ? 'dosen' : 'mahasiswa') . '/dashboard.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $role = 'Mahasiswa'; // Default role
    
    // Validasi input
    if (empty($name) || empty($email) || empty($password) || empty($confirmPassword)) {
        $error = 'Semua field harus diisi';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid';
    } elseif ($password !== $confirmPassword) {
        $error = 'Konfirmasi password tidak cocok';
    } elseif (strlen($password) < 8) {
        $error = 'Password minimal 8 karakter';
    } else {
        // Cek apakah email sudah terdaftar
        require_once __DIR__ . '/../models/User.php';
        $userModel = new User();
        
        if ($userModel->findByEmail($email)) {
            $error = 'Email sudah terdaftar';
        } else {
            // Buat akun baru
            $userId = uniqid('usr_', true);
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            $userData = [
                'userID' => $userId,
                'name' => $name,
                'email' => $email,
                'password' => $hashedPassword,
                'role' => $role
            ];
            
            if ($userModel->create($userData)) {
                $success = 'Pendaftaran berhasil! Silakan login.';
                // Kosongkan form
                $name = $email = '';
            } else {
                $error = 'Terjadi kesalahan. Silakan coba lagi.';
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
    <title>Daftar Akun - Aplikasi Pemantauan Emosi Harian</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 28rem;
        }
    </style>
</head>
<body>
    <div class="px-4 py-8 w-full">
        <div class="card mx-auto overflow-hidden">
            <div class="p-8">
                <div class="text-center mb-8">
                    <h1 class="text-2xl font-bold text-gray-800">Daftar Akun Baru</h1>
                    <p class="text-gray-600">Daftar untuk memulai menggunakan aplikasi</p>
                </div>

                <?php if ($error): ?>
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                        <p><?php echo htmlspecialchars($error); ?></p>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                        <p><?php echo htmlspecialchars($success); ?></p>
                    </div>
                <?php endif; ?>

                <form action="" method="POST" class="space-y-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                        <input type="text" id="name" name="name" required
                               value="<?php echo htmlspecialchars($name ?? ''); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Masukkan nama lengkap">
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" id="email" name="email" required
                               value="<?php echo htmlspecialchars($email ?? ''); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="contoh@email.com">
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <input type="password" id="password" name="password" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Minimal 8 karakter">
                    </div>

                    <div>
                        <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Ketik ulang password">
                    </div>

                    <div class="pt-2">
                        <button type="submit"
                                class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out">
                            Daftar Sekarang
                        </button>
                    </div>
                </form>

                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-600">
                        Sudah punya akun? 
                        <a href="login.php" class="font-medium text-blue-600 hover:text-blue-500">
                            Masuk disini
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
