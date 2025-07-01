<?php
require_once 'Model.php';

class ManualRecommendation extends Model {
    protected $table = 'manual_recommendations';
    protected $primaryKey = 'recommendationID';

    public function __construct() {
        parent::__construct();
    }

    public function getForStudent($studentId) {
        $query = "SELECT mr.*, u.name as dosen_name 
                 FROM " . $this->table . " mr
                 JOIN users u ON mr.dosenID = u.userID
                 WHERE mr.mahasiswaID = :studentId
                 ORDER BY mr.created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':studentId', $studentId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByDosen($dosenId) {
        $query = "SELECT mr.*, u.name as mahasiswa_name, er.emotionType
                 FROM " . $this->table . " mr
                 JOIN users u ON mr.mahasiswaID = u.userID
                 JOIN emotion_records er ON mr.emotionID = er.emotionID
                 WHERE mr.dosenID = :dosenId
                 ORDER BY mr.created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':dosenId', $dosenId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
