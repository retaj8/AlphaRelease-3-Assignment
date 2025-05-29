<?php
class Message {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // دالة لجلب الرسائل الواردة
    public function getInboxMessages($username) {
        $query = "SELECT m.*, u.role as sender_role 
                  FROM messages m 
                  JOIN users u ON m.sender = u.username
                  WHERE m.receiver = :username AND m.is_deleted_by_receiver = 0
                  ORDER BY m.send_date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute(['username' => $username]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // دالة لجلب الرسائل الصادرة
    public function getSentMessages($username) {
        $query = "SELECT m.*, u.role as receiver_role
                  FROM messages m 
                  JOIN users u ON m.receiver = u.username
                  WHERE m.sender = :username AND m.is_deleted_by_sender = 0
                  ORDER BY m.send_date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute(['username' => $username]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // دالة لحذف الرسالة
    public function deleteMessage($message_id, $username, $isInbox) {
        if ($isInbox) {
            $stmt = $this->conn->prepare("UPDATE messages SET is_deleted_by_receiver = 1 WHERE message_id = :message_id AND receiver = :username");
        } else {
            $stmt = $this->conn->prepare("UPDATE messages SET is_deleted_by_sender = 1 WHERE message_id = :message_id AND sender = :username");
        }
        $stmt->execute(['message_id' => $message_id, 'username' => $username]);
    }

    // دالة لإرسال رسالة جديدة
    public function sendMessage($sender, $receiver, $subject, $message_content) {
        $stmt = $this->conn->prepare("INSERT INTO messages (sender, receiver, subject, message_content) VALUES (:sender, :receiver, :subject, :content)");
        return $stmt->execute([
            'sender' => $sender,
            'receiver' => $receiver,
            'subject' => $subject,
            'content' => $message_content
        ]);
    }

    // دالة لجلب عدد الرسائل غير المقروءة
    public function getUnreadCount($username) {
        $query = "SELECT COUNT(*) FROM messages WHERE receiver = :username AND is_read = 0 AND is_deleted_by_receiver = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->execute(['username' => $username]);
        return $stmt->fetchColumn();
    }
}
?>
