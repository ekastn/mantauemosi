<?php
require_once 'Model.php';

class User extends Model {
    protected $table = 'users';
    protected $primaryKey = 'userID';

    public function __construct() {
        parent::__construct();
    }

    public function findByEmail($email) {
        $query = "SELECT * FROM " . $this->table . " WHERE email = :email LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getStudents() {
        $query = "SELECT * FROM " . $this->table . " WHERE role = 'Mahasiswa' ORDER BY name";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLatestEmotion($userId) {
        $query = "SELECT * FROM emotion_records WHERE userID = :userID ORDER BY timestamp DESC LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':userID', $userId);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function create($data) {
        $query = "INSERT INTO " . $this->table . " (userID, name, email, password, role) 
                 VALUES (:userID, :name, :email, :password, :role)";
        
        $stmt = $this->db->prepare($query);
        
        $params = [
            ':userID' => $data['userID'],
            ':name' => $data['name'],
            ':email' => $data['email'],
            ':password' => $data['password'],
            ':role' => $data['role']
        ];
        
        return $stmt->execute($params);
    }
}
?>
