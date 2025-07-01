<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/EmotionRecord.php';
require_once __DIR__ . '/../models/ManualRecommendation.php';

// Check if user is logged in and is a lecturer
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Dosen') {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: students.php');
    exit;
}

$userModel = new User();
$emotionModel = new EmotionRecord();
$recommendationModel = new ManualRecommendation();

$studentId = $_GET['id'];
$student = $userModel->find($studentId);

if (!$student || $student['role'] !== 'Mahasiswa') {
    $_SESSION['error'] = 'Data mahasiswa tidak ditemukan';
    header('Location: students.php');
    exit;
}

// Get student's emotion history
$emotions = $emotionModel->getByUser($studentId, 30);
$emotionStats = $emotionModel->getEmotionStats($studentId);
$recommendations = $recommendationModel->getForStudent($studentId);

// Handle recommendation submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    try {
        $recommendationData = [
            'dosenID' => $_SESSION['user_id'],
            'mahasiswaID' => $studentId,
            'emotionID' => $_POST['emotion_id'],
            'message' => trim($_POST['message'])
        ];
        
        if ($recommendationModel->create($recommendationData)) {
            $_SESSION['success'] = 'Rekomendasi berhasil dikirim';
            header('Location: student_detail.php?id=' . $studentId);
            exit;
        } else {
            $error = 'Gagal mengirim rekomendasi. Silakan coba lagi.';
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Mahasiswa - Aplikasi Pemantauan Emosi Harian</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100">
    <!-- Header -->
    <header class="bg-blue-600 text-white p-4 shadow">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center">
                <a href="students.php" class="mr-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <h1 class="text-xl font-bold">Detail Mahasiswa</h1>
            </div>
            <div class="flex items-center space-x-4">
                <span><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                <a href="<?php echo BASE_URL; ?>auth/logout.php" class="text-blue-200 hover:text-white">Logout</a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto p-4">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <!-- Student Info -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="flex items-start justify-between">
                <div class="flex items-center">
                    <div class="h-16 w-16 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 text-2xl font-bold">
                        <?php echo strtoupper(substr($student['name'], 0, 1)); ?>
                    </div>
                    <div class="ml-4">
                        <h2 class="text-2xl font-bold"><?php echo htmlspecialchars($student['name']); ?></h2>
                        <p class="text-gray-600"><?php echo htmlspecialchars($student['email']); ?></p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-500">Terakhir Login</p>
                    <p class="font-medium">
                        <?php 
                        $lastLogin = $student['last_login'] ?? 'Belum pernah login';
                        echo $lastLogin === 'Belum pernah login' ? $lastLogin : date('d M Y H:i', strtotime($lastLogin));
                        ?>
                    </p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Emotion Stats -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <h3 class="text-lg font-semibold mb-4">Statistik Emosi</h3>
                    <div class="h-64">
                        <canvas id="emotionChart"></canvas>
                    </div>
                </div>

                <!-- Recent Emotions -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold mb-4">Riwayat Emosi</h3>
                    <div class="space-y-4">
                        <?php if (empty($emotions)): ?>
                            <p class="text-gray-500 text-center py-4">Belum ada catatan emosi</p>
                        <?php else: ?>
                            <?php foreach ($emotions as $emotion): 
                                $emotionColor = EmotionRecord::getEmotionColor($emotion['emotionType']);
                            ?>
                                <div class="border-b pb-4 last:border-b-0 last:pb-0">
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="font-medium"><?php echo $emotion['emotionType']; ?></span>
                                        <span class="text-sm text-gray-500">
                                            <?php echo date('d M Y H:i', strtotime($emotion['timestamp'])); ?>
                                        </span>
                                    </div>
                                    <?php if (!empty($emotion['description'])): ?>
                                        <p class="text-gray-600 text-sm"><?php echo htmlspecialchars($emotion['description']); ?></p>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Recommendations -->
            <div>
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <h3 class="text-lg font-semibold mb-4">Beri Rekomendasi</h3>
                    <form action="" method="POST" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Berdasarkan Emosi</label>
                            <select name="emotion_id" class="w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Pilih emosi</option>
                                <?php foreach ($emotions as $emotion): ?>
                                    <option value="<?php echo $emotion['emotionID']; ?>">
                                        <?php echo $emotion['emotionType']; ?> - <?php echo date('d M Y', strtotime($emotion['timestamp'])); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Pesan Rekomendasi</label>
                            <textarea name="message" rows="4" required
                                      class="w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500"
                                      placeholder="Tulis rekomendasi Anda..."></textarea>
                        </div>
                        <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Kirim Rekomendasi
                        </button>
                    </form>
                </div>

                <!-- Previous Recommendations -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold mb-4">Rekomendasi Sebelumnya</h3>
                    <div class="space-y-4">
                        <?php if (empty($recommendations)): ?>
                            <p class="text-gray-500 text-sm">Belum ada rekomendasi</p>
                        <?php else: ?>
                            <?php foreach ($recommendations as $rec): ?>
                                <div class="border-l-4 border-blue-500 pl-4 py-2">
                                    <p class="text-sm text-gray-700"><?php echo htmlspecialchars($rec['message']); ?></p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        <?php echo date('d M Y H:i', strtotime($rec['created_at'])); ?>
                                    </p>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
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
