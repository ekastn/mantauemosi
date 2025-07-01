<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/ManualRecommendation.php';
require_once __DIR__ . '/../models/User.php';

// Check if user is logged in and is a lecturer
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Dosen') {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit;
}

$recommendationModel = new ManualRecommendation();
$userModel = new User();

// Get all recommendations given by this lecturer
$recommendations = $recommendationModel->getByDosen($_SESSION['user_id']);

// Get all students for filter
$students = $userModel->getStudents();

// Process filters
$studentFilter = $_GET['student'] ?? '';
$dateFilter = $_GET['date'] ?? '';

if ($studentFilter) {
    $recommendations = array_filter($recommendations, function($rec) use ($studentFilter) {
        return $rec['mahasiswaID'] === $studentFilter;
    });
}

if ($dateFilter) {
    $filterDate = new DateTime($dateFilter);
    $recommendations = array_filter($recommendations, function($rec) use ($filterDate) {
        $recDate = new DateTime($rec['created_at']);
        return $recDate->format('Y-m-d') === $filterDate->format('Y-m-d');
    });
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekomendasi - Aplikasi Pemantauan Emosi Harian</title>
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
                <h1 class="text-xl font-bold">Manajemen Rekomendasi</h1>
            </div>
            <div class="flex items-center space-x-4">
                <span><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                <a href="<?php echo BASE_URL; ?>auth/logout.php" class="text-blue-200 hover:text-white">Logout</a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto p-4">
        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-semibold mb-4">Filter Rekomendasi</h2>
            <form action="" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mahasiswa</label>
                    <select name="student" class="w-full border border-gray-300 rounded-md p-2">
                        <option value="">Semua Mahasiswa</option>
                        <?php foreach ($students as $student): ?>
                            <option value="<?php echo $student['userID']; ?>" <?php echo $studentFilter === $student['userID'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($student['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
                    <input type="date" name="date" value="<?php echo htmlspecialchars($dateFilter); ?>" 
                           class="w-full border border-gray-300 rounded-md p-2">
                </div>
                <div class="flex items-end space-x-2">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        <i class="fas fa-filter mr-1"></i> Filter
                    </button>
                    <a href="?" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Recommendations List -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h2 class="text-lg font-semibold">Daftar Rekomendasi</h2>
                    <span class="text-sm text-gray-500">
                        <?php echo count($recommendations); ?> rekomendasi ditemukan
                    </span>
                </div>
            </div>
            
            <?php if (empty($recommendations)): ?>
                <div class="p-6 text-center text-gray-500">
                    <i class="fas fa-info-circle text-4xl text-gray-300 mb-2"></i>
                    <p>Tidak ada rekomendasi yang sesuai dengan filter</p>
                </div>
            <?php else: ?>
                <div class="divide-y divide-gray-200">
                    <?php foreach ($recommendations as $rec): 
                        $student = $userModel->find($rec['mahasiswaID']);
                        $emotion = (new EmotionRecord())->find($rec['emotionID']);
                    ?>
                        <div class="p-6 hover:bg-gray-50">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <div class="flex items-center mb-2">
                                        <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-semibold mr-3">
                                            <?php echo strtoupper(substr($student['name'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <h3 class="font-medium"><?php echo htmlspecialchars($student['name']); ?></h3>
                                            <p class="text-sm text-gray-500">
                                                <?php echo date('d M Y H:i', strtotime($rec['created_at'])); ?>
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <div class="ml-13">
                                        <?php if ($emotion): ?>
                                            <div class="mb-2">
                                                <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full <?php echo $this->getEmotionColor($emotion['emotionType']); ?>">
                                                    <?php echo $emotion['emotionType']; ?>
                                                </span>
                                                <span class="text-sm text-gray-500 ml-2">
                                                    <?php echo date('d M Y', strtotime($emotion['timestamp'])); ?>
                                                </span>
                                            </div>
                                            <?php if (!empty($emotion['description'])): ?>
                                                <p class="text-sm text-gray-600 mb-2">
                                                    "<?php echo htmlspecialchars($emotion['description']); ?>"
                                                </p>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                        
                                        <div class="bg-blue-50 p-3 rounded-md">
                                            <p class="text-blue-800"><?php echo nl2br(htmlspecialchars($rec['message'])); ?></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="ml-4 flex-shrink-0">
                                    <a href="student_detail.php?id=<?php echo $student['userID']; ?>" 
                                       class="text-blue-600 hover:text-blue-800 text-sm">
                                        <i class="fas fa-user-graduate mr-1"></i> Profil
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
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
