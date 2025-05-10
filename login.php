<?php
session_start();
include 'conn.php'; // الاتصال بقاعدة البيانات
include_once 'user.php'; // الكلاس الخاص بإدارة المستخدمين
include_once 'role.php'; // الكلاس الخاص بالدور


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // استلام البيانات من الفورم
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // التحقق من صحة الإدخالات
    if (empty($username) || empty($password)) {
        echo "يرجى ملء جميع الحقول.";
        exit();
    }

    try {
        // إنشاء كائن المستخدم
        $user = new User($conn);

        // محاولة تسجيل الدخول
        $result = $user->login($username, $password);

        // التحقق من النتيجة
        if (strpos($result, 'تم تسجيل الدخول بنجاح') !== false) {
            // إنشاء كائن للدور بناءً على الجلسة الحالية
            $roleObj = new Role( $_SESSION['role']);

            // توجيه المستخدم إلى الصفحة بناءً على دوره
            $roleObj->locationUser();
        } else {
            echo "<script>alert('$result'); window.history.back();</script>";
        }
    } catch (Exception $e) {
        echo "حدث خطأ  " . $e->getMessage();
    }
}
?>


<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light d-flex justify-content-center align-items-center vh-100">

    <div class="card p-4 shadow-lg" style="width: 400px;">
        <h2 class="text-center mb-4">تسجيل الدخول</h2>
        <form action="login.php" method="POST">
            <div class="mb-3">
                <label for="username" class="form-label">اسم المستخدم</label>
                <input type="text" id="username" name="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">كلمة المرور</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">دخول</button>
        </form>
    </div>
 <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
 <p><a href="login.php">ليس لديك حساب !<p>
</body>
</html>