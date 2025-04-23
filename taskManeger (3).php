<?php
include_once("conn.php");

class TaskManager {
    private $conn;
    public function __construct($connection) {
        $this->conn = $connection;
    }

    public function getTasksByProject($project_id) {
        try {
            $stmt = $this->conn->prepare("SELECT t.taskName, t.deadline, t.status, f.file_name 
                                          FROM task t 
                                          LEFT JOIN files f ON t.file_id = f.file_id 
                                          WHERE t.project_id = ?");
            $stmt->execute([$project_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("خطأ في جلب المهام: " . $e->getMessage());
        }
    }
}

$project_id = $_GET['project_id'] ?? null;
if (!$project_id) {
    die("رقم المشروع غير موجود.");
}

$taskManager = new TaskManager($conn);
$tasks = $taskManager->getTasksByProject($project_id);
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>مهام المشروع</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }
        h1 {
            text-align: center;
            margin-top: 20px;
            color: #333;
        }
        .container {
            width: 80%;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        a {
            display: inline-block;
            margin: 10px;
            padding: 10px 20px;
            background-color: #1a6691;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        a:hover {
            background-color:#1abc9c;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: right;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
            color: #333;
        }
        td a {
            color: #007BFF;
            text-decoration: none;
        }
        td a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>المهام الخاصة بالمشروع رقم: <?= htmlspecialchars($project_id) ?></h1>

        <a href="add_task.php?project_id=<?= $project_id ?>">إضافة مهمة</a>
        <a href="file_task.php?project_id=<?= $project_id ?>">ملفات المشروع</a>

        <table>
            <tr>
                <th>اسم المهمة</th>
                <th>الموعد النهائي</th>
                <th>الحالة</th>
                <th>الملف</th>
            </tr>
            <?php foreach ($tasks as $task): ?>
                <tr>
                    <td><?= htmlspecialchars($task['taskName']) ?></td>
                    <td><?= htmlspecialchars($task['deadline']) ?></td>
                    <td><?= htmlspecialchars($task['status']) ?></td>
                    <td>
                        <?php if ($task['file_name']): ?>
                            <a href="uploads/<?= htmlspecialchars($task['file_name']) ?>" download>تحميل</a>
                        <?php else: ?>
                            لا يوجد
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>
