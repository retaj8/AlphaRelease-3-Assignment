<?php
 class Task{
    private $db;

    public function __construct($dbconnection){
        $this->conn=$dbconnection;
    }

    public function grtstudentTask($username){
        try{
            $sql="SELECT t.*, p.project_name
            FROM task t
            t.project ON 
            "
        }
    }
 }



?>