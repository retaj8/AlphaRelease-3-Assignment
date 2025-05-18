<?php
session_start();
include 'conn.php'; // الاتصال بقاعدة البيانات
include 'Messag.php'; // كلاس الرسائل

// التحقق من تسجيل الدخول
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$currentTab = isset($_GET['tab']) ? $_GET['tab'] : 'inbox';

$messageObj = new Message($conn);

// حذف الرسالة
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $message_id = $_GET['id'];
    $isInbox = $currentTab == 'inbox';
    $messageObj->deleteMessage($message_id, $username, $isInbox);
    header("Location: messages.php?tab=".$currentTab);
    exit();
}

// جلب الرسائل
$inbox_messages = $messageObj->getInboxMessages($username);
$sent_messages = $messageObj->getSentMessages($username);
$unread_count = $messageObj->getUnreadCount($username);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الرسائل | <?php echo htmlspecialchars($username); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap');
        
        body {
            font-family: 'Tajawal', sans-serif;
            background-color: #f8f9fa;
        }
        
        .sidebar {
            width: 250px;
            background: #343a40;
            color: white;
            height: 100vh;
            position: fixed;
            right: 0;
            top: 0;
            padding-top: 20px;
            z-index: 1000;
            box-shadow: -2px 0 5px rgba(0,0,0,0.2);
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
            padding: 15px 20px;
            transition: all 0.3s;
        }
        
        .sidebar ul li a:hover, .sidebar ul li a.active {
            background-color: #495057;
            color: #fff;
        }
        
        .content {
            margin-right: 250px;
            padding: 20px;
        }
        
        .message-tabs {
            display: flex;
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .message-tab {
            padding: 15px 25px;
            cursor: pointer;
            font-weight: 500;
            color: #495057;
            position: relative;
        }
        
        .message-tab.active {
            background-color: #5D5CDE;
            color: white;
        }
        
        .message-list {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 10px;
            overflow-y: auto;
            max-height: 400px;
        }
        
        .message-item {
            display: flex;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #e9ecef;
            transition: all 0.2s;
            text-decoration: none;
            color: inherit;
        }
        
        .message-item:last-child {
            border-bottom: none;
        }
        
        .message-item:hover {
            background-color: #f8f9fa;
        }
        
        .message-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: #6c757d;
            margin-left: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
        }
        
        .message-content {
            flex: 1;
            margin-left: 15px;
        }
        
        .message-sender {
            font-weight: 500;
            margin-bottom: 5px;
            display: flex;
            justify-content: space-between;
        }
        
        .message-subject {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .message-preview {
            color: #6c757d;
            font-size: 0.9rem;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 500px;
        }
        
        .message-time {
            color: #6c757d;
            font-size: 0.8rem;
            text-align: left;
        }
        
        .message-actions {
            margin-right: 10px;
        }
        
        .message-action-btn {
            background: none;
            border: none;
            color: #6c757d;
            cursor: pointer;
            padding: 5px;
            margin-right: 5px;
            font-size: 14px;
        }
        
        .message-action-btn:hover {
            color: #495057;
        }
        
        .message-action-btn.delete:hover {
            color: #dc3545;
        }
        
        .no-messages {
            text-align: center;
            padding: 50px 0;
            color: #6c757d;
        }
        
        .no-messages i {
            font-size: 48px;
            margin-bottom: 15px;
        }
        
        .no-messages h3 {
            font-weight: 500;
            margin-bottom: 15px;
        }
        
        /* تخصيص للشاشات الصغيرة */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                right: 0;
            }
            
            .content {
                margin-right: 0;
                padding: 15px;
            }
        }
    </style>
</head>
<body>

    <!-- الشريط الجانبي -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="user-pic">
                <i class="fas fa-user"></i>
            </div>
            <h4><?php echo htmlspecialchars($username); ?></h4>
            <small><?php echo $_SESSION['role']; ?></small>
        </div>
        
        <ul>
            <li><a href="student_dashboard.php"><i class="fas fa-home"></i> الرئيسية</a></li>
            <li><a href="profile.php"><i class="fas fa-user-circle"></i> ملفي الشخصي</a></li>
            <li><a href="add_project.php"><i class="fas fa-plus-circle"></i> إضافة مشروع</a></li>
            <li><a href="student_dashboard.php"><i class="fas fa-tasks"></i> عرض المهام</a></li>
            <li><a href="submit_task.php"><i class="fas fa-check-circle"></i> تسليم مهمة</a></li>
            <li><a href="log.php"><i class="fas fa-history"></i> السجل</a></li>
            <li><a href="pagemessag.php" class="active"><i class="fas fa-envelope"></i> المراسلة</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> تسجيل الخروج</a></li>
        </ul>
    </aside>

    <!-- المحتوى الرئيسي -->
    <main class="content">
        <div class="messages-header">
            <h1><i class="fas fa-envelope"></i> الرسائل</h1>
            <a href="send_message.php" class="btn btn-primary">
                <i class="fas fa-pen"></i> رسالة جديدة
            </a>
        </div>
        
        <div class="message-tabs">
            <a href="?tab=inbox" class="message-tab <?php echo $currentTab == 'inbox' ? 'active' : ''; ?>">
                <i class="fas fa-inbox"></i> البريد الوارد
                <?php if ($unread_count > 0): ?>
                    <span class="badge"><?php echo $unread_count; ?></span>
                <?php endif; ?>
            </a>
            <a href="?tab=sent" class="message-tab <?php echo $currentTab == 'sent' ? 'active' : ''; ?>">
                <i class="fas fa-paper-plane"></i> الرسائل المرسلة
            </a>
        </div>
        
        <div class="message-list">
            <?php if ($currentTab == 'inbox'): ?>
                <?php if (count($inbox_messages) > 0): ?>
                    <?php foreach ($inbox_messages as $message): ?>
                        <a href="view_message.php?id=<?php echo $message['message_id']; ?>" class="message-item <?php echo $message['is_read'] ? '' : 'unread';
                                                ?>">
                                                <div class="message-avatar">
                                                    <?php echo mb_substr($message['sender'], 0, 1, 'UTF-8'); ?>
                                                </div>
                                                <div class="message-content">
                                                    <div class="message-sender">
                                                        <div>
                                                            <?php echo htmlspecialchars($message['sender']); ?>
                                                            <span class="badge-role badge-<?php echo strtolower($message['sender_role']); ?>">
                                                                <?php echo htmlspecialchars($message['sender_role']); ?>
                                                            </span>
                                                        </div>
                                                        <div class="message-time">
                                                            <?php echo date('d/m/Y H:i', strtotime($message['send_date'])); ?>
                                                        </div>
                                                    </div>
                                                    <div class="message-subject"><?php echo htmlspecialchars($message['subject']); ?></div>
                                                    <div class="message-preview"><?php echo mb_substr(strip_tags($message['message_content']), 0, 100, 'UTF-8'); ?>...</div>
                                                </div>
                                                <div class="message-actions">
                                                    <a href="?tab=inbox&action=delete&id=<?php echo $message['message_id']; ?>" class="message-action-btn delete" onclick="return confirm('هل أنت متأكد من رغبتك في حذف هذه الرسالة؟');">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </a>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="no-messages">
                                            <i class="fas fa-inbox"></i>
                                            <h3>صندوق الوارد فارغ</h3>
                                            <p>ليس لديك أي رسائل حتى الآن.</p>
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <?php if (count($sent_messages) > 0): ?>
                                        <?php foreach ($sent_messages as $message): ?>
                                            <a href="view_message.php?id=<?php echo $message['message_id']; ?>&sent=1" class="message-item">
                                                <div class="message-avatar">
                                                    <?php echo mb_substr($message['receiver'], 0, 1, 'UTF-8'); ?>
                                                </div>
                                                <div class="message-content">
                                                    <div class="message-sender">
                                                        <div>
                                                            إلى: <?php echo htmlspecialchars($message['receiver']); ?>
                                                            <span class="badge-role badge-<?php echo strtolower($message['receiver_role']); ?>">
                                                                <?php echo htmlspecialchars($message['receiver_role']); ?>
                                                            </span>
                                                        </div>
                                                        <div class="message-time">
                                                            <?php echo date('d/m/Y H:i', strtotime($message['send_date'])); ?>
                                                        </div>
                                                    </div>
                                                    <div class="message-subject"><?php echo htmlspecialchars($message['subject']); ?></div>
                                                    <div class="message-preview"><?php echo mb_substr(strip_tags($message['message_content']), 0, 100, 'UTF-8'); ?>...</div>
                                                </div>
                                                <div class="message-actions">
                                                    <a href="?tab=sent&action=delete&id=<?php echo $message['message_id']; ?>" class="message-action-btn delete" onclick="return confirm('هل أنت متأكد من رغبتك في حذف هذه الرسالة؟');">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </a>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="no-messages">
                                            <i class="fas fa-paper-plane"></i>
                                            <h3>لم ترسل أي رسائل</h3>
                                            <p>لم تقم بإرسال أي رسائل حتى الآن.</p>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </main>
                    
                        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
                    </body>
                    </html>
                    