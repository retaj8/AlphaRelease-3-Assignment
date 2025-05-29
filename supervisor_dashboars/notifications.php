<?php
session_start();
include_once("conn.php");

// التحقق من تسجيل الدخول كمشرف
if (!isset($_SESSION['username'], $_SESSION['role']) || $_SESSION['role'] !== 'Supervisor') {
    header('Location: login.php');
    exit();
}

$username = $_SESSION['username'];

// تحديث حالة الإشعارات لتكون مقروءة
if (isset($_GET['mark_read']) && $_GET['mark_read'] == 'all') {
    try {
        $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE recipient = ?");
        $stmt->execute([$username]);
    } catch (PDOException $e) {
        error_log("خطأ في تحديث حالة الإشعارات: " . $e->getMessage());
    }
}

// جلب الإشعارات
try {
    $stmt = $conn->prepare("SELECT * FROM notifications WHERE recipient = ? ORDER BY created_at DESC");
    $stmt->execute([$username]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // حساب عدد الإشعارات غير المقروءة
    $stmt = $conn->prepare("SELECT COUNT(*) FROM notifications WHERE recipient = ? AND is_read = 0");
    $stmt->execute([$username]);
    $unreadCount = $stmt->fetchColumn();
} catch (PDOException $e) {
    error_log("خطأ في جلب الإشعارات: " . $e->getMessage());
    $notifications = [];
    $unreadCount = 0;
}
?>
<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>الإشعارات</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f1f4f8;
            direction: rtl;
        }
        
        .main-container {
            max-width: 800px;
            margin: 30px auto;
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .header {
            background-color: #1f2d3d;
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header h1 {
            margin: 0;
            font-size: 1.5rem;
        }
        
        .notifications-container {
            padding: 0;
            max-height: 600px;
            overflow-y: auto;
        }
        
        .notification-item {
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            transition: background-color 0.2s;
            position: relative;
        }
        
        .notification-item:hover {
            background-color: #f9f9f9;
        }
        
        .notification-item.unread {
            background-color: #f0f7ff;
        }
        
        .notification-item.unread::before {
            content: '';
            position: absolute;
            top: 20px;
            right: 8px;
            width: 8px;
            height: 8px;
            background-color: #3498db;
            border-radius: 50%;
        }
        
        .notification-title {
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .notification-unread .notification-title {
            color: #3498db;
        }
        
        .notification-message {
            color: #7f8c8d;
            margin-bottom: 8px;
        }
        
        .notification-time {
            color: #95a5a6;
            font-size: 0.85rem;
            text-align: left;
        }
        
        .no-notifications {
            padding: 30px;
            text-align: center;
            color: #7f8c8d;
        }
        
        .actions-bar {
            padding: 15px 20px;
            background-color: #f8f9fa;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .mark-all-read {
            color: #3498db;
            text-decoration: none;
            cursor: pointer;
        }
        
        .mark-all-read:hover {
            text-decoration: underline;
        }
        
        .back-button {
            color: #2c3e50;
            text-decoration: none;
            display: flex;
            align-items: center;
        }
        
        .back-button i {
            margin-left: 8px;
        }
        
        .badge-counter {
            background-color: #e74c3c;
            color: white;
            border-radius: 50%;
            padding: 0.25em 0.6em;
            font-size: 0.75rem;
            margin-right: 8px;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="header">
            <h1>
                <i class="fas fa-bell me-2"></i> الإشعارات
                <?php if ($unreadCount > 0): ?>
                    <span class="badge-counter"><?= $unreadCount ?></span>
                <?php endif; ?>
            </h1>
            <a href="dashboard_supervisor.php" class="back-button">
                <i class="fas fa-arrow-left"></i> العودة للوحة التحكم
            </a>
        </div>
        
        <?php if ($unreadCount > 0): ?>
            <div class="actions-bar">
                <a href="notifications.php?mark_read=all" class="mark-all-read">
                    <i class="fas fa-check-double me-1"></i> تعليم الكل كمقروء
                </a>
                <span class="text-muted"><?= $unreadCount ?> إشعارات غير مقروءة</span>
            </div>
        <?php endif; ?>
        
        <div class="notifications-container">
            <?php if (count($notifications) > 0): ?>
                <?php foreach ($notifications as $notification): ?>
                    <div class="notification-item <?= $notification['is_read'] ? '' : 'unread' ?>">
                        <div class="notification-title">
                            <?= htmlspecialchars($notification['title']) ?>
                        </div>
                        <div class="notification-message">
                            <?= htmlspecialchars($notification['message']) ?>
                        </div>
                        <div class="notification-time">
                            <?= date('d/m/Y H:i', strtotime($notification['created_at'])) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-notifications">
                    <i class="far fa-bell-slash fa-3x mb-3"></i>
                    <p>لا توجد إشعارات لعرضها</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>