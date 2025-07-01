<?php
require_once 'Model.php';

class EmotionRecord extends Model {
    protected $table = 'emotion_records';
    protected $primaryKey = 'emotionID';

    public function __construct() {
        parent::__construct();
    }

    public function getByUser($userId, $limit = 30) {
        $query = "SELECT * FROM " . $this->table . " 
                 WHERE userID = :userID 
                 ORDER BY timestamp DESC 
                 LIMIT :limit";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':userID', $userId);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get emotion statistics
     * @param string|null $userId If null, get stats for all users
     * @return array Array of emotion statistics
     */
    public function getEmotionStats($userId = null) {
        if ($userId) {
            // Get stats for a specific user
            $query = "SELECT 
                        emotionType, 
                        COUNT(*) as count,
                        (COUNT(*) * 100.0 / (SELECT COUNT(*) FROM " . $this->table . " WHERE userID = :userID)) as percentage
                      FROM " . $this->table . " 
                      WHERE userID = :userID 
                      GROUP BY emotionType";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':userID', $userId);
        } else {
            // Get stats for all users
            $query = "SELECT 
                        emotionType, 
                        COUNT(*) as count,
                        (COUNT(*) * 100.0 / (SELECT COUNT(*) FROM " . $this->table . ") * 1.0) as percentage
                      FROM " . $this->table . " 
                      GROUP BY emotionType";
            $stmt = $this->db->prepare($query);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function detectNegativePattern($userId) {
        // Check for 3 or more negative emotions in the last 5 days
        $query = "SELECT COUNT(*) as negative_count 
                 FROM " . $this->table . " 
                 WHERE userID = :userID 
                 AND emotionType IN ('Sedih', 'Marah', 'Cemas', 'Lelah', 'Bosan')
                 AND timestamp >= DATE_SUB(NOW(), INTERVAL 5 DAY)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':userID', $userId);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return ($result['negative_count'] >= 3);
    }
    
    /**
     * Get recent emotion records
     * @param int $limit Number of records to return
     * @return array Array of emotion records
     */
    public function getRecent($limit = 5) {
        $query = "SELECT e.*, u.name, u.email 
                 FROM " . $this->table . " e
                 JOIN users u ON e.userID = u.userID
                 ORDER BY e.timestamp DESC 
                 LIMIT :limit";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get CSS class for emotion type
     * @param string $emotionType Type of emotion
     * @return string CSS class
     */
    public static function getEmotionColor($emotionType) {
        $colors = [
            'Senang' => 'bg-green-100 text-green-800',
            'Sedih' => 'bg-blue-100 text-blue-800',
            'Marah' => 'bg-red-100 text-red-800',
            'Cemas' => 'bg-yellow-100 text-yellow-800',
            'Lelah' => 'bg-purple-100 text-purple-800',
            'Bosan' => 'bg-gray-100 text-gray-800',
            'Bersemangat' => 'bg-pink-100 text-pink-800',
            'Tenang' => 'bg-indigo-100 text-indigo-800'
        ];
        
        return $colors[$emotionType] ?? 'bg-gray-100 text-gray-800';
    }
}
?>
