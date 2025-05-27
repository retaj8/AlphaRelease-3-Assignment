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
$dashboard = new StudentDashboardFacade($conn, $username);

// تعليم جميع الإشعارات كمقروءة إذا تم طلب ذلك
if (isset($_GET['mark_read']) && $_GET['mark_read'] == 'all') {
    $dashboard->markAllNotificationsAsRead();
    header("Location: notifications.php");
    exit();
}

// جلب الإشعارات
try {
    $query = "SELECT * FROM notifications 
              WHERE recipient = :username 
              ORDER BY created_at DESC";
    $stmt = $conn->prepare($query);
    $stmt->execute(['username' => $username]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // حساب عدد الإشعارات غير المقروءة
    $query = "SELECT COUNT(*) FROM notifications 
              WHERE recipient = :username AND is_read = 0";
    $stmt = $conn->prepare($query);
    $stmt->execute(['username' => $username]);
    $unreadCount = $stmt->fetchColumn();
} catch (PDOException $e) {
    $notifications = [];
    $unreadCount = 0;
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الإشعارات | <?php echo htmlspecialchars($username); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap');
        
        body {
            font-family: 'Tajawal', sans-serif;
            background-color: #f8f9fa;
            direction: rtl;
        }
        
        .container {
            max-width: 800px;
            margin: 50px auto;
        }
        
        .card {
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .card-header {
            background-color: #5D5CDE;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .notification-item {
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            transition: background-color 0.2s;
            position: relative;
        }
        
        .notification-item:hover {
            background-color: #f8f9fa;
        }
        
        .notification-item:last-child {
            border-bottom: none;
        }
        
        .notification-item.unread {
            background-color: #f0f7ff;
        }
        
        .notification-item.unread::before {
            content: '';
            position: absolute;
            right: 8px;
            top: 20px;
            width: 8px;
            height: 8px;
            background-color: #5D5CDE;
            border-radius: 50%;
        }
        
        .notification-title {
            font-weight: bold;
            margin-bottom: 8px;
            padding-right: 15px;
        }
        
        .notification-message {
            color: #333;
            margin-bottom: 10px;
        }
        
        .notification-time {
            color: #6c757d;
            font-size: 0.85rem;
            text-align: left;
        }
        
        .actions-bar {
            padding: 10px 20px;
            background-color: #f8f9fa;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid #eee;
        }
        
        .back-button {
            color: #5D5CDE;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }
        
        .back-button i {
            margin-left: 8px;
        }
        
        .mark-all-read {
            color: #5D5CDE;
            text-decoration: none;
            font-weight: 500;
            cursor: pointer;
        }
        
        .mark-all-read:hover {
            color: #4A49B0;
            text-decoration: underline;
        }
        
        .no-notifications {
            padding: 50px 20px;
            text-align: center;
            color: #6c757d;
        }
        
        .no-notifications i {
            font-size: 3rem;
            margin-bottom: 15px;
            color: #dee2e6;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">
                    <i class="fas fa-bell me-2"></i> الإشعارات
                    <?php if ($unreadCount > 0): ?>
                        <span class="badge bg-danger rounded-pill ms-2"><?php echo $unreadCount; ?></span>
                    <?php endif; ?>
                </h4>
                <a href="student_dashboard.php" class="text-white">
                    <i class="fas fa-times"></i>
                </a>
            </div>
            
            <?php if (count($notifications) > 0): ?>
                <?php if ($unreadCount > 0): ?>
                    <div class="actions-bar">
                        <span>لديك <?php echo $unreadCount; ?> إشعارات غير مقروءة</span>
                        <a href="?mark_read=all" class="mark-all-read">
                            <i class="fas fa-check-double me-1"></i> تعليم الكل كمقروء
                        </a>
                    </div>
                <?php endif; ?>
                
                <div class="notifications-list">
                    <?php foreach ($notifications as $notification): ?>
                        <div class="notification-item <?php echo $notification['is_read'] ? '' : 'unread'; ?>">
                            <div class="notification-title">
                                <?php echo htmlspecialchars($notification['title']); ?>
                            </div>
                            <div class="notification-message">
                                <?php echo htmlspecialchars($notification['message']); ?>
                            </div>
                            <div class="notification-time">
                                <?php echo date('d/m/Y H:i', strtotime($notification['created_at'])); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-notifications">
                    <i class="fas fa-bell-slash"></i>
                    <h5>لا توجد إشعارات</h5>
                    <p>ستظهر هنا الإشعارات عندما تتلقى تحديثات حول المشاريع والمهام.</p>
                    <a href="student_dashboard.php" class="btn btn-primary mt-3">
                        <i class="fas fa-arrow-right me-1"></i> العودة للوحة التحكم
                    </a>
                </div>
            <?php endif; ?>
            
            <?php if (count($notifications) > 0): ?>
                <div class="actions-bar">
                    <a href="student_dashboard.php" class="back-button">
                        <i class="fas fa-arrow-right"></i> العودة للوحة التحكم
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>