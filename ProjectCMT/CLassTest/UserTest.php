<?php
use PHPUnit\Framework\TestCase;
use ProjectCMT\Class\User;
use ProjectCMT\Class\Role;
/**
 * اختبارات كلاس User
 * الهدف: التحقق من صحة جميع عمليات إدارة المستخدمين
 */
class UserTest extends TestCase
{
    private $pdo;
    private $user;

    /**
     * إعداد البيانات قبل كل اختبار
     */
    protected function setUp(): void
    {
        $this->pdo = createTestDatabase();
        seedTestData($this->pdo);
        $this->user = new User($this->pdo);

        // بدء جلسة للاختبارات
        if (session_status() === PHP_SESSION_NONE) {
           // session_start();
        }
    }

    /**
     * اختبار 1: تسجيل مستخدم جديد بنجاح
     */
    public function testSuccessfulUserRegistration()
    {
        $username = 'newuser';
        $email = 'newuser@example.com';
        $password = 'password123';
        $role = 'Student';

        $result = $this->user->register($username, $email, $password, $role);

        $this->assertEquals('تم تسجيل المستخدم بنجاح!', $result);

        // التحقق من إدراج البيانات
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $userData = $stmt->fetch(\PDO::FETCH_ASSOC);

        $this->assertNotFalse($userData);
        $this->assertEquals($username, $userData['username']);
        $this->assertEquals($email, $userData['email']);
        $this->assertTrue(password_verify($password, $userData['password']));
    }

    /**
     * اختبار 2: منع تسجيل مستخدم مكرر
     */
    public function testDuplicateUserRegistration()
    {
        $result = $this->user->register('student1', 'newemail@example.com', 'password123', 'Student');
        $this->assertEquals('!إسم المستخدم موجود بالفعل', $result);
    }

    /**
     * اختبار 3: التحقق من كلمة مرور ضعيفة (قصيرة)
     */
    public function testWeakPasswordTooShort()
    {
        $result = $this->user->register('testuser', 'test@example.com', 'short', 'Student');
        $this->assertStringContainsString('يجب أن تكون كلمة المرور 8 أحرف على الأقل', $result);
    }

    /**
     * اختبار 4: التحقق من كلمة مرور بدون أرقام
     */
    public function testWeakPasswordNoNumbers()
    {
        $result = $this->user->register('testuser', 'test@example.com', 'password', 'Student');
        $this->assertStringContainsString('تحتوي على رقم واحد على الأقل', $result);
    }

    /**
     * اختبار 5: تسجيل دخول ناجح
     */
    public function testSuccessfulLogin()
    {
        $result = $this->user->login('student1', 'password123');
        $this->assertStringContainsString('تم تسجيل الدخول بنجاح', $result);
        $this->assertEquals('student1', $_SESSION['username']);
        $this->assertEquals('student', $_SESSION['role']);
    }

    /**
     * اختبار 6: فشل تسجيل الدخول - بيانات خاطئة
     */
    public function testFailedLogin()
    {
        $result = $this->user->login('nonexistent', 'wrongpassword');
        $this->assertEquals('اسم المستخدم أو كلمة المرور غير صحيحة.', $result);
    }

    /**
     * اختبار 7: جلب بيانات المستخدم
     */
    public function testGetUserByUsername()
    {
        $userData = $this->user->getUserByUsername('student1');
        $this->assertIsArray($userData);
        $this->assertEquals('student1', $userData['username']);
        $this->assertEquals('student1@test.com', $userData['email']);
        $this->assertEquals('student', $userData['role']);
    }

    /**
     * اختبار 8: التحقق من كلمة المرور
     */
    public function testVerifyPassword()
    {
        $this->assertTrue($this->user->verifyPassword('student1', 'password123'));
        $this->assertFalse($this->user->verifyPassword('student1', 'wrongpassword'));
    }

    /**
     * اختبار 9: تحديث الملف الشخصي
     */
    public function testUpdateUserProfile()
    {
        $result = $this->user->updateUserProfile('student1', 'newemail@example.com', 'newpassword123');
        $this->assertEquals('تم تحديث البيانات بنجاح!', $result);

        // التحقق من التحديث
        $userData = $this->user->getUserByUsername('student1');
        $this->assertEquals('newemail@example.com', $userData['email']);
        $this->assertTrue($this->user->verifyPassword('student1', 'newpassword123'));
    }

    /**
     * اختبار 10: محاولة تسجيل مستخدم مع اسم مستخدم فارغ
     */
    public function testRegisterWithEmptyUsername()
    {
        $result = $this->user->register('', 'test@example.com', 'password123', 'Student');
        $this->assertStringContainsString('اسم المستخدم مطلوب', $result);
    }

    /**
     * اختبار 11: محاولة تسجيل مستخدم مع بريد إلكتروني فارغ
     */
    public function testRegisterWithEmptyEmail()
    {
        $result = $this->user->register('testuser', '', 'password123', 'Student');
        $this->assertStringContainsString('البريد الإلكتروني مطلوب', $result);
    }

    /**
     * اختبار 12: محاولة تسجيل مستخدم مع كلمة مرور فارغة
     */
    public function testRegisterWithEmptyPassword()
    {
        $result = $this->user->register('testuser', 'test@example.com', '', 'Student');
        $this->assertStringContainsString('كلمة المرور مطلوبة', $result);
    }

    /**
     * اختبار 13: محاولة تسجيل الدخول مع اسم مستخدم فارغ
     */
    public function testLoginWithEmptyUsername()
    {
        $result = $this->user->login('', 'password123');
        $this->assertStringContainsString('اسم المستخدم مطلوب', $result);
    }

    /**
     * اختبار 14: محاولة تسجيل الدخول مع كلمة مرور فارغة
     */
    public function testLoginWithEmptyPassword()
    {
        $result = $this->user->login('student1', '');
        $this->assertStringContainsString('كلمة المرور مطلوبة', $result);
    }

    /**
     * تنظيف البيانات بعد كل اختبار
     */
    protected function tearDown(): void
    {
        $this->pdo = null;
        $this->user = null;
      //  $_SESSION = [];
    }
}
