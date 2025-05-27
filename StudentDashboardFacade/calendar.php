<?php
session_start();
include 'conn.php';
include 'Task.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Student') {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$taskObj = new Task($conn);

// جلب مهام التقويم
$tasks = $taskObj->getCalendarTasks($username);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تقويم المهام</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.0/main.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap');
        
        body {
            font-family: 'Tajawal', sans-serif;
            background-color: #f8f9fa;
            direction: rtl;
        }
        
        .container {
            max-width: 1000px;
            margin: 50px auto;
        }
        
        .card {
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .card-header {
            background-color: #5D5CDE;
            color: white;
            font-weight: bold;
            padding: 15px 20px;
        }
        
        /* تخصيص التقويم */
        .fc {
            direction: rtl;
            text-align: right;
        }
        
        .fc-header-toolbar {
            margin-bottom: 1.5em !important;
        }
        
        .fc-button-primary {
            background-color: #5D5CDE !important;
            border-color: #5D5CDE !important;
        }
        
        .fc-button-primary:hover {
            background-color: #4A49B0 !important;
            border-color: #4A49B0 !important;
        }
        
        .fc-daygrid-day-number, .fc-col-header-cell-cushion {
            color: #333;
            text-decoration: none;
        }
        
        .fc-event {
            cursor: pointer;
            border-radius: 4px;
            padding: 2px 4px;
        }
        
        /* تخصيص النوافذ المنبثقة */
        .task-popup {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            padding: 15px;
            position: absolute;
            width: 300px;
            z-index: 1000;
            display: none;
        }
        
        .task-popup-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .task-popup-title {
            font-weight: bold;
            font-size: 1.1rem;
            margin: 0;
        }
        
        .task-popup-close {
            cursor: pointer;
            font-size: 1.2rem;
            color: #6c757d;
        }
        
        .task-popup-close:hover {
            color: #343a40;
        }
        
        .task-popup-body {
            margin-bottom: 10px;
        }
        
        .task-popup-actions {
            text-align: center;
        }
        
        .btn-view-task {
            background-color: #5D5CDE;
            color: white;
            border: none;
            border-radius: 20px;
            padding: 5px 15px;
            font-size: 0.9rem;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-view-task:hover {
            background-color: #4A49B0;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><i class="fas fa-calendar-alt me-2"></i> تقويم المهام</h4>
            </div>
            <div class="card-body">
                <div id="calendar"></div>
                
                <!-- نافذة منبثقة لعرض معلومات المهمة -->
                <div id="taskPopup" class="task-popup">
                    <div class="task-popup-header">
                        <h5 class="task-popup-title" id="popupTitle"></h5>
                        <span class="task-popup-close">&times;</span>
                    </div>
                    <div class="task-popup-body">
                        <div><strong>الموعد النهائي:</strong> <span id="popupDate"></span></div>
                    </div>
                    <div class="task-popup-actions">
                        <a href="#" id="viewTaskLink" class="btn-view-task">
                            <i class="fas fa-eye me-1"></i> عرض المهمة
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-3">
            <a href="student_dashboard.php" class="btn btn-secondary">
                <i class="fas fa-arrow-right me-1"></i> العودة للوحة التحكم
            </a>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.0/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.0/locales/ar.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendar');
            const popup = document.getElementById('taskPopup');
            const popupTitle = document.getElementById('popupTitle');
            const popupDate = document.getElementById('popupDate');
            const viewTaskLink = document.getElementById('viewTaskLink');
            const closePopup = document.querySelector('.task-popup-close');
            
            // إغلاق النافذة المنبثقة عند النقر على زر الإغلاق
            closePopup.addEventListener('click', function() {
                popup.style.display = 'none';
            });
            
            // إغلاق النافذة المنبثقة عند النقر خارجها
            document.addEventListener('click', function(e) {
                if (!popup.contains(e.target) && e.target.className !== 'fc-event-title' && !e.target.closest('.fc-event')) {
                    popup.style.display = 'none';
                }
            });
            
            // إنشاء التقويم
            const calendar = new FullCalendar.Calendar(calendarEl, {
                locale: 'ar',
                direction: 'rtl',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                initialView: 'dayGridMonth',
                events: <?php echo json_encode($tasks); ?>,
                eventClick: function(info) {
                    // تحديد موقع النافذة المنبثقة
                    const rect = info.el.getBoundingClientRect();
                    popup.style.top = (rect.bottom + window.scrollY) + 'px';
                    popup.style.left = (rect.left + window.scrollX) + 'px';
                    
                    // تعبئة المعلومات
                    popupTitle.textContent = info.event.title;
                    popupDate.textContent = new Date(info.event.start).toLocaleDateString('ar-SA');
                    viewTaskLink.href = 'submit_task.php?id=' + info.event.id;
                    
                    // إظهار النافذة المنبثقة
                    popup.style.display = 'block';
                }
            });
            
            calendar.render();
        });
    </script>
</body>
</html>