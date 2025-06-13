<?php

// filepath: c:\wamp64\www\projectCMT\tests\RoleTest.php

require_once __DIR__ . '/../Class/Role.php';
use PHPUnit\Framework\TestCase;
use ProjectCMT\Class\Role;

/**
 * اختبارات شاملة لكلاس Role
 */
class RoleTest extends TestCase {
    private $role;

    /**
     * إعداد البيئة قبل كل اختبار
     */
    protected function setUp(): void {
        // إنشاء كائن Role للاختبار مع تمرير اسم الدور
        $this->role = new Role("Admin");
    }

    /**
     * تنظيف البيئة بعد كل اختبار
     */
    protected function tearDown(): void {
        $this->role = null;
    }

    /**
     * اختبار 1: إنشاء دور Admin بنجاح
     */
    public function testCreateAdminRoleSuccessfully() {
        $roleName = "Admin";
        $role = new Role($roleName);

        $this->assertEquals($roleName, $role->getRoleName());
        $this->assertTrue($role->isAdmin());
        $this->assertFalse($role->isStudent());
        $this->assertFalse($role->isSupervisor());
    }

    /**
     * اختبار 2: إنشاء دور Student بنجاح
     */
    public function testCreateStudentRoleSuccessfully() {
        $roleName = "Student";
        $role = new Role($roleName);

        $this->assertEquals($roleName, $role->getRoleName());
        $this->assertTrue($role->isStudent());
        $this->assertFalse($role->isAdmin());
        $this->assertFalse($role->isSupervisor());
    }

    /**
     * اختبار 3: إنشاء دور Supervisor بنجاح
     */
    public function testCreateSupervisorRoleSuccessfully() {
        $roleName = "Supervisor";
        $role = new Role($roleName);

        $this->assertEquals($roleName, $role->getRoleName());
        $this->assertTrue($role->isSupervisor());
        $this->assertFalse($role->isAdmin());
        $this->assertFalse($role->isStudent());
    }

    /**
     * اختبار 4: إنشاء دور مخصص
     */
    public function testCreateCustomRole() {
        $roleName = "teamleader";
        $role = new Role($roleName);

        $this->assertEquals($roleName, $role->getRoleName());
        $this->assertFalse($role->isAdmin());
        $this->assertFalse($role->isStudent());
        $this->assertFalse($role->isSupervisor());
    }

    /**
     * اختبار 5: التحقق من حساسية الأحرف
     */
    public function testRoleNameCaseSensitivity() {
        $adminLower = new Role("admin");
        $adminUpper = new Role("ADMIN");
        $adminProper = new Role("Admin");

        $this->assertFalse($adminLower->isAdmin());
        $this->assertFalse($adminUpper->isAdmin());
        $this->assertTrue($adminProper->isAdmin());
    }

    /**
     * اختبار 6: التحقق من اسم الدور الفارغ
     */
    public function testEmptyRoleName() {
        $role = new Role("");

        $this->assertEquals("", $role->getRoleName());
        $this->assertFalse($role->isAdmin());
        $this->assertFalse($role->isStudent());
        $this->assertFalse($role->isSupervisor());
    }

    /**
     * اختبار 7: التحقق من اسم الدور null
     */
    public function testNullRoleName() {
        $role = new Role(null);

        $this->assertNull($role->getRoleName());
        $this->assertFalse($role->isAdmin());
        $this->assertFalse($role->isStudent());
        $this->assertFalse($role->isSupervisor());
    }
}