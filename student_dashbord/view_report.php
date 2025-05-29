<?php
session_start();
include('conn.php');
include('Project.php');

// تأكد أن المستخدم مشرف
if (!isset($_SESSION['username'], $_SESSION['role']) || $_SESSION['role'] !== 'Supervisor') {
    header('Location: login.php');
    exit();
}

$supervisorUsername = $_SESSION['username'];
$projectObj = new Project($conn);
$projects = [];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['show_projects'])) {
    $projects = $projectObj->searchProjects("", $supervisorUsername); // جلب كل المشاريع لهذا المشرف
}
?>
<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>مشاريعي - المشرف</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f1f4f8;
            font-family: 'Segoe UI', sans-serif;
        }
        .navbar {
            background-color: #1f2d3d;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        .navbar .username {
            color: #fff;
            font-weight: bold;
        }
        .sidebar {
            width: 240px;
            height: 100vh;
            background: #2c3e50;
            position: fixed;
            top: 0;
            left: 0;
            padding-top: 70px;
            box-shadow: 2px 0 8px rgba(0,0,0,0.1);
        }
        .sidebar h5 {
            color: #ecf0f1;
            text-align: center;
            margin-bottom: 20px;
        }
        .sidebar button {
            width: 80%;
            margin: 0 auto;
            display: block;
        }
        .main {
            margin-left: 240px;
            padding: 30px;
        }
        .main h3 {
            font-weight: bold;
            color: #2c3e50;
        }
        .table {
            border-radius: 12px;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
        }
        .table thead {
            background: #3498db;
            color: #fff;
        }
        .table td, .table th {
            vertical-align: middle;
        }
        .btn-outline-primary {
            border-radius: 20px;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg fixed-top px-4">
    <div class="container-fluid justify-content-end">
        <span class="username"><?php echo $_SESSION['username']; ?> (مشرف)</span>
    </div>
</nav>

<!-- Sidebar -->
<div class="sidebar">
    <h5>لوحة التحكم</h5>
    <form method="post" class="text-center">
        <button type="submit" name="show_projects" class="btn btn-light">عرض المشاريع</button>
    </form>
</div>

<!-- Main Content -->
<div class="main">
    <h3 class="text-center mb-4">المشاريع الخاصة بك</h3>

    <?php if (!empty($projects)): ?>
        <div class="table-responsive">
            <table class="table table-hover text-center align-middle">
                <thead>
                    <tr>
                        <th>اسم المشروع</th>
                        <th>تاريخ البداية</th>
                        <th>تاريخ النهاية</th>
                        <th>الحالة</th>
                        <th>إجراء</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($projects as $project): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($project['project_name']); ?></td>
                            <td><?php echo htmlspecialchars($project['start_date']); ?></td>
                            <td><?php echo htmlspecialchars($project['end_date']); ?></td>
                            <td>
                                <span class="badge bg-<?php echo $project['status'] === 'مكتمل' ? 'success' : ($project['status'] === 'قيد التنفيذ' ? 'warning' : 'secondary'); ?>">
                                    <?php echo htmlspecialchars($project['status']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="report.php?project_id=<?php echo $project['project_id']; ?>" class="btn btn-outline-primary btn-sm">
                                    <i class="fa-solid fa-file-alt me-1"></i> تقرير
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php elseif ($_SERVER["REQUEST_METHOD"] == "POST"): ?>
        <p class="text-center text-muted">لا توجد مشاريع لعرضها حاليًا.</p>
    <?php endif; ?>
</div>

</body>
</html>
