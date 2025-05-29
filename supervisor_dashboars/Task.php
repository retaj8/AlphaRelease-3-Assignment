<?php
/**
 * كلاس Task - يدير العمليات المتعلقة بالمهام
 */
class Task {
    private $conn;

    /**
     * دالة البناء
     */
    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }

    /**
     * جلب مهام الطالب
     */
    public function getStudentTasks($username) {
        try {
            $sql = "SELECT t.*, p.project_name 
                    FROM task t 
                    JOIN projects p ON t.project_id = p.project_id
                    JOIN project_members pm ON p.project_id = pm.project_id
                    WHERE pm.member_name = :username
                    ORDER BY t.deadline ASC";
                    
            $stmt = $this->conn->prepare($sql);
            $stmt->execute(['username' => $username]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("خطأ في جلب مهام الطالب: " . $e->getMessage());
            return [];
        }
    }

    /**
     * تسليم المهمة المنجزة من الطالب
     */
    public function submitTask($taskID, $studentName, $notes, $fileId = null) {
        try {
            // تحديث حالة المهمة
            $sql = "UPDATE task SET 
                    status = 'مكتملة', 
                    assigned_to = :student_name";
            
            // إضافة تحديث file_id إذا كان هناك ملف
            if ($fileId !== null) {
                $sql .= ", file_id = :file_id";
            }
            
            $sql .= " WHERE taskID = :task_id";
            
            $params = [
                'student_name' => $studentName,
                'task_id' => $taskID
            ];
            
            if ($fileId !== null) {
                $params['file_id'] = $fileId;
            }
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            
            // إذا تم تحديث المهمة بنجاح
            if ($stmt->rowCount() > 0) {
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("خطأ في تسليم المهمة: " . $e->getMessage());
            return false;
        }
    }

    /**
     * رفع ملف للمهمة
     */
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
            error_log("خطأ في رفع ملف المهمة: " . $e->getMessage());
            return false;
        }
    }

    /**
     * تحديث ملف المهمة
     */
    public function updateTaskFile($taskID, $fileID) {
        try {
            $sql = "UPDATE task SET file_id = :file_id WHERE taskID = :task_id";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([
                'file_id' => $fileID,
                'task_id' => $taskID
            ]);
        } catch (PDOException $e) {
            error_log("خطأ في تحديث ملف المهمة: " . $e->getMessage());
            return false;
        }
    }

    /**
     * جلب تفاصيل مهمة معينة
     */
    public function getTaskById($taskID) {
        try {
            $sql = "SELECT t.*, p.project_name, f.file_name 
                    FROM task t 
                    JOIN projects p ON t.project_id = p.project_id 
                    LEFT JOIN files f ON t.file_id = f.file_id 
                    WHERE t.taskID = :task_id";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute(['task_id' => $taskID]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("خطأ في جلب تفاصيل المهمة: " . $e->getMessage());
            return false;
        }
    }

    /**
     * جلب المهام القادمة للطالب
     */
    public function getUpcomingTasks($username, $limit = 5) {
        try {
            $sql = "SELECT t.*, p.project_name 
                    FROM task t 
                    JOIN projects p ON t.project_id = p.project_id
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
            error_log("خطأ في جلب المهام القادمة: " . $e->getMessage());
            return [];
        }
    }

    /**
     * جلب جميع المهام للتقويم
     */
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
                    JOIN projects p ON t.project_id = p.project_id
                    JOIN project_members pm ON p.project_id = pm.project_id
                    WHERE pm.member_name = :username";
                    
            $stmt = $this->conn->prepare($sql);
            $stmt->execute(['username' => $username]);
            $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // تحويل تاريخ البداية إلى تنسيق FullCalendar
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
    }
    
    /**
     * تحليل حالة المهام وتصنيفها
     */
    public function analyzeProjectTasks($project_id) {
        try {
            $tasks = $this->getTasksByProject($project_id);
            
            $analysis = [
                'total' => count($tasks),
                'completed' => 0,
                'in_progress' => 0,
                'late' => 0,
                'team_performance' => [],
                'tasks' => $tasks,
                'progress_percentage' => 0
            ];
            
            foreach ($tasks as $task) {
                // تحديد حالة المهمة
                if ($task['status'] === 'مكتملة') {
                    $analysis['completed']++;
                } elseif (strtotime($task['deadline']) < time() && $task['status'] !== 'مكتملة') {
                    $analysis['late']++;
                } else {
                    $analysis['in_progress']++;
                }
                
                // تحليل أداء الفريق
                $user = $task['assigned_to'] ?? 'غير معين';
                
                if (!isset($analysis['team_performance'][$user])) {
                    $analysis['team_performance'][$user] = [
                        'completed' => 0,
                        'late' => 0,
                        'in_progress' => 0
                    ];
                }
                
                if ($task['status'] === 'مكتملة') {
                    $analysis['team_performance'][$user]['completed']++;
                } elseif (strtotime($task['deadline']) < time() && $task['status'] !== 'مكتملة') {
                    $analysis['team_performance'][$user]['late']++;
                } else {
                    $analysis['team_performance'][$user]['in_progress']++;
                }
            }
            
            // حساب نسبة الإنجاز
            if ($analysis['total'] > 0) {
                $analysis['progress_percentage'] = round(($analysis['completed'] / $analysis['total']) * 100);
            }
                
            return $analysis;
        } catch (Exception $e) {
            error_log("خطأ في تحليل مهام المشروع: " . $e->getMessage());
            throw new Exception("حدث خطأ أثناء تحليل مهام المشروع");
        }
    }
    
    /**
     * جلب جميع المهام الخاصة بمشروع معين
     */
    public function getTasksByProject($project_id) {
        try {
            $stmt = $this->conn->prepare("SELECT t.taskID, t.taskName, t.deadline, t.status, 
                                         t.file_id, f.file_name, t.assigned_to  
                                         FROM task t 
                                         LEFT JOIN files f ON t.file_id = f.file_id 
                                         WHERE t.project_id = ?");
            $stmt->execute([$project_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("خطأ في جلب المهام: " . $e->getMessage());
            throw new Exception("حدث خطأ أثناء جلب المهام");
        }
    }
public function getFilesByProject($projectID) {
    try {
        $stmt = $this->conn->prepare("
            SELECT t.taskName, f.file_name 
            FROM task t 
            JOIN files f ON t.file_id = f.file_id 
            WHERE t.project_id = ?
        ");
        $stmt->execute([$projectID]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("حدث خطأ أثناء جلب الملفات: " . $e->getMessage());
    }
}
public function updateLateTasksStatus() {
    try {
        $today = date('Y-m-d');
        $stmt = $this->conn->prepare("UPDATE task SET status = 'متأخرة' WHERE deadline < ? AND status != 'مكتملة'");
        $stmt->execute([$today]);
    } catch (PDOException $e) {
        die("خطأ أثناء تحديث حالة المهام المتأخرة: " . $e->getMessage());
    }
}
public function getProjectMembers($projectId) {
    $stmt = $this->conn->prepare("
        SELECT member_name 
        FROM project_members 
        WHERE project_id = :project_id
    ");
    $stmt->execute(['project_id' => $projectId]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN); // يرجع مصفوفة بأسماء الأعضاء
}
private function saveFile($file) {
    $uploadDir = 'uploads/tasks/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileName = time() . '_' . basename($file['name']);
    $fileType = $file['type'];
    $targetPath = $uploadDir . $fileName;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        // استخدام $this->conn
        $stmt = $this->conn->prepare("INSERT INTO files (file_name, file_type) VALUES (?, ?)");
        $stmt->execute([$fileName, $fileType]);
        return $this->conn->lastInsertId();
    } else {
        throw new Exception("فشل رفع الملف إلى السيرفر");
    }
}

public function addTask($taskName, $deadline, $project_id, $assigned_to = '', $file = null) {
    $file_id = 0; // قيمة افتراضية

    if ($file && isset($file['error']) && $file['error'] === 0) {
        // نستخدم دالة saveFile للرفع والتسجيل
        $file_id = $this->saveFile($file);
    }

    $status = 'جديدة'; // الحالة الافتراضية

    $stmt = $this->conn->prepare("
        INSERT INTO task (taskName, status, deadline, project_id, file_id, assigned_to)
        VALUES (:taskName, :status, :deadline, :project_id, :file_id, :assigned_to)
    ");

    $result = $stmt->execute([
        ':taskName' => $taskName,
        ':status' => $status,
        ':deadline' => $deadline,
        ':project_id' => $project_id,
        ':file_id' => $file_id,
        ':assigned_to' => $assigned_to
    ]);

    if (!$result) {
        throw new Exception("فشل في إضافة المهمة.");
    }

    return $this->conn->lastInsertId();
}


}
?>