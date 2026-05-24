<?php
session_start();
require_once "../../../config/database.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $database = new Database();
    $db = $database->getConnection();

    $quiz_id = $_POST['quiz_id'];
    $user_id = $_SESSION['user_id'];
    $user_answers = $_POST['answers'] ?? []; 
    
    // ១. ទាញយកសំណួរទាំងអស់
    $stmt = $db->prepare("SELECT * FROM questions WHERE quiz_id = ?");
    $stmt->execute([$quiz_id]);
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $total_questions = count($questions);
    $correct_count = 0;
    $review_data = [];

    foreach ($questions as $q) {
        $q_id = $q['id'];
        $submitted = $user_answers[$q_id] ?? null;
        $correct = $q['correct_option'];
        
        $is_correct = ($submitted === $correct);
        if ($is_correct) $correct_count++;

        $review_data[] = [
            'question' => $q['question_text'],
            'submitted' => $submitted,
            'correct' => $correct,
            'is_correct' => $is_correct,
            'options' => [
                'A' => $q['option_a'],
                'B' => $q['option_b'],
                'C' => $q['option_c'],
                'D' => $q['option_d']
            ]
        ];
    }

    $score_percent = ($total_questions > 0) ? ($correct_count / $total_questions) * 100 : 0;
    $status = ($score_percent >= 50) ? 'Passed' : 'Failed';

    try {
        $db->beginTransaction();

        // ២.១ រក្សាទុកក្នុង results
        $sqlInsertResult = "INSERT INTO results (user_id, quiz_id, score, total_questions, status, created_at) 
                            VALUES (?, ?, ?, ?, ?, NOW())";
        $stmtSave = $db->prepare($sqlInsertResult);
        $stmtSave->execute([$user_id, $quiz_id, $score_percent, $total_questions, $status]);
        
        $result_id = $db->lastInsertId();

        // ២.២ រក្សាទុកក្នុង student_answers
        $sqlInsertAnswer = "INSERT INTO student_answers (result_id, question_id, selected_option) 
                            VALUES (?, ?, ?)";
        $stmtAnswer = $db->prepare($sqlInsertAnswer);

        foreach ($questions as $q) {
            $stmtAnswer->execute([$result_id, $q['id'], $user_answers[$q['id']] ?? null]);
        }

        // --- បន្ថែមថ្មី៖ ផ្ញើ Notification ទៅគ្រូ ---
        
        // ក. ទាញយក teacher_id និងចំណងជើងវិញ្ញាសា និងឈ្មោះសិស្ស
        $infoStmt = $db->prepare("SELECT q.teacher_id, q.title, u.full_name 
                                  FROM quizzes q 
                                  JOIN users u ON u.id = ? 
                                  WHERE q.id = ?");
        $infoStmt->execute([$user_id, $quiz_id]);
        $info = $infoStmt->fetch(PDO::FETCH_ASSOC);

        if ($info) {
            $teacher_id = $info['teacher_id'];
            $quiz_title = $info['title'];
            $student_name = $info['full_name'];
            $notif_title = "សិស្សបានបញ្ជូនកិច្ចការ";
            $notif_msg = "សិស្ស $student_name បានបញ្ចប់វិញ្ញាសា '$quiz_title' ជាមួយពិន្ទុ " . round($score_percent) . "%។";

            // ខ. បញ្ចូលទៅក្នុង Table notifications
            $notifSql = "INSERT INTO notifications (user_id, title, message, is_read, created_at) 
                         VALUES (?, ?, ?, 0, NOW())";
            $db->prepare($notifSql)->execute([$teacher_id, $notif_title, $notif_msg]);
        }
        // --------------------------------------

        $db->commit(); 
    } catch (PDOException $e) {
        $db->rollBack();
        error_log("Database Error: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <title>លទ្ធផល និងការពិនិត្យឡើងវិញ</title>
    <link rel="stylesheet" href="../../../../node_modules/bootstrap/dist/css/bootstrap.min.css">

    <!-- Line 9 - fix this -->
    <script src="../../../../node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="../../../public/fontawesome-free-7.2.0-web/css/all.min.css">
    <link rel="stylesheet" href="../../../public/css/studentstyle/take_quiz.css">
</head>
<body>
<div class="container">
    <div class="result-card shadow-sm">
        <h2 class="fw-bold text-primary mb-3">លទ្ធផលនៃការប្រឡង</h2>
        <div class="display-2 fw-bold <?= $score_percent >= 50 ? 'text-success' : 'text-danger' ?>">
            <?= round($score_percent) ?>%
        </div>
        <p class="text-muted fs-5 mt-2">អ្នកឆ្លើយត្រូវ <?= $correct_count ?> ក្នុងចំណោម <?= $total_questions ?> សំណួរ</p>
        
        <div class="mt-4">
            <a href="available_quizzes.php" class="btn btn-primary rounded-pill px-5 fw-bold">រួចរាល់</a>
        </div>
    </div>

    <div class="review-section">
        <h4 class="fw-bold mb-4 text-dark"><i class="fa-solid fa-list-check me-2"></i> ពិនិត្យចម្លើយឡើងវិញ</h4>
        
        <?php foreach ($review_data as $index => $review): ?>
            <div class="review-item shadow-sm <?= $review['is_correct'] ? 'correct' : 'wrong' ?>">
                <div class="d-flex justify-content-between align-items-start">
                    <h6 class="fw-bold text-dark">
                        សំណួរទី <?= ($index + 1) ?>៖ <?= htmlspecialchars($review['question']) ?>
                    </h6>
                    <span>
                        <?php if ($review['is_correct']): ?>
                            <i class="fa-solid fa-circle-check text-success status-icon"></i>
                        <?php else: ?>
                            <i class="fa-solid fa-circle-xmark text-danger status-icon"></i>
                        <?php endif; ?>
                    </span>
                </div>

                <div class="mt-3 small">
                    <div class="mb-1">
                        <span class="text-muted">ចម្លើយរបស់អ្នក៖ </span>
                        <span class="<?= $review['is_correct'] ? 'correct-text' : 'wrong-text' ?>">
                            <?= $review['submitted'] ? $review['submitted'] . ". " . htmlspecialchars($review['options'][$review['submitted']]) : "មិនបានឆ្លើយ" ?>
                        </span>
                    </div>
                    
                    <?php if (!$review['is_correct']): ?>
                        <div class="mt-1">
                            <span class="text-muted">ចម្លើយត្រឹមត្រូវគឺ៖ </span>
                            <span class="correct-text">
                                <?= $review['correct'] ?>. <?= htmlspecialchars($review['options'][$review['correct']]) ?>
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

</body>
</html>