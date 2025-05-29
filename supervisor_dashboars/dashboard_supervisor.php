<?php
session_start();
include 'conn.php';
include_once 'Task.php';
include_once 'NotificationSystem.php';


// التحقق من أن المستخدم مشرف
if (!isset($_SESSION['username'], $_SESSION['role']) || $_SESSION['role'] !== 'Supervisor') {
    header('Location: login.php');
    exit();
}

$username = $_SESSION['username'];

try {
    // جلب مشاريع المشرف
    $stmt = $conn->prepare("SELECT * FROM Projects WHERE LOWER(supervisor) = LOWER(:username)");
    $stmt->execute(['username' => $username]);
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // حساب عدد الإشعارات غير المقروءة
    $stmt = $conn->prepare("SELECT COUNT(*) FROM notifications WHERE recipient = ? AND is_read = 0");
    $stmt->execute([$username]);
    $unreadNotificationsCount = $stmt->fetchColumn();
    
    // جلب آخر 5 إشعارات
    $stmt = $conn->prepare("SELECT * FROM notifications WHERE recipient = ? ORDER BY created_at DESC LIMIT 5");
    $stmt->execute([$username]);
    $recentNotifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log($e->getMessage(), 3, "error_log.txt");
    $projects = [];
    $unreadNotificationsCount = 0;
    $recentNotifications = [];
}

// دالة للحصول على المهام الخاصة بمشروع
function getTasksByProject($conn, $project_id) {
    try {
        $taskManager = new Task($conn);
        return $taskManager->getTasksByProject($project_id);
    } catch (Exception $e) {
        error_log($e->getMessage(), 3, "error_log.txt");
        return [];
    }
}

