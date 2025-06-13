<?php
namespace ProjectCMT\Class;

class Project {
    private $db;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    // جلب قائمة الطلاب
    public function getStudents() {
    try {
        $sql = "SELECT username FROM users WHERE role = 'student'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_COLUMN); // إرجاع أسماء الطلاب فقط
    } catch (\PDOException $e) {
        return [];
    }
}

    // جلب قائمة المشرفين
    public function getSupervisors() {
    try {
        $sql = "SELECT username FROM users WHERE role = 'supervisor'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_COLUMN); // إرجاع أسماء المشرفين فقط
    } catch (\PDOException $e) {
        return [];
    }
}

    // إضافة المشروع
    public function addProject($projectData, $teamMembers) {
        try {
            $this->db->beginTransaction();

            $sql = "INSERT INTO projects (project_code, project_name, start_date, end_date, status, supervisor, student, team_leader)
                    VALUES (:project_code, :project_name, :start_date, :end_date, :status, :supervisor, :student, :team_leader)";
            $stmt = $this->db->prepare($sql);

            $stmt->execute([
                ':project_code' => $projectData['project_code'],
                ':project_name' => $projectData['project_name'],
                ':start_date' => $projectData['start_date'],
                ':end_date' => $projectData['end_date'],
                ':status' => $projectData['status'],
                ':supervisor' => $projectData['supervisor'],
                ':student' => $projectData['student'],
                ':team_leader' => $projectData['team_leader']
            ]);

            $projectId = $this->db->lastInsertId();

            $sqlMembers = "INSERT INTO project_members (project_id, member_name) VALUES (:project_id, :member_name)";
            $stmtMembers = $this->db->prepare($sqlMembers);

            foreach ($teamMembers as $member) {
                $stmtMembers->execute([':project_id' => $projectId, ':member_name' => $member]);
            }

            $this->db->commit();
            return "تمت إضافة المشروع بنجاح!";
        } catch (\Exception $e) {
            $this->db->rollBack();
            return "خطأ أثناء إضافة المشروع: " . $e->getMessage();
        }
    }

    // دالة التحقق اذا كان المشروع موجود مسبقا
    public function isprojectExists($projectId, $projectName) {
        try {
            $sql = "SELECT * FROM projects WHERE project_id=:project_id OR project_name=:project_name";
            $smt = $this->db->prepare($sql);
            $smt->execute(['project_id' => $projectId, 'project_name' => $projectName]);
            return $smt->fetch(\PDO::FETCH_ASSOC) !== false;
        } catch (\PDOException $e) {
            return false;
        }
    }

    // دالة للبحث عن المشروع
   
    public function searchProjects($searchQuery) {
    try {
        $query = '%' . $searchQuery . '%';

        $sql = "SELECT * FROM projects 
                WHERE project_name LIKE :query";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['query' => $query]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    } catch (\PDOException $e) {
        return [];
    }
}

    // دالة حذف مشروع 
    public function deleteProjectById($projectid) {
        try {
            $checkSql = "SELECT COUNT(*) FROM projects WHERE project_id = :project_id";
            $checkStmt = $this->db->prepare($checkSql);
            $checkStmt->execute(['project_id' => $projectid]);

            if ($checkStmt->fetchColumn() == 0) {
                return "❌ المشروع غير موجود في قاعدة البيانات!";
            }

            $sql = "DELETE FROM projects WHERE project_id = :project_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['project_id' => $projectid]);

            return "✅ تم حذف المشروع بنجاح!";
        } catch (\PDOException $e) {
            return "❌ خطأ أثناء الحذف: " . $e->getMessage();
        }
    }

    public function getProjectById($projectId) {
        try {
            $sql = "SELECT * FROM projects WHERE project_id = :project_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['project_id' => $projectId]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            return null;
        }
    }

    public function updateProject($projectData, $teamMembers) {
        try {
            $this->db->beginTransaction();

            $sql = "UPDATE projects SET 
                    project_name = :project_name, 
                    start_date = :start_date, 
                    end_date = :end_date, 
                    status = :status, 
                    team_leader = :team_leader, 
                    supervisor = :supervisor 
                    WHERE project_id = :project_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($projectData);

            $deleteMembersSQL = "DELETE FROM project_members WHERE project_id = :project_id";
            $deleteStmt = $this->db->prepare($deleteMembersSQL);
            $deleteStmt->execute(['project_id' => $projectData['project_id']]);

            $sqlMembers = "INSERT INTO project_members (project_id, member_name) VALUES (:project_id, :member_name)";
            $stmtMembers = $this->db->prepare($sqlMembers);
            foreach ($teamMembers as $member) {
                $stmtMembers->execute(['project_id' => $projectData['project_id'], 'member_name' => $member]);
            }

            $this->db->commit();
            return "✅ تم تحديث المشروع بنجاح!";
        } catch (\Exception $e) {
            $this->db->rollBack();
            return "❌ خطأ أثناء التحديث: " . $e->getMessage();
        }
    }
}
