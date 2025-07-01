<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/EmotionRecord.php';
require_once __DIR__ . '/../models/Notification.php';
require_once __DIR__ . '/../models/ManualRecommendation.php';

// Check if user is logged in and is a lecturer
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Dosen') {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit;
}

$userModel = new User();
$emotionModel = new EmotionRecord();
$notificationModel = new Notification();
$recommendationModel = new ManualRecommendation();

$userId = $_SESSION['user_id'];

// Get counts for dashboard
$totalStudents = count($userModel->getStudents());
$recentEmotions = $emotionModel->getRecent(5);
$unreadNotifications = $notificationModel->getUnreadCount($userId);
$recentRecommendations = $recommendationModel->getByDosen($userId, 5);

// Update session unread count
$_SESSION['unread_notifications'] = $unreadNotifications;

// Get emotion statistics for chart
$emotionStats = $emotionModel->getEmotionStats();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Dosen - Aplikasi Pemantauan Emosi Harian</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100">
    <!-- Header -->
    <header class="bg-blue-600 text-white p-4 shadow">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center">
                <h1 class="text-xl font-bold">Aplikasi Pemantauan Emosi Harian</h1>
            </div>
            <div class="flex items-center space-x-4">
                <a href="notifications.php" class="relative">
                    <i class="fas fa-bell text-xl"></i>
                    <?php if ($unreadNotifications > 0): ?>
                        <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-4 w-4 flex items-center justify-center">
                            <?php echo $unreadNotifications; ?>
                        </span>
                    <?php endif; ?>
                </a>
                <span><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                <a href="<?php echo BASE_URL; ?>auth/logout.php" class="text-blue-200 hover:text-white">Logout</a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="flex h-screen">
        <!-- Sidebar -->
        <aside class="bg-white w-64 shadow-lg">
            <div class="p-4">
                <div class="flex items-center space-x-3 p-3 bg-blue-50 rounded-lg">
                    <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-semibold">
                        <?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?>
                    </div>
                    <div>
                        <p class="font-medium text-sm"><?php echo htmlspecialchars($_SESSION['user_name']); ?></p>
                        <p class="text-xs text-gray-500">Dosen</p>
                    </div>
                </div>
            </div>
            <nav class="mt-2">
                <a href="dashboard.php" class="flex items-center px-6 py-3 text-blue-600 bg-blue-50 border-r-4 border-blue-600">
                    <i class="fas fa-home mr-3"></i>
                    <span>Dashboard</span>
                </a>
                <a href="students.php" class="flex items-center px-6 py-3 text-gray-600 hover:bg-gray-100">
                    <i class="fas fa-users mr-3"></i>
                    <span>Daftar Mahasiswa</span>
                </a>
                <a href="recommendations.php" class="flex items-center px-6 py-3 text-gray-600 hover:bg-gray-100">
                    <i class="fas fa-comment-medical mr-3"></i>
                    <span>Rekomendasi</span>
                </a>
                <a href="notifications.php" class="flex items-center px-6 py-3 text-gray-600 hover:bg-gray-100">
                    <i class="fas fa-bell mr-3"></i>
                    <span>Notifikasi</span>
                    <?php if ($unreadNotifications > 0): ?>
                        <span class="ml-auto bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                            <?php echo $unreadNotifications; ?>
                        </span>
                    <?php endif; ?>
                </a>
                <a href="profile.php" class="flex items-center px-6 py-3 text-gray-600 hover:bg-gray-100">
                    <i class="fas fa-user mr-3"></i>
                    <span>Profil Saya</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto p-6">
            <h2 class="text-2xl font-bold mb-6">Dashboard Dosen</h2>
            
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4">
                            <i class="fas fa-users text-xl"></i>
                        </div>
                        <div>
                            <p class="text-gray-500 text-sm">Total Mahasiswa</p>
                            <p class="text-2xl font-bold"><?php echo $totalStudents; ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-100 text-green-600 mr-4">
                            <i class="fas fa-smile text-xl"></i>
                        </div>
                        <div>
                            <p class="text-gray-500 text-sm">Catatan Emosi Hari Ini</p>
                            <p class="text-2xl font-bold"><?php echo count($recentEmotions); ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-yellow-100 text-yellow-600 mr-4">
                            <i class="fas fa-bell text-xl"></i>
                        </div>
                        <div>
                            <p class="text-gray-500 text-sm">Notifikasi Belum Dibaca</p>
                            <p class="text-2xl font-bold"><?php echo $unreadNotifications; ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Emotion Distribution Chart -->
                <div class="lg:col-span-2 bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold mb-4">Distribusi Emosi Mahasiswa</h3>
                    <div class="h-64">
                        <canvas id="emotionChart"></canvas>
                    </div>
                </div>
                
                <!-- Recent Recommendations -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Rekomendasi Terbaru</h3>
                        <a href="recommendations.php" class="text-sm text-blue-600 hover:underline">Lihat Semua</a>
                    </div>
                    <div class="space-y-4">
                        <?php if (empty($recentRecommendations)): ?>
                            <p class="text-gray-500 text-sm">Belum ada rekomendasi</p>
                        <?php else: ?>
                            <?php foreach ($recentRecommendations as $rec): 
                                $student = $userModel->find($rec['mahasiswaID']);
                            ?>
                                <div class="border-l-4 border-blue-500 pl-4 py-2">
                                    <p class="text-sm font-medium">
                                        <a href="student_detail.php?id=<?php echo $student['userID']; ?>" class="text-blue-600 hover:underline">
                                            <?php echo htmlspecialchars($student['name']); ?>
                                        </a>
                                    </p>
                                    <p class="text-sm text-gray-600 truncate"><?php echo htmlspecialchars($rec['message']); ?></p>
                                    <p class="text-xs text-gray-400 mt-1">
                                        <?php echo date('d M Y', strtotime($rec['created_at'])); ?>
                                    </p>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Recent Emotions -->
            <div class="bg-white rounded-lg shadow p-6 mt-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">Catatan Emosi Terbaru</h3>
                    <a href="students.php" class="text-sm text-blue-600 hover:underline">Lihat Semua</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mahasiswa</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Emosi</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deskripsi</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (empty($recentEmotions)): ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">Tidak ada data emosi terbaru</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recentEmotions as $emotion): 
                                    $student = $userModel->find($emotion['userID']);
                                ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-semibold text-sm mr-2">
                                                    <?php echo strtoupper(substr($student['name'], 0, 1)); ?>
                                                </div>
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($student['name']); ?></div>
                                                    <div class="text-xs text-gray-500"><?php echo htmlspecialchars($student['email']); ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 text-xs rounded-full <?php echo $this->getEmotionColor($emotion['emotionType']); ?>">
                                                <?php echo $emotion['emotionType']; ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500">
                                            <?php echo !empty($emotion['description']) ? 
                                                htmlspecialchars(substr($emotion['description'], 0, 50)) . (strlen($emotion['description']) > 50 ? '...' : '') : 
                                                '<span class="text-gray-400">Tidak ada deskripsi</span>'; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo date('d M Y H:i', strtotime($emotion['timestamp'])); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="student_detail.php?id=<?php echo $student['userID']; ?>" class="text-blue-600 hover:text-blue-900">Detail</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Emotion Distribution Chart
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

        // Helper function to get color based on emotion type
        function getEmotionColor(emotionType) {
            const colors = {
                'Senang': 'bg-green-100 text-green-800',
                'Sedih': 'bg-blue-100 text-blue-800',
                'Marah': 'bg-red-100 text-red-800',
                'Cemas': 'bg-yellow-100 text-yellow-800',
                'Lelah': 'bg-purple-100 text-purple-800',
                'Bosan': 'bg-gray-100 text-gray-800',
                'Bersemangat': 'bg-pink-100 text-pink-800',
                'Tenang': 'bg-indigo-100 text-indigo-800'
            };
            return colors[emotionType] || 'bg-gray-100 text-gray-800';
        }
    </script>
</body>
</html>
