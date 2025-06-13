<?php

namespace ProjectCMT\Class;

class User {
    private $db;

    public function __construct($pdo) {
        $this->db = $pdo;
    }

    public function register($username, $email, $password, $role) {
        if (empty($username)) return "اسم المستخدم مطلوب";
        if (empty($email)) return "البريد الإلكتروني مطلوب";
        if (empty($password)) return "كلمة المرور مطلوبة";
        if (strlen($password) < 8) return "يجب أن تكون كلمة المرور 8 أحرف على الأقل";
        if (!preg_match('/\d/', $password)) return "تحتوي على رقم واحد على الأقل";

        $stmt = $this->db->prepare("SELECT id FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        if ($stmt->fetch(\PDO::FETCH_ASSOC)) {
            return "!إسم المستخدم موجود بالفعل";
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO users (username, email, password, role) VALUES (:username, :email, :password, :role)");
        $stmt->execute([
            'username' => $username,
            'email' => $email,
            'password' => $hashedPassword,
            'role' => $role
        ]);
        return "تم تسجيل المستخدم بنجاح!";
    }

    public function login($username, $password) {
        if (empty($username)) return "اسم المستخدم مطلوب";
        if (empty($password)) return "كلمة المرور مطلوبة";

        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            return "تم تسجيل الدخول بنجاح";
        }
        return "اسم المستخدم أو كلمة المرور غير صحيحة.";
    }

    public function getUserByUsername($username) {
        $stmt = $this->db->prepare("SELECT id, username, email, role FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function verifyPassword($username, $currentPassword) {
        $stmt = $this->db->prepare("SELECT password FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($user && password_verify($currentPassword, $user['password'])) {
            return true;
        }
        return false;
    }

    public function updateUserProfile($username, $email, $password) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("UPDATE users SET email = :email, password = :password WHERE username = :username");
        $stmt->execute([
            'email' => $email,
            'password' => $hashedPassword,
            'username' => $username
        ]);
        return "تم تحديث البيانات بنجاح!";
    }
}