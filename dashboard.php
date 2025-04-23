<?php
include ('conn_cmt.php');
include ('Project.php');

$project = new Project($conn);
$message = "";
// عند البحث عن مشروع
$projects = [];
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['search'])) {
    $searchQuery = trim($_POST['search_query']);
    $projects = $project->searchProjects($searchQuery);
}
?>
<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>لوحة التحكم</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f9f9f9;
            font-family: 'Arial', sans-serif;
        }
        .navbar {
            background-color: #34495e;
        }
        .navbar-brand {
            color: white;
            font-weight: bold;
        }
        .table {
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .btn-primary {
            background-color: #007bff;
            border: none;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .btn-danger:hover {
            background-color: #b30000;
        }
    </style>
</head>
<body>
    <!-- شريط التنقل -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="#">لوحة التحكم</a>
        </div>
    </nav>

    <!-- محتوى الصفحة -->
    <div class="container mt-5">
        <h2 class="text-center text-primary mb-4">إدارة المشاريع</h2>
        
        <!-- نموذج البحث -->
        <form method="post" class="mb-4">
            <div class="input-group">
                <input type="text" name="search_query" class="form-control" placeholder="بحث عن المشروع..." required>
                <button type="submit" name="search" class="btn btn-primary">بحث</button>
            </div>
        </form>

        <!-- جدول المشاريع -->
        <?php if (!empty($projects)): ?>
            <table class="table table-hover text-center">
                <thead class="table-primary">
                    <tr>
                        <th>معرف المشروع</th>
                        <th>اسم المشروع</th>
                        <th>الحالة</th>
                        <th>تاريخ البداية</th>
                        <th>تاريخ النهاية</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($projects as $project): ?>
                        <tr>
                            <td><?php echo $project['project_id']; ?></td>
                            <td><?php echo $project['project_name']; ?></td>
                            <td><?php echo $project['status']; ?></td>
                            <td><?php echo $project['start_date']; ?></td>
                            <td><?php echo $project['end_date']; ?></td>
                            <td><a href="edit_project.php?id=<?php echo $project['project_id']; ?>" class="btn btn-warning btn-sm"><i class="fa-solid fa-edit"></i> تعديل</a>
                                <form method="post" action="delete_project.php" style="display:inline-block;">
                                    <input type="hidden" name="project_id" value="<?php echo $project['project_id']; ?>">
                                    <button type="submit" name="delete" class="btn btn-danger btn-sm" onclick="return confirm('هل أنت متأكد من الحذف؟');"><i class="fa-solid fa-trash"></i> حذف</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-center text-secondary">لا توجد مشاريع لعرضها.</p>
        <?php endif; ?>
    </div>
</body>
</html>
