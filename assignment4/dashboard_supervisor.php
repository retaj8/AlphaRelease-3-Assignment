<?php
session_start();
include 'conn.php';

if (!isset($conn)) {
    die("خطأ: لم يتم تعريف الاتصال بقاعدة البيانات. تأكد من أن conn.php يحتوي على المتغير \$conn.");
}

// التحقق من أن المستخدم مشرف
if (!isset($_SESSION['username'], $_SESSION['role']) || $_SESSION['role'] !== 'Supervisor') {
    header('Location: login.php');
    exit();
}

$username = $_SESSION['username'];

try {
    $stmt = $conn->prepare("SELECT * FROM Projects WHERE LOWER(supervisor) = LOWER(:username)");
    $stmt->execute(['username' => $username]);
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log($e->getMessage(), 3, "error_log.txt");
    $projects = [];
}

// دالة للحصول على المهام الخاصة بمشروع
function getTasksByProject($conn, $project_id) {
    try {
        $stmt = $conn->prepare("SELECT * FROM task WHERE project_id = :pid");
        $stmt->execute(['pid' => $project_id]);
        $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (empty($tasks)) {
            error_log("لا توجد مهام للمشروع: " . $project_id, 3, "error_log.txt");
        }
        return $tasks;
    } catch (PDOException $e) {
        error_log($e->getMessage(), 3, "error_log.txt");
        return [];
    }
}

// دالة لتحديد حالة المهمة
function getTaskStatus($task) {
    if ($task['status'] === 'completed') {
        return 'تم التسليم';
    } elseif (strtotime($task['deadline']) < time()) {
        return 'متأخرة';
    } else {
        return 'قيد التنفيذ';
    }
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>لوحة تحكم المشرف</title>
    <style>
    body {
        display: flex;
        direction: rtl;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: #1e1e2f;
        color: #f0f0f0;
        margin: 0;
    }

    .menu {
        width: 240px;
        background: #14142b;
        color: #ffffff;
        padding: 30px 20px;
        height: 100vh;
        box-shadow: 2px 0 8px rgba(0, 0, 0, 0.3);
        border-top-left-radius: 16px;
        border-bottom-left-radius: 16px;
    }

    .menu h3 {
        font-size: 1.4rem;
        margin-bottom: 30px;
        color: #fcd34d;
        border-bottom: 2px solid #fcd34d;
        padding-bottom: 10px;
    }

    .menu a {
        display: block;
        color: #ffffff;
        margin: 18px 0;
        text-decoration: none;
        font-size: 1rem;
        transition: all 0.3s;
        padding: 8px 12px;
        border-radius: 10px;
    }

    .menu a:hover {
        background-color: #2c2c4a;
        color: #fcd34d;
    }

    .content {
        flex: 1;
        padding: 40px;
        background-color: #282c3f;
    }

    .content h2 {
        font-size: 2rem;
        margin-bottom: 30px;
        color: #fcd34d;
    }

    .project {
        background: #1c1f33;
        border: 1px solid #3a3a5c;
        border-radius: 20px;
        padding: 20px;
        margin-bottom: 25px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        transition: transform 0.2s;
    }

    .project:hover {
        transform: scale(1.01);
    }

    .project h3 {
        margin-top: 0;
        font-size: 1.4rem;
        margin-bottom: 15px;
    }

    .project-link {
        color: #60a5fa;
        text-decoration: none;
        transition: color 0.3s;
    }

    .project-link:hover {
        color: #3b82f6;
    }

    .task {
        margin-right: 15px;
        margin-bottom: 8px;
        line-height: 1.8;
    }

    .task-status {
        font-weight: bold;
        color: #22d3ee;
        margin-right: 8px;
    }

    li {
        list-style: none;
        padding: 4px 0;
        border-bottom: 1px dashed #444;
    }

    li:last-child {
        border-bottom: none;
    }

    p {
        font-size: 1rem;
        color: #ccc;
    }
</style>

</head>
<body>
    <div class="menu">
        <h3>القائمة</h3>
        <a href="dashboard_supervisor.php">الرئيسية</a>
        <a href="search_project.php">المشاريع</a>
        <a href="view_report.php">الملفات</a>
       
    </div>

    <div class="content">
        <h2>مشاريعي</h2>
        <?php if (empty($projects)): ?>
            <p>لا توجد مشاريع مرتبطة بهذا المشرف.</p>
        <?php else: ?>
            <?php foreach ($projects as $project): ?>
                <div class="project">
                    <h3>
                        <a class="project-link" href="taskManeger.php?project_id=<?= $project['id']; ?>">
                            <?= htmlspecialchars($project['project_name'], ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    </h3>

                    <ul>
                        <?php
                            $tasks = getTasksByProject($conn, $project['id']);
                            if (empty($tasks)): 
                        ?>
                            <li>لا توجد مهام لهذا المشروع.</li>
                        <?php else: ?>
                            <?php foreach ($tasks as $task): 
                                $status = getTaskStatus($task);
                            ?>
                                <li class="task">
                                    <?= htmlspecialchars($task['taskName'] ?? 'عنوان غير متوفر', ENT_QUOTES, 'UTF-8') ?> -
                                    <span class="task-status"><?= $status ?></span>
                                    (تاريخ التسليم: <?= htmlspecialchars($task['deadline'] ?? 'غير متوفر', ENT_QUOTES, 'UTF-8') ?>)
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>
