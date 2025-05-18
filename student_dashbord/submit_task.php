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
$message = '';
$messageType = '';
$task = null;
$errors = [];

// إذا تم تحديد رقم المهمة
if (isset($_GET['id'])) {
    try {
        $taskID = $_GET['id'];
        $task = $taskObj->getTaskById($taskID);
        
        // التحقق من أن المهمة موجودة
        if (!$task) {
            throw new Exception("المهمة غير موجودة!");
        }
    } catch (Exception $e) {
        $message = $e->getMessage();
        $messageType = 'danger';
    }
}

// إذا تم إرسال النموذج
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    try {
        $taskID = $_POST['task_id'];
        $notes = trim($_POST['notes']);
        $fileID = null;
        
        // التحقق من أن الحقول ليست فارغة
        if (empty($taskID)) {
            $errors[] = "يرجى تحديد المهمة!";
        }
        
        
        // التحقق من وجود ملف مرفق
        if (!isset($_FILES['task_file']) || $_FILES['task_file']['error'] != 0) {
            $errors[] = "يرجى إرفاق ملف المهمة!";
        }
        
        // إذا لم تكن هناك أخطاء، استمر في عملية تسليم المهمة
        if (empty($errors)) {
            // رفع الملف إذا وُجد
            if (isset($_FILES['task_file']) && $_FILES['task_file']['error'] == 0) {
                $uploadDir = 'uploads/';
                
                // إنشاء مجلد الرفع إذا لم يكن موجودًا
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $fileName = basename($_FILES['task_file']['name']);
                $fileType = $_FILES['task_file']['type'];
                $targetPath = $uploadDir . $fileName;
                
                // نقل الملف إلى المجلد
                if (move_uploaded_file($_FILES['task_file']['tmp_name'], $targetPath)) {
                    // إضافة الملف إلى قاعدة البيانات
                    $fileID = $taskObj->uploadTaskFile($fileName, $fileType);
                    
                    if (!$fileID) {
                        throw new Exception("حدث خطأ أثناء تخزين معلومات الملف!");
                    }
                    
                    // تحديث ملف المهمة
                    if (!$taskObj->updateTaskFile($taskID, $fileID)) {
                        throw new Exception("حدث خطأ أثناء تحديث ملف المهمة!");
                    }
                } else {
                    throw new Exception("حدث خطأ أثناء رفع الملف!");
                }
            }
            
            // تسليم المهمة
            $result = $taskObj->submitTask($taskID, $username, $notes, $fileID);
            
            if ($result) {
                $message = "تم تسليم المهمة بنجاح!";
                $messageType = 'success';
                // إعادة تحميل بيانات المهمة
                $task = $taskObj->getTaskById($taskID);
            } else {
                throw new Exception("حدث خطأ أثناء تسليم المهمة!");
            }
        } else {
            $message = "يرجى تصحيح الأخطاء التالية:";
            $messageType = 'danger';
        }
    } catch (Exception $e) {
        $message = $e->getMessage();
        $messageType = 'danger';
    }
}

