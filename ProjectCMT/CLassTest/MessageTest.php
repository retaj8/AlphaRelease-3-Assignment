<?php
use PHPUnit\Framework\TestCase; 
require_once(__DIR__ . '/../Class/Messag.php');
 

class MessageTest extends TestCase {
    private $pdo;
    private $message;


    protected function setUp(): void {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // إنشاء الجداول الضرورية للاختبار
        $this->pdo->exec("
            CREATE TABLE users (
                username TEXT PRIMARY KEY,
                role TEXT
            );
            CREATE TABLE messages (
                message_id INTEGER PRIMARY KEY AUTOINCREMENT,
                sender TEXT,
                receiver TEXT,
                subject TEXT,
                message_content TEXT,
                send_date DATETIME DEFAULT CURRENT_TIMESTAMP,
                is_deleted_by_receiver INTEGER DEFAULT 0,
                is_deleted_by_sender INTEGER DEFAULT 0,
                is_read INTEGER DEFAULT 0
            );
        ");

        // إنشاء نسخة من كلاس Message للاختبار
        require_once(__DIR__ . '/../Class/Messag.php'); // عدل المسار حسب مكان الكلاس عندك
        $this->message = new Message($this->pdo);

        // إدخال بيانات اختبارية في جدول users
        $this->pdo->exec("INSERT INTO users (username, role) VALUES ('alice', 'admin'), ('bob', 'user')");
    }

    public function testSendMessage() {
        $result = $this->message->sendMessage('alice', 'bob', 'Hello', 'Test message');
        $this->assertTrue($result);

        // تحقق من أن الرسالة أُضيفت بنجاح
        $stmt = $this->pdo->query("SELECT * FROM messages WHERE sender = 'alice' AND receiver = 'bob'");
        $msg = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertEquals('Hello', $msg['subject']);
    }

    public function testGetInboxMessages() {
        // أضف رسالة مباشرة في قاعدة البيانات للاختبار
        $this->pdo->exec("INSERT INTO messages (sender, receiver, subject, message_content) VALUES ('alice', 'bob', 'Hi', 'Content')");

        $messages = $this->message->getInboxMessages('bob');
        $this->assertCount(1, $messages);
        $this->assertEquals('alice', $messages[0]['sender']);
    }

    public function testDeleteMessage() {
        $this->pdo->exec("INSERT INTO messages (sender, receiver, subject, message_content) VALUES ('alice', 'bob', 'Test', 'Content')");
        $message_id = $this->pdo->lastInsertId();

        $this->message->deleteMessage($message_id, 'bob', true);

        $stmt = $this->pdo->prepare("SELECT is_deleted_by_receiver FROM messages WHERE message_id = :id");
        $stmt->execute(['id' => $message_id]);
        $isDeleted = $stmt->fetchColumn();

        $this->assertEquals(1, $isDeleted);
    }

    public function testGetUnreadCount() {
        $this->pdo->exec("INSERT INTO messages (sender, receiver, subject, message_content, is_read) VALUES ('alice', 'bob', 'Unread', 'Content', 0)");
        $count = $this->message->getUnreadCount('bob');
        $this->assertEquals(1, $count);
    }
}
?>
