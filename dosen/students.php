<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/EmotionRecord.php';

// Check if user is logged in and is a lecturer
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Dosen') {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit;
}

$userModel = new User();
$emotionModel = new EmotionRecord();

// Get all students with their latest emotion
$students = $userModel->getStudents();

// Process search
$search = $_GET['search'] ?? '';
if ($search) {
    $students = array_filter($students, function($student) use ($search) {
        return stripos($student['name'], $search) !== false || 
               stripos($student['email'], $search) !== false;
    });
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Mahasiswa - Aplikasi Pemantauan Emosi Harian</title>
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
                <h1 class="text-xl font-bold">Daftar Mahasiswa</h1>
            </div>
            <div class="flex items-center space-x-4">
                <span><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                <a href="<?php echo BASE_URL; ?>auth/logout.php" class="text-blue-200 hover:text-white">Logout</a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto p-4">
        <!-- Search and Filter -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <form action="" method="GET" class="flex space-x-4">
                <div class="flex-1">
                    <input type="text" name="search" placeholder="Cari mahasiswa..." 
                           value="<?php echo htmlspecialchars($search); ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="fas fa-search"></i> Cari
                </button>
                <a href="?" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    Reset
                </a>
            </form>
        </div>

        <!-- Students List -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Nama Mahasiswa
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Email
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Emosi Terakhir
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Terakhir Update
                            </th>
                            <th scope="col" class="relative px-6 py-3">
                                <span class="sr-only">Aksi</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($students)): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                    Tidak ada data mahasiswa
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($students as $student): 
                                $latestEmotion = $userModel->getLatestEmotion($student['userID']);
                                $emotionColor = EmotionRecord::getEmotionColor($latestEmotion['emotionType'] ?? '');
                            ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-600 font-semibold">
                                                <?php echo strtoupper(substr($student['name'], 0, 1)); ?>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    <?php echo htmlspecialchars($student['name']); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo htmlspecialchars($student['email']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if ($latestEmotion): ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $emotionColor; ?>">
                                                <?php echo $latestEmotion['emotionType']; ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-gray-400 text-sm">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo $latestEmotion ? date('d M Y H:i', strtotime($latestEmotion['timestamp'])) : '-' ; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="student_detail.php?id=<?php echo $student['userID']; ?>" class="text-blue-600 hover:text-blue-900">
                                            <i class="fas fa-eye mr-1"></i> Detail
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
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
