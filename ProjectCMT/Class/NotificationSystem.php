<?php

interface NotificationObserver {
    public function update($data);
}

class NotificationSystem {
    private static $instance = null;
    private $observers = [];
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
    
     public static function resetInstance() {
    self::$instance = null;
}

    /**
     * المُنشئ الخاص
     */
    private function __construct($dbConnection) {
        $this->conn = $dbConnection;
        
        // إضافة المراقبين الافتراضيين
        $this->attach(new DatabaseNotificationObserver($dbConnection));
        $this->attach(new MessageNotificationObserver($dbConnection));
    }
    
    /**
     * إضافة مراقب
     */
    public function attach(NotificationObserver $observer) {
        $this->observers[] = $observer;
    }
    
    /**
     * إزالة مراقب
     */
    public function detach(NotificationObserver $observer) {
        $key = array_search($observer, $this->observers, true);
        if ($key !== false) {
            unset($this->observers[$key]);
        }
    }
    
    /**
     * إبلاغ جميع المراقبين
     */
    private function notify($data) {
        foreach ($this->observers as $observer) {
            $observer->update($data);
        }
    }
    
    /**
     * إشعار عند إضافة مهمة جديدة
     */
    public function notifyNewTask($taskId, $taskName, $projectId, $deadline, $supervisor) {
        try {
            // جلب أعضاء المشروع
            $stmt = $this->conn->prepare("SELECT member_name FROM project_members WHERE project_id = ?");
            $stmt->execute([$projectId]);
            $members = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            // جلب اسم المشروع
            $stmt = $this->conn->prepare("SELECT project_name FROM projects WHERE project_id = ?");
            $stmt->execute([$projectId]);
            $projectName = $stmt->fetchColumn();
            
            // إنشاء بيانات الإشعار
            $data = [
                'type' => 'new_task',
                'task_id' => $taskId,
                'task_name' => $taskName,
                'project_id' => $projectId,
                'project_name' => $projectName,
                'deadline' => $deadline,
                'supervisor' => $supervisor,
                'members' => $members,
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
            // إبلاغ المراقبين
            $this->notify($data);
            
            return true;
        } catch (Exception $e) {
            error_log("خطأ في إشعار المهمة الجديدة: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * إشعار عند تسليم مهمة
     */
    public function notifyTaskSubmission($taskId, $studentName, $supervisor) {
        try {
            // جلب تفاصيل المهمة
            $stmt = $this->conn->prepare("SELECT t.taskName, p.project_name, p.project_id 
                                        FROM task t 
                                        JOIN projects p ON t.project_id = p.project_id 
                                        WHERE t.taskID = ?");
            $stmt->execute([$taskId]);
            $taskData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$taskData) {
                throw new Exception("المهمة غير موجودة");
            }
            
            // إنشاء بيانات الإشعار
            $data = [
                'type' => 'task_submission',
                'task_id' => $taskId,
                'task_name' => $taskData['taskName'],
                'project_id' => $taskData['project_id'],
                'project_name' => $taskData['project_name'],
                'student_name' => $studentName,
                'supervisor' => $supervisor,
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
            // إبلاغ المراقبين
            $this->notify($data);
            
            return true;
        } catch (Exception $e) {
            error_log("خطأ في إشعار تسليم المهمة: " . $e->getMessage());
            return false;
        }
    }
}

/**
 * مراقب قاعدة البيانات - يحفظ الإشعارات في جدول notifications
 */
class DatabaseNotificationObserver implements NotificationObserver {
    private $conn;
    
    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }
    
    /**
     * استقبال تحديث من النظام
     */
    public function update($data) {
        try {
            switch ($data['type']) {
                case 'new_task':
                    $this->handleNewTask($data);
                    break;
                case 'task_submission':
                    $this->handleTaskSubmission($data);
                    break;
            }
        } catch (Exception $e) {
            error_log("خطأ في معالجة الإشعار: " . $e->getMessage());
        }
    }
    
    /**
     * معالجة إشعار مهمة جديدة
     */
    private function handleNewTask($data) {
        $title = "مهمة جديدة: " . $data['task_name'];
        $message = "تم إضافة مهمة جديدة ({$data['task_name']}) للمشروع {$data['project_name']}. موعد التسليم: {$data['deadline']}";
        
        // إضافة إشعار لكل عضو في المشروع
        foreach ($data['members'] as $member) {
            $this->addNotification($title, $message, $member);
        }
    }
    
    /**
     * معالجة إشعار تسليم مهمة
     */
    private function handleTaskSubmission($data) {
        $title = "تم تسليم مهمة: " . $data['task_name'];
        $message = "قام الطالب {$data['student_name']} بتسليم المهمة {$data['task_name']} للمشروع {$data['project_name']}";
        
        // إضافة إشعار للمشرف
        $this->addNotification($title, $message, $data['supervisor']);
    }
    
    /**
     * إضافة إشعار لقاعدة البيانات
     */
    private function addNotification($title, $message, $recipient) {
        try {
            $stmt = $this->conn->prepare("INSERT INTO notifications (type, title, message, recipient) 
                                        VALUES ('system', ?, ?, ?)");
            $stmt->execute([$title, $message, $recipient]);
        } catch (PDOException $e) {
            error_log("خطأ في إضافة إشعار: " . $e->getMessage());
        }
    }
}

/**
 * مراقب الرسائل - يرسل رسائل داخلية
 */
class MessageNotificationObserver implements NotificationObserver {
    private $conn;
    
    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }
    
    /**
     * استقبال تحديث من النظام
     */
    public function update($data) {
        try {
            switch ($data['type']) {
                case 'new_task':
                    $this->handleNewTask($data);
                    break;
                case 'task_submission':
                    $this->handleTaskSubmission($data);
                    break;
            }
        } catch (Exception $e) {
            error_log("خطأ في معالجة إشعار الرسالة: " . $e->getMessage());
        }
    }
    
    /**
     * معالجة إشعار مهمة جديدة
     */
    private function handleNewTask($data) {
        $subject = "مهمة جديدة: " . $data['task_name'];
        $content = "تم إضافة مهمة جديدة ({$data['task_name']}) للمشروع {$data['project_name']}. موعد التسليم: {$data['deadline']}";
        
        // إرسال رسالة لكل عضو في المشروع
        foreach ($data['members'] as $member) {
            $this->sendMessage($data['supervisor'], $member, $subject, $content);
        }
    }
    
    /**
     * معالجة إشعار تسليم مهمة
     */
    private function handleTaskSubmission($data) {
        $subject = "تم تسليم مهمة: " . $data['task_name'];
        $content = "قام الطالب {$data['student_name']} بتسليم المهمة {$data['task_name']} للمشروع {$data['project_name']}";
        
        // إرسال رسالة للمشرف
        $this->sendMessage($data['student_name'], $data['supervisor'], $subject, $content);
    }
    
    /**
     * إرسال رسالة
     */
    private function sendMessage($sender, $receiver, $subject, $content) {
        try {
            $stmt = $this->conn->prepare("INSERT INTO messages (sender, receiver, subject, message_content) 
                                        VALUES (?, ?, ?, ?)");
            $stmt->execute([$sender, $receiver, $subject, $content]);
        } catch (PDOException $e) {
            error_log("خطأ في إرسال رسالة: " . $e->getMessage());
        }
    }
   
}
?>