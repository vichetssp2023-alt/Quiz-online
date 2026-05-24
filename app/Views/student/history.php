<?php
session_start();
require_once "../../../config/database.php";

// ប្រាកដថាមាន Session user_id
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$database = new Database();
$db = $database->getConnection();

// ទាញយកទិន្នន័យប្រវត្តិប្រឡង ដោយ Join ជាមួយ Table quizzes ដើម្បីយកចំណងជើងវិញ្ញាសា
$query = "SELECT r.*, q.title 
          FROM results r 
          JOIN quizzes q ON r.quiz_id = q.id 
          WHERE r.user_id = ? 
          ORDER BY r.created_at DESC";

$stmt = $db->prepare($query);
$stmt->execute([$user_id]);
$history = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ប្រវត្តិប្រឡង</title>
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
            <h2 class="fw-bold text-dark">ប្រវត្តិប្រឡង <span class="text-primary">.</span></h2>
            <p class="text-muted">សមិទ្ធផល និងការវាយតម្លៃសមត្ថភាពរបស់អ្នក</p>
        </div>

        <div class="glass-card fw-bold ">
            <div class="table-responsive">
                <table class="table ">
                    <thead>
                        <tr>
                            <th>វិញ្ញាសាដែលបានប្រឡង</th>
                            <th>ពិន្ទុសម្រេចបាន</th>
                            <th>កាលបរិច្ឆេទ</th>
                            <th>ស្ថានភាព</th>
                            <th class="text-end">សកម្មភាព</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($history) > 0): ?>
                            <?php foreach ($history as $row): 
                                $isPass = ($row['status'] === 'Passed');
                            ?>
                            <tr>
                                <td>
                                    <div class="quiz-info-title"><?= htmlspecialchars($row['title']) ?></div>
                                    <div class="text-muted small"><i class="fa-solid fa-layer-group me-1"></i> <?= $row['total_questions'] ?> សំណួរ</div>
                                </td>
                                <td>
                                    <div class="score-box <?= $isPass ? 'score-pass' : 'score-fail' ?>">
                                        <?= round($row['score']) ?>%
                                    </div>
                                </td>
                                <td>
                                    <div class="text-dark fw-medium small"><?= date('d M, Y', strtotime($row['created_at'])) ?></div>
                                    <div class="text-muted" style="font-size: 0.7rem;"><?= date('h:i A', strtotime($row['created_at'])) ?></div>
                                </td>
                                <td>
                                    <span class="status-pill <?= $isPass ? 'pill-pass' : 'pill-fail' ?>">
                                        <i class="fa-solid <?= $isPass ? 'fa-circle-check' : 'fa-circle-xmark' ?>"></i>
                                        <?= $isPass ? 'ជាប់' : 'ធ្លាក់' ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="view_review.php?quiz_id=<?= $row['quiz_id'] ?>" class="btn-review-mini">
                                        <span>Review</span>
                                        <i class="fa-solid fa-arrow-right-long"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="py-4">
                                        <i class="fa-solid fa-inbox fa-3x text-light mb-3"></i>
                                        <p class="text-muted">មិនទាន់មានទិន្នន័យនៅឡើយទេ</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>