<?php

$servername= 'mysql:host=localhost; dbname=project_cmt';
$username= 'root';
$password='';

try{
$conn = new PDO($servername,$username, $password); 
   $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
  

  }
catch(PDOException $e)
    {
     echo "Error :" . $e->getMessage();
    }

?>
