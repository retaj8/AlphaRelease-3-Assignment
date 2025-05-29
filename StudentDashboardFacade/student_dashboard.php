<?php
session_start();
include 'conn.php';
include_once 'StudentDashboardFacade.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Student') {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

// استخدام الواجهة الموحدة للوحة التحكم
$dashboard = new StudentDashboardFacade($conn, $username);
$dashboardData = $dashboard->getDashboardData();

// تعليم جميع الإشعارات كمقروءة إذا تم طلب ذلك
if (isset($_GET['mark_read']) && $_GET['mark_read'] == 'all') {
    $dashboard->markAllNotificationsAsRead();
    header("Location: student_dashboard.php");
    exit();
}

// استخراج البيانات
$ongoing_projects = $dashboardData['ongoing_projects'] ?? [];
$completed_projects = $dashboardData['completed_projects'] ?? [];
$upcoming_tasks = $dashboardData['upcoming_tasks'] ?? [];
$unread_notifications = $dashboardData['unread_notifications'] ?? [];
$unread_messages_count = $dashboardData['unread_messages_count'] ?? 0;
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة الطالب | <?php echo htmlspecialchars($username); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap');
        
        body {
            font-family: 'Tajawal', sans-serif;
            background-color: #f8f9fa;
            direction: rtl;
        }
        
        .main-container {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 250px;
            background-color: #343a40;
            color: white;
            padding-top: 20px;
            position: fixed;
            height: 100vh;
            right: 0;
        }
        
        .sidebar-header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 1px solid #495057;
            margin-bottom: 20px;
        }
        
        .sidebar-header .user-pic {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin: 0 auto 10px;
            background-color: #6c757d;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
        }
        
        .sidebar ul {
            list-style: none;
            padding: 0;
        }
        
        .sidebar ul li {
            padding: 0;
        }
        
        .sidebar ul li a {
            color: white;
            text-decoration: none;
            display: block;
            padding: 12px 20px;
            transition: all 0.3s;
            position: relative;
        }
        
        .sidebar ul li a:hover, .sidebar ul li a.active {
            background-color: #495057;
            color: #fff;
        }
        
        .notification-badge {
            position: absolute;
            top: 10px;
            left: 20px;
            background-color: #dc3545;
            color: white;
            border-radius: 50%;
            padding: 0.25em 0.6em;
            font-size: 0.75rem;
        }
        
        .content {
            flex: 1;
            margin-right: 250px;
            padding: 30px;
        }
        
        .dashboard-header {
            margin-bottom: 30px;
        }
        
        .dashboard-card {
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            overflow: hidden;
        }
        
        .card-header {
            background-color: #5D5CDE;
            color: white;
            padding: 15px 20px;
            font-weight: bold;
        }
        
        .card-body {
            padding: 20px;
        }
        
        .project-item, .task-item {
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .project-item:last-child, .task-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .badge-status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
        }
        
        .action-btn {
            display: inline-block;
            background-color: #5D5CDE;
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            margin-top: 10px;
            transition: background-color 0.3s;
        }
        
        .action-btn:hover {
            background-color: #4A49B0;
            color: white;
        }
        
        .notification-item {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
            transition: background-color 0.2s;
        }
        
        .notification-item:hover {
            background-color: #f8f9fa;
        }
        
        .notification-item:last-child {
            border-bottom: none;
        }
        
        .notification-title {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .notification-time {
            color: #6c757d;
            font-size: 0.85rem;
            text-align: left;
        }
        
        .mark-all-read {
            display: block;
            text-align: center;
            color: #5D5CDE;
            text-decoration: none;
            padding: 10px;
            background-color: #f8f9fa;
            font-weight: 500;
        }
        
        .mark-all-read:hover {
            background-color: #e9ecef;
            color: #4A49B0;
        }
        
        .no-items-message {
            padding: 15px;
            text-align: center;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <!-- الشريط الجانبي -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="user-pic">
                    <i class="fas fa-user"></i>
                </div>
                <h4><?php echo htmlspecialchars($username); ?></h4>
                <small>طالب</small>
            </div>
            
            <ul>
                <li><a href="student_dashboard.php" class="active"><i class="fas fa-home me-2"></i> الرئيسية</a></li>
                <li><a href="submit_task.php"><i class="fas fa-check-circle me-2"></i> تسليم مهمة</a></li>
                <li><a href="add_project.php"><i class="fas fa-check-circle me-2"></i> إضافة مشروع </a></li>
                <li><a href="calendar.php"><i class="fas fa-calendar-alt me-2"></i> التقويم</a></li>
                <li><a href="pagemessag.php">
                    <i class="fas fa-envelope me-2"></i> الرسائل
                    <?php if ($unread_messages_count > 0): ?>
                        <span class="notification-badge"><?php echo $unread_messages_count; ?></span>
                    <?php endif; ?>
                </a></li>
                <li><a href="notifications.php">
                    <i class="fas fa-bell me-2"></i> الإشعارات
                    <?php if (count($unread_notifications) > 0): ?>
                        <span class="notification-badge"><?php echo count($unread_notifications); ?></span>
                    <?php endif; ?>
                </a></li>
                <li><a href="profile.php"><i class="fas fa-user-circle me-2"></i> الملف الشخصي</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> تسجيل الخروج</a></li>
            </ul>
        </aside>

        <!-- المحتوى الرئيسي -->
        <main class="content">
            <div class="dashboard-header">
                <h1>مرحباً، <?php echo htmlspecialchars($username); ?>!</h1>
                <p>هذه هي لوحة التحكم الخاصة بك، يمكنك متابعة مشاريعك ومهامك منها.</p>
            </div>
            
            <div class="row">
                <div class="col-lg-8">
                    <!-- المهام القادمة -->
                    <div class="dashboard-card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-tasks me-2"></i> المهام القادمة</span>
                            <a href="submit_task.php" class="btn btn-sm btn-light">عرض الكل</a>
                        </div>
                        <div class="card-body">
                            <?php if (count($upcoming_tasks) > 0): ?>
                                <?php foreach ($upcoming_tasks as $task): ?>
                                    <div class="task-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h5 class="mb-1"><?php echo htmlspecialchars($task['taskName']); ?></h5>
                                            <?php 
                                                $statusClass = '';
                                                if ($task['status'] === 'مكتملة') {
                                                    $badgeClass = 'bg-success';
                                                } elseif (strtotime($task['deadline']) < time()) {
                                                    $badgeClass = 'bg-danger';
                                                } else {
                                                    $badgeClass = 'bg-warning';
                                                }
                                            ?>
                                            <span class="badge <?php echo $badgeClass; ?> badge-status"><?php echo htmlspecialchars($task['status']); ?></span>
                                        </div>
                                        <p class="mb-1">المشروع: <?php echo htmlspecialchars($task['project_name']); ?></p>
                                        <div class="text-muted">
                                            <i class="fas fa-calendar-alt me-1"></i> تاريخ التسليم: <?php echo htmlspecialchars($task['deadline']); ?>
                                        </div>
                                        <a href="submit_task.php?id=<?php echo $task['taskID']; ?>" class="action-btn">
                                            <i class="fas fa-check me-1"></i> تسليم المهمة
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="no-items-message">
                                    <i class="fas fa-check-circle mb-3 text-success fa-2x"></i>
                                    <p>ليس لديك مهام قادمة حالياً!</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- المشاريع قيد التنفيذ -->
                    <div class="dashboard-card">
                        <div class="card-header">
                            <i class="fas fa-project-diagram me-2"></i> المشاريع قيد التنفيذ
                        </div>
                        <div class="card-body">
                            <?php if (count($ongoing_projects) > 0): ?>
                                <?php foreach ($ongoing_projects as $project): ?>
                                    <div class="project-item">
                                        <h5 class="mb-1"><?php echo htmlspecialchars($project['project_name']); ?></h5>
                                        <div class="d-flex flex-wrap text-muted mb-2">
                                            <div class="me-3">
                                                <i class="fas fa-calendar-alt me-1"></i> 
                                                تاريخ البدء: <?php echo htmlspecialchars($project['start_date']); ?>
                                            </div>
                                            <div class="me-3">
                                                <i class="fas fa-calendar-check me-1"></i> 
                                                تاريخ الانتهاء: <?php echo htmlspecialchars($project['end_date']); ?>
                                            </div>
                                            <div class="me-3">
                                                <i class="fas fa-user-tie me-1"></i> 
                                                المشرف: <?php echo htmlspecialchars($project['supervisor']); ?>
                                            </div>
                                        </div>
                                        <div>
                                            <span class="badge bg-warning badge-status"><?php echo htmlspecialchars($project['status']); ?></span>
                                        </div>
                                        <a href="project_details.php?id=<?php echo $project['project_id']; ?>" class="action-btn">
                                            <i class="fas fa-eye me-1"></i> عرض التفاصيل
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="no-items-message">
                                    <i class="fas fa-info-circle mb-3 text-info fa-2x"></i>
                                    <p>ليس لديك مشاريع قيد التنفيذ حالياً!</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <!-- الإشعارات -->
                    <div class="dashboard-card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-bell me-2"></i> الإشعارات</span>
                            <a href="notifications.php" class="btn btn-sm btn-light">عرض الكل</a>
                        </div>
                        <div class="card-body p-0">
                            <?php if (count($unread_notifications) > 0): ?>
                                <?php foreach ($unread_notifications as $notification): ?>
                                    <div class="notification-item">
                                        <div class="notification-title">
                                            <?php echo htmlspecialchars($notification['title']); ?>
                                        </div>
                                        <div class="notification-message mb-2">
                                            <?php echo htmlspecialchars($notification['message']); ?>
                                        </div>
                                        <div class="notification-time">
                                            <?php echo date('d/m/Y H:i', strtotime($notification['created_at'])); ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                <a href="?mark_read=all" class="mark-all-read">
                                    <i class="fas fa-check-double me-1"></i> تعليم الكل كمقروء
                                </a>
                            <?php else: ?>
                                <div class="no-items-message">
                                    <i class="fas fa-bell-slash mb-3 text-muted fa-2x"></i>
                                    <p>ليس لديك إشعارات جديدة!</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- المشاريع المكتملة -->
                    <div class="dashboard-card">
                        <div class="card-header">
                            <i class="fas fa-check-circle me-2"></i> المشاريع المكتملة
                        </div>
                        <div class="card-body">
                            <?php if (count($completed_projects) > 0): ?>
                                <?php foreach ($completed_projects as $project): ?>
                                    <div class="project-item">
                                        <h5 class="mb-1"><?php echo htmlspecialchars($project['project_name']); ?></h5>
                                        <div class="text-muted mb-2">
                                            <i class="fas fa-calendar-check me-1"></i> تاريخ الانتهاء: <?php echo htmlspecialchars($project['end_date']); ?>
                                        </div>
                                        <div>
                                            <span class="badge bg-success badge-status">مكتمل</span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="no-items-message">
                                    <i class="fas fa-clipboard-check mb-3 text-muted fa-2x"></i>
                                    <p>ليس لديك مشاريع مكتملة حالياً!</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>