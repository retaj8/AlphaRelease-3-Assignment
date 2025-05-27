<?php
include 'conn.php'; // الاتصال بقاعدة البيانات
include 'user.php'; // الكلاس الخاص بإدارة المستخدمين

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // استلام البيانات من الفورم
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $role = $_POST['role'];

    // التحقق من صحة الإدخالات
    if (empty($username) || empty($email) || empty($password) || empty($role)) {
        echo "يرجى ملء جميع الحقول.";
        exit();
    }

    try {
        // إنشاء كائن المستخدم
        $user = new User($conn);

        // محاولة تسجيل المستخدم
        $result = $user->register($username, $email, $password, $role);

        // عرض الرسالة النهائية بناءً على النتيجة
        if (strpos($result, 'تم تسجيل المستخدم بنجاح') !== false) {
            $roleObj = new Role($conn, $_SESSION['username']);

            // توجيه المستخدم إلى الصفحة بناءً على دوره
            $roleObj->locationUser();
            echo "<script>alert('تم التسجيل بنجاح!'); window.location.href = 'login.html';</script>";
        } else {
            echo "<script>alert('$result'); window.history.back();</script>";
        }
    } catch (Exception $e) {
        echo "خطأ: " . $e->getMessage();
    }
}
?>



<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل مستخدم جديد</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light d-flex justify-content-center align-items-center vh-100">

    <div class="card p-4 shadow-lg" style="width: 400px;">
        <h2 class="text-center mb-4">تسجيل مستخدم جديد</h2>
        <form action="register.php" method="POST">
            <div class="mb-3">
                <label for="username" class="form-label">اسم المستخدم</label>
                <input type="text" id="username" name="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">البريد الإلكتروني</label>
                <input type="email" id="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">كلمة المرور</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="role" class="form-label">الدور</label>
                <select id="role" name="role" class="form-select" required>
                    <option value="Student">طالب</option>
                    <option value="Supervisor">مشرف</option>
                    <option value="Admin">مدير</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary w-100">تسجيل</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>