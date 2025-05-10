<?php
class Role {
    private $roleName;

    public function __construct($roleName) {
        $this->roleName = $roleName;
    }

    public function getRoleName() {
        return $this->roleName;
    }

    public function isAdmin() {
        return $this->roleName === "Admin";
    }

    public function isStudent() {
        return $this->roleName === "Student";
    }

    public function isSupervisor() {
        return $this->roleName === "Supervisor";
    }

    public function locationUser() {
        switch ($this->roleName) {
            case "Student":
                header("Location: student_dashboard.php");
                exit();
            case "Supervisor":
                header("Location: dashboard_supervisor.php");
                exit();
            
            default:
                header("Location: login.php");
                exit();
        }
    }
}
?>
