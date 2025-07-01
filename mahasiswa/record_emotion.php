<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/EmotionController.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Mahasiswa') {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit;
}

$emotionController = new EmotionController();
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $result = $emotionController->recordEmotion(
            $_SESSION['user_id'],
            [
                'emotionType' => $_POST['emotionType'],
                'description' => $_POST['description'] ?? ''
            ]
        );
        
        if ($result) {
            $_SESSION['success'] = 'Emosi berhasil dicatat!';
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Gagal mencatat emosi. Silakan coba lagi.';
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
    <title>Catat Emosi - Aplikasi Pemantauan Emosi Harian</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <!-- Header -->
    <header class="bg-blue-600 text-white p-4 shadow">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold">Catat Emosi</h1>
            <div class="flex items-center space-x-4">
                <span><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                <a href="<?php echo BASE_URL; ?>auth/logout.php" class="text-blue-200 hover:text-white">Logout</a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto p-4 max-w-2xl">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-6">Bagaimana perasaan Anda hari ini?</h2>
            
            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form action="" method="POST" class="space-y-6">
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="emotionType">
                        Pilih Emosi Anda
                    </label>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <label class="emotion-option">
                            <input type="radio" name="emotionType" value="Senang" class="hidden peer" required>
                            <div class="p-4 border-2 rounded-lg text-center cursor-pointer hover:bg-blue-50 peer-checked:border-blue-500 peer-checked:bg-blue-50">
                                <div class="text-4xl mb-2">😊</div>
                                <span>Senang</span>
                            </div>
                        </label>
                        <label class="emotion-option">
                            <input type="radio" name="emotionType" value="Sedih" class="hidden peer">
                            <div class="p-4 border-2 rounded-lg text-center cursor-pointer hover:bg-blue-50 peer-checked:border-blue-500 peer-checked:bg-blue-50">
                                <div class="text-4xl mb-2">😢</div>
                                <span>Sedih</span>
                            </div>
                        </label>
                        <label class="emotion-option">
                            <input type="radio" name="emotionType" value="Marah" class="hidden peer">
                            <div class="p-4 border-2 rounded-lg text-center cursor-pointer hover:bg-blue-50 peer-checked:border-blue-500 peer-checked:bg-blue-50">
                                <div class="text-4xl mb-2">😠</div>
                                <span>Marah</span>
                            </div>
                        </label>
                        <label class="emotion-option">
                            <input type="radio" name="emotionType" value="Cemas" class="hidden peer">
                            <div class="p-4 border-2 rounded-lg text-center cursor-pointer hover:bg-blue-50 peer-checked:border-blue-500 peer-checked:bg-blue-50">
                                <div class="text-4xl mb-2">😰</div>
                                <span>Cemas</span>
                            </div>
                        </label>
                        <label class="emotion-option">
                            <input type="radio" name="emotionType" value="Lelah" class="hidden peer">
                            <div class="p-4 border-2 rounded-lg text-center cursor-pointer hover:bg-blue-50 peer-checked:border-blue-500 peer-checked:bg-blue-50">
                                <div class="text-4xl mb-2">😴</div>
                                <span>Lelah</span>
                            </div>
                        </label>
                        <label class="emotion-option">
                            <input type="radio" name="emotionType" value="Bosan" class="hidden peer">
                            <div class="p-4 border-2 rounded-lg text-center cursor-pointer hover:bg-blue-50 peer-checked:border-blue-500 peer-checked:bg-blue-50">
                                <div class="text-4xl mb-2">😑</div>
                                <span>Bosan</span>
                            </div>
                        </label>
                        <label class="emotion-option">
                            <input type="radio" name="emotionType" value="Bersemangat" class="hidden peer">
                            <div class="p-4 border-2 rounded-lg text-center cursor-pointer hover:bg-blue-50 peer-checked:border-blue-500 peer-checked:bg-blue-50">
                                <div class="text-4xl mb-2">😃</div>
                                <span>Bersemangat</span>
                            </div>
                        </label>
                        <label class="emotion-option">
                            <input type="radio" name="emotionType" value="Tenang" class="hidden peer">
                            <div class="p-4 border-2 rounded-lg text-center cursor-pointer hover:bg-blue-50 peer-checked:border-blue-500 peer-checked:bg-blue-50">
                                <div class="text-4xl mb-2">😌</div>
                                <span>Tenang</span>
                            </div>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="description">
                        Deskripsi (opsional)
                    </label>
                    <textarea 
                        id="description" 
                        name="description" 
                        rows="4" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Apa yang membuat Anda merasa seperti ini?"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                </div>

                <div class="flex justify-end space-x-4">
                    <a href="dashboard.php" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Batal
                    </a>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </main>

    <style>
        .emotion-option input[type="radio"]:checked + div {
            border-color: #3B82F6;
            background-color: #EFF6FF;
        }
        .emotion-option input[type="radio"]:focus + div {
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5);
        }
    </style>
</body>
</html>
