<?php
include_once("conn.php");

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
        font-family: 'Segoe UI', sans-serif; 
        background: linear-gradient(135deg, #e6f7ff, #f4f4f9); 
        padding: 0; 
        margin: 0;
        direction: rtl; 
    }

    .sidebar {
        width: 240px;
        background-color: #2c3e50;
        color: white;
        height: 100vh;
        position: fixed;
        top: 0;
        right: 0;
        padding-top: 40px;
        padding-left: 15px;
        box-sizing: border-box;
    }

    .sidebar h2 {
        text-align: center;
        margin-bottom: 30px;
        font-size: 1.8rem;
    }

    .sidebar a {
        display: block;
        padding: 15px 20px;
        color: white;
        text-decoration: none;
        margin-bottom: 15px;
        background-color: #34495e;
        border-radius: 8px;
        text-align: center;
        font-size: 1.1rem;
        transition: background-color 0.3s ease;
    }

    .sidebar a:hover {
        background-color: #1abc9c;
    }

    .content {
        margin-right: 270px;
        padding: 40px;
        background-color: #ffffff;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        border-radius: 12px;
    }

    h2 {
        text-align: center;
        color: #333;
        font-size: 1.8rem;
        margin-bottom: 30px;
    }

    table { 
        width: 80%; 
        margin: auto; 
        border-collapse: collapse; 
        background: #fff; 
        border-radius: 8px;
        overflow: hidden;
    }

    th, td { 
        padding: 14px; 
        border: 1px solid #ccc; 
        text-align: center; 
        font-size: 1.1rem;
    }

    th { 
        background-color: #1abc9c; 
        color: white; 
        font-size: 1.2rem;
    }

    td {
        background-color: #f9f9f9;
    }

    a { 
        color: #007bff; 
        text-decoration: none; 
        font-weight: bold;
    }

    a:hover {
        color: #0056b3;
        text-decoration: underline;
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
