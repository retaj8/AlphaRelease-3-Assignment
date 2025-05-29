<?php
session_start();
include 'conn.php';
include 'Project.php';

if (!isset($_GET['project_id'])) {
    echo "رقم المشروع غير موجود.";
    exit();
}
$projectID = $_GET['project_id'];
$projectObj = new Project($conn);
$project = $projectObj->getProjectById($projectID);

// جلب المهام المرتبطة بالمشروع (بالاعتماد على projects.id)
$stmtTasks = $conn->prepare("SELECT * FROM task WHERE project_id = ?");
$stmtTasks->execute([$projectID]);
$tasks = $stmtTasks->fetchAll(PDO::FETCH_ASSOC);

// جلب أعضاء الفريق (بناء على projects.project_id)
$stmtMembers = $conn->prepare("SELECT member_name FROM project_members WHERE project_id = ?");
$stmtMembers->execute([$projectID]);

$members = $stmtMembers->fetchAll(PDO::FETCH_ASSOC);



// حساب الإحصائيات
$totalTasks = count($tasks);
$completed = $inProgress = $late = 0;
$teamPerformance = [];

foreach ($tasks as $task) {
    switch ($task['status']) {
        case 'مكتملة':
            $completed++;
            break;
        case 'قيد التنفيذ':
            $inProgress++;
            break;
        case 'متأخرة':
            $late++;
            break;
    }

    // حساب أداء كل عضو فريق
    $user = $task['member_name'] ?? 'غير معروف';

    if (!isset($teamPerformance[$user])) {
        $teamPerformance[$user] = ['مكتملة' => 0, 'متأخرة' => 0];
    }

    if ($task['status'] === 'مكتملة') {
        $teamPerformance[$user]['مكتملة']++;
    } elseif ($task['status'] === 'متأخرة') {
        $teamPerformance[$user]['متأخرة']++;
    }
}
// دالة لحساب عدد المهام المنجزة
function countCompletedTasks($tasks) {
    $count = 0;
    foreach ($tasks as $task) {
        if ($task['status'] === 'مكتملة') {
            $count++;
        }
    }
    return $count;
}

// دالة لحساب عدد المهام غير المنجزة (أي ليست "مكتملة")
function countUncompletedTasks($tasks) {
    $count = 0;
    foreach ($tasks as $task) {
        if ($task['status'] !== 'مكتملة') {
            $count++;
        }
    }
    return $count;
}

$progress = $totalTasks > 0 ? round(($completed / $totalTasks) * 100) : 0;
$today = date("Y-m-d");
$completedTasks = countCompletedTasks($tasks);
$uncompletedTasks = countUncompletedTasks($tasks);

