<?php

include 'conn.php';
include_once 'role.php';

class User {
    private $db;

    public function __construct($conn) {
        $this->db = $conn;
    }

    public function login($username, $password) {
        try {
            $stmt = $this->db->prepare("SELECT username, email, password, role FROM Users WHERE username = :username");
            $stmt->execute(['username' => $username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];

                $role = new Role( $user['role']);
                return "تم تسجيل الدخول بنجاح! دورك: " . $role->locationUser();
            } else {
                return "اسم المستخدم أو كلمة المرور غير صحيحة.";
            }
        } catch (PDOException $e) {
            error_log($e->getMessage(), 3, "error_log.txt");
            return "حدث خطأ أثناء تسجيل الدخول، يرجى المحاولة لاحقًا.";
        }
    }

    public function register($username, $email, $password, $role) {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM Users WHERE email = :email OR username = :username");
            $stmt->execute(['username' => $username, 'email' => $email]);
             if ($stmt->fetchColumn() > 0) {
                return "!إسم المستخدم موجود بالفعل";
            }

            if (strlen($password) < 8 || !preg_match('/[0-9]/', $password)) {
                return "يجب أن تكون كلمة المرور 8 أحرف على الأقل وتحتوي على رقم واحد على الأقل.";
            }

            $hashpassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("INSERT INTO Users(username, email, password, role) VALUES (:username, :email, :password, :role)");
            $stmt->execute([
                'username' => $username,
                'email' => $email,
                'password' => $hashpassword,
                'role' => $role
            ]);

            return "تم تسجيل المستخدم بنجاح!";
        } catch (PDOException $e) {
            error_log($e->getMessage(), 3, "error_log.txt");
            return "حدث خطأ أثناء العملية، يرجى المحاولة لاحقًا.";
        }
    }
}
?>