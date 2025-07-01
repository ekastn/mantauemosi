<?php
require_once __DIR__ . '/../models/EmotionRecord.php';
require_once __DIR__ . '/../models/Notification.php';

class EmotionController {
    private $emotionModel;
    private $notificationModel;
    private $userModel;

    public function __construct() {
        $this->emotionModel = new EmotionRecord();
        $this->notificationModel = new Notification();
        $this->userModel = new User();
    }

    public function recordEmotion($userId, $data) {
        // Validate input
        $validEmotions = ['Senang', 'Sedih', 'Marah', 'Cemas', 'Lelah', 'Bosan', 'Bersemangat', 'Tenang'];
        
        if (!in_array($data['emotionType'], $validEmotions)) {
            throw new Exception('Emosi tidak valid');
        }

        // Prepare emotion data
        $emotionData = [
            'userID' => $userId,
            'emotionType' => $data['emotionType'],
            'description' => $data['description'] ?? ''
        ];

        // Save emotion record
        $result = $this->emotionModel->create($emotionData);
        
        if ($result) {
            // Check for negative patterns
            $hasNegativePattern = $this->emotionModel->detectNegativePattern($userId);
            
            if ($hasNegativePattern) {
                $this->sendNegativePatternNotification($userId, $data['emotionType']);
            }
            
            return true;
        }
        
        return false;
    }

    private function sendNegativePatternNotification($userId, $emotionType) {
        $message = "Kami melihat pola emosi negatif yang berulang. ";
        $message .= $this->getRecommendationMessage($emotionType);
        
        $notificationData = [
            'userID' => $userId,
            'message' => $message,
            'is_read' => 0
        ];
        
        $this->notificationModel->create($notificationData);
    }

    private function getRecommendationMessage($emotionType) {
        $recommendations = [
            'Sedih' => 'Coba lakukan aktivitas yang menyenangkan atau bicarakan perasaan Anda dengan teman dekat.',
            'Marah' => 'Coba tarik napas dalam-dalam, hitung sampai 10, atau lakukan relaksasi otot progresif.',
            'Cemas' => 'Coba lakukan latihan pernapasan dalam atau meditasi selama 5 menit.',
            'Lelah' => 'Istirahat sejenak, minum air putih, dan lakukan peregangan ringan.',
            'Bosan' => 'Cari aktivitas baru yang menantang atau pelajari keterampilan baru.',
            'default' => 'Pertimbangkan untuk berbicara dengan dosen atau konselor kampus untuk dukungan lebih lanjut.'
        ];

        return $recommendations[$emotionType] ?? $recommendations['default'];
    }

    public function getEmotionStats($userId) {
        return $this->emotionModel->getEmotionStats($userId);
    }

    public function getRecentEmotions($userId, $limit = 5) {
        return $this->emotionModel->getByUser($userId, $limit);
    }
}
?>