// دالة لتنسيق التاريخ
function formatDate($date) {
    if (!$date) return '';
    
    $timestamp = strtotime($date);
    return date('d/m/Y', $timestamp);
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>لوحة تحكم المشرف</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
    body {
        display: flex;
        direction: rtl;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: #1e1e2f;
        color: #f0f0f0;
        margin: 0;
    }

    .menu {
        width: 240px;
        background: #14142b;
        color: #ffffff;
        padding: 30px 20px;
        height: 100vh;
        box-shadow: 2px 0 8px rgba(0, 0, 0, 0.3);
        position: fixed;
        overflow-y: auto;
    }

    .menu-header {
        text-align: center;
        margin-bottom: 30px;
    }

    .user-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background-color: #6c757d;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 40px;
        margin: 0 auto 15px;
    }

    .menu h3 {
        font-size: 1.4rem;
        margin-bottom: 5px;
        color:rgb(193, 199, 201);
    }
    
    .user-role {
        color: #a0aec0;
        margin-bottom: 20px;
    }

    .menu-section {
        font-size: 0.9rem;
        color: #a0aec0;
        margin-top: 20px;
        margin-bottom: 10px;
        text-transform: uppercase;
    }

    .menu a {
        display: block;
        color: #ffffff;
        margin: 8px 0;
        text-decoration: none;
        font-size: 1rem;
        transition: all 0.3s;
        padding: 10px 15px;
        border-radius: 10px;
        display: flex;
        align-items: center;
    }

    .menu a i {
        margin-left: 10px;
        width: 20px;
        text-align: center;
    }

    .menu a:hover, .menu a.active {
        background-color: #2c2c4a;
        color:rgb(190, 197, 197);
    }
    
    .notification-badge {
        background-color: #e74c3c;
        color: white;
        border-radius: 50%;
        padding: 0.25em 0.6em;
        font-size: 0.75rem;
        margin-right: auto;
    }

    .content {
        flex: 1;
        padding: 40px;
        margin-right: 240px;
    }

    .dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
    }
    
    .welcome-text h2 {
        font-size: 2rem;
        margin-bottom: 10px;
        color:hsl(204, 6.40%, 69.40%);
    }
    
    .welcome-text p {
        color: #a0aec0;
        font-size: 1.1rem;
    }
    
    .date-display {
        background-color: #2c2c4a;
        padding: 15px 20px;
        border-radius: 10px;
        text-align: center;
    }
    
    .today-date {
        font-size: 1.2rem;
        font-weight: bold;
        color:rgb(248, 245, 234);
    }

    .projects-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    
    .projects-header h3 {
        font-size: 1.5rem;
        color:rgb(77, 188, 252);
        margin: 0;
    }
    
    .add-project-btn {
        background-color: #2c2c4a;
        color: white;
        border: none;
        padding: 8px 15px;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s;
        text-decoration: none;
        display: inline-block;
    }
    
    .add-project-btn:hover {
        background-color: #3c3c6a;
        color: white;
    }

    .project-list {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
    }

    .project {
        background: #1c1f33;
        border: 1px solid #3a3a5c;
        border-radius: 20px;
        padding: 20px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        transition: transform 0.2s;
    }

    .project:hover {
        transform: translateY(-5px);
    }

    .project h3 {
        margin-top: 0;
        font-size: 1.4rem;
        margin-bottom: 15px;
    }

    .project-link {
        color: #60a5fa;
        text-decoration: none;
        transition: color 0.3s;
    }

    .project-link:hover {
        color: #3b82f6;
    }
    
    .project-info {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        margin-bottom: 15px;
    }
    
    .project-info-item {
        flex: 0 0 48%;
        margin-bottom: 10px;
    }
    
    .project-info-label {
        color: #a0aec0;
        font-size: 0.9rem;
        margin-bottom: 3px;
    }
    
    .project-info-value {
        font-weight: bold;
    }
    
    .project-progress {
        margin-top: 15px;
    }
    
    .progress-label {
        display: flex;
        justify-content: space-between;
        margin-bottom: 5px;
    }
    
    .progress-percentage {
        font-weight: bold;
        color:rgb(235, 112, 74);
    }
    
    .progress {
        height: 10px;
        background-color: #2c2c4a;
        border-radius: 10px;
        overflow: hidden;
    }
    
    .progress-bar {
        background-color: #60a5fa;
        height: 100%;
    }
    
    .project-actions {
        margin-top: 15px;
        display: flex;
        gap: 10px;
    }
    
    .project-action-btn {
        padding: 8px 15px;
        border-radius: 8px;
        background-color: #2c2c4a;
        color: white;
        text-decoration: none;
        font-size: 0.9rem;
        transition: all 0.3s;
        display: inline-flex;
        align-items: center;
    }
    
    .project-action-btn i {
        margin-left: 5px;
    }
    
    .project-action-btn:hover {
        background-color: #3c3c6a;
        color: white;
    }
    
    .project-action-btn.report {
        background-color: #60a5fa;
    }
    
    .project-action-btn.report:hover {
        background-color: #3b82f6;
    }
    
    .notifications-card {
        background: #1c1f33;
        border: 1px solid #3a3a5c;
        border-radius: 20px;
        padding: 20px;
        margin-top: 30px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
    }
    
    .notifications-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }
    
    .notifications-header h3 {
        font-size: 1.5rem;
        color: #fcd34d;
        margin: 0;
    }
    
    .view-all-link {
        color: #60a5fa;
        text-decoration: none;
    }
    
    .view-all-link:hover {
        text-decoration: underline;
        color: #3b82f6;
    }
    
    .notification-item {
        padding: 10px 15px;
        border-bottom: 1px dashed #3a3a5c;
        position: relative;
    }
    
    .notification-item:last-child {
        border-bottom: none;
    }
    
    .notification-item.unread::before {
        content: '';
        position: absolute;
        top: 15px;
        right: 0;
        width: 8px;
        height: 8px;
        background-color: #60a5fa;
        border-radius: 50%;
    }
    
    .notification-title {
        font-weight: bold;
        color: white;
        margin-bottom: 5px;
    }
    
    .notification-content {
        color: #a0aec0;
        font-size: 0.9rem;
        margin-bottom: 5px;
    }
    
    .notification-time {
        color: #718096;
        font-size: 0.8rem;
        text-align: left;
    }
    
    .no-projects {
        background: #1c1f33;
        border: 1px solid #3a3a5c;
        border-radius: 20px;
        padding: 40px 20px;
        text-align: center;
    }
    
    .no-projects i {
        font-size: 3rem;
        color: #a0aec0;
        margin-bottom: 20px;
    }
    
    .no-projects p {
        font-size: 1.2rem;
        color: #a0aec0;
        margin-bottom: 20px;
    }
</style>

