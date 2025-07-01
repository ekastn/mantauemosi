<?php
require_once __DIR__ . '/../models/ManualRecommendation.php';
require_once __DIR__ . '/../models/Notification.php';

class RecommendationController {
    private $recommendationModel;
    private $notificationModel;
    private $userModel;

    public function __construct() {
        $this->recommendationModel = new ManualRecommendation();
        $this->notificationModel = new Notification();
        $this->userModel = new User();
    }

    public function createRecommendation($dosenId, $data) {
        // Validate input
        if (empty($data['mahasiswaID']) || empty($data['emotionID']) || empty($data['message'])) {
            throw new Exception('Semua field harus diisi');
        }

        // Prepare recommendation data
        $recommendationData = [
            'dosenID' => $dosenId,
            'mahasiswaID' => $data['mahasiswaID'],
            'emotionID' => $data['emotionID'],
            'message' => $data['message']
        ];

        // Save recommendation
        $result = $this->recommendationModel->create($recommendationData);
        
        if ($result) {
            // Create notification for student
            $dosen = $this->userModel->find($dosenId);
            $notificationMessage = "Anda menerima rekomendasi baru dari " . $dosen['name'] . ": " . substr($data['message'], 0, 50) . "...";
            
            $notificationData = [
                'userID' => $data['mahasiswaID'],
                'message' => $notificationMessage,
                'is_read' => 0
            ];
            
            $this->notificationModel->create($notificationData);
            
            return true;
        }
        
        return false;
    }

    public function getStudentRecommendations($studentId) {
        return $this->recommendationModel->getForStudent($studentId);
    }

    public function getDosenRecommendations($dosenId) {
        return $this->recommendationModel->getByDosen($dosenId);
    }
}
?>
