<?php
include 'conn.php';
require_once 'Project.php';

$project = new Project($conn); // تأكد أن الكلاس متصل بقاعدة البيانات
//$projectData = $project->getProjectById($_GET['id']);

$message = "";

// التحقق من وجود ID المشروع في GET
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $projectData = $project->getProjectById($_GET['id']);
    if (!$projectData) {
        echo "❌ المشروع غير موجود.";
        exit;
    }
} else {
    echo "❌ لم يتم توفير معرف المشروع.";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $projectId = isset($_POST['project_id']) ? $_POST['project_id'] : '';
    $projectName = isset($_POST['project_name']) ? $_POST['project_name'] : '';
    $startDate = isset($_POST['start_date']) ? $_POST['start_date'] : '';
    $endDate = isset($_POST['end_date']) ? $_POST['end_date'] : '';
    $status = isset($_POST['status']) ? $_POST['status'] : '';
    $teamMembers = isset($_POST['team_members']) ? $_POST['team_members'] : [];

    if (empty($projectId) || empty($projectName) || empty($startDate) || empty($endDate)) {
        $message = "❌ يرجى ملء جميع الحقول المطلوبة.";
    } else {
        $projectData = [
            'project_id' => $projectId,
            'project_name' => $projectName,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => $status,
            'team_leader' => $_POST['team_leader'],
            'supervisor' => $_POST['supervisor']
        ];

        $message = $project->updateProject($projectData, $teamMembers);

    }
}
?>
 




<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>تعديل مشروع</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f9f9f9; /* خلفية هادئة */
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh; /* جعل الصفحة متوسطة */
        }
        .form-card {
            width: 90%;
            max-width: 400px; /* تصغير العرض */
            padding: 20px;
            border-radius: 12px;
            background-color: #ffffff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* إضافة ظل لطيف */
        }
        .form-label {
            font-size: 0.9rem; /* تحسين الحجم */
            font-weight: bold;
            color: #007bff; /* اللون الأزرق للتمييز */
        }
        .btn-primary {
            background-color: #007bff;
            border: none;
            border-radius: 50px;
            padding: 10px 20px;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="form-card">
        <h2 class="text-center text-primary mb-4">تعديل مشروع</h2>
        
        <?php if (!empty($message)): ?>
            <div class="alert alert-info text-center"><?php echo $message; ?></div>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="project_id" value="<?php echo isset($projectData['project_id']) ? $projectData['project_id'] : ''; ?>">
            <div class="mb-3">
                <label class="form-label">اسم المشروع</label>
                <input type="text" name="project_name" class="form-control"
                       value="<?php echo isset($projectData['project_name']) ? $projectData['project_name'] : ''; ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">تاريخ البداية</label>
                <input type="date" name="start_date" class="form-control"

رتاج, [20/04/2025 12:20 م]
value="<?php echo isset($projectData['start_date']) ? $projectData['start_date'] : ''; ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">تاريخ النهاية</label>
                <input type="date" name="end_date" class="form-control"
                       value="<?php echo isset($projectData['end_date']) ? $projectData['end_date'] : ''; ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">الحالة</label>
                <select name="status" class="form-control" required>
                    <option value="Pending" <?php echo isset($projectData['status']) && $projectData['status'] == 'Pending' ? 'selected' : ''; ?>>قيد التنفيذ</option>
                    <option value="Completed" <?php echo isset($projectData['status']) && $projectData['status'] == 'Completed' ? 'selected' : ''; ?>>مكتمل</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">مدير الفريق</label>
                <input type="text" name="team_leader" class="form-control"
                       value="<?php echo isset($projectData['team_leader']) ? $projectData['team_leader'] : ''; ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">المشرف</label>
                <input type="text" name="supervisor" class="form-control"
                       value="<?php echo isset($projectData['supervisor']) ? $projectData['supervisor'] : ''; ?>">
            </div>
            <button type="submit" class="btn btn-primary w-100">حفظ التعديلات</button>
        </form>
    </div>
</body>
</html>

