<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Notification.php';

// Check if user is logged in and is a lecturer
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Dosen') {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit;
}

$notificationModel = new Notification();
$userId = $_SESSION['user_id'];

// Get all notifications
$notifications = $notificationModel->getUserNotifications($userId);

// Mark all notifications as read
foreach ($notifications as $notification) {
    if (!$notification['is_read']) {
        $notificationModel->markAsRead($notification['notificationID']);
    }
}

// Update session unread count
$_SESSION['unread_notifications'] = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi - Aplikasi Pemantauan Emosi Harian</title>
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
                <h1 class="text-xl font-bold">Notifikasi</h1>
            </div>
            <div class="flex items-center space-x-4">
                <span><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                <a href="<?php echo BASE_URL; ?>auth/logout.php" class="text-blue-200 hover:text-white">Logout</a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto p-4 max-w-3xl">
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <!-- Notifications Header -->
            <div class="border-b border-gray-200 px-6 py-4">
                <div class="flex justify-between items-center">
                    <h2 class="text-lg font-semibold">Notifikasi Saya</h2>
                    <span class="text-sm text-gray-500"><?php echo count($notifications); ?> notifikasi</span>
                </div>
            </div>

            <!-- Notifications List -->
            <div class="divide-y divide-gray-200">
                <?php if (empty($notifications)): ?>
                    <div class="p-6 text-center text-gray-500">
                        <i class="fas fa-bell-slash text-4xl text-gray-300 mb-2"></i>
                        <p>Tidak ada notifikasi</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($notifications as $notification): ?>
                        <div class="p-4 hover:bg-gray-50 transition-colors">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                                        <i class="fas fa-bell"></i>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm text-gray-800">
                                        <?php echo htmlspecialchars($notification['message']); ?>
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        <?php echo $this->timeAgo($notification['created_at']); ?> yang lalu
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php
    // Helper function to format time ago
    function timeAgo($datetime) {
        $time = strtotime($datetime);
        $time = time() - $time;
        $time = ($time < 1) ? 1 : $time;
        $tokens = [
            31536000 => 'tahun',
            2592000 => 'bulan',
            604800 => 'minggu',
            86400 => 'hari',
            3600 => 'jam',
            60 => 'menit',
            1 => 'detik'
        ];

        foreach ($tokens as $unit => $text) {
            if ($time < $unit) continue;
            $numberOfUnits = floor($time / $unit);
            return $numberOfUnits . ' ' . $text . ($numberOfUnits > 1 ? '' : '');
        }
        return 'beberapa detik';
    }
    ?>
</body>
</html>
