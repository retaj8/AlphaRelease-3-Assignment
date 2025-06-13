<?php
/**
 * ملف تهيئة الاختبارات
 */
define('PHPUNIT_RUNNING', true);
// تضمين جميع ملفات النظام
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', realpath(__DIR__ . '/../'));
}
require_once __DIR__ . '/../vendor/autoload.php';



/**
 * إنشاء قاعدة بيانات اختبار في الذاكرة
 */
/**
 * إنشاء قاعدة بيانات اختبار محسنة
 * 
 * @return PDO اتصال قاعدة البيانات
 */
function createTestDatabase() {
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // إنشاء جدول المستخدمين مع جميع الحقول المطلوبة
    $pdo->exec("
        CREATE TABLE users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            role VARCHAR(20) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    // إنشاء جدول المشاريع المحسن
    $pdo->exec("
        CREATE TABLE projects (
            project_id INTEGER PRIMARY KEY AUTOINCREMENT,
            project_code VARCHAR(50) UNIQUE NOT NULL,
            project_name VARCHAR(255) NOT NULL,
            description TEXT,
            start_date DATE NOT NULL,
            end_date DATE NOT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'Pending',
            supervisor VARCHAR(100) NOT NULL,
            student VARCHAR(100) NOT NULL,
            team_leader VARCHAR(100) NOT NULL,
            budget DECIMAL(10,2) DEFAULT 0.00,
            progress_percentage INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (supervisor) REFERENCES users(username),
            FOREIGN KEY (student) REFERENCES users(username),
            FOREIGN KEY (team_leader) REFERENCES users(username)
        )
    ");
    
    // إنشاء جدول أعضاء المشروع
    $pdo->exec("
        CREATE TABLE project_members (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            project_id INTEGER NOT NULL,
            member_name VARCHAR(100) NOT NULL,
            role VARCHAR(50) DEFAULT 'Member',
            joined_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (project_id) REFERENCES projects(project_id) ON DELETE CASCADE,
            FOREIGN KEY (member_name) REFERENCES users(username),
            UNIQUE(project_id, member_name)
        )
    ");
    
    // إنشاء جدول المهام المحسن
    $pdo->exec("
        CREATE TABLE tasks (
            taskID INTEGER PRIMARY KEY AUTOINCREMENT,
            taskName VARCHAR(255) NOT NULL,
            description TEXT,
            status VARCHAR(50) DEFAULT 'غير منجزة',
            priority VARCHAR(20) DEFAULT 'Medium',
            deadline DATE NOT NULL,
            project_id INTEGER NOT NULL,
            assigned_to VARCHAR(100),
            created_by VARCHAR(100),
            file_id INTEGER,
            completion_percentage INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (project_id) REFERENCES projects(project_id) ON DELETE CASCADE,
            FOREIGN KEY (assigned_to) REFERENCES users(username),
            FOREIGN KEY (created_by) REFERENCES users(username)
        )
    ");
    
    // إنشاء جدول الأدوار
    $pdo->exec("
        CREATE TABLE roles (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(50) UNIQUE NOT NULL,
            description TEXT NOT NULL,
            permissions TEXT, -- JSON format
            is_active INTEGER DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    // إنشاء جدول الملفات
    $pdo->exec("
        CREATE TABLE files (
            file_id INTEGER PRIMARY KEY AUTOINCREMENT,
            file_name VARCHAR(255) NOT NULL,
            file_type VARCHAR(100) NOT NULL,
            file_size INTEGER DEFAULT 0,
            file_path VARCHAR(500) NOT NULL,
            uploaded_by VARCHAR(100),
            project_id INTEGER,
            task_id INTEGER,
            uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (uploaded_by) REFERENCES users(username),
            FOREIGN KEY (project_id) REFERENCES projects(project_id),
            FOREIGN KEY (task_id) REFERENCES tasks(taskID)
        )
    ");
    
    // إنشاء جدول الإشعارات
    $pdo->exec("
        CREATE TABLE notifications (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            type VARCHAR(50) NOT NULL,
            title VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            recipient VARCHAR(100) NOT NULL,
            sender VARCHAR(100),
            is_read INTEGER DEFAULT 0,
            priority VARCHAR(20) DEFAULT 'Normal',
            action_url VARCHAR(500),
            expires_at DATETIME,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (recipient) REFERENCES users(username),
            FOREIGN KEY (sender) REFERENCES users(username)
        )
    ");
    
    // إنشاء جدول الرسائل
    $pdo->exec("
        CREATE TABLE messages (
            message_id INTEGER PRIMARY KEY AUTOINCREMENT,
            sender VARCHAR(100) NOT NULL,
            receiver VARCHAR(100) NOT NULL,
            subject VARCHAR(255) NOT NULL,
            message_content TEXT NOT NULL,
            priority VARCHAR(20) DEFAULT 'Normal',
            is_read INTEGER DEFAULT 0,
            is_deleted_by_sender INTEGER DEFAULT 0,
            is_deleted_by_receiver INTEGER DEFAULT 0,
            attachment_path VARCHAR(500),
            reply_to INTEGER,
            send_date DATETIME DEFAULT CURRENT_TIMESTAMP,
            read_date DATETIME,
            FOREIGN KEY (sender) REFERENCES users(username),
            FOREIGN KEY (receiver) REFERENCES users(username),
            FOREIGN KEY (reply_to) REFERENCES messages(message_id)
        )
    ");
    
    // إنشاء جدول سجل الأنشطة
    $pdo->exec("
        CREATE TABLE activity_log (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id VARCHAR(100) NOT NULL,
            action VARCHAR(100) NOT NULL,
            entity_type VARCHAR(50) NOT NULL,
            entity_id INTEGER,
            details TEXT,
            ip_address VARCHAR(45),
            user_agent VARCHAR(500),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(username)
        )
    ");
    
    return $pdo;
}

/**
 * إضافة بيانات تجريبية شاملة
 * 
 * @param PDO $pdo اتصال قاعدة البيانات
 */
function seedTestData($pdo) {
    try {
        $pdo->beginTransaction();
        
        // تشفير كلمة المرور للاختبارات
        $hashedPassword = password_hash('password123', PASSWORD_DEFAULT);
        
        // إضافة المستخدمين (مع كلمة المرور هذه المرة!)
        $pdo->exec("
            INSERT INTO users (username, email, password, role) VALUES 
            ('student1', 'student1@test.com', '$hashedPassword', 'student'),
            ('student2', 'student2@test.com', '$hashedPassword', 'student'),
            ('student3', 'student3@test.com', '$hashedPassword', 'student'),
            ('supervisor1', 'supervisor1@test.com', '$hashedPassword', 'supervisor'),
            ('supervisor2', 'supervisor2@test.com', '$hashedPassword', 'supervisor'),
            ('admin1', 'admin1@test.com', '$hashedPassword', 'admin'),
            ('testuser', 'testuser@test.com', '$hashedPassword', 'student')
        ");
        
        // إضافة الأدوار الافتراضية
        $rolesData = [
            ['Admin', 'مدير النظام - صلاحيات كاملة', '["full_access", "user_management", "system_settings"]'],
            ['Supervisor', 'مشرف المشاريع - إدارة المشاريع والطلاب', '["project_management", "student_supervision", "reports"]'],
            ['Student', 'طالب - الوصول للمشاريع المخصصة', '["project_participation", "task_management", "file_upload"]'],
            ['Manager', 'مدير القسم - إدارة المشاريع والموارد', '["project_management", "resource_management", "team_coordination"]']
        ];
        
        $roleStmt = $pdo->prepare("INSERT INTO roles (name, description, permissions) VALUES (?, ?, ?)");
        foreach ($rolesData as $role) {
            $roleStmt->execute($role);
        }
        
        // إضافة مشاريع تجريبية
        $projectsData = [
            [
                'PROJ001', 'نظام إدارة المكتبة الجامعية', 'نظام شامل لإدارة المكتبة',
                '2024-01-15', '2024-06-15', 'In Progress', 'supervisor1', 'student1', 'student1'
            ],
            [
                'PROJ002', 'تطبيق التجارة الإلكترونية', 'متجر إلكتروني متكامل',
                '2024-02-01', '2024-08-01', 'Pending', 'supervisor2', 'student2', 'student2'
            ],
            [
                'PROJ003', 'نظام إدارة المستشفى', 'نظام إدارة المرضى والمواعيد',
                '2024-03-01', '2024-09-01', 'Planning', 'supervisor1', 'student3', 'student3'
            ]
        ];
        
        $projectStmt = $pdo->prepare("
            INSERT INTO projects (project_code, project_name, description, start_date, end_date, status, supervisor, student, team_leader) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        foreach ($projectsData as $project) {
            $projectStmt->execute($project);
        }
        
        // إضافة أعضاء المشاريع
        $membersData = [
            [1, 'student1'], [1, 'student2'],
            [2, 'student2'], [2, 'student3'],
            [3, 'student3'], [3, 'student1']
        ];
        
        $memberStmt = $pdo->prepare("INSERT INTO project_members (project_id, member_name) VALUES (?, ?)");
        foreach ($membersData as $member) {
            $memberStmt->execute($member);
        }
        
        // إضافة مهام تجريبية
        $tasksData = [
            ['تحليل المتطلبات', 'تحليل وتوثيق متطلبات النظام', 'مكتملة', 'High', '2024-02-15', 1, 'student1', 'supervisor1'],
            ['تصميم قاعدة البيانات', 'تصميم هيكل قاعدة البيانات', 'في التقدم', 'High', '2024-03-01', 1, 'student2', 'supervisor1'],
            ['تطوير الواجهة الأمامية', 'تطوير واجهة المستخدم', 'غير منجزة', 'Medium', '2024-04-01', 1, 'student1', 'supervisor1'],
            ['اختبار النظام', 'اختبار شامل للنظام', 'غير منجزة', 'High', '2024-05-15', 2, 'student2', 'supervisor2']
        ];
        
        $taskStmt = $pdo->prepare("
            INSERT INTO tasks (taskName, description, status, priority, deadline, project_id, assigned_to, created_by) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        foreach ($tasksData as $task) {
            $taskStmt->execute($task);
        }
        
        // إضافة إشعارات تجريبية
        $notificationsData = [
            ['task_assigned', 'مهمة جديدة', 'تم تعيين مهمة جديدة لك', 'student1', 'supervisor1'],
            ['project_update', 'تحديث المشروع', 'تم تحديث حالة المشروع', 'student2', 'supervisor1'],
            ['deadline_reminder', 'تذكير موعد', 'اقتراب موعد تسليم المهمة', 'student1', 'system']
        ];
        
        $notificationStmt = $pdo->prepare("
            INSERT INTO notifications (type, title, message, recipient, sender) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        foreach ($notificationsData as $notification) {
            $notificationStmt->execute($notification);
        }
        
        // إضافة رسائل تجريبية
        $messagesData = [
            ['supervisor1', 'student1', 'استفسار حول المشروع', 'أريد مناقشة تقدم المشروع معك'],
            ['student1', 'supervisor1', 'رد: استفسار حول المشروع', 'شكراً لك، سأرسل التقرير قريباً'],
            ['admin1', 'supervisor1', 'اجتماع المشرفين', 'اجتماع يوم الأحد الساعة 10 صباحاً']
        ];
        
        $messageStmt = $pdo->prepare("
            INSERT INTO messages (sender, receiver, subject, message_content) 
            VALUES (?, ?, ?, ?)
        ");
        
        foreach ($messagesData as $message) {
            $messageStmt->execute($message);
        }
               
        $pdo->commit();
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw new Exception("خطأ في إضافة البيانات التجريبية: " . $e->getMessage());
    }
}

/**
 * تنظيف قاعدة البيانات
 * 
 * @param PDO $pdo اتصال قاعدة البيانات
 */
function cleanTestDatabase($pdo) {
    $tables = [
        'activity_log', 'messages', 'notifications', 'files', 
        'tasks', 'project_members', 'projects', 'roles', 'users'
    ];
    
    foreach ($tables as $table) {
        $pdo->exec("DELETE FROM $table");
    }
}

/**
 * دالة مساعدة للتحقق من تساوي المصفوفات
 * 
 * @param array $expected المصفوفة المتوقعة
 * @param array $actual المصفوفة الفعلية
 * @param string $message رسالة الخطأ
 * @return bool
 */
function assertArraysEqual($expected, $actual, $message = '') {
    if (count($expected) !== count($actual)) {
        return false;
    }
    
    foreach ($expected as $key => $value) {
        if (!array_key_exists($key, $actual) || $actual[$key] !== $value) {
            return false;
        }
    }
    
    return true;
}

/**
 * دالة مساعدة لإنشاء مستخدم اختبار
 * 
 * @param PDO $pdo اتصال قاعدة البيانات
 * @param string $username اسم المستخدم
 * @param string $email البريد الإلكتروني
 * @param string $role الدور
 * @return array بيانات المستخدم المُنشأ
 */
function createTestUser($pdo, $username, $email, $role = 'student') {
    $hashedPassword = password_hash('password123', PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("
        INSERT INTO users (username, email, password, role) 
        VALUES (?, ?, ?, ?)
    ");
    
    $stmt->execute([$username, $email, $hashedPassword, $role]);
    
    return [
        'id' => $pdo->lastInsertId(),
        'username' => $username,
        'email' => $email,
        'role' => $role
    ];
}

/**
 * دالة مساعدة لإنشاء مشروع اختبار
 * 
 * @param PDO $pdo اتصال قاعدة البيانات
 * @param array $data بيانات المشروع
 * @return array بيانات المشروع المُنشأ
 */
function createTestProject($pdo, $data = []) {
    $defaultData = [
        'project_code' => 'TEST' . rand(1000, 9999),
        'project_name' => 'مشروع اختبار',
        'description' => 'وصف مشروع الاختبار',
        'start_date' => '2024-01-01',
        'end_date' => '2024-12-31',
        'status' => 'Pending',
        'supervisor' => 'supervisor1',
        'student' => 'student1',
        'team_leader' => 'student1'
    ];
    
    $projectData = array_merge($defaultData, $data);
    
    $stmt = $pdo->prepare("
        INSERT INTO projects (project_code, project_name, description, start_date, end_date, status, supervisor, student, team_leader) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $projectData['project_code'],
        $projectData['project_name'],
        $projectData['description'],
        $projectData['start_date'],
        $projectData['end_date'],
        $projectData['status'],
        $projectData['supervisor'],
        $projectData['student'],
        $projectData['team_leader']
    ]);
    
    $projectData['project_id'] = $pdo->lastInsertId();
    return $projectData;
}

/**
 * إعداد متغيرات البيئة للاختبار
 */
function setupTestEnvironment() {
    // إعداد المنطقة الزمنية
    date_default_timezone_set('Asia/Riyadh');
    
    // تعطيل رسائل الخطأ في الاختبارات
    error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
    
    // إعداد المتغيرات العامة للاختبار
    $GLOBALS['test_mode'] = true;
    $GLOBALS['test_start_time'] = microtime(true);
}

// تشغيل إعداد البيئة
setupTestEnvironment();

echo "✅ تم إعداد بيئة الاختبار بنجاح...\n";
?>