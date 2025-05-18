<?php
session_start();
include 'conn.php';
include 'Task.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Student') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'غير مصرح']);
    exit();
}

$username = $_SESSION['username'];

try {
    $taskObj = new Task($conn);
    
    // جلب مهام التقويم
    $events = $taskObj->getCalendarTasks($username);
    
    // إرجاع البيانات بتنسيق JSON
    header('Content-Type: application/json');
    echo json_encode($events);
} catch (Exception $e) {
    // إرجاع خطأ بتنسيق JSON
    header('Content-Type: application/json');
    echo json_encode(['error' => 'حدث خطأ أثناء جلب الأحداث: ' . $e->getMessage()]);
}
?>