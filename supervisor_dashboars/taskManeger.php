<?php 
session_start();
include_once("conn.php");
include_once("Task.php");

// التحقق من تسجيل الدخول كمشرف
if (!isset($_SESSION['username'], $_SESSION['role']) || $_SESSION['role'] !== 'Supervisor') {
    die("غير مصرح لك بالوصول إلى هذه الصفحة.");
}

$username = $_SESSION['username'];

// تعريف متغير project_id
$project_id = $_GET['project_id'] ?? null;

if (!$project_id) {
    die("رقم المشروع غير موجود.");
}

// استخدام كلاس Task للتعامل مع المهام
$taskManager = new Task($conn);

try {
    // جلب اسم المشروع (بعد تحديث قاعدة البيانات project_id هو المعرف الرئيسي)
    $stmt = $conn->prepare("SELECT project_name FROM projects WHERE project_id = ?");
    $stmt->execute([$project_id]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);
    $projectName = $project ? $project['project_name'] : "غير معروف";
    
    // جلب المهام
    $tasks = $taskManager->getFilesByProject($project_id);
    
    // تحديث حالات المهام المتأخرة تلقائياً
    $updatedTasks = $taskManager->updateLateTasksStatus($project_id);
    if ($updatedTasks > 0) {
        // إذا كان هناك تحديث، نقوم بإعادة جلب المهام
        $tasks = $taskManager->getTasksByProject($project_id);
    }
} catch (Exception $e) {
    die("حدث خطأ: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>مهام المشروع</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1e1e2f, #2c3e50);
            margin: 0;
            padding: 0;
            color: #f4f4f4;
            direction: rtl;
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

        .action-button {
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

        .action-button:hover {
            background: linear-gradient(135deg, #1abc9c, #16a085);
            transform: scale(1.05);
            color: white;
        }

        .action-button i {
            margin-left: 8px;
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
            display: inline-block;
            margin: 0 5px;
        }

        td a:hover {
            color: #0ea5e9;
            text-decoration: underline;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: bold;
        }
        
        .status-complete {
            background-color: #10b981;
            color: white;
        }
        
        .status-in-progress {
            background-color: #f59e0b;
            color: white;
        }
        
        .status-late {
            background-color: #ef4444;
            color: white;
        }

        .back-button {
            margin-top: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>المهام الخاصة بالمشروع: <?= htmlspecialchars($projectName) ?></h1>

        <div class="actions">
            <a href="add_task.php?project_id=<?= $project_id ?>" class="action-button">
                <i class="fas fa-plus"></i> إضافة مهمة
            </a>
            <a href="file_task.php?project_id=<?= $project_id ?>" class="action-button">
                <i class="fas fa-file"></i> ملفات المشروع
            </a>
            <a href="report.php?project_id=<?= $project_id ?>" class="action-button">
                <i class="fas fa-chart-bar"></i> تقرير المشروع
            </a>
        </div>

        <?php if (count($tasks) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>اسم المهمة</th>
                        <th>الموعد النهائي</th>
                        <th>الحالة</th>
                        <th>القائم بالمهمة</th>
                        <th>الملف</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tasks as $task): ?>
                        <tr>
                            <td><?= htmlspecialchars($task['taskName']) ?></td>
                            <td><?= htmlspecialchars($task['deadline']) ?></td>
                            <td>
                                <?php 
                                    $statusClass = 'status-in-progress'; // القيمة الافتراضية
                                      if ($task['status'] === 'مكتملة') {
                                             $statusClass = 'status-complete';
                                           } elseif ($task['status'] === 'متأخرة') {
                                             $statusClass = 'status-late';
                                             }

                                ?>
                                <span class="status-badge <?= $statusClass ?>"><?= htmlspecialchars($task['status']) ?></span>
                            </td>
                            <td><?= htmlspecialchars($task['assigned_to'] ?? 'غير معين') ?></td>
                            <td>
                                <?php if ($task['file_name']): ?>
                                    <a href="uploads/<?= htmlspecialchars($task['file_name']) ?>" download>
                                        <i class="fas fa-download"></i> تحميل
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">لا يوجد</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="edit_task.php?id=<?= $task['taskID'] ?>" title="تعديل المهمة">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php if ($task['status'] !== 'مكتملة'): ?>
                                    <a href="update_task_status.php?id=<?= $task['taskID'] ?>&status=مكتملة" title="تعليم كمكتملة">
                                        <i class="fas fa-check-circle"></i>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-info mt-4 text-center">
                لا توجد مهام لهذا المشروع. قم بإضافة مهام جديدة.
            </div>
        <?php endif; ?>
        
        <div class="back-button">
            <a href="dashboard_supervisor.php" class="action-button">
                <i class="fas fa-arrow-right"></i> العودة للوحة التحكم
            </a>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>