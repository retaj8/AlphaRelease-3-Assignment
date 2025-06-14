<?php


use PHPUnit\Framework\TestCase;
use ProjectCMT\Class\Project;/**
/**
 * اختبارات كلاس Project
 */
class ProjectTest extends TestCase
{
    private $pdo;
    private $project;
    
    protected function setUp(): void
    {
        $this->pdo = createTestDatabase();
        seedTestData($this->pdo);
        $this->project = new Project($this->pdo);
        
    }
    
    /**
     * اختبار 1: جلب قائمة الطلاب
     */
    public function testGetStudents()
    {
        $students = $this->project->getStudents();
        
        $this->assertIsArray($students);
        $this->assertContains('student1', $students);
        $this->assertContains('student2', $students);
        $this->assertNotContains('supervisor1', $students);
    }
    
    /**
     * اختبار 2: جلب قائمة المشرفين
     */
    public function testGetSupervisors()
    {
        $supervisors = $this->project->getSupervisors();
        
        $this->assertIsArray($supervisors);
        $this->assertContains('supervisor1', $supervisors);
        $this->assertNotContains('student1', $supervisors);
    }
    
    /**
     * اختبار 3: إضافة مشروع جديد
     */
    public function testAddProject()
    {
        $projectData = [
            'project_code' => 'TEST001',
            'project_name' => 'مشروع اختبار',
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
            'status' => 'Pending',
            'supervisor' => 'supervisor1',
            'student' => 'student1',
            'team_leader' => 'student1'
        ];
        
        $teamMembers = ['student1', 'student2'];
        
        $result = $this->project->addProject($projectData, $teamMembers);
        
        $this->assertEquals('تمت إضافة المشروع بنجاح!', $result);
        
        // التحقق من إدراج المشروع
        $stmt = $this->pdo->prepare("SELECT * FROM projects WHERE project_code = ?");
        $stmt->execute(['TEST001']);
        $project = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $this->assertNotFalse($project);
        $this->assertEquals('مشروع اختبار', $project['project_name']);
        
        // التحقق من إضافة أعضاء الفريق
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM project_members WHERE project_id = ?");
        $stmt->execute([$project['project_id']]);
        $memberCount = $stmt->fetchColumn();
        
        $this->assertEquals(2, $memberCount);
    }
    
    /**
     * اختبار 4: التحقق من وجود مشروع
     */
    public function testIsProjectExists()
    {
        // إضافة مشروع للاختبار
        $projectData = [
            'project_code' => 'EXISTS001',
            'project_name' => 'مشروع موجود',
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
            'status' => 'Pending',
            'supervisor' => 'supervisor1',
            'student' => 'student1',
            'team_leader' => 'student1'
        ];
        
        $this->project->addProject($projectData, ['student1']);
        
        // اختبار الوجود
        $this->assertTrue($this->project->isProjectExists('EXISTS001', 'اسم آخر'));
        $this->assertTrue($this->project->isProjectExists('كود آخر', 'مشروع موجود'));
        $this->assertFalse($this->project->isProjectExists('NOTEXISTS', 'غير موجود'));
    }
    
    /**
     * اختبار 5: البحث عن المشاريع
     */
    public function testSearchProjects()
    {
        // إضافة مشاريع للبحث
        $projects = [
            ['TEST001', 'نظام إدارة المكتبة'],
            ['TEST002', 'موقع التجارة الإلكترونية'],
            ['TEST003', 'تطبيق الجوال']
        ];
        
        foreach ($projects as $proj) {
            $projectData = [
                'project_code' => $proj[0],
                'project_name' => $proj[1],
                'start_date' => '2024-01-01',
                'end_date' => '2024-12-31',
                'status' => 'Pending',
                'supervisor' => 'supervisor1',
                'student' => 'student1',
                'team_leader' => 'student1'
            ];
            $this->project->addProject($projectData, ['student1']);
        }
        
        // اختبار البحث
        $results = $this->project->searchProjects('إدارة');
        $this->assertCount(1, $results);
        
        $results = $this->project->searchProjects('TEST');
        $this->assertCount(3, $results);
        
        $results = $this->project->searchProjects('غير موجود');
        $this->assertCount(0, $results);
    }
    
    /**
     * اختبار 6: جلب مشروع بالمعرف
     */
    public function testGetProjectById()
    {
        // إضافة مشروع
        $projectData = [
            'project_code' => 'GET001',
            'project_name' => 'مشروع للجلب',
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
            'status' => 'Pending',
            'supervisor' => 'supervisor1',
            'student' => 'student1',
            'team_leader' => 'student1'
        ];
        
        $this->project->addProject($projectData, ['student1']);
        
        // جلب المشروع بالمعرف الجديد
        $stmt = $this->pdo->prepare("SELECT project_id FROM projects WHERE project_code = ?");
        $stmt->execute(['GET001']);
        $projectId = $stmt->fetchColumn();
        
        $project = $this->project->getProjectById($projectId);
        
        $this->assertIsArray($project);
        $this->assertEquals('مشروع للجلب', $project['project_name']);
    }
    
    /**
     * اختبار 7: تحديث مشروع
     */
    public function testUpdateProject()
    {
        // إضافة مشروع أولاً
        $projectData = [
            'project_code' => 'UPDATE001',
            'project_name' => 'مشروع قديم',
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
            'status' => 'Pending',
            'supervisor' => 'supervisor1',
            'student' => 'student1',
            'team_leader' => 'student1'
        ];
        
        $this->project->addProject($projectData, ['student1']);
        
        // جلب معرف المشروع
        $stmt = $this->pdo->prepare("SELECT project_id FROM projects WHERE project_code = ?");
        $stmt->execute(['UPDATE001']);
        $projectId = $stmt->fetchColumn();
        
        // تحديث المشروع
        $updatedData = [
            'project_id' => $projectId,
            'project_name' => 'مشروع محدث',
            'start_date' => '2024-02-01',
            'end_date' => '2024-11-30',
            'status' => 'In Progress',
            'supervisor' => 'supervisor1',
            'team_leader' => 'student1'
        ];
        
        $result = $this->project->updateProject($updatedData, ['student2']);
        
        $this->assertEquals('✅ تم تحديث المشروع بنجاح!', $result);
        
        // التحقق من التحديث
        $project = $this->project->getProjectById($projectId);
        $this->assertEquals('مشروع محدث', $project['project_name']);
    }
    
    /**
     * اختبار 8: حذف مشروع
     */
    public function testDeleteProject()
    {
        // إضافة مشروع للحذف
        $projectData = [
            'project_code' => 'DELETE001',
            'project_name' => 'مشروع للحذف',
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
            'status' => 'Pending',
            'supervisor' => 'supervisor1',
            'student' => 'student1',
            'team_leader' => 'student1'
        ];
        
        $this->project->addProject($projectData, ['student1']);
        
        // جلب معرف المشروع
        $stmt = $this->pdo->prepare("SELECT project_id FROM projects WHERE project_code = ?");
        $stmt->execute(['DELETE001']);
        $projectId = $stmt->fetchColumn();
        
        // حذف المشروع
        $result = $this->project->deleteProjectById($projectId);
        
        $this->assertEquals('✅ تم حذف المشروع بنجاح!', $result);
        
        // التحقق من الحذف
        $project = $this->project->getProjectById($projectId);
        $this->assertFalse($project);
    }
    
    protected function tearDown(): void
    {
        $this->pdo = null;
        $this->project = null;
    }
}
?>