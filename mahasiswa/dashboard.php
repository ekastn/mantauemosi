<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/EmotionController.php';
require_once __DIR__ . '/../controllers/RecommendationController.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Mahasiswa') {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit;
}

$emotionController = new EmotionController();
$recommendationController = new RecommendationController();
$notificationModel = new Notification();
$userModel = new User();

// Get user data
$userId = $_SESSION['user_id'];
$user = $userModel->find($userId);

// Get emotion stats
$emotionStats = $emotionController->getEmotionStats($userId);
$recentEmotions = $emotionController->getRecentEmotions($userId, 5);
$recommendations = $recommendationController->getStudentRecommendations($userId);
$notifications = $notificationModel->getUserNotifications($userId, 5);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Mahasiswa - Aplikasi Pemantauan Emosi Harian</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100">
    <!-- Header -->
    <header class="bg-blue-600 text-white p-4 shadow">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold">Dashboard Mahasiswa</h1>
            <div class="flex items-center space-x-4">
                <span class="relative">
                    <a href="notifications.php" class="text-white hover:text-blue-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <?php if ($_SESSION['unread_notifications'] > 0): ?>
                            <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                                <?php echo $_SESSION['unread_notifications']; ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </span>
                <span><?php echo htmlspecialchars($user['name']); ?></span>
                <a href="<?php echo BASE_URL; ?>auth/logout.php" class="text-blue-200 hover:text-white">Logout</a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto p-4">
        <!-- Welcome Section -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-semibold mb-2">Selamat datang, <?php echo htmlspecialchars($user['name']); ?>!</h2>
            <p class="text-gray-600">Bagaimana perasaan Anda hari ini?</p>
            
            <!-- Record Emotion Button -->
            <div class="mt-4">
                <a href="record_emotion.php" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
                    </svg>
                    Catat Emosi Hari Ini
                </a>
            </div>
        </div>

        <!-- Stats and Recent Emotions -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Emotion Stats -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">Statistik Emosi</h3>
                <div class="h-64">
                    <canvas id="emotionChart"></canvas>
                </div>
            </div>

            <!-- Recent Emotions -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">Emosi Terakhir</h3>
                <div class="space-y-4">
                    <?php if (empty($recentEmotions)): ?>
                        <p class="text-gray-500">Belum ada catatan emosi.</p>
                    <?php else: ?>
                        <?php foreach ($recentEmotions as $emotion): ?>
                            <div class="border-b pb-3 last:border-b-0 last:pb-0">
                                <div class="flex justify-between items-center">
                                    <span class="font-medium"><?php echo htmlspecialchars($emotion['emotionType']); ?></span>
                                    <span class="text-sm text-gray-500"><?php echo date('d M Y H:i', strtotime($emotion['timestamp'])); ?></span>
                                </div>
                                <?php if (!empty($emotion['description'])): ?>
                                    <p class="text-sm text-gray-600 mt-1"><?php echo htmlspecialchars($emotion['description']); ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recommendations -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h3 class="text-lg font-semibold mb-4">Rekomendasi untuk Anda</h3>
            <?php if (empty($recommendations)): ?>
                <p class="text-gray-500">Belum ada rekomendasi.</p>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($recommendations as $rec): ?>
                        <div class="border-l-4 border-blue-500 pl-4 py-2">
                            <p class="text-gray-700"><?php echo htmlspecialchars($rec['message']); ?></p>
                            <p class="text-sm text-gray-500 mt-1">Dari: <?php echo htmlspecialchars($rec['dosen_name']); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script>
        // Emotion Chart
        const emotionCtx = document.getElementById('emotionChart').getContext('2d');
        const emotionData = {
            labels: [
                <?php 
                $emotionLabels = [];
                $emotionCounts = [];
                foreach ($emotionStats as $stat) {
                    $emotionLabels[] = '"' . $stat['emotionType'] . '"';
                    $emotionCounts[] = $stat['count'];
                }
                echo implode(',', $emotionLabels);
                ?>
            ],
            datasets: [{
                data: [<?php echo implode(',', $emotionCounts); ?>],
                backgroundColor: [
                    '#3B82F6', // blue
                    '#10B981', // green
                    '#F59E0B', // yellow
                    '#EF4444', // red
                    '#8B5CF6', // purple
                    '#EC4899', // pink
                    '#14B8A6', // teal
                    '#F97316'  // orange
                ]
            }]
        };

        new Chart(emotionCtx, {
            type: 'doughnut',
            data: emotionData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                    }
                }
            }
        });
    </script>
</body>
</html>
