<?php
include_once("conn_cmt.php");

$project_id = $_GET['project_id'] ?? null;

if (!$project_id) {
    die("رقم المشروع غير موجود.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $taskName = $_POST['taskName'];
        $deadline = $_POST['deadline'];
        $fileName = null;

        // رفع الملف
        if (isset($_FILES['taskFile']) && $_FILES['taskFile']['error'] == 0) {
            $uploadDir = 'uploads/';
            $fileName = basename($_FILES['taskFile']['name']);
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['taskFile']['tmp_name'], $targetPath)) {
                $stmt = $conn->prepare("INSERT INTO files (file_name) VALUES (?)");
                $stmt->execute([$fileName]);
                $file_id = $conn->lastInsertId();
            } else {
                $file_id = null;
            }
        } else {
            $file_id = null;
        }

        // إدخال بيانات المهمة
        $stmt = $conn->prepare("INSERT INTO task (taskName, deadline, status, project_id, file_id) VALUES (?, ?, 'غير منجزة', ?, ?)");
        $stmt->execute([$taskName, $deadline, $project_id, $file_id]);

        // توجيه إلى صفحة المهام
        header("Location: taskManeger.php?project_id=" . urlencode($project_id));
        exit();
    } catch (PDOException $e) {
        die("حدث خطأ أثناء إضافة المهمة: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>إضافة مهمة</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f4f4;
            padding: 0;
            margin: 0;
            display: flex;
            direction: rtl;
        }

        .sidebar {
            width: 200px;
            background-color: #2c3e50;
            height: 100vh;
            padding-top: 40px;
            box-shadow: -2px 0 5px rgba(0, 0, 0, 0.1);
        }

        .sidebar h2 {
            color: #fff;
            text-align: center;
            margin-bottom: 30px;
        }

        .sidebar a {
            display: block;
            background-color: #34495e;
            color: #ecf0f1;
            text-decoration: none;
            padding: 12px 20px;
            margin: 10px;
            border-radius: 6px;
            text-align: center;
            transition: background 0.3s ease;
        }

        .sidebar a:hover {
            background-color: #3c6382;
        }

        .main-content {
            flex: 1;
            padding: 40px;
        }

        .container {
            max-width: 600px;
            background-color: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin: auto;
        }

        h1 {
            text-align: center;
            color: #444;
        }

        form label {
            display: block;
            margin-top: 15px;
            color: #333;
        }

        input[type="text"], input[type="date"], input[type="file"] {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }

        input[type="submit"] {
            margin-top: 20px;
            background-color: #007BFF;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            width: 100%;
            cursor: pointer;
            font-size: 16px;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>لوحة التحكم</h2>
    <a href="dashboard.php">الرئيسية</a>
    <a href="all_project.php">المشاريع</a>
    
    <a href="#">التقارير</a>
    <a href="#">الإعدادات</a>
</div>

<div class="main-content">
    <div class="container">
        <h1>إضافة مهمة جديدة</h1>
        <form method="POST" enctype="multipart/form-data">
            <label>اسم المهمة:</label>
            <input type="text" name="taskName" required>

            <label>تاريخ التسليم:</label>
            <input type="date" name="deadline" required>

            <label>ملف المهمة:</label>
            <input type="file" name="taskFile" required>

            <input type="submit" value="إضافة">
        </form>
    </div>
</div>

</body>
</html>
