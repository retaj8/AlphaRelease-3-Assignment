<?php
/**
 * RoleFactory - نمط Factory لإنشاء الأدوار بطريقة منظمة
 * 
 * يسهل إنشاء الأدوار مع صلاحياتها المناسبة
 */
class RoleFactory {
    // تعريف الصلاحيات لكل دور
    const ADMIN_PERMISSIONS = [
        'user_management',      // إدارة المستخدمين
        'project_management',   // إدارة المشاريع
        'system_settings',      // إعدادات النظام
        'reports_access',       // الوصول للتقارير
        'role_management',      // إدارة الأدوار
        'full_access'          // صلاحية كاملة
    ];
    
    const SUPERVISOR_PERMISSIONS = [
        'project_management',   // إدارة المشاريع
        'student_supervision',  // الإشراف على الطلاب
        'reports_create',       // إنشاء التقارير
        'task_assignment',      // تعيين المهام
        'progress_monitoring'   // متابعة التقدم
    ];
    
    const STUDENT_PERMISSIONS = [
        'project_participation', // المشاركة في المشاريع
        'task_management',       // إدارة المهام الشخصية
        'file_upload',          // رفع الملفات
        'progress_report',      // تقارير التقدم
        'message_send'          // إرسال الرسائل
    ];
    
    const MANAGER_PERMISSIONS = [
        'project_management',   // إدارة المشاريع
        'resource_management',  // إدارة الموارد
        'team_coordination',    // تنسيق الفرق
        'budget_management',    // إدارة الميزانية
        'reports_access'        // الوصول للتقارير
    ];
    
    /**
     * إنشاء دور مدير النظام
     */
    public static function createAdmin(): Role {
        return new Role(
            'Admin', 
            self::ADMIN_PERMISSIONS, 
            'مدير النظام - صلاحيات كاملة لإدارة جميع جوانب النظام'
        );
    }
    
    /**
     * إنشاء دور المشرف
     */
    public static function createSupervisor(): Role {
        return new Role(
            'Supervisor', 
            self::SUPERVISOR_PERMISSIONS, 
            'مشرف المشاريع - الإشراف على الطلاب وإدارة المشاريع'
        );
    }
    
    /**
     * إنشاء دور الطالب
     */
    public static function createStudent(): Role {
        return new Role(
            'Student', 
            self::STUDENT_PERMISSIONS, 
            'طالب - المشاركة في المشاريع وتنفيذ المهام'
        );
    }
    
    /**
     * إنشاء دور المدير
     */
    public static function createManager(): Role {
        return new Role(
            'Manager', 
            self::MANAGER_PERMISSIONS, 
            'مدير القسم - إدارة المشاريع والموارد والفرق'
        );
    }
    
    /**
     * إنشاء دور مخصص
     */
    public static function createCustomRole(string $name, array $permissions, string $description = ''): Role {
        return new Role($name, $permissions, $description);
    }
    
    /**
     * إنشاء دور من مصفوفة
     */
    public static function createFromArray(array $data): Role {
        return new Role(
            $data['name'] ?? '',
            $data['permissions'] ?? [],
            $data['description'] ?? ''
        );
    }
    
    /**
     * الحصول على جميع الأدوار الافتراضية
     */
    public static function getAllDefaultRoles(): array {
        return [
            'Admin' => self::createAdmin(),
            'Supervisor' => self::createSupervisor(),
            'Student' => self::createStudent(),
            'Manager' => self::createManager()
        ];
    }
    
    /**
     * التحقق من صحة الصلاحيات
     */
    public static function validatePermissions(array $permissions): bool {
        $validPermissions = array_merge(
            self::ADMIN_PERMISSIONS,
            self::SUPERVISOR_PERMISSIONS,
            self::STUDENT_PERMISSIONS,
            self::MANAGER_PERMISSIONS
        );
        
        foreach ($permissions as $permission) {
            if (!in_array($permission, $validPermissions)) {
                return false;
            }
        }
        
        return true;
    }
}
?>