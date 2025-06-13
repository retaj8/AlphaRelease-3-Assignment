<?php
namespace ProjectCMT\Class; 

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
    // أثناء الاختبار لا تنفذ header
    if (defined('PHPUNIT_RUNNING')) {
        return "redirect: " . $this->roleName;
    }
    switch ($this->roleName) {
        case "Student":
            header("Location: student_dashboard.php");
            exit();
        case "Supervisor":
            header("Location: dashboard_supervisor.php");
            exit();
        // ...
    }
}
}
?>
