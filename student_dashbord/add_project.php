<?php
include 'conn.php';
include 'Project.php';

$project = new Project($conn);
$students = $project->getStudents(); // للحصول على الطلاب
$supervisors = $project->getSupervisors(); // للحصول على المشرفين

$message = ""; // متغير لتخزين الرسائل

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $projectId = trim($_POST['project_id']);
    $projectName = trim($_POST['project_name']);
    $startDate = $_POST['start_date'];
    $endDate = $_POST['end_date'];
    $status = $_POST['status'];
    $teamLeader = $_POST['team_leader'];
    $supervisor = $_POST['supervisor'];
    $teamMembers = isset($_POST['member_name']) ? $_POST['member_name'] : [];
    
    // تحقق إذا كان المشروع موجودًا مسبقًا 
    if ($project->isprojectExists($projectId, $projectName)) {
        $message = "❌ المشروع معرف مسبقًا! الرجاء إدخال مشروع جديد.";
    } else {
        
        $result = $project->addProject([
            'project_id' => $projectId,
            'project_name' => $projectName,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => $status,
            'team_leader' => $teamLeader,
            'supervisor' => $supervisor,
            'student' => implode(', ', $teamMembers) // تحويل مصفوفة الأعضاء إلى نص
        ], $teamMembers);

        $message = $result;  
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>إضافة مشروع جديد</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .btn-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
        .btn-primary:hover {
            background-color: #0b5ed7;
            border-color: #0a58ca;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="card p-4">
            <h2 class="text-center text-primary mb-4">إضافة مشروع جديد</h2>

            <?php if (!empty($message)): ?>
                <div class="alert <?php echo strpos($message, '❌') !== false ? 'alert-danger' : 'alert-success'; ?> text-center"><?php echo $message; ?></div>
            <?php endif; ?>

            <form method="post">
                <div class="mb-3">
                    <label class="form-label">معرف المشروع</label>
                    <input type="text" name="project_id" class="form-control" placeholder="أدخل معرف المشروع" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">اسم المشروع</label>
                    <input type="text" name="project_name" class="form-control" placeholder="أدخل اسم المشروع" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">تاريخ البداية</label>
                    <input type="date" name="start_date" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">تاريخ النهاية</label>
                    <input type="date" name="end_date" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">الحالة</label>
                    <select name="status" class="form-control" required>
                        <option value="Pending">قيد التنفيذ</option>
                        <option value="Completed">مكتمل</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">مدير الفريق</label>
                    <input type="text" name="team_leader" class="form-control" placeholder="أدخل مدير الفريق" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">المشرف</label>
                    <select name="supervisor" class="form-control" required>
                        <option value="">اختر المشرف</option>
                        <?php foreach ($supervisors as $supervisor): ?>
                            <option value="<?php echo $supervisor; ?>"><?php echo $supervisor; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">أعضاء الفريق</label>
                    <div class="form-check">
                        <?php foreach ($students as $student): ?>
                            <input class="form-check-input" type="checkbox" name="member_name[]" value="<?php echo $student; ?>" id="member-<?php echo $student; ?>">
                            <label class="form-check-label" for="member-<?php echo $student; ?>"><?php echo $student; ?></label><br>
                        <?php endforeach; ?>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100">إضافة المشروع</button>
            </form>
        </div>
    </div>
</body>
</html>