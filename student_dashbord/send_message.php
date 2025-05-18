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
$messageObj = new Message($conn);

// معالجة إرسال الرسالة
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $receiver = $_POST['receiver'];
    $subject = $_POST['subject'];
    $message_content = $_POST['message_content'];

    // إرسال الرسالة
    if ($messageObj->sendMessage($username, $receiver, $subject, $message_content)) {
        echo json_encode(['status' => 'success', 'message' => 'تم إرسال الرسالة بنجاح!']);
        exit();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'حدث خطأ أثناء إرسال الرسالة.']);
        exit();
    }
}

// جلب قائمة المستخدمين (لإرسال الرسالة)
$query = "SELECT username FROM users WHERE username != :username";
$stmt = $conn->prepare($query);
$stmt->execute(['username' => $username]);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إرسال رسالة جديدة</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            font-family: 'Tajawal', sans-serif;
            background-color: #f8f9fa;
        }
        .container {
            margin-top: 50px;
        }
        .alert {
            display: none; /* إخفاء الإشعارات بشكل افتراضي */
        }
    </style>
</head>
<body>

<div class="container">
    <h1>إرسال رسالة جديدة</h1>
    <div id="alert" class="alert" role="alert"></div>
    <form id="messageForm">
        <div class="mb-3">
            <label for="receiver" class="form-label">إلى:</label>
            <select name="receiver" id="receiver" class="form-select" required>
                <option value="">اختر المستلم</option>
                <?php foreach ($users as $user): ?>
                    <option value="<?php echo htmlspecialchars($user['username']); ?>"><?php echo htmlspecialchars($user['username']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="subject" class="form-label">الموضوع:</label>
            <input type="text" name="subject" id="subject" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="message_content" class="form-label">محتوى الرسالة:</label>
            <textarea name="message_content" id="message_content" class="form-control" rows="5" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">إرسال</button>
        <button type="button" class="btn btn-secondary" id="cancelButton">إلغاء</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        $('#messageForm').on('submit', function(e) {
            e.preventDefault(); // منع إعادة تحميل الصفحة
            $.ajax({
                type: 'POST',
                url: 'send_message.php',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    $('#alert').removeClass('alert-danger').addClass('alert-success').text(response.message).show();
                },
                error: function() {
                    $('#alert').removeClass('alert-success').addClass('alert-danger').text('حدث خطأ أثناء إرسال الرسالة.').show();
                }
            });
        });

        $('#cancelButton').on('click', function() {
            $('#alert').removeClass('alert-success').addClass('alert-danger').text('تم إلغاء إرسال الرسالة.').show();
        });
    });
</script>
</body>
</html>
