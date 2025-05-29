<?php
/**
 * كلاس StudentDashboardFacade - واجهة موحدة للوحة تحكم الطالب
 */
class StudentDashboardFacade {
    private $conn;
    private $username;
    private $taskObj;
    private $projectObj;
    private $messageObj;
    
    /**
     * إنشاء الواجهة مع تهيئة الكائنات اللازمة
     */
    public function __construct($dbConnection, $username) {
        $this->conn = $dbConnection;
        $this->username = $username;
        
        // تضمين الملفات اللازمة
        require_once 'Task.php';
        require_once 'Project.php';
        
        // التحقق إذا كان ملف الرسائل موجود
        if (file_exists('Message.php')) {
            require_once 'Message.php';
            $this->messageObj = new Message($this->conn);
        } elseif (file_exists('Messag.php')) {
            require_once 'Messag.php';
            $this->messageObj = new Message($this->conn);
        } else {
            // إنشاء كائن وهمي إذا لم يكن ملف الرسائل موجود
            $this->messageObj = new DummyMessage();
        }
        
        // تهيئة الكائنات
        $this->taskObj = new Task($this->conn);
        $this->projectObj = new Project($this->conn);
        
        // تهيئة نظام الإشعارات إذا لم يكن موجودًا
        $this->initNotificationSystem();
    }
    
