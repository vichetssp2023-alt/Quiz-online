<?php
session_start();
require_once "../../../config/database.php";

$result_id = $_GET['result_id'] ?? null;
$quiz_id = $_GET['quiz_id'] ?? null; 
$user_id = $_SESSION['user_id'];
$db = (new Database())->getConnection();

if ($result_id) {
    // ទាញយក quiz_id ពី Table results
    $stmtResult = $db->prepare("SELECT quiz_id FROM results WHERE id = ? AND user_id = ?");
    $stmtResult->execute([$result_id, $user_id]);
    $result = $stmtResult->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        $quiz_id = $result['quiz_id'];
    }
}

if (!$quiz_id) {
    die("រកមិនឃើញវិញ្ញាសា ឬលទ្ធផលឡើយ");
}

// ទាញយកចំណងជើងវិញ្ញាសា
// ... កូដខាងលើរក្សាទុកដដែល ...

// ទាញយកចំណងជើងវិញ្ញាសា
$stmtQuiz = $db->prepare("SELECT title FROM quizzes WHERE id = ?");
$stmtQuiz->execute([$quiz_id]);
$quiz = $stmtQuiz->fetch(PDO::FETCH_ASSOC);

// ប្រសិនបើអត់មានទិន្នន័យក្នុង $quiz ទេ ឱ្យវាចេញអក្សរទទេ ឬ Unknown
$quizTitle = $quiz ? $quiz['title'] : "មិនស្គាល់វិញ្ញាសា";

// ... កូដទាញយក $questions រក្សាទុកដដែល ...
// កែសម្រួលកូដត្រង់កន្លែងទាញយកសំណួរ
$stmt = $db->prepare("
    SELECT q.*, sa.selected_option 
    FROM questions q 
    LEFT JOIN student_answers sa ON q.id = sa.question_id AND sa.result_id = ?
    WHERE q.quiz_id = ?
");

// ប្រសិនបើ result_id មកតាម GET គឺទទេ យើងត្រូវទាញវាចេញពី Table results វិញ
if (!$result_id && $quiz_id) {
    $stmtId = $db->prepare("SELECT id FROM results WHERE quiz_id = ? AND user_id = ? ORDER BY id DESC LIMIT 1");
    $stmtId->execute([$quiz_id, $user_id]);
    $res = $stmtId->fetch();
    $result_id = $res['id'] ?? null;
}

$stmt->execute([$result_id, $quiz_id]);
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ពិនិត្យវិញ្ញាសាឡើងវិញ</title>
    <link rel="stylesheet" href="../../../../node_modules/bootstrap/dist/css/bootstrap.min.css">

    <!-- Line 9 - fix this -->
    <script src="../../../../node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="../../../public/fontawesome-free-7.2.0-web/css/all.min.css">
    <link rel="stylesheet" href="../../../public/css/studentstyle/review.css">
</head>
<body>
    <?php include 'partials/sidebar.php'; ?>

    <div class="main-content">
        <div class="review-header d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold text-dark mb-1">ពិនិត្យវិញ្ញាសាឡើងវិញ</h3>
                <p class="text-muted mb-0"><i class="fa-solid fa-book-open me-2"></i><?= htmlspecialchars($quiz['title']) ?></p>
            </div>
            <a href="history.php" class="btn btn-back">
                <i class="fa-solid fa-arrow-left me-2"></i>ត្រឡប់ទៅប្រវត្តិ
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
                $isSelected = ($key === $q['selected_option']); // ទាញយកចម្លើយដែលសិស្សបានរើស

                // កំណត់ស្ទីលតាមលក្ខខណ្ឌ
                $boxClass = '';
                $textClass = '';
                
                if ($isCorrect) {
                    $boxClass = 'border-success bg-success-subtle'; // ពណ៌បៃតងសម្រាប់ចម្លើយត្រូវ
                    $textClass = 'text-success fw-bold';
                } elseif ($isSelected && !$isCorrect) {
                    $boxClass = 'border-danger bg-danger-subtle'; // ពណ៌ក្រហមសម្រាប់ចម្លើយដែលសិស្សរើសខុស
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
                            <span class="text-danger small me-1">អ្នកបានជ្រើសរើស</span>
                            <i class="fa-solid fa-circle-xmark text-danger"></i>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endforeach; ?>
</body>
</html>