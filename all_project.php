<?php
include_once("conn_cmt.php");

class ProjectManager {
    private $conn;

    public function __construct($connection) {
        $this->conn = $connection;
    }

    public function getAllProjects() {
        try {
            $stmt = $this->conn->query("SELECT id, project_name FROM projects");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("خطأ في جلب المشاريع: " . $e->getMessage());
        }
    }
}

$projectManager = new ProjectManager($conn);
$projects = $projectManager->getAllProjects();
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>المشاريع</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            direction: rtl;
            background-color: #f4f4f4;
        }
        .sidebar {
            width: 250px;
            background-color: #2c3e50;
            color: white;
            height: 100vh;
            position: fixed;
            right: 0;
            top: 0;
            padding: 20px;
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
        }
        .sidebar a:hover {
            background-color: #1abc9c;
        }
        .content {
            margin-right: 270px;
            padding: 40px;
        }
        h1 {
            color: #2c3e50;
        }
        ul {
            list-style: none;
            padding: 0;
        }
        li {
            background-color: white;
            margin-bottom: 10px;
            padding: 15px;
            border-radius: 6px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        li a {
            background-color: #2980b9;
            color: white;
            padding: 8px 12px;
            text-decoration: none;
            border-radius: 4px;
        }
        li a:hover {
            background-color: #1a6691;
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
        <h1>المشاريع</h1>
        <ul>
            <?php foreach ($projects as $p): ?>
                <li>
                    <?= htmlspecialchars($p['project_name']) ?>
                    <a href="taskManeger.php?project_id=<?= $p['id'] ?>">عرض المهام</a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</body>
</html>
