<?php
require_once 'Model.php';

class Notification extends Model {
    protected $table = 'notifications';
    protected $primaryKey = 'notificationID';

    public function __construct() {
        parent::__construct();
    }

    public function getUnreadCount($userId) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table . " 
                 WHERE userID = :userID AND is_read = 0";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':userID', $userId);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }

    public function getUserNotifications($userId, $limit = 10) {
        $query = "SELECT * FROM " . $this->table . " 
                 WHERE userID = :userID 
                 ORDER BY created_at DESC 
                 LIMIT :limit";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':userID', $userId);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function markAsRead($notificationId) {
        $query = "UPDATE " . $this->table . " 
                 SET is_read = 1 
                 WHERE notificationID = :notificationID";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':notificationID', $notificationId);
        return $stmt->execute();
    }
}
?>
