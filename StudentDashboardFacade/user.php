<?php
include 'conn.php';
include_once 'role.php';

class User {
    private $db;

    public function __construct($conn) {
        $this->db = $conn;
    }

    /**
     * تسجيل الدخول للمستخدم
     */
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

    /**
     * تسجيل مستخدم جديد
     */
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
    
    /**
     * جلب بيانات المستخدم بواسطة اسم المستخدم
     */
    public function getUserByUsername($username) {
        try {
            $stmt = $this->db->prepare("SELECT id, username, email, role FROM Users WHERE username = :username");
            $stmt->execute(['username' => $username]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log($e->getMessage(), 3, "error_log.txt");
            return false;
        }
    }
    
    /**
     * التحقق من صحة كلمة المرور الحالية
     */
    public function verifyPassword($username, $currentPassword) {
        try {
            $stmt = $this->db->prepare("SELECT password FROM Users WHERE username = :username");
            $stmt->execute(['username' => $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($currentPassword, $user['password'])) {
                return true;
            }
            
            return false;
        } catch (PDOException $e) {
            error_log($e->getMessage(), 3, "error_log.txt");
            return false;
        }
    }
    
    /**
     * تحديث بيانات المستخدم
     */
    public function updateUserProfile($username, $email, $newPassword = null) {
        try {
            // التحقق إذا كان البريد الإلكتروني مستخدم من قبل مستخدم آخر
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM Users WHERE email = :email AND username != :username");
            $stmt->execute(['email' => $email, 'username' => $username]);
            
            if ($stmt->fetchColumn() > 0) {
                return "البريد الإلكتروني مستخدم بالفعل من قبل مستخدم آخر.";
            }
            
            // إعداد استعلام التحديث
            if ($newPassword) {
                // التحقق من قوة كلمة المرور
                if (strlen($newPassword) < 8 || !preg_match('/[0-9]/', $newPassword)) {
                    return "يجب أن تكون كلمة المرور 8 أحرف على الأقل وتحتوي على رقم واحد على الأقل.";
                }
                
                $hashPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $this->db->prepare("UPDATE Users SET email = :email, password = :password WHERE username = :username");
                $stmt->execute([
                    'email' => $email,
                    'password' => $hashPassword,
                    'username' => $username
                ]);
            } else {
                $stmt = $this->db->prepare("UPDATE Users SET email = :email WHERE username = :username");
                $stmt->execute([
                    'email' => $email,
                    'username' => $username
                ]);
            }
            
            // تحديث متغير الجلسة للبريد الإلكتروني
            $_SESSION['email'] = $email;
            
            return "تم تحديث البيانات بنجاح!";
        } catch (PDOException $e) {
            error_log($e->getMessage(), 3, "error_log.txt");
            return "حدث خطأ أثناء تحديث البيانات، يرجى المحاولة لاحقًا.";
        }
    }
}
?>