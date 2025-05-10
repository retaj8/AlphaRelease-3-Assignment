<?php
include_once("conn.php");

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
        background: linear-gradient(135deg, #1e1e2f, #2c3e50);
        margin: 0;
        padding: 0;
        display: flex;
        direction: rtl;
    }

    .sidebar {
        width: 220px;
        background-color: #1a1d2e;
        height: 100vh;
        padding-top: 40px;
        box-shadow: -2px 0 5px rgba(0, 0, 0, 0.2);
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
    }

    form label {
        display: block;
        margin-top: 18px;
        color: #333;
        font-size: 1.1rem;
    }

    input[type="text"], input[type="date"], input[type="file"] {
        width: 100%;
        padding: 12px;
        margin-top: 6px;
        border-radius: 6px;
        border: 1px solid #ccc;
        font-size: 1rem;
        background-color: #f9f9f9;
        transition: background 0.3s ease;
    }

    input[type="text"]:focus, input[type="date"]:focus, input[type="file"]:focus {
        background-color: #eaf1f4;
        border-color: #1abc9c;
    }

    input[type="submit"] {
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
    }

    input[type="submit"]:hover {
        background-color: #0056b3;
    }

    input[type="file"] {
        font-size: 1rem;
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
