<?php

 
class SupervisorNotificationSystem {
    private static $instance = null;
    private $conn;
    
    /**
     * الحصول على النسخة الوحيدة من النظام
     */
    public static function getInstance($dbConnection = null) {
        if (self::$instance === null) {
            self::$instance = new self($dbConnection);
        }
        return self::$instance;
    }
    
    /**
     * المُنشئ الخاص
     */
    private function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }
    
    /**
     * إشعار الطلاب بإضافة مهمة جديدة
     */
    public function notifyNewTask($taskId, $taskName, $projectId, $deadline, $supervisor) {
        // استخدام نظام الإشعارات العام
        return NotificationSystem::getInstance($this->conn)
            ->notifyNewTask($taskId, $taskName, $projectId, $deadline, $supervisor);
    }
    
    /**
     * إشعار الطلاب بتغيير حالة المشروع
     */
    public function notifyProjectStatusChange($projectId, $newStatus, $supervisor) {
        try {
            // جلب معلومات المشروع
            $stmt = $this->conn->prepare("SELECT project_name FROM projects WHERE project_id = ?");
            $stmt->execute([$projectId]);
            $projectName = $stmt->fetchColumn();
            
            // جلب أعضاء المشروع
            $stmt = $this->conn->prepare("SELECT member_name FROM project_members WHERE project_id = ?");
            $stmt->execute([$projectId]);
            $members = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            // إضافة إشعار لكل عضو
            foreach ($members as $member) {
                $this->addNotification(
                    "تغيير حالة المشروع: " . $projectName,
                    "تم تغيير حالة المشروع {$projectName} إلى {$newStatus}",
                    $member
                );
            }
            
            // إرسال رسائل للطلاب
            foreach ($members as $member) {
                $this->sendMessage(
                    $supervisor,
                    $member,
                    "تغيير حالة المشروع: " . $projectName,
                    "تم تغيير حالة المشروع {$projectName} إلى {$newStatus}"
                );
            }
            
            return true;
        } catch (Exception $e) {
            error_log("خطأ في إشعار تغيير حالة المشروع: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * جلب الإشعارات الخاصة بالمشرف
     */
    public function getSupervisorNotifications($supervisor, $limit = 10) {
        try {
            $query = "SELECT * FROM notifications 
                      WHERE recipient = :supervisor 
                      ORDER BY created_at DESC 
                      LIMIT :limit";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':supervisor', $supervisor, PDO::PARAM_STR);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("خطأ في جلب إشعارات المشرف: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * إضافة إشعار لقاعدة البيانات
     */
    private function addNotification($title, $message, $recipient) {
        try {
            $stmt = $this->conn->prepare("INSERT INTO notifications (type, title, message, recipient) 
                                        VALUES ('supervisor', ?, ?, ?)");
            $stmt->execute([$title, $message, $recipient]);
            return true;
        } catch (PDOException $e) {
            error_log("خطأ في إضافة إشعار: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * إرسال رسالة
     */
    private function sendMessage($sender, $receiver, $subject, $content) {
        try {
            $stmt = $this->conn->prepare("INSERT INTO messages (sender, receiver, subject, message_content) 
                                        VALUES (?, ?, ?, ?)");
            $stmt->execute([$sender, $receiver, $subject, $content]);
            return true;
        } catch (PDOException $e) {
            error_log("خطأ في إرسال رسالة: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * تعليم جميع الإشعارات كمقروءة
     */
    public function markAllNotificationsAsRead($supervisor) {
        try {
            $query = "UPDATE notifications SET is_read = 1 WHERE recipient = :supervisor";
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['supervisor' => $supervisor]);
            return true;
        } catch (PDOException $e) {
            error_log("خطأ في تعليم الإشعارات كمقروءة: " . $e->getMessage());
            return false;
        }
    }
}
?>