<?php
include_once("conn_cmt.php");

class FileManager {
    private $conn;

    public function __construct($connection) {
        $this->conn = $connection;
    }

    public function getFilesByProjectId($project_id) {
        try {
            $stmt = $this->conn->prepare("SELECT t.taskName, f.file_name 
                                         FROM task t 
                                         JOIN files f ON t.file_id = f.file_id 
                                         WHERE t.project_id = :project_id");
            $stmt->bindParam(':project_id', $project_id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("خطأ في جلب الملفات: " . $e->getMessage());
        }
    }
}

$project_id = $_GET['project_id'] ?? null;
if (!$project_id) {
    die("رقم المشروع غير محدد.");
}

$fileManager = new FileManager($conn);
$files = $fileManager->getFilesByProjectId($project_id);
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>عرض ملفات المشروع</title>
    <style>
        body { 
            font-family: Arial; 
            background: #f4f4f4; 
            padding: 0; 
            margin: 0;
            direction: rtl; 
        }

        .sidebar {
            width: 250px;
            background-color: #2c3e50;
            color: white;
            height: 100vh;
            position: fixed;
            top: 0;
            right: 0;
            padding: 20px;
            box-sizing: border-box;
        }

        .sidebar h2 {
            text-align: center;
            margin-bottom: 30px;
        }

        .sidebar a {
            display: block;
            padding: 10px;
            color: white;
            text-decoration: none;
            margin-bottom: 10px;
            background-color: #34495e;
            border-radius: 5px;
            text-align: center;
        }

        .sidebar a:hover {
            background-color: #1abc9c;
        }

        .content {
            margin-right: 270px;
            padding: 20px;
        }

        table { 
            width: 60%; 
            margin: auto; 
            border-collapse: collapse; 
            background: #fff; 
        }

        th, td { 
            padding: 12px; 
            border: 1px solid #ccc; 
            text-align: center; 
        }

        th { 
            background-color: rgb(0, 179, 255); 
            color: white; 
        }

        a { 
            color: green; 
            text-decoration: none; 
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

    <div class="content">
        <h2 style="text-align:center;">ملفات المشروع رقم (<?= htmlspecialchars($project_id) ?>)</h2>
        <table>
            <tr>
                <th>اسم المهمة</th>
                <th>اسم الملف</th>
                <th>تحميل</th>
            </tr>
            <?php foreach ($files as $file): ?>
            <tr>
                <td><?= htmlspecialchars($file['taskName']) ?></td>
                <td><?= htmlspecialchars($file['file_name']) ?></td>
                <td><a href="uploads/<?= urlencode($file['file_name']) ?>" download>تحميل</a></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>
