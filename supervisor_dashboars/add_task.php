<?php
session_start();
include_once("conn.php");
include_once("Task.php");

// التحقق من تسجيل الدخول كمشرف
if (!isset($_SESSION['username'], $_SESSION['role']) || $_SESSION['role'] !== 'Supervisor') {
    die("غير مصرح لك بالوصول إلى هذه الصفحة.");
}

$username = $_SESSION['username'];
$project_id = $_GET['project_id'] ?? null;

if (!$project_id) {
    die("رقم المشروع غير موجود.");
}

$message = '';
$messageType = '';

// استخدام كلاس Task للتعامل مع المهام
$taskObj = new Task($conn);

// جلب أعضاء المشروع
try {
    $members = $taskObj->getProjectMembers($project_id);
} catch (Exception $e) {
    $members = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $taskName = trim($_POST['taskName']);
        $deadline = $_POST['deadline'];
        $assigned_to = $_POST['assigned_to'] ?? null;
        
        // التحقق من البيانات
        if (empty($taskName)) {
            throw new Exception("يجب إدخال اسم المهمة");
        }
        
        if (empty($deadline)) {
            throw new Exception("يجب إدخال الموعد النهائي");
        }
        
        // إضافة المهمة
        if (isset($_FILES['taskFile']) && $_FILES['taskFile']['error'] == 0) {
            $taskId = $taskObj->addTask($taskName, $deadline, $project_id, $assigned_to, $_FILES['taskFile']);
        } else {
            $taskId = $taskObj->addTask($taskName, $deadline, $project_id, $assigned_to);
        }
        
        $message = "تم إضافة المهمة بنجاح";
        $messageType = "success";
        
        // التوجيه بعد 2 ثانية
        header("refresh:2;url=taskManeger.php?project_id=" . urlencode($project_id));
        
    } catch (Exception $e) {
        $message = $e->getMessage();
        $messageType = "danger";
    }
}

// جلب معلومات المشروع - تصحيح الاستعلام
try {
    $stmt = $conn->prepare("SELECT project_name FROM projects WHERE project_id = ?");
    $stmt->execute([$project_id]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);
    $projectName = $project ? $project['project_name'] : "غير معروف";
} catch (PDOException $e) {
    $projectName = "خطأ في جلب اسم المشروع";
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>إضافة مهمة</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #1e1e2f, #2c3e50);
            margin: 0;
            padding: 0;
            display: flex;
            direction: rtl;
            min-height: 100vh;
        }

        .sidebar {
            width: 220px;
            background-color: #1a1d2e;
            height: 100vh;
            padding-top: 40px;
            box-shadow: -2px 0 5px rgba(0, 0, 0, 0.2);
            position: fixed;
        }

        .sidebar h2 {
            color: #fff;
            text-align: center;
            margin-bottom: 30px;
            font-size: 1.5rem;
        }

        .sidebar a {
            display: block;
            background-color: #34495e;
            color: #ecf0f1;
            text-decoration: none;
            padding: 14px 22px;
            margin: 10px;
            border-radius: 8px;
            text-align: center;
            font-weight: bold;
            transition: background 0.3s ease;
            font-size: 1.1rem;
        }

        .sidebar a:hover {
            background-color: #1abc9c;
            color: #fff;
        }

        .main-content {
            flex: 1;
            padding: 40px;
            background: linear-gradient(135deg, #f4f4f9, #ecf0f1);
            margin-right: 220px;
        }

        .container {
            max-width: 600px;
            background-color: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            margin: auto;
        }

        h1 {
            text-align: center;
            color: #333;
            font-size: 1.8rem;
            margin-bottom: 25px;
        }

        .project-info {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            text-align: center;
        }

        .project-info h4 {
            color: #2c3e50;
            font-size: 1.2rem;
            margin-bottom: 5px;
        }

        .project-info p {
            color: #7f8c8d;
            margin-bottom: 0;
        }

        form label {
            display: block;
            margin-top: 18px;
            color: #333;
            font-size: 1.1rem;
            font-weight: 500;
        }

        input[type="text"], input[type="date"], input[type="file"], select {
            width: 100%;
            padding: 12px;
            margin-top: 6px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 1rem;
            background-color: #f9f9f9;
            transition: background 0.3s ease;
        }

        input[type="text"]:focus, input[type="date"]:focus, input[type="file"]:focus, select:focus {
            background-color: #eaf1f4;
            border-color: #1abc9c;
        }

        .btn-submit {
            margin-top: 25px;
            background-color: #007BFF;
            color: white;
            padding: 15px 25px;
            border: none;
            border-radius: 8px;
            width: 100%;
            cursor: pointer;
            font-size: 1.2rem;
            transition: background 0.3s ease;
            font-weight: bold;
        }

        .btn-submit:hover {
            background-color: #0056b3;
        }

        .btn-cancel {
            margin-top: 10px;
            background-color: #6c757d;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            width: 100%;
            cursor: pointer;
            font-size: 1.1rem;
            transition: background 0.3s ease;
        }

        .btn-cancel:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>لوحة التحكم</h2>
    <a href="dashboard_supervisor.php">الرئيسية</a>
    <a href="search_project.php">المشاريع</a>
    <a href="view_report.php">التقارير</a>
    <a href="taskManeger.php?project_id=<?= $project_id ?>">عودة للمهام</a>
</div>

<div class="main-content">
    <div class="container">
        <h1>إضافة مهمة جديدة</h1>
        
        <div class="project-info">
            <h4>المشروع:</h4>
            <p><?= htmlspecialchars($projectName) ?></p>
        </div>
        
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?= $messageType ?>" role="alert">
                <?= $message ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data">
            <label>اسم المهمة:</label>
            <input type="text" name="taskName" required>

            <label>تاريخ التسليم:</label>
            <input type="date" name="deadline" required>

            <label>تعيين المهمة إلى:</label>
            <select name="assigned_to">
                <option value="">-- اختر عضو --</option>
                <?php foreach ($members as $member): ?>
                    <option value="<?= htmlspecialchars($member) ?>"><?= htmlspecialchars($member) ?></option>
                <?php endforeach; ?>
            </select>
            <small class="text-muted d-block mt-2">اختياري: يمكنك تعيين المهمة إلى أحد أعضاء الفريق</small>

            <label>ملف المهمة:</label>
            <input type="file" name="taskFile">
            <small class="text-muted d-block mt-2">اختياري: يمكنك إرفاق ملف مع المهمة</small>

            <button type="submit" class="btn-submit">
                <i class="fas fa-plus-circle ml-1"></i> إضافة المهمة
            </button>
            
            <a href="taskManeger.php?project_id=<?= $project_id ?>" class="btn-cancel d-block text-center text-decoration-none">
                <i class="fas fa-times-circle ml-1"></i> إلغاء
            </a>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>