</head>
<body>
    <div class="menu">
        <div class="menu-header">
            <div class="user-avatar">
                <i class="fas fa-user"></i>
            </div>
            <h3><?= htmlspecialchars($username) ?></h3>
            <div class="user-role">مشرف</div>
        </div>
        
        <div class="menu-section">القائمة الرئيسية</div>
        <a href="dashboard_supervisor.php" class="active">
            <i class="fas fa-home"></i> الرئيسية
        </a>
        <a href="search_project.php">
            <i class="fas fa-project-diagram"></i> المشاريع
        </a>
        <a href="view_report.php">
            <i class="fas fa-chart-bar"></i> التقارير
        </a>
         <a href="profile.php">
            <i class="fas fa-chart-bar"></i> الملف الشخصي 
        </a>
        
        <div class="menu-section">المزيد</div>
        </a>
        <a href="pagemessag.php">
            <i class="fas fa-project-diagram"></i> الرسائل
        </a>
        <a href="notifications.php">
            <i class="fas fa-bell"></i> الإشعارات
            <?php if ($unreadNotificationsCount > 0): ?>
                <span class="notification-badge"><?= $unreadNotificationsCount ?></span>
            <?php endif; ?>
        </a>
        <a href="#">
            <i class="fas fa-cog"></i> الإعدادات
        </a>
        <a href="logout.php">
            <i class="fas fa-sign-out-alt"></i> تسجيل الخروج
        </a>
    </div>

    <div class="content">
        <div class="dashboard-header">
            <div class="welcome-text">
                <h2>مرحباً، <?= htmlspecialchars($username) ?>!</h2>
                <p>مرحباً بك في لوحة تحكم المشرف. يمكنك إدارة المشاريع والمهام هنا.</p>
            </div>
            <div class="date-display">
                <div class="today-date">
                    <?= date("Y/m/d") ?>
                </div>
                <div class="day-name">
                    <?= date("l") ?>
                </div>
            </div>
        </div>
        
        <div class="projects-header">
            <h3>مشاريعي</h3>
            <a href="add_project.php" class="add-project-btn">
                <i class="fas fa-plus"></i> إضافة مشروع جديد
            </a>
        </div>
        
        <?php if (empty($projects)): ?>
            <div class="no-projects">
                <i class="fas fa-folder-open"></i>
                <p>لا توجد مشاريع مرتبطة بهذا المشرف.</p>
                <a href="add_project.php" class="add-project-btn">
                    <i class="fas fa-plus"></i> إضافة مشروع جديد
                </a>
            </div>
        <?php else: ?>
            <div class="project-list">
                <?php foreach ($projects as $project): 
                    $tasks = getTasksByProject($conn, $project['project_code']);
                    $totalTasks = count($tasks);
                    $completedTasks = 0;
                    
                    foreach ($tasks as $task) {
                        if ($task['status'] === 'مكتملة') {
                            $completedTasks++;
                        }
                    }
                    
                    $progressPercentage = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;
                ?>
                    <div class="project">
                        <h3>
                            <a class="project-link" href="taskManeger.php?project_id=<?= $project['project_code']; ?>">
                                <?= htmlspecialchars($project['project_name'], ENT_QUOTES, 'UTF-8') ?>
                            </a>
                        </h3>
                        
                        <div class="project-info">
                            <div class="project-info-item">
                                <div class="project-info-label">تاريخ البدء</div>
                                <div class="project-info-value"><?= formatDate($project['start_date']) ?></div>
                            </div>
                            <div class="project-info-item">
                                <div class="project-info-label">تاريخ الانتهاء</div>
                                <div class="project-info-value"><?= formatDate($project['end_date']) ?></div>
                            </div>
                            <div class="project-info-item">
                                <div class="project-info-label">الحالة</div>
                                <div class="project-info-value"><?= htmlspecialchars($project['status']) ?></div>
                            </div>
                            <div class="project-info-item">
                                <div class="project-info-label">عدد المهام</div>
                                <div class="project-info-value"><?= $totalTasks ?></div>
                            </div>
                        </div>
                        
                        <div class="project-progress">
                            <div class="progress-label">
                                <span>التقدم</span>
                                <span class="progress-percentage"><?= $progressPercentage ?>%</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar" style="width: <?= $progressPercentage ?>%"></div>
                            </div>
                        </div>
                        
                        <div class="project-actions">
                            <a href="taskManeger.php?project_id=<?= $project['project_code']; ?>" class="project-action-btn">
                                <i class="fas fa-tasks"></i> المهام
                            </a>
                            <a href="report.php?project_id=<?= $project['project_code']; ?>" class="project-action-btn report">
                                <i class="fas fa-chart-bar"></i> التقرير
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <div class="notifications-card">
            <div class="notifications-header">
                <h3>آخر الإشعارات</h3>
                <a href="notifications.php" class="view-all-link">عرض الكل</a>
            </div>
            
            <?php if (count($recentNotifications) > 0): ?>
                <?php foreach ($recentNotifications as $notification): ?>
                    <div class="notification-item <?= $notification['is_read'] ? '' : 'unread' ?>">
                        <div class="notification-title">
                            <?= htmlspecialchars($notification['title']) ?>
                        </div>
                        <div class="notification-content">
                            <?= htmlspecialchars($notification['message']) ?>
                        </div>
                        <div class="notification-time">
                            <?= date('d/m/Y H:i', strtotime($notification['created_at'])) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="notification-item">
                    <div class="notification-content text-center">
                        لا توجد إشعارات جديدة
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>