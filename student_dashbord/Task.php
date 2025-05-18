<?php
class Task {
    private $conn;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }

    // دالة لجلب مهام الطالب
    public function getStudentTasks($username) {
        try {
            $sql = "SELECT t.*, p.project_name 
                    FROM task t 
                    JOIN projects p ON t.project_id = p.id
                    JOIN project_members pm ON p.project_id = pm.project_id
                    WHERE pm.member_name = :username
                    ORDER BY t.deadline ASC";
                    
            $stmt = $this->conn->prepare($sql);
            $stmt->execute(['username' => $username]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    // دالة لإرسال المهمة المنجزة من الطالب
    public function submitTask($taskID, $studentName, $notes, $fileId = null) {
        try {
            // تحديث حالة المهمة
            $sql = "UPDATE task SET 
                    status = 'مكتملة', 
                    assigned_to = :student_name
                    WHERE taskID = :task_id";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                'student_name' => $studentName,
                'task_id' => $taskID
            ]);
            
            // إذا تم تحديث المهمة بنجاح، أضف سجل لتسليم المهمة
            if ($stmt->rowCount() > 0) {
                return true;
            }
            return false;
        } catch (PDOException $e) {
            return false;
        }
    }

    // دالة لرفع ملف للمهمة
    public function uploadTaskFile($fileName, $fileType) {
        try {
            $sql = "INSERT INTO files (file_name, file_type) VALUES (:file_name, :file_type)";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                'file_name' => $fileName,
                'file_type' => $fileType
            ]);
            
            return $this->conn->lastInsertId();
        } catch (PDOException $e) {
            return false;
        }
    }

    // دالة لتحديث ملف المهمة
    public function updateTaskFile($taskID, $fileID) {
        try {
            $sql = "UPDATE task SET file_id = :file_id WHERE taskID = :task_id";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([
                'file_id' => $fileID,
                'task_id' => $taskID
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // دالة لجلب تفاصيل مهمة معينة
    public function getTaskById($taskID) {
        try {
            $sql = "SELECT t.*, p.project_name, f.file_name 
                    FROM task t 
                    JOIN projects p ON t.project_id = p.id 
                    LEFT JOIN files f ON t.file_id = f.file_id 
                    WHERE t.taskID = :task_id";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute(['task_id' => $taskID]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    // دالة لجلب المهام القادمة للطالب
    public function getUpcomingTasks($username, $limit = 5) {
        try {
            $sql = "SELECT t.*, p.project_name 
                    FROM task t 
                    JOIN projects p ON t.project_id = p.id
                    JOIN project_members pm ON p.project_id = pm.project_id
                    WHERE pm.member_name = :username
                    AND t.status != 'مكتملة'
                    AND t.deadline >= CURDATE()
                    ORDER BY t.deadline ASC
                    LIMIT :limit";
                    
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

// دالة لجلب جميع المهام للتقويم
    public function getCalendarTasks($username) {
        try {
            $sql = "SELECT t.taskID as id, t.taskName as title, t.deadline as start, 
                    CASE 
                        WHEN t.status = 'مكتملة' THEN '#28a745' 
                        WHEN t.deadline < CURDATE() AND t.status != 'مكتملة' THEN '#dc3545'
                        ELSE '#ffc107' 
                    END as backgroundColor,
                    CASE 
                        WHEN t.status = 'مكتملة' THEN '#28a745' 
                        WHEN t.deadline < CURDATE() AND t.status != 'مكتملة' THEN '#dc3545'
                        ELSE '#ffc107' 
                    END as borderColor,
                    t.status as description
                    FROM task t 
                    JOIN projects p ON t.project_id = p.id
                    JOIN project_members pm ON p.project_id = pm.project_id
                    WHERE pm.member_name = :username";
                    
            $stmt = $this->conn->prepare($sql);
            $stmt->execute(['username' => $username]);
            $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // تحويل تاريخ البداية إلى تنسيق  FullCalendar
            foreach ($events as &$event) {
                // التأكد من أن التاريخ صالح
                if (!empty($event['start'])) {
                    
                    $event['allDay'] = true;
                }
            }
            
            return $events;
        } catch (PDOException $e) {
            error_log("خطأ في جلب مهام التقويم: " . $e->getMessage());
            return [];
        }
    }}
?>