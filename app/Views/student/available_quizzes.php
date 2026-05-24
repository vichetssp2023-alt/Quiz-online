<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: ../../../public/index.php");
    exit();
}

require_once "../../../config/database.php"; 

try {
    $database = new Database();
    $db = $database->getConnection();
    $student_id = $_SESSION['user_id'];

    // ១. ទាញយក class_id របស់សិស្ស
    $stmt_user = $db->prepare("SELECT class_id FROM users WHERE id = ?");
    $stmt_user->execute([$student_id]);
    $user_data = $stmt_user->fetch(PDO::FETCH_ASSOC);
    $class_id = $user_data['class_id'] ?? null;

    // ២. កែសម្រួល Query៖ បន្ថែម subquery ដើម្បីរាប់ចំនួនដងដែលសិស្សបានធ្វើ (used_attempts)
    $query = "SELECT q.*, u.username as teacher_name, 
            (SELECT COUNT(*) FROM questions WHERE quiz_id = q.id) as actual_question_count,
            (SELECT COUNT(*) FROM results WHERE quiz_id = q.id AND user_id = ?) as used_attempts
            FROM quizzes q 
            LEFT JOIN users u ON q.teacher_id = u.id 
            WHERE q.class_id = ? 
            AND q.open_date <= NOW() 
            ORDER BY q.id DESC";
            
    $stmt = $db->prepare($query);
    // បញ្ជូន student_id សម្រាប់ subquery និង class_id សម្រាប់ filter
    $stmt->execute([$student_id, $class_id]);
    $quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>វិញ្ញាសាប្រឡង - QuizMaster</title>
    <link rel="stylesheet" href="../../../../node_modules/bootstrap/dist/css/bootstrap.min.css">

    <!-- Line 9 - fix this -->
    <script src="../../../../node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="../../../public/fontawesome-free-7.2.0-web/css/all.min.css">
    <link rel="stylesheet" href="../../../public/css/studentstyle/available.css">
    <style>
        .attempt-badge { font-size: 0.75rem; padding: 4px 8px; border-radius: 12px; }
    </style>
</head>
<body>

<div class="d-flex">
    <?php include 'partials/sidebar.php'; ?>

    <main class="main-content w-100 p-4">
        <div class="mb-5">
            <h3 class="fw-bold">វិញ្ញាសាសម្រាប់អ្នក ✍️</h3>
            <p class="text-muted">ជ្រើសរើសវិញ្ញាសាខាងក្រោមដើម្បីសាកល្បងសមត្ថភាព</p>
        </div>

        <div class="row g-4">
            <?php if (!empty($quizzes)): ?>
                <?php foreach ($quizzes as $quiz): 
                    $is_overdue = strtotime($quiz['due_date']) < time();
                    $is_active = ($quiz['status'] === 'active');
                    
                    // ពិនិត្យមើលចំនួនដងដែលអនុញ្ញាត (Attempts Limit)
                    $attempts_left = $quiz['attempts_limit'] - $quiz['used_attempts'];
                    $has_attempts = ($attempts_left > 0);
                    
                    // លក្ខខណ្ឌសម្រាប់អនុញ្ញាត៖ Active + មិនទាន់ហួសពេល + នៅសល់កូតា
                    $can_take_quiz = ($is_active && !$is_overdue && $has_attempts);
                ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card quiz-card p-4 h-100 shadow-sm <?= !$can_take_quiz ? 'opacity-75 bg-light' : '' ?>">
                            <div class="mb-3 d-flex justify-content-between align-items-start">
                                <div class="teacher-info d-flex align-items-center">
                                    <div class="avatar-sm me-2">
                                        <i class="fa-solid fa-user-tie text-primary"></i>
                                    </div>
                                    <span class="teacher-text" style="font-size: 0.85rem;">
                                        គ្រូ៖ <strong class="text-dark"><?= htmlspecialchars($quiz['teacher_name'] ?? 'គ្រូជំនួយ') ?></strong>
                                    </span>
                                </div>
                                
                                <div class="d-flex flex-column align-items-end gap-1">
                                    <?php if (!$is_active): ?>
                                        <span class="badge bg-secondary">Inactive</span>
                                    <?php elseif ($is_overdue): ?>
                                        <span class="badge bg-danger">Overdue</span>
                                    <?php elseif (!$has_attempts): ?>
                                        <span class="badge bg-warning text-dark">Completed</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php endif; ?>
                                    
                                    <span class="attempt-badge border <?= $has_attempts ? 'text-primary border-primary' : 'text-danger border-danger' ?>">
                                        <i class="fa-solid fa-redo"></i> <?= $quiz['used_attempts'] ?> / <?= $quiz['attempts_limit'] ?>
                                    </span>
                                </div>
                            </div>

                            <h5 class="fw-bold mb-2 text-dark"><?= htmlspecialchars($quiz['title']) ?></h5>
                            
                            <div class="date-info mb-3 small">
                                <div class="text-muted">
                                    <i class="fa-regular fa-calendar-check me-1"></i> បើក៖ <?= date('d/M/Y H:i', strtotime($quiz['open_date'])) ?>
                                </div>
                                <div class="<?= $is_overdue ? 'text-danger fw-bold' : 'text-muted' ?>">
                                    <i class="fa-regular fa-calendar-xmark me-1"></i> បិទ៖ <?= date('d/M/Y H:i', strtotime($quiz['due_date'])) ?>
                                </div>
                            </div>

                            <div class="row g-2 mb-4">
                                <div class="col-4">
                                    <div class="info-box text-center p-2 bg-white border rounded">
                                        <span class="d-block small text-muted">នាទី</span>
                                        <span class="text-primary fw-bold"><?= $quiz['duration'] ?></span>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="info-box text-center p-2 bg-white border rounded">
                                        <span class="d-block small text-muted">សំណួរ</span>
                                        <span class="text-warning fw-bold"><?= $quiz['actual_question_count'] ?></span>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="info-box text-center p-2 bg-white border rounded">
                                        <span class="d-block small text-muted">ពិន្ទុ</span>
                                        <span class="text-success fw-bold"><?= $quiz['total_score'] ?></span>
                                    </div>
                                </div>
                            </div>

                            <?php if ($can_take_quiz): ?>
                                <a href="take_quiz.php?id=<?= $quiz['id'] ?>" class="btn btn-primary w-100 rounded-pill py-2">
                                    ចាប់ផ្តើមប្រឡង <i class="fa-solid fa-circle-play ms-2"></i>
                                </a>
                            <?php else: ?>
                                <button class="btn btn-secondary w-100 rounded-pill py-2" disabled>
                                    <?php 
                                        if (!$is_active) echo 'ផ្អាកជាបណ្តោះអាសន្ន';
                                        elseif ($is_overdue) echo 'ហួសពេលកំណត់';
                                        elseif (!$has_attempts) echo 'អស់ចំនួនដងអនុញ្ញាត';
                                    ?> 
                                    <i class="fa-solid fa-lock ms-2"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <p class="text-muted">មិនទាន់មានវិញ្ញាសាទេ</p>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

</body>
</html>