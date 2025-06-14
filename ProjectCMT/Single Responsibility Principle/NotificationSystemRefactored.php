<?php

// --- واجهة المراقب ---
interface NotificationObserver {
    public function update(array $data);
}

// --- كلاس مسؤول عن بناء بيانات الإشعارات (Builder) ---
class NotificationDataBuilder {
    private PDO $conn;

    public function __construct(PDO $conn) {
        $this->conn = $conn;
    }

    public function buildNewTaskData(int $taskId, string $taskName, int $projectId, string $deadline, string $supervisor): array {
        $stmt = $this->conn->prepare("SELECT member_name FROM project_members WHERE project_id = ?");
        $stmt->execute([$projectId]);
        $members = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $stmt = $this->conn->prepare("SELECT project_name FROM projects WHERE project_id = ?");
        $stmt->execute([$projectId]);
        $projectName = $stmt->fetchColumn();

        return [
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
    }

    public function buildTaskSubmissionData(int $taskId, string $studentName, string $supervisor): array {
        $stmt = $this->conn->prepare("SELECT t.taskName, p.project_name, p.project_id 
                                      FROM task t 
                                      JOIN projects p ON t.project_id = p.project_id 
                                      WHERE t.taskID = ?");
        $stmt->execute([$taskId]);
        $taskData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$taskData) {
            throw new Exception("المهمة غير موجودة");
        }

        return [
            'type' => 'task_submission',
            'task_id' => $taskId,
            'task_name' => $taskData['taskName'],
            'project_id' => $taskData['project_id'],
            'project_name' => $taskData['project_name'],
            'student_name' => $studentName,
            'supervisor' => $supervisor,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
}

// --- النظام الرئيسي للإشعارات ---
class NotificationSystem {
    private array $observers = [];
    private NotificationDataBuilder $dataBuilder;

    public function __construct(NotificationDataBuilder $builder) {
        $this->dataBuilder = $builder;
    }

    public function attach(NotificationObserver $observer): void {
        // Avoid duplicate attaching
        if (!in_array($observer, $this->observers, true)) {
            $this->observers[] = $observer;
        }
    }

    public function detach(NotificationObserver $observer): void {
        $this->observers = array_filter(
            $this->observers,
            fn($obs) => $obs !== $observer
        );
    }

    private function notify(array $data): void {
        foreach ($this->observers as $observer) {
            $observer->update($data);
        }
    }

    public function notifyNewTask(int $taskId, string $taskName, int $projectId, string $deadline, string $supervisor): bool {
        try {
            $data = $this->dataBuilder->buildNewTaskData($taskId, $taskName, $projectId, $deadline, $supervisor);
            $this->notify($data);
            return true;
        } catch (Exception $e) {
            error_log("خطأ في إشعار المهمة الجديدة: " . $e->getMessage());
            return false;
        }
    }

    public function notifyTaskSubmission(int $taskId, string $studentName, string $supervisor): bool {
        try {
            $data = $this->dataBuilder->buildTaskSubmissionData($taskId, $studentName, $supervisor);
            $this->notify($data);
            return true;
        } catch (Exception $e) {
            error_log("خطأ في إشعار تسليم المهمة: " . $e->getMessage());
            return false;
        }
    }
}

// --- Trait لمشاركة وظائف إضافة الإشعارات والرسائل ---
trait NotificationDatabaseTrait {
    private PDO $conn;

    private function addNotification(string $title, string $message, string $recipient): void {
        try {
            $stmt = $this->conn->prepare("INSERT INTO notifications (type, title, message, recipient) VALUES ('system', ?, ?, ?)");
            $stmt->execute([$title, $message, $recipient]);
        } catch (PDOException $e) {
            error_log("خطأ في إضافة إشعار: " . $e->getMessage());
        }
    }

    private function sendMessage(string $sender, string $receiver, string $subject, string $content): void {
        try {
            $stmt = $this->conn->prepare("INSERT INTO messages (sender, receiver, subject, message_content) VALUES (?, ?, ?, ?)");
            $stmt->execute([$sender, $receiver, $subject, $content]);
        } catch (PDOException $e) {
            error_log("خطأ في إرسال رسالة: " . $e->getMessage());
        }
    }
}

// --- مراقب إشعارات المهمة الجديدة لقاعدة البيانات ---
class NewTaskDatabaseNotificationObserver implements NotificationObserver {
    use NotificationDatabaseTrait;

    public function __construct(PDO $conn) {
        $this->conn = $conn;
    }

    public function update(array $data): void {
        if ($data['type'] !== 'new_task') return;

        $title = "مهمة جديدة: " . $data['task_name'];
        $message = "تم إضافة مهمة جديدة ({$data['task_name']}) للمشروع {$data['project_name']}. موعد التسليم: {$data['deadline']}";

        foreach ($data['members'] as $member) {
            $this->addNotification($title, $message, $member);
        }
    }
}

// --- مراقب إشعارات تسليم المهمة لقاعدة البيانات ---
class TaskSubmissionDatabaseNotificationObserver implements NotificationObserver {
    use NotificationDatabaseTrait;

    public function __construct(PDO $conn) {
        $this->conn = $conn;
    }

    public function update(array $data): void {
        if ($data['type'] !== 'task_submission') return;

        $title = "تم تسليم مهمة: " . $data['task_name'];
        $message = "قام الطالب {$data['student_name']} بتسليم المهمة {$data['task_name']} للمشروع {$data['project_name']}";

        $this->addNotification($title, $message, $data['supervisor']);
    }
}

// --- مراقب إشعارات المهمة الجديدة لإرسال الرسائل ---
class NewTaskMessageNotificationObserver implements NotificationObserver {
    use NotificationDatabaseTrait;

    public function __construct(PDO $conn) {
        $this->conn = $conn;
    }

    public function update(array $data): void {
        if ($data['type'] !== 'new_task') return;

        $subject = "مهمة جديدة: " . $data['task_name'];
        $content = "تم إضافة مهمة جديدة ({$data['task_name']}) للمشروع {$data['project_name']}. موعد التسليم: {$data['deadline']}";

        foreach ($data['members'] as $member) {
            $this->sendMessage($data['supervisor'], $member, $subject, $content);
        }
    }
}

// --- مراقب إشعارات تسليم المهمة لإرسال الرسائل ---
class TaskSubmissionMessageNotificationObserver implements NotificationObserver {
    use NotificationDatabaseTrait;

    public function __construct(PDO $conn) {
        $this->conn = $conn;
    }

    public function update(array $data): void {
        if ($data['type'] !== 'task_submission') return;

        $subject = "تم تسليم مهمة: " . $data['task_name'];
        $content = "قام الطالب {$data['student_name']} بتسليم المهمة {$data['task_name']} للمشروع {$data['project_name']}";

        $this->sendMessage($data['student_name'], $data['supervisor'], $subject, $content);
    }
}

?>
