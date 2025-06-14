<?php

use PHPUnit\Framework\TestCase;
require_once(__DIR__ . '/../Class/NotificationSystem.php');

class NotificationSystemTest extends TestCase {
    private $pdoMock;
    private $stmtMock;
    private $observerMock;

    protected function setUp(): void {
        NotificationSystem::resetInstance();

       
        $this->pdoMock = $this->createMock(PDO::class);
        $this->stmtMock = $this->createMock(PDOStatement::class);

        $this->observerMock = $this->createMock(NotificationObserver::class);

        // الإرجاع التلقائي للكائن الوهمي
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);
    }

    public function testNotifyNewTaskCallsObserversOncePerObserver() {
        // إعداد البيانات الوهمية
        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('fetchAll')->willReturn(['student1', 'student2']);
        $this->stmtMock->method('fetchColumn')->willReturn('Project X');

        // التحقق أن update() تُستدعى مرة واحدة فقط
        $this->observerMock->expects($this->once())->method('update')->with($this->callback(function($data) {
            return $data['type'] === 'new_task';
        }));

        $system = NotificationSystem::getInstance($this->pdoMock);
        $system->attach($this->observerMock);

        $result = $system->notifyNewTask(1, 'Design DB', 101, '2025-06-20', 'prof123');

        $this->assertTrue($result);
    }

    public function testNotifyTaskSubmissionCallsObserversOncePerObserver() {
        // إعداد البيانات الوهمية
        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('fetch')->willReturn([
            'taskName' => 'UML Diagrams',
            'project_name' => 'AI App',
            'project_id' => 202
        ]);

        // التأكد من أن observer يتم استدعاؤه مرة واحدة
        $this->observerMock->expects($this->once())->method('update')->with($this->callback(function($data) {
            return $data['type'] === 'task_submission';
        }));

        $system = NotificationSystem::getInstance($this->pdoMock);
        $system->attach($this->observerMock);

        $result = $system->notifyTaskSubmission(55, 'Ali', 'prof456');

        $this->assertTrue($result);
    }

    public function testNotifyTaskSubmissionWithMissingTask() {
        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('fetch')->willReturn(false); // simulate task not found

        $this->observerMock->expects($this->never())->method('update');

        $system = NotificationSystem::getInstance($this->pdoMock);
        $system->attach($this->observerMock);

        $result = $system->notifyTaskSubmission(99, 'Ahmed', 'prof999');
        $this->assertFalse($result);
    }

    public function testNotifyNewTaskWithQueryFailure() {
        // simulate DB failure
        $this->stmtMock->method('execute')->willThrowException(new PDOException("DB Error"));

        $this->observerMock->expects($this->never())->method('update');

        $system = NotificationSystem::getInstance($this->pdoMock);
        $system->attach($this->observerMock);

        $result = $system->notifyNewTask(2, 'API Integration', 105, '2025-06-30', 'prof789');
        $this->assertFalse($result);
    }
}
?>