?>
<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>تقرير المشروع</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            direction: rtl;
            background-color: #f1f4f8;
            font-family: 'Segoe UI', sans-serif;
        }
        .report-box {
            background: #ffffff;
            padding: 2.5rem;
            border-radius: 15px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
            margin-top: 3rem;
        }
        .section-title {
            font-weight: bold;
            font-size: 1.2rem;
            margin-top: 2rem;
            color: #2c3e50;
        }
        .stat-box {
            background: #ecf0f1;
            border-radius: 12px;
            padding: 1.2rem;
            text-align: center;
            box-shadow: inset 0 0 10px rgba(0,0,0,0.05);
        }
        .stat-box h5 {
            color: #34495e;
            font-size: 1rem;
            margin-bottom: 0.5rem;
        }
        .stat-box span {
            font-size: 1.3rem;
            font-weight: bold;
            color: #2980b9;
        }
        .table thead {
            background-color: #3498db;
            color: white;
        }
        .badge-status {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.9rem;
        }
        .badge-success { background-color: #2ecc71; color: white; }
        .badge-warning { background-color: #f1c40f; color: white; }
        .badge-danger { background-color: #e74c3c; color: white; }
        .btn-custom {
            border-radius: 25px;
            padding: 10px 20px;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="container report-box">
   <h4 class="text-center mb-4 text-primary">
        <i class="fas fa-clipboard-list me-2"></i>
        تقرير تقدم المشروع: <span class="text-dark"><?php echo htmlspecialchars($project['project_name'] ?? ''); ?>
</span>
    </h4>


    <div class="row text-center mb-4">
    <div class="col-md-2">
        <div class="stat-box">
            <h5><i class="fa-solid fa-list-check me-1"></i> عدد المهام</h5>
            <span><?php echo $totalTasks; ?></span>
        </div>
    </div>
    
    <div class="col-md-2">
        <div class="stat-box">
            <h5><i class="fa-solid fa-spinner me-1"></i> قيد التنفيذ</h5>
            <span><?php echo $inProgress; ?></span>
        </div>
    </div>
    <div class="col-md-2">
        <div class="stat-box">
            <h5><i class="fa-solid fa-clock me-1"></i> متأخرة</h5>
            <span><?php echo $late; ?></span>
        </div>
    </div>
    <div class="col-md-2">
        <div class="stat-box">
            <h5><i class="fa-solid fa-check me-1"></i> تم إنجازه</h5>
            <span><?php echo $completedTasks; ?></span>
        </div>
    </div>
    <div class="col-md-2">
        <div class="stat-box">
            <h5><i class="fa-solid fa-xmark me-1"></i> غير منجز</h5>
            <span><?php echo $uncompletedTasks; ?></span>
        </div>
    </div>
</div>


    <p class="text-muted text-end"><i class="fa-regular fa-calendar-days me-1"></i> تاريخ اليوم: <?php echo $today; ?></p>
    <p class="text-muted text-end"><i class="fa-solid fa-chart-line me-1"></i> نسبة الإنجاز: <strong><?php echo $progress; ?>%</strong></p>

    <div class="section-title">👥 أداء الفريق:</div>
    <ul class="list-group list-group-flush mb-3">
        <?php foreach ($teamPerformance as $member => $stats): ?>
            <li class="list-group-item">
                <i class="fa-solid fa-user me-1"></i> <?php echo htmlspecialchars($member); ?> - 
                <span class="text-success"><?php echo $stats['مكتملة']; ?> مكتملة</span>
                <?php if ($stats['متأخرة']): ?>
                    , <span class="text-danger"><?php echo $stats['متأخرة']; ?> متأخرة</span>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>

    <div class="section-title">📌 تفاصيل المهام:</div>
    <div class="table-responsive">
        <table class="table table-hover align-middle text-center">
            <thead>
                <tr>
                    <th>#</th>
                    <th>اسم المهمة</th>
                    <th>الحالة</th>
                    <th>تاريخ التسليم</th>
                    <th>المستخدم المكلف</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tasks as $index => $task): ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td><?php echo htmlspecialchars($task['taskName']); ?></td>
                        <td>
                            <?php
                                $status = $task['status'];
                                $badgeClass = match ($status) {
                                    'مكتملة' => 'badge-success',
                                    'قيد التنفيذ' => 'badge-warning',
                                    'متأخرة' => 'badge-danger',
                                    default => 'bg-secondary'
                                };
                            ?>
                            <span class="badge badge-status <?php echo $badgeClass; ?>">
                                <?php echo $status; ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($task['deadline']); ?></td>
                        <td><?php echo htmlspecialchars($task['member_name'] ?? 'غير معروف'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="mt-4 text-center">
        <a href="generate_pdf.php?project_id=<?php echo $projectID; ?>" class="btn btn-danger btn-custom me-2">
            <i class="fa-solid fa-file-pdf me-1"></i> توليد PDF
        </a>
        <a href="update_tasks_status.php?project_id=<?php echo $projectID; ?>" class="btn btn-outline-primary btn-custom">
            <i class="fa-solid fa-arrows-rotate me-1"></i> تحديث الحالات
        </a>
    </div>
</div>

</body>
</html>