    /**
     * تهيئة جدول الإشعارات إذا لم يكن موجودًا
     */
    private function initNotificationSystem() {
        try {
            // التحقق من وجود جدول الإشعارات
            $query = "SHOW TABLES LIKE 'notifications'";
            $result = $this->conn->query($query);
            
            // إذا لم يكن الجدول موجودًا، أنشئه
            if ($result->rowCount() == 0) {
                $sql = "CREATE TABLE IF NOT EXISTS notifications (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    type VARCHAR(50) NOT NULL,
                    title VARCHAR(255) NOT NULL,
                    message TEXT NOT NULL,
                    recipient VARCHAR(100) NOT NULL,
                    is_read TINYINT(1) DEFAULT 0,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )";
                $this->conn->exec($sql);
            }
        } catch (PDOException $e) {
            // تسجيل الخطأ فقط ولا تظهر للمستخدم
            error_log("خطأ في تهيئة نظام الإشعارات: " . $e->getMessage());
        }
    }
    
    /**
     * جلب جميع البيانات اللازمة للوحة التحكم
     */
    public function getDashboardData() {
        try {
            $data = [];
            
            // جلب المشاريع
            $data['ongoing_projects'] = $this->getOngoingProjects();
            $data['completed_projects'] = $this->getCompletedProjects();
            
            // جلب المهام
            $data['upcoming_tasks'] = $this->taskObj->getUpcomingTasks($this->username);
            
            // جلب الإشعارات غير المقروءة
            $data['unread_notifications'] = $this->getUnreadNotifications();
            
            // جلب الرسائل غير المقروءة
            $data['unread_messages_count'] = $this->messageObj->getUnreadCount($this->username);
            
            return $data;
        } catch (Exception $e) {
            return [
                'error' => true,
                'message' => 'حدث خطأ أثناء جلب البيانات: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * جلب المشاريع قيد التنفيذ للطالب
     */
    public function getOngoingProjects() {
        try {
            $query = "SELECT p.* FROM projects p 
                      JOIN project_members pm ON p.project_id = pm.project_id 
                      WHERE pm.member_name = :username 
                      AND (p.status = 'Pending' OR p.status = 'قيد التنفيذ')";
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['username' => $this->username]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("خطأ في جلب المشاريع قيد التنفيذ: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * جلب المشاريع المكتملة للطالب
     */
    public function getCompletedProjects() {
        try {
            $query = "SELECT p.* FROM projects p 
                      JOIN project_members pm ON p.project_id = pm.project_id 
                      WHERE pm.member_name = :username 
                      AND p.status = 'مكتمل'";
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['username' => $this->username]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("خطأ في جلب المشاريع المكتملة: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * جلب الإشعارات غير المقروءة للطالب
     */
    public function getUnreadNotifications() {
        try {
            $query = "SELECT * FROM notifications 
                      WHERE recipient = :username 
                      AND is_read = 0 
                      ORDER BY created_at DESC 
                      LIMIT 5";
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['username' => $this->username]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("خطأ في جلب الإشعارات: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * تسليم مهمة مع ملف
     */
    public function submitTaskWithFile($taskId, $username, $notes, $file) {
        try {
            $fileId = null;
            
            // رفع الملف إذا وجد
            if ($file && $file['error'] == 0) {
                $fileId = $this->uploadTaskFile($file);
            }
            
            // تحديث ملف المهمة إذا كان الملف تم رفعه بنجاح
            if ($fileId) {
                $this->taskObj->updateTaskFile($taskId, $fileId);
            }
            
            // تسليم المهمة
            $result = $this->taskObj->submitTask($taskId, $username, $notes, $fileId);
            
            if ($result) {
                // إشعار المشرف بتسليم المهمة
                $this->notifyTaskSubmission($taskId, $username);
                
                return [
                    'success' => true,
                    'message' => 'تم تسليم المهمة بنجاح'
                ];
            } else {
                throw new Exception("فشل في تسليم المهمة");
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * رفع ملف للمهمة
     */
    private function uploadTaskFile($file) {
        $uploadDir = 'uploads/';
        
        // إنشاء مجلد الرفع إذا لم يكن موجودًا
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileName = basename($file['name']);
        $fileType = $file['type'];
        $targetPath = $uploadDir . $fileName;
        
        // تجنب تكرار الأسماء
        if (file_exists($targetPath)) {
            $fileInfo = pathinfo($fileName);
            $fileName = $fileInfo['filename'] . '_' . time() . '.' . $fileInfo['extension'];
            $targetPath = $uploadDir . $fileName;
        }
        
        // نقل الملف إلى المجلد
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return $this->taskObj->uploadTaskFile($fileName, $fileType);
        }
        
        throw new Exception("فشل في رفع الملف");
    }
    
    /**
     * إشعار المشرف بتسليم المهمة
     */
    private function notifyTaskSubmission($taskId, $studentName) {
        try {
            // التحقق من وجود نظام الإشعارات
            if (!class_exists('NotificationSystem')) {
                return false;
            }
            
            // جلب تفاصيل المهمة
            $task = $this->taskObj->getTaskById($taskId);
            if (!$task) {
                throw new Exception("المهمة غير موجودة");
            }
            
            // جلب تفاصيل المشروع
            $project = $this->projectObj->getProjectById($task['project_id']);
            if (!$project) {
                throw new Exception("المشروع غير موجود");
            }
            
            // إنشاء إشعار للمشرف
            $title = "تم تسليم مهمة: " . $task['taskName'];
            $message = "قام الطالب {$studentName} بتسليم المهمة {$task['taskName']} للمشروع {$project['project_name']}";
            
            // استدعاء نظام الإشعارات
            NotificationSystem::getInstance($this->conn)
                ->notifyTaskSubmission($taskId, $studentName, $project['supervisor']);
                
            // إرسال رسالة للمشرف
            $this->messageObj->sendMessage(
                $studentName,
                $project['supervisor'],
                $title,
                $message
            );
            
            return true;
        } catch (Exception $e) {
            error_log("خطأ في إرسال إشعار تسليم المهمة: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * جلب تفاصيل مهمة محددة
     */
    public function getTaskDetails($taskId) {
        try {
            $task = $this->taskObj->getTaskById($taskId);
            if (!$task) {
                throw new Exception("المهمة غير موجودة");
            }
            return $task;
        } catch (Exception $e) {
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * تعليم جميع الإشعارات كمقروءة
     */
    public function markAllNotificationsAsRead() {
        try {
            $query = "UPDATE notifications SET is_read = 1 WHERE recipient = :username";
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['username' => $this->username]);
            return true;
        } catch (PDOException $e) {
            error_log("خطأ في تعليم الإشعارات كمقروءة: " . $e->getMessage());
            return false;
        }
    }
}


 


?>