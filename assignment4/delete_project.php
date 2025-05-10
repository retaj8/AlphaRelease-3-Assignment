<?php 
include 'conn.php';
include 'Project.php';

$project= new project($conn);
$message="";


//حذف مشروع 
if($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['delete'])){

    $projectid=$_POST['project_id'];

    if(!empty($projectid)){
        $resulte=$project->deleteProjectById($projectid);
        $message =  "✅ تم حذف المشروع بنجاح!" ;
    } else {
        $message = "❌ لم يتم تحديد المشروع للحذف.";
    }
}

?>

<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>حذف المشروع</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <?php if (!empty($message)): ?>
            <div class="alert alert-info text-center"><?php echo $message; ?></div>
        <?php endif; ?>
    </div>
</body>
</html>