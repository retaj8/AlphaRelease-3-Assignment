<?php 
session_start();
include_once("conn.php");

// التحقق من تسجيل الدخول كمشرف
if (!isset($_SESSION['username'], $_SESSION['role']) || $_SESSION['role'] !== 'Supervisor') {
    die("غير مصرح لك بالوصول إلى هذه الصفحة.");
}

$username = $_SESSION['username'];

// 👇 أول عرّف المتغير project_id
$project_id = $_GET['project_id'] ?? null;
try {
    $stmt = $conn->prepare("SELECT project_name FROM Projects WHERE id = ?");
    $stmt->execute([$project_id]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);
    $projectName = $project ? $project['project_name'] : "غير معروف";
} catch (PDOException $e) {
    $projectName = "خطأ في جلب اسم المشروع";
}

if (!$project_id) {
    die("رقم المشروع غير موجود.");
}


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
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: linear-gradient(135deg, #1e1e2f, #2c3e50);
        margin: 0;
        padding: 0;
        color: #f4f4f4;
    }

    h1 {
        text-align: center;
        margin: 30px 0;
        font-size: 2rem;
        color: #fcd34d;
        text-shadow: 1px 1px 3px #000;
    }

    .container {
        width: 85%;
        margin: auto;
        padding: 30px;
        background-color: #1c1f33;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    }

    a {
        display: inline-block;
        margin: 10px 5px;
        padding: 10px 25px;
        background: linear-gradient(135deg, #00c6ff, #0072ff);
        color: white;
        text-decoration: none;
        border-radius: 12px;
        font-weight: bold;
        box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        transition: all 0.3s ease;
    }

    a:hover {
        background: linear-gradient(135deg, #1abc9c, #16a085);
        transform: scale(1.05);
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 30px;
        background-color: #2a2f4a;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.25);
    }

    th, td {
        padding: 16px;
        text-align: right;
        border-bottom: 1px solid #3b3f5c;
        color: #e0e0e0;
    }

    th {
        background-color: #1a1d2e;
        color: #fcd34d;
        font-size: 1rem;
        letter-spacing: 1px;
    }

    td a {
        color: #38bdf8;
        font-weight: bold;
        text-decoration: none;
        transition: color 0.3s;
    }

    td a:hover {
        color: #0ea5e9;
        text-decoration: underline;
    }

    td {
        font-size: 0.95rem;
    }

    ::selection {
        background: #fcd34d;
        color: #1a1d2e;
    }
</style>

</head>
<body>
    <div class="container">
    <h1>المهام الخاصة بالمشروع: <?= htmlspecialchars($projectName) ?></h1>


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
