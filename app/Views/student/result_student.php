<?php
session_start();
require_once "../../../config/database.php";

// ១. ត្រួតពិនិត្យ Session
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$database = new Database();
$db = $database->getConnection();

// ២. ទាញយកទិន្នន័យប្រវត្តិប្រឡងទាំងអស់
$query = "SELECT r.*, q.title 
          FROM results r 
          JOIN quizzes q ON r.quiz_id = q.id 
          WHERE r.user_id = ? 
          ORDER BY r.created_at DESC";

$stmt = $db->prepare($query);
$stmt->execute([$user_id]);
$history = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ៣. គណនាស្ថិតិសរុប
$total_exams = count($history);
$average_score = 0;
$pass_count = 0;

if ($total_exams > 0) {
    $sum_score = 0;
    foreach ($history as $row) {
        $sum_score += $row['score'];
        if ($row['status'] === 'Passed') {
            $pass_count++;
        }
    }
    $average_score = $sum_score / $total_exams;
    $pass_rate = ($pass_count / $total_exams) * 100;
} else {
    $pass_rate = 0;
}
?>

<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>លទ្ធផលរបស់ខ្ញុំ</title>
    <link rel="stylesheet" href="../../../../node_modules/bootstrap/dist/css/bootstrap.min.css">

    <!-- Line 9 - fix this -->
    <script src="../../../../node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="../../../public/fontawesome-free-7.2.0-web/css/all.min.css">
   <link rel="stylesheet" href="../../../public/css/studentstyle/result.css">
</head>
<body>

    <?php include 'partials/sidebar.php'; ?>

    <div class="main-content">
        <div class="mb-5">
            <h2 class="fw-bold text-dark">លទ្ធផលសរុប <span class="text-primary">.</span></h2>
            <p class="text-muted">ពិនិត្យមើលការរីកចម្រើន និងស្ថិតិនៃការសិក្សារបស់អ្នក</p>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="stat-card d-flex align-items-center">
                    <div class="icon-box bg-primary-subtle text-primary me-3">
                        <i class="fa-solid fa-clipboard-list fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-bold">ប្រឡងសរុប</div>
                        <div class="fs-3 fw-bold"><?= $total_exams ?> ដង</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card d-flex align-items-center">
                    <div class="icon-box bg-success-subtle text-success me-3">
                        <i class="fa-solid fa-bullseye fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-bold">មធ្យមភាគ</div>
                        <div class="fs-3 fw-bold"><?= number_format($average_score, 1) ?>%</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card d-flex align-items-center">
                    <div class="icon-box bg-warning-subtle text-warning me-3">
                        <i class="fa-solid fa-trophy fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-bold">អត្រាជាប់</div>
                        <div class="fs-3 fw-bold"><?= round($pass_rate) ?>%</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="glass-card">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">វិញ្ញាសា</th>
                            <th class="text-center">ពិន្ទុ</th>
                            <th>កាលបរិច្ឆេទ</th>
                            <th>ស្ថានភាព</th>
                            <th class="text-end pe-4">សកម្មភាព</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($total_exams > 0): ?>
                            <?php foreach ($history as $row): 
                                $isPass = ($row['status'] === 'Passed');
                            ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($row['title']) ?></div>
                                    <div class="text-muted small">ID: #<?= $row['quiz_id'] ?></div>
                                </td>
                                <td class="text-center">
                                    <span class="score-badge <?= $isPass ? 'pass' : 'fail' ?>">
                                        <?= round($row['score']) ?>%
                                    </span>
                                </td>
                                <td>
                                    <div class="small fw-medium text-dark"><?= date('d M, Y', strtotime($row['created_at'])) ?></div>
                                    <div class="text-muted small" style="font-size: 0.75rem;"><?= date('h:i A', strtotime($row['created_at'])) ?></div>
                                </td>
                                <td>
                                    <span class="status-pill <?= $isPass ? 'pill-pass' : 'pill-fail' ?>">
                                        <i class="fa-solid <?= $isPass ? 'fa-circle-check' : 'fa-circle-xmark' ?>"></i>
                                        <?= $isPass ? 'ជាប់' : 'ធ្លាក់' ?>
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="view_review.php?quiz_id=<?= $row['quiz_id'] ?>" class="btn-review-mini">
                                        Review <i class="fa-solid fa-arrow-right-long"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">មិនទាន់មានទិន្នន័យលទ្ធផលនៅឡើយទេ។</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>