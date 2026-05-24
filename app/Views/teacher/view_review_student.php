<?php
session_start();
require_once "../../../config/database.php";

// ទទួល ID របស់ Result ដើម្បីដឹងថាជាវិញ្ញាសាមួយណា និងជារបស់សិស្សណា
$result_id = $_GET['id'] ?? null; 
$db = (new Database())->getConnection();

// ១. ទាញទិន្នន័យពីតារាង results ដើម្បីរក quiz_id និងព័ត៌មានសិស្ស
$stmtResult = $db->prepare("
    SELECT r.quiz_id, q.title, u.full_name 
    FROM results r 
    JOIN quizzes q ON r.quiz_id = q.id 
    JOIN users u ON r.user_id = u.id 
    WHERE r.id = ?
");
$stmtResult->execute([$result_id]);
$resultData = $stmtResult->fetch(PDO::FETCH_ASSOC);

if (!$resultData) {
    die("រកមិនឃើញទិន្នន័យលទ្ធផលឡើយ");
}

$quiz_id = $resultData['quiz_id'];

// ២. ទាញយកសំណួរទាំងអស់ និងចម្លើយដែលសិស្សបានជ្រើសរើស (ប្រើ LEFT JOIN)
// យើងប្រើ sa.result_id = ? ដើម្បីធានាថាវាបង្ហាញតែចម្លើយរបស់សិស្សម្នាក់នេះក្នុង Result នេះ
$stmt = $db->prepare("
    SELECT q.*, sa.selected_option 
    FROM questions q 
    LEFT JOIN student_answers sa ON q.id = sa.question_id AND sa.result_id = ?
    WHERE q.quiz_id = ?
");
$stmt->execute([$result_id, $quiz_id]);
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>គ្រូពិនិត្យវិញ្ញាសាឡើងវិញ</title>
   <link rel="stylesheet" href="../../../../node_modules/bootstrap/dist/css/bootstrap.min.css">

    <!-- Line 9 - fix this -->
    <script src="../../../../node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="../../../public/fontawesome-free-7.2.0-web/css/all.min.css">
    <link rel="stylesheet" href="../../../public/css/studentstyle/review.css">
</head>
<body>
    <?php include 'partials/sidebar.php'; ?>

    <div class="main-content">
        <div class="review-header d-flex justify-content-between align-items-center shadow-sm p-3 mb-4 bg-white rounded">
            <div>
                <h3 class="fw-bold text-primary mb-1">ពិនិត្យលទ្ធផលសិស្ស</h3>
                <p class="mb-0 text-dark">
                    <strong>សិស្ស៖</strong> <?= htmlspecialchars($resultData['full_name']) ?> | 
                    <strong>វិញ្ញាសា៖</strong> <?= htmlspecialchars($resultData['title']) ?>
                </p>
            </div>
            <a href="student_results.php" class="btn btn-outline-secondary">
                <i class="fa-solid fa-arrow-left me-2"></i>ត្រឡប់ក្រោយ
            </a>
        </div>

        <?php foreach ($questions as $index => $q): ?>
            <div class="question-card shadow-sm border-0 mb-4 p-4 bg-white rounded">
                <span class="badge bg-primary mb-3">សំណួរទី <?= ($index + 1) ?></span>
                <h5 class="fw-bold text-dark mb-4"><?= htmlspecialchars($q['question_text']) ?></h5>
                
                <div class="options-list">
                    <?php 
                    $options = [
                        'A' => $q['option_a'],
                        'B' => $q['option_b'],
                        'C' => $q['option_c'],
                        'D' => $q['option_d']
                    ];

                    foreach ($options as $key => $val): 
                        if (empty($val)) continue;

                        $isCorrect = ($key === $q['correct_option']);
                        $isSelected = ($key === $q['selected_option']); 

                        $boxClass = '';
                        $textClass = '';
                        
                        if ($isCorrect) {
                            $boxClass = 'border-success bg-success-subtle'; 
                            $textClass = 'text-success fw-bold';
                        } elseif ($isSelected && !$isCorrect) {
                            $boxClass = 'border-danger bg-danger-subtle'; 
                            $textClass = 'text-danger fw-bold';
                        }
                    ?>
                        <div class="p-3 border rounded mb-2 d-flex align-items-center <?= $boxClass ?>">
                            <div class="option-label me-3 fw-bold"><?= $key ?></div>
                            <span class="<?= $textClass ?>"><?= htmlspecialchars($val) ?></span>
                            
                            <div class="ms-auto">
                                <?php if ($isCorrect): ?>
                                    <span class="text-success small me-1">ចម្លើយត្រឹមត្រូវ</span>
                                    <i class="fa-solid fa-circle-check text-success"></i>
                                <?php elseif ($isSelected && !$isCorrect): ?>
                                    <span class="text-danger small me-1">សិស្សបានជ្រើសរើស</span>
                                    <i class="fa-solid fa-circle-xmark text-danger"></i>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>