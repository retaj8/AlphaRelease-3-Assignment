<?php
session_start();
include 'conn.php';
include 'user.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$role = $_SESSION['role'];
$email = $_SESSION['email'];

// إنشاء كائن المستخدم
$userObj = new User($conn);
$user = $userObj->getUserByUsername($username);

// متغيرات لعرض الرسائل
$message = '';
$messageType = '';

// التحقق من إرسال النموذج
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // جلب البيانات من النموذج
    $email = trim($_POST['email']);
    $currentPassword = $_POST['current_password'];
    $newPassword = trim($_POST['new_password']);
    $confirmPassword = trim($_POST['confirm_password']);
    
    // التحقق من الحقول
    $errors = [];
    
    // التحقق من البريد الإلكتروني
    if (empty($email)) {
        $errors[] = "البريد الإلكتروني مطلوب";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "صيغة البريد الإلكتروني غير صحيحة";
    }
    
    // التحقق من كلمة المرور الحالية إذا تم إدخال كلمة مرور جديدة
    if (!empty($newPassword)) {
        if (empty($currentPassword)) {
            $errors[] = "يجب إدخال كلمة المرور الحالية لتغيير كلمة المرور";
        } elseif (!$userObj->verifyPassword($username, $currentPassword)) {
            $errors[] = "كلمة المرور الحالية غير صحيحة";
        }
        
        // التحقق من تطابق كلمة المرور الجديدة وتأكيدها
        if ($newPassword !== $confirmPassword) {
            $errors[] = "كلمة المرور الجديدة وتأكيدها غير متطابقين";
        }
    }
    
    // إذا لم تكن هناك أخطاء، قم بتحديث البيانات
    if (empty($errors)) {
        $updatePassword = !empty($newPassword) ? $newPassword : null;
        $result = $userObj->updateUserProfile($username, $email, $updatePassword);
        
        if (strpos($result, "تم تحديث") !== false) {
            $message = $result;
            $messageType = 'success';
        } else {
            $message = $result;
            $messageType = 'danger';
        }
    } else {
        $message = "يرجى تصحيح الأخطاء التالية:";
        $messageType = 'danger';
    }
}

// تحديد العنوان حسب دور المستخدم
$pageTitle = "الملف الشخصي";
$roleTitle = "";
switch ($role) {
    case "Student":
        $roleTitle = "طالب";
        break;
    case "Supervisor":
        $roleTitle = "مشرف";
        break;
    case "team_leader":
        $roleTitle = "قائد فريق";
        break;
    default:
        $roleTitle = $role;
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> | <?php echo htmlspecialchars($username); ?></title>
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
        
        .user-info {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .user-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background-color: #6c757d;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            margin-left: 20px;
        }
        
        .user-details h4 {
            margin-bottom: 5px;
            color: #333;
        }
        
        .user-role {
            display: inline-block;
            background-color: #5D5CDE;
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .form-label {
            font-weight: 500;
        }
        
        .password-section {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
        }
        
        .password-section h5 {
            margin-bottom: 20px;
            color: #333;
        }
        
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #5D5CDE;
            text-decoration: none;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><i class="fas fa-user-circle me-2"></i> الملف الشخصي</h4>
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
                
                <div class="user-info">
                    <div class="user-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="user-details">
                        <h4><?php echo htmlspecialchars($username); ?></h4>
                        <span class="user-role"><?php echo htmlspecialchars($roleTitle); ?></span>
                    </div>
                </div>
                
                <form method="post" action="" class="needs-validation">
                    <div class="mb-3">
                        <label for="username" class="form-label">اسم المستخدم</label>
                        <input type="text" class="form-control" id="username" value="<?php echo htmlspecialchars($username); ?>" disabled>
                        <div class="form-text">لا يمكن تغيير اسم المستخدم</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">البريد الإلكتروني</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                    </div>
                    
                    <div class="password-section">
                        <h5>تغيير كلمة المرور</h5>
                        <div class="mb-3">
                            <label for="current_password" class="form-label">كلمة المرور الحالية</label>
                            <input type="password" class="form-control" id="current_password" name="current_password">
                            <div class="form-text">أدخل كلمة المرور الحالية فقط إذا كنت ترغب في تغييرها</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="new_password" class="form-label">كلمة المرور الجديدة</label>
                            <input type="password" class="form-control" id="new_password" name="new_password">
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">تأكيد كلمة المرور الجديدة</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> حفظ التغييرات
                    </button>
                </form>
                
                <div class="mt-4">
                    <?php
                    // تحديد الرابط للعودة حسب دور المستخدم
                    $backLink = "";
                    switch ($role) {
                        case "Student":
                            $backLink = "student_dashboard.php";
                            break;
                        case "Supervisor":
                            $backLink = "dashboard_supervisor.php";
                            break;
                        case "team_leader":
                            $backLink = "team_leader_dashboard.php";
                            break;
                        default:
                            $backLink = "index.php";
                    }
                    ?>
                    <a href="<?php echo $backLink; ?>" class="back-link">
                        <i class="fas fa-arrow-right me-1"></i> العودة للوحة التحكم
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>