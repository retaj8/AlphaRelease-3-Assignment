<?php
use PHPUnit\Framework\TestCase;
require_once(__DIR__ . '/../Class/Task.php');


class TaskTest extends TestCase
{
    private $pdo;
    private $task;

    protected function setUp(): void
    {

        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->pdo->exec("
            CREATE TABLE task (
                taskID INTEGER PRIMARY KEY AUTOINCREMENT,
                taskName TEXT,
                status TEXT,
                deadline TEXT,
                project_id INTEGER,
                file_id INTEGER,
                assigned_to TEXT
            );
            CREATE TABLE projects (
                project_id INTEGER PRIMARY KEY AUTOINCREMENT,
                project_name TEXT
            );
            CREATE TABLE project_members (
                member_id INTEGER PRIMARY KEY AUTOINCREMENT,
                project_id INTEGER,
                member_name TEXT
            );
            CREATE TABLE files (
                file_id INTEGER PRIMARY KEY AUTOINCREMENT,
                file_name TEXT,
                file_type TEXT
            );
        ");

        // إدخال مشروع وعضو
        $this->pdo->exec("INSERT INTO projects (project_name) VALUES ('مشروع تجريبي')");
        $this->pdo->exec("INSERT INTO project_members (project_id, member_name) VALUES (1, 'test_user')");

        $this->task = new Task($this->pdo);
    }

    public function testAddTask()
    {
        $taskId = $this->task->addTask('اختبار المهمة', '2025-06-20', 1, 'test_user');
        $this->assertIsNumeric($taskId);
    }

    public function testGetStudentTasks()
    {
        $this->task->addTask('مهمة طالب', '2025-06-22', 1, 'test_user');
        $tasks = $this->task->getStudentTasks('test_user');
        $this->assertNotEmpty($tasks);
    }

    public function testSubmitTask()
    {
        $taskId = $this->task->addTask('مهمة للتسليم', '2025-06-21', 1);
        $result = $this->task->submitTask($taskId, 'test_user', 'ملاحظات');
        $this->assertTrue($result);
    }

    public function testUploadTaskFile()
    {
        $fileId = $this->task->uploadTaskFile('test_file.pdf', 'application/pdf');
        $this->assertIsNumeric($fileId);
    }

    public function testUpdateTaskFile()
    {
        $taskId = $this->task->addTask('مهمة بملف', '2025-06-25', 1);
        $fileId = $this->task->uploadTaskFile('ملف_تجريبي.pdf', 'application/pdf');
        $result = $this->task->updateTaskFile($taskId, $fileId);
        $this->assertTrue($result);
    }

    public function testGetTaskById()
    {
        $taskId = $this->task->addTask('تفاصيل مهمة', '2025-06-30', 1, 'test_user');
        $task = $this->task->getTaskById($taskId);
        $this->assertEquals('تفاصيل مهمة', $task['taskName']);
    }

  
 public function testGetUpcomingTasks()
{

    $this->pdo->exec("INSERT OR IGNORE INTO project_members (project_id, member_name) VALUES (1, 'test_user')");

    // إدخال مهمة مستقبلية
    $this->pdo->exec("
        INSERT INTO task (taskName, deadline, status, project_id, assigned_to)
        VALUES ('مهمة قادمة', '" . date('Y-m-d', strtotime('+2 days')) . "', 'نشطة', 1, 'test_user')
    ");

    // اختبار الدالة
   $upcoming = $this->task->getUpcomingTasks('test_user', 5, date('Y-m-d'));

    $this->assertNotEmpty($upcoming);
}




    public function testAnalyzeProjectTasks()
    {
        $this->task->addTask('تحليل', date('Y-m-d'), 1, 'test_user');
        $analysis = $this->task->analyzeProjectTasks(1);
        $this->assertArrayHasKey('total', $analysis);
    }

    public function testGetTasksByProject()
    {
        $this->task->addTask('مهمة للمشروع', '2025-06-18', 1);
        $tasks = $this->task->getTasksByProject(1);
        $this->assertNotEmpty($tasks);
    }

    public function testGetFilesByProject()
    {
        $fileId = $this->task->uploadTaskFile('ملف_تجربة.txt', 'text/plain');
        $taskId = $this->task->addTask('مهمة لها ملف', '2025-06-19', 1, 'test_user');
        $this->task->updateTaskFile($taskId, $fileId);
        $files = $this->task->getFilesByProject(1);
        $this->assertNotEmpty($files);
    }

    public function testUpdateLateTasksStatus()
    {
        $this->task->addTask('مهمة قديمة', date('Y-m-d', strtotime('-3 days')), 1);
        $this->task->updateLateTasksStatus();
        $stmt = $this->pdo->query("SELECT status FROM task WHERE taskName = 'مهمة قديمة'");
        $status = $stmt->fetchColumn();
        $this->assertEquals('متأخرة', $status);
    }

    public function testGetProjectMembers()
    {
        $members = $this->task->getProjectMembers(1);
        $this->assertContains('test_user', $members);
    }
}
?>