// جلب قائمة المهام للاختيار إذا لم يتم تحديد مهمة
if (!$task) {
    try {
        $tasks = $taskObj->getStudentTasks($username);
    } catch (Exception $e) {
        $message = "حدث خطأ أثناء جلب المهام: " . $e->getMessage();
        $messageType = 'danger';
        $tasks = [];
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسليم مهمة</title>
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
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .card-header {
            background-color: #5D5CDE;
            color: white;
            font-weight: bold;
            padding: 15px 20px;
        }
        
        .btn-primary {
            background-color: #5D5CDE;
            border-color: #5D5CDE;
        }
        
        .btn-primary:hover {
            background-color: #4A49B0;
            border-color: #4A49B0;
        }
        
        .task-details {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .task-details h5 {
            margin-bottom: 15px;
            color: #5D5CDE;
        }
        
        .task-info {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 10px;
        }
        
        .task-info-item {
            margin-left: 20px;
            margin-bottom: 10px;
        }
        
        .task-info-label {
            font-weight: bold;
            color: #6c757d;
        }
        
        .badge-status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.9rem;
        }
        
        .badge-success { background-color: #2ecc71; color: white; }
        .badge-warning { background-color: #f1c40f; color: white; }
        .badge-danger { background-color: #e74c3c; color: white; }
        
        .error-text {
            color: #e74c3c;
            font-size: 0.9rem;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><i class="fas fa-check-circle me-2"></i> تسليم مهمة</h4>
            </div>
            <div class="card-body">
                <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $messageType; ?>" role="alert">
                    <?php echo $message; ?>
                    <?php if (!empty($errors)): ?>
                    <ul class="mb-0 mt-2">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <?php if (isset($task) && $task): ?>
                <!-- عرض تفاصيل المهمة المحددة -->
                <div class="task-details">
                    <h5>تفاصيل المهمة</h5>
                    <div class="task-info">
                        <div class="task-info-item">
                            <span class="task-info-label">اسم المهمة:</span>
                            <?php echo htmlspecialchars($task['taskName']); ?>
                        </div>
                        <div class="task-info-item">
                            <span class="task-info-label">المشروع:</span>
                            <?php echo htmlspecialchars($task['project_name']); ?>
                        </div>
                        <div class="task-info-item">
                            <span class="task-info-label">تاريخ التسليم:</span>
                            <?php echo htmlspecialchars($task['deadline']); ?>
                        </div>
                        <div class="task-info-item">
                            <span class="task-info-label">الحالة:</span>
                            <?php 
                                $statusClass = '';
                                if ($task['status'] === 'مكتملة') {
                                    $statusClass = 'badge-success';
                                } elseif (strtotime($task['deadline']) < time()) {
                                    $statusClass = 'badge-danger';
                                } else {
                                    $statusClass = 'badge-warning';
                                }
                            ?>
                            <span class="badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($task['status']); ?></span>
                        </div>
                    </div>
                </div>
                
                <?php if ($task['status'] !== 'مكتملة'): ?>
                <form method="post" enctype="multipart/form-data" onsubmit="return validateForm()">
                    <input type="hidden" name="task_id" value="<?php echo $task['taskID']; ?>">
                    <div class="mb-3">
                        <label for="notes" class="form-label">ملاحظات</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="task_file" class="form-label">ملف المهمة <span class="text-danger">*</span></label>
                        <input class="form-control" type="file" id="task_file" name="task_file" required>
                        <div class="form-text">الرجاء إرفاق ملف المهمة المنجزة.</div>
                        <div id="file-error" class="error-text d-none">يرجى اختيار ملف للتسليم</div>
                    </div>
                    <button type="submit" name="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane me-1"></i> تسليم المهمة
                    </button>
                    <a href="student_dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-right me-1"></i> العودة
                    </a>
                </form>
                <?php else: ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i> تم تسليم هذه المهمة بالفعل!
                </div>
                <div class="mb-3">
                    <?php if (isset($task['file_name']) && !empty($task['file_name'])): ?>
                    <label class="form-label">الملف المرفق:</label>
                    <div>
                        <a href="uploads/<?php echo urlencode($task['file_name']); ?>" class="btn btn-sm btn-outline-primary" download>
                            <i class="fas fa-download me-1"></i> <?php echo htmlspecialchars($task['file_name']); ?>
                        </a>
                    </div>
                    <?php else: ?>
                    <p>لا يوجد ملف مرفق.</p>
                    <?php endif; ?>
                </div>
                <a href="student_dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-right me-1"></i> العودة
                </a>
                <?php endif; ?>
                
                <?php elseif (isset($tasks) && !empty($tasks)): ?>
                <!-- اختيار المهمة من القائمة -->
                <h5 class="mb-3">اختر المهمة للتسليم</h5>
                <div class="list-group">
                    <?php foreach ($tasks as $t): ?>
                    <a href="submit_task.php?id=<?php echo $t['taskID']; ?>" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1"><?php echo htmlspecialchars($t['taskName']); ?></h6>
                            <?php 
                                $statusClass = '';
                                if ($t['status'] === 'مكتملة') {
                                    $statusClass = 'badge-success';
                                } elseif (strtotime($t['deadline']) < time()) {
                                    $statusClass = 'badge-danger';
                                } else {
                                    $statusClass = 'badge-warning';
                                }
                            ?>
                            <span class="badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($t['status']); ?></span>
                        </div>
                        <p class="mb-1">المشروع: <?php echo htmlspecialchars($t['project_name']); ?></p>
                        <small>تاريخ التسليم: <?php echo htmlspecialchars($t['deadline']); ?></small>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i> لا توجد مهام متاحة للتسليم.
                </div>
                <?php endif; ?>
                
                <?php if (!isset($task) || !$task): ?>
                <div class="mt-3">
                    <a href="student_dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-right me-1"></i> العودة للوحة التحكم
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        // التحقق من صحة النموذج قبل الإرسال (للتأكد من جانب العميل)
        function validateForm() {
            let isValid = true;
            const fileInput = document.getElementById('task_file');
            const fileError = document.getElementById('file-error');
            
            // التحقق من وجود ملف
            if (fileInput && fileInput.files.length === 0) {
                fileError.classList.remove('d-none');
                isValid = false;
            } else if (fileError) {
                fileError.classList.add('d-none');
            }
            
            return isValid;
        }
        
        // تحقق تلقائي عند تغيير قيمة حقل الملف
        document.addEventListener('DOMContentLoaded', function() {
            const fileInput = document.getElementById('task_file');
            const fileError = document.getElementById('file-error');
            
            if (fileInput) {
                fileInput.addEventListener('change', function() {
                    if (this.files.length === 0) {
                        fileError.classList.remove('d-none');
                    } else {
                        fileError.classList.add('d-none');
                    }
                });
            }
        });
    </script>
</body>
</html>