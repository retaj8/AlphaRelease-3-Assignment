<?php
session_start();
include_once("conn.php");
include_once("Task.php");

// التحقق من تسجيل الدخول كمشرف
if (!isset($_SESSION['username'], $_SESSION['role']) || $_SESSION['role']!== 'Supervisor') {
    die("غير مصرح لك بالوصول إلى هذه الصفحة.");
}

$username = $_SESSION['username'];

// ✅ التحقق من تمرير project_id عبر الرابط
if (!isset($_GET['project_id']) || empty($_GET['project_id'])) {
    die("رقم المشروع غير محدد. تأكد من أنك تستخدم رابطًا صحيحًا يحتوي على project_id.");
}

$project_id = $_GET['project_id'];

try {
    // ✅ جلب معلومات المشروع مع حماية ضد SQL Injection
    $stmt = $conn->prepare("SELECT project_name FROM projects WHERE project_id =?");
    $stmt->bindParam(1, $project_id, PDO::PARAM_INT);
    $stmt->execute();
    $project = $stmt->fetch(PDO::FETCH_ASSOC);
    $projectName = $project && isset($project['project_name'])? $project['project_name']: "غير معروف";

    // ✅ استخدام كلاس Task للحصول على ملفات المشروع
    $taskManager = new Task($conn);
    $files = $taskManager->getFilesByProject($project_id);
} catch (Exception $e) {
    die("حدث خطأ: ". htmlspecialchars($e->getMessage()));
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>عرض ملفات المشروع</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Segoe UI', sans-serif; direction: rtl;}
.container { max-width: 900px; margin: auto; padding: 20px;}
.project-info { text-align: center; background: #f8f9fa; padding: 15px; border-radius: 10px;}
        table { width: 100%; border-collapse: collapse; background: #fff;}
        th, td { padding: 10px; text-align: center; border-bottom: 1px solid #ddd;}
        th { background: #1abc9c; color: white;}
.download-link { color: #007bff; text-decoration: none; font-weight: bold;}
.no-files { text-align: center; padding: 20px; color: #777;}
    </style>
</head>
<body>
    <div class="container">
        <div class="project-info">
            <h2>ملفات المشروع: <?= htmlspecialchars($projectName)?></h2>
        </div>

        <?php if ($files && count($files)> 0):?>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>اسم المهمة</th>
                        <th>اسم الملف</th>
                        <th>نوع الملف</th>
                        <th>تحميل</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($files as $index => $file):?>
                    <tr>
                        <td><?= $index + 1?></td>
                        <td><?= htmlspecialchars($file['taskName'])?></td>
                        <td><?= htmlspecialchars($file['file_name'])?></td>
                        <td><?= strtoupper(pathinfo($file['file_name'], PATHINFO_EXTENSION))?></td>
                        <td><a href="uploads/<?= urlencode($file['file_name'])?>" download class="download-link">تحميل</a></td>
                    </tr>
                    <?php endforeach;?>
                </tbody>
            </table>
        <?php else:?>
            <div class="no-files">لا توجد ملفات لهذا المشروع حالياً</div>
        <?php endif;?>
    </div>
</body>
</html>
