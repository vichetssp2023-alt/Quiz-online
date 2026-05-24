<?php
session_start();
require_once "../../../config/database.php";

$quiz_id = $_GET['id'] ?? null;
$database = new Database();
$db = $database->getConnection();

// ១. ទាញយកព័ត៌មានវិញ្ញាសា
$stmtQuiz = $db->prepare("SELECT * FROM quizzes WHERE id = ?");
$stmtQuiz->execute([$quiz_id]);
$quiz = $stmtQuiz->fetch(PDO::FETCH_ASSOC);

// ២. ទាញយកសំណួរទាំងអស់
$stmtQuestions = $db->prepare("SELECT * FROM questions WHERE quiz_id = ?");
$stmtQuestions->execute([$quiz_id]);
$questions = $stmtQuestions->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ប្រឡង៖ <?= htmlspecialchars($quiz['title']) ?></title>
    <link rel="stylesheet" href="../../../../node_modules/bootstrap/dist/css/bootstrap.min.css">

    <!-- Line 9 - fix this -->
    <script src="../../../../node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="../../../public/fontawesome-free-7.2.0-web/css/all.min.css">
     <link rel="stylesheet" href="../../../public/css/studentstyle/take_quiz.css">
</head>
<body>

<div class="container quiz-container">
    <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded-4 shadow-sm border-start border-primary border-4">
        <div>
            <h4 class="fw-bold text-primary mb-0"><?= htmlspecialchars($quiz['title']) ?></h4>
            <small class="text-muted">សំណួរទី <span id="currentIdx">1</span> / <?= count($questions) ?></small>
        </div>
        <div id="timer" class="timer-box"><?= $quiz['duration'] ?>:00</div>
    </div>

    <form id="quizForm" action="submit_quiz.php" method="POST">
        <input type="hidden" name="quiz_id" value="<?= $quiz_id ?>">

        <?php foreach ($questions as $index => $q): ?>
            <div class="question-card <?= $index === 0 ? 'active' : '' ?>" id="q-<?= $index ?>">
                <h5 class="fw-bold mb-4">សំណួរទី <?= $index + 1 ?>៖ <?= htmlspecialchars($q['question_text']) ?></h5>
                
                <div class="options-container">
                    <?php 
                    $options = [
                        'A' => $q['option_a'], 
                        'B' => $q['option_b'], 
                        'C' => $q['option_c'], 
                        'D' => $q['option_d']
                    ]; 
                    foreach($options as $key => $val): if(!empty($val)): ?>
                        <label class="option-item" for="q<?= $q['id'] ?>_<?= $key ?>">
                            <input type="radio" name="answers[<?= $q['id'] ?>]" value="<?= $key ?>" id="q<?= $q['id'] ?>_<?= $key ?>" required>
                            <span><strong><?= $key ?>.</strong> <?= htmlspecialchars($val) ?></span>
                        </label>
                    <?php endif; endforeach; ?>
                </div>

                <div class="d-flex justify-content-between mt-5">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" onclick="prevQuestion(<?= $index ?>)" <?= $index === 0 ? 'style="visibility:hidden"' : '' ?>>
                        <i class="fa-solid fa-arrow-left me-2"></i> ថយក្រោយ
                    </button>
                    
                    <?php if ($index === count($questions) - 1): ?>
                        <button type="submit" class="btn btn-success rounded-pill px-5 fw-bold shadow">បញ្ជូនចម្លើយ <i class="fa-solid fa-check-double ms-2"></i></button>
                    <?php else: ?>
                        <button type="button" class="btn btn-primary rounded-pill px-5 fw-bold shadow" onclick="nextQuestion(<?= $index ?>)">
                            បន្ទាប់ <i class="fa-solid fa-arrow-right ms-2"></i>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </form>
</div>

<script>
    function nextQuestion(index) {
        // ពិនិត្យថាបានជ្រើសរើសចម្លើយឬនៅ
        const currentCard = document.getElementById(`q-${index}`);
        if (!currentCard.querySelector('input[type="radio"]:checked')) {
            alert("សូមជ្រើសរើសចម្លើយមួយសិន!"); return;
        }
        currentCard.classList.remove('active');
        document.getElementById(`q-${index + 1}`).classList.add('active');
        document.getElementById('currentIdx').innerText = index + 2;
    }

    function prevQuestion(index) {
        document.getElementById(`q-${index}`).classList.remove('active');
        document.getElementById(`q-${index - 1}`).classList.add('active');
        document.getElementById('currentIdx').innerText = index;
    }

    // Timer Logic
    let timeLeft = <?= $quiz['duration'] ?> * 60;
    setInterval(() => {
        let m = Math.floor(timeLeft / 60);
        let s = timeLeft % 60;
        document.getElementById('timer').innerText = `${m}:${s < 10 ? '0' : ''}${s}`;
        if (timeLeft <= 0) document.getElementById('quizForm').submit();
        timeLeft--;
    }, 1000);
    
</script>

</body>
</html>