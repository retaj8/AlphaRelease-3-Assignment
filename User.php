<?php
class User {
    private $db;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    public function login($username, $email) {
        $sql = "SELECT COUNT(*) FROM users WHERE username = :username OR email = :email";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['username' => $username, 'email' => $email]);
        return $stmt->fetchColumn() > 0; // إذا كان هناك سجل، المستخدم موجود
    }

    public function register($username, $email, $password) {
        try {
            $sql = "INSERT INTO users (username, email, password) VALUES (:username, :email, :password)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':username' => $username,
                ':email' => $email,
                ':password' => $password
            ]);
            return "✅ تم تسجيل المستخدم بنجاح!";
        } catch (PDOException $e) {
            return "❌ خطأ أثناء التسجيل: " . $e->getMessage();
        }
    }
    
}
?>"C:\wamp64\www\project438"