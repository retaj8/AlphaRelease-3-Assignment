<?php
session_start();
include 'conn.php';
include 'user.php';
include_once 'role.php';
include_once 'Task.php';
include_once 'Project.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Student') {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$taskObj = new Task($conn);

// جلب المشاريع قيد التنفيذ والمكتملة
function getCompletedProjects($conn, $username) {
    $query = "SELECT p.* FROM projects p 
              JOIN project_members pm ON p.project_id = pm.project_id 
              WHERE pm.member_name = :username AND p.status = 'مكتمل'";
    $stmt = $conn->prepare($query);
    $stmt->execute(['username' => $username]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getOngoingProjects($conn, $username) {
    $query = "SELECT p.* FROM projects p 
              JOIN project_members pm ON p.project_id = pm.project_id 
              WHERE pm.member_name = :username AND (p.status = 'Pending' OR p.status = 'قيد التنفيذ')";
    $stmt = $conn->prepare($query);
    $stmt->execute(['username' => $username]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// جلب المهام القادمة
$upcomingTasks = $taskObj->getUpcomingTasks($username, 5);
$completed_projects = getCompletedProjects($conn, $username);
$ongoing_projects = getOngoingProjects($conn, $username);

?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة الطالب | <?php echo htmlspecialchars($username); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.0/main.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap');
        
        body {
            font-family: 'Tajawal', sans-serif;
            display: flex;
            margin: 0;
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
            flex: 1;
            margin-right: 250px;
            padding: 20px;
        }
        
        .dashboard-header {
            border-bottom: 1px solid #dee2e6;
            margin-bottom: 20px;
            padding-bottom: 10px;
        }
        
        .dashboard-card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            overflow: hidden;
        }
        
        .card-header {
            background-color: #5D5CDE;
            color: white;
            padding: 15px 20px;
            font-weight: bold;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
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
        
        .project-title, .task-title {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .project-info, .task-info {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
        }
        
        .badge-in-progress {
            background-color: #ffc107;
            color: #212529;
        }
        
        .badge-completed {
            background-color: #28a745;
            color: white;
        }
        
        .badge-late {
            background-color: #dc3545;
            color: white;
        }
        
        .action-btn {
            background-color: #5D5CDE;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            text-decoration: none;
            display: inline-block;
            margin-top: 10px;
            font-size: 0.9rem;
        }
        
        .action-btn:hover {
            background-color: #4A49B0;
            color: white;
        }
        
        .no-data-message {
            color: #6c757d;
            font-style: italic;
            text-align: center;
            padding: 20px;
        }
        
        .file-link {
            display: inline-flex;
            align-items: center;
            color: #5D5CDE;
            text-decoration: none;
        }
        
        .file-link i {
            margin-left: 5px;
        }
        
        .file-link:hover {
            text-decoration: underline;
        }
        
        /* تخصيص للتقويم المصغر */
        .mini-calendar {
            height: 300px;
            font-size: 0.9rem;
        }
        
        .fc-daygrid-day-number, .fc-col-header-cell-cushion {
            color: #333;
            text-decoration: none;
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
            <small>طالب</small>
        </div>
        
        <ul>
            <li><a href="student_dashboard.php" class="active"><i class="fas fa-home"></i> الرئيسية</a></li>
            <li><a href="profile.php"><i class="fas fa-user-circle"></i> ملفي الشخصي</a></li>
            <li><a href="add_project.php"><i class="fas fa-plus-circle"></i> إضافة مشروع</a></li>
            <li><a href="submit_task.php"><i class="fas fa-check-circle"></i> تسليم مهمة</a></li>
            <li><a href="calendar.php"><i class="fas fa-calendar-alt"></i> التقويم</a></li>
            <li><a href="pagemessag.php"><i class="fas fa-envelope"></i> المراسلة</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> تسجيل الخروج</a></li>
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
                        <?php if (!empty($upcomingTasks)): ?>
                            <?php foreach ($upcomingTasks as $task): ?>
                                <div class="task-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="task-title"><?php echo htmlspecialchars($task['taskName']); ?></h5>
                                        <?php 
                                            $statusClass = '';
                                            if ($task['status'] === 'مكتملة') {
                                                $statusClass = 'badge-completed';
                                            } elseif (strtotime($task['deadline']) < time()) {
                                                $statusClass = 'badge-late';
                                            } else {
                                                $statusClass = 'badge-in-progress';
                                            }
                                        ?>
                                        <span class="badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($task['status']); ?></span>
                                    </div>
                                    <p class="mb-1">المشروع: <?php echo htmlspecialchars($task['project_name']); ?></p>
                                    <div class="task-info">
                                        <i class="fas fa-calendar-alt me-1"></i> تاريخ التسليم: <?php echo htmlspecialchars($task['deadline']); ?>
                                    </div>
                                    <a href="submit_task.php?id=<?php echo $task['taskID']; ?>" class="action-btn mt-2">
                                        <i class="fas fa-check me-1"></i> تسليم المهمة
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-data-message">
                                <i class="fas fa-info-circle me-1"></i> لا توجد مهام قادمة حالياً
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
                                    <div class="project-title"><?php echo htmlspecialchars($project['project_name']); ?></div>
                                    <div class="project-info">
                                        <span><i class="fas fa-calendar-alt me-1"></i> تاريخ البدء: <?php echo htmlspecialchars($project['start_date']); ?></span> | 
                                        <span><i class="fas fa-calendar-check me-1"></i> تاريخ الانتهاء: <?php echo htmlspecialchars($project['end_date']); ?></span>
                                    </div>
                                    <div class="project-info">
                                        <span><i class="fas fa-user me-1"></i> قائد الفريق: <?php echo htmlspecialchars($project['team_leader']); ?></span> | 
                                        <span><i class="fas fa-user-tie me-1"></i> المشرف: <?php echo htmlspecialchars($project['supervisor']); ?></span>
                                    </div>
                                    <div>
                                        <span class="badge badge-in-progress"><?php echo htmlspecialchars($project['status']); ?></span>
                                    </div>
                                    <a href="project_details.php?id=<?php echo htmlspecialchars($project['project_id']); ?>" class="action-btn">
                                        <i class="fas fa-eye me-1"></i> عرض التفاصيل
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-data-message">
                                <i class="fas fa-info-circle me-1"></i> لا توجد مشاريع قيد التنفيذ حالياً
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <!-- التقويم المصغر -->
                <div class="dashboard-card mb-4">
                    <div class="card-header">
                        <i class="fas fa-calendar-alt me-2"></i> التقويم
                    </div>
                    <div class="card-body">
                        <div id="miniCalendar" class="mini-calendar"></div>
                        <div class="text-center mt-3">
                            <a href="calendar.php" class="btn btn-primary btn-sm">
                                <i class="fas fa-calendar-day me-1"></i> عرض التقويم الكامل
                            </a>
                        </div>
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
                                    <div class="project-title"><?php echo htmlspecialchars($project['project_name']); ?></div>
                                    <div class="project-info">
                                        <i class="fas fa-calendar-check me-1"></i> تاريخ الانتهاء: <?php echo htmlspecialchars($project['end_date']); ?>
                                    </div>
                                    <div>
                                        <span class="badge badge-completed">مكتمل</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-data-message">
                                <i class="fas fa-info-circle me-1"></i> لا توجد مشاريع مكتملة حالياً
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.0/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.0/locales/ar.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('miniCalendar');
            
            // إنشاء التقويم المصغر
            const calendar = new FullCalendar.Calendar(calendarEl, {
                locale: 'ar',
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next',
                    center: 'title',
                    right: ''
                },
                height: 'auto',
                // يمكن إضافة الأحداث من AJAX
                eventClick: function(info) {
                    window.location.href = 'submit_task.php?id=' + info.event.id;
                }
            });
            
            calendar.render();
            
            // جلب الأحداث من AJAX (المهام)
            fetch('get_calendar_events.php')
                .then(response => response.json())
                .then(data => {
                    data.forEach(event => {
                        calendar.addEvent(event);
                    });
                })
                .catch(error => console.error('Error loading events:', error));
        });
    </script>
</body>
</html>