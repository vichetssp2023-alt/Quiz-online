<?php
session_start();
require_once "../../../config/database.php"; 

$database = new Database();
$db = $database->getConnection();

$current_teacher_id = $_SESSION['user_id'] ?? 0; 
$quiz_id = $_GET['quiz_id'] ?? '';

// ១. ទាញទិន្នន័យ Quiz ឬបញ្ជី Folder
if (!empty($quiz_id)) {
    $quiz_stmt = $db->prepare("SELECT title FROM quizzes WHERE id = ? AND teacher_id = ?");
    $quiz_stmt->execute([$quiz_id, $current_teacher_id]);
    $quiz = $quiz_stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($quiz) {
        $quiz_title = htmlspecialchars($quiz['title']);
        $q_stmt = $db->prepare("SELECT * FROM questions WHERE quiz_id = ? ORDER BY id DESC");
        $q_stmt->execute([$quiz_id]);
        $questions = $q_stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        header("Location: manage_questions.php?error=unauthorized");
        exit();
    }
} else {
    // Join with classes table to get the class_name
    $quiz_list_stmt = $db->prepare("
        SELECT qz.*, c.class_name, 
        (SELECT COUNT(*) FROM questions WHERE quiz_id = qz.id) as q_count 
        FROM quizzes qz 
        LEFT JOIN classes c ON qz.class_id = c.id 
        WHERE qz.teacher_id = ? 
        ORDER BY qz.id DESC
    ");
    $quiz_list_stmt->execute([$current_teacher_id]);
    $quizzes = $quiz_list_stmt->fetchAll(PDO::FETCH_ASSOC);
    $quiz_title = "ថតវិញ្ញាសាទាំងអស់";
}
?>
<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>គ្រប់គ្រងសំណួរ - <?= $quiz_title ?></title>
    
    <link rel="stylesheet" href="../../../../node_modules/bootstrap/dist/css/bootstrap.min.css">

    <!-- Line 9 - fix this -->
    <script src="../../../../node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="../../../public/fontawesome-free-7.2.0-web/css/all.min.css">
    <link rel="stylesheet" href="../../../public/css/teacherstyle/question.css">
</head>
<body class="bg-light">
    <?php include 'partials/sidebar.php'; ?>

    <div class="main-content">
        <div class="mb-4 d-flex justify-content-between align-items-center">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="manage_questions.php" class="text-decoration-none text-primary">វិញ្ញាសា</a></li>
                        <?php if(!empty($quiz_id)): ?>
                            <li class="breadcrumb-item active"><?= $quiz_title ?></li>
                        <?php endif; ?>
                    </ol>
                </nav>
                <h2 class="fw-bold m-0">
                    <i class="fas <?= empty($quiz_id) ? 'fa-folder-tree' : 'fa-folder-open' ?> text-primary me-2"></i>
                    <?= $quiz_title ?>
                </h2>
            </div>
            <?php if(!empty($quiz_id)): ?>
                <a href="manage_questions.php" class="btn btn-white shadow-sm rounded-pill px-4">
                    <i class="fas fa-arrow-left me-2"></i>ត្រឡប់ក្រោយ
                </a>
            <?php endif; ?>
        </div>

        <?php if(empty($quiz_id)): ?>
            <div class="row g-4">
                <?php foreach($quizzes as $qz): ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <a href="?quiz_id=<?= $qz['id'] ?>" class="card h-100 folder-card text-center p-4 shadow-sm text-decoration-none border-0">
                        <div class="folder-icon mb-3">
                            <i class="fas fa-folder fa-5x text-warning"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-1"><?= htmlspecialchars($qz['title']) ?></h6>
                        
                        <div class="small text-primary mb-2">
                            <i class="fas fa-users me-1"></i> 
                            <?= !empty($qz['class_name']) ? htmlspecialchars($qz['class_name']) : 'គ្មានថ្នាក់' ?>
                        </div>

                        <span class="badge bg-light text-muted border px-3 rounded-pill">
                            <?= $qz['q_count'] ?> សំណួរ
                        </span>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>

        <?php else: ?>
            <div class="row g-4">
                <div class="col-xl-4">
                    <div class="custom-card p-4 sticky-top" style="top: 30px; z-index: 10;">
                        <h5 class="fw-bold mb-4 text-primary">បន្ថែមសំណួរថ្មី</h5>
                        <form action="../../Controllers/QuestionController.php" method="POST">
                            <input type="hidden" name="quiz_id" value="<?= $quiz_id ?>">
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted text-uppercase">ប្រធានសំណួរ</label>
                                <textarea name="question_text" class="form-control border-light-subtle bg-light" rows="3" required placeholder="បញ្ចូលសំណួររបស់អ្នក..."></textarea>
                            </div>
                            <div class="row g-2 mb-3">
                                <?php foreach(['a', 'b', 'c', 'd'] as $opt): ?>
                                <div class="col-6">
                                    <label class="form-label small fw-bold text-muted">ជម្រើស <?= strtoupper($opt) ?></label>
                                    <input type="text" name="option_<?= $opt ?>" class="form-control border-light-subtle bg-light" required>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="mb-4">
                                <label class="form-label small fw-bold text-muted text-uppercase">ចម្លើយត្រឹមត្រូវ</label>
                                <select name="correct_option" class="form-select border-light-subtle bg-light">
                                    <option value="A">A</option><option value="B">B</option>
                                    <option value="C">C</option><option value="D">D</option>
                                </select>
                            </div>
                            <button type="submit" name="save_question" class="btn btn-primary w-100 rounded-pill py-2 fw-bold shadow-sm">
                                <i class="fas fa-save me-2"></i>រក្សាទុកសំណួរ
                            </button>
                        </form>
                    </div>
                </div>

                <div class="col-xl-8">
                    <div class="custom-card overflow-hidden">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4 col-text">ខ្លឹមសារសំណួរ</th>
                                        <th class="text-center col-ans">ចម្លើយ</th>
                                        <th class="text-end pe-4 col-action">សកម្មភាព</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($questions)): ?>
                                        <tr><td colspan="3" class="text-center py-5 text-muted">មិនទាន់មានសំណួរនៅឡើយទេ</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($questions as $q): ?>
                                        <tr>
                                            <td class="ps-4">
                                                <div class="fw-semibold text-dark text-truncate" style="max-width: 100%;"><?= htmlspecialchars($q['question_text']) ?></div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-3"><?= $q['correct_option'] ?></span>
                                            </td>
                                            <td class="text-end pe-4">
                                                <button class="btn btn-sm btn-white border shadow-sm rounded-circle me-1" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editModal<?= $q['id'] ?>" title="កែប្រែ">
                                                    <i class="fas fa-edit text-warning"></i>
                                                </button>
                                                <a href="../../Controllers/QuestionController.php?action=delete&id=<?= $q['id'] ?>&quiz_id=<?= $quiz_id ?>" 
                                                   class="btn btn-sm btn-white border shadow-sm rounded-circle" 
                                                   onclick="return confirm('តើអ្នកពិតជាចង់លុបសំណួរនេះមែនទេ?')" title="លុប">
                                                    <i class="fas fa-trash text-danger"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

        <?php if(!empty($quiz_id)): ?>
        <?php foreach ($questions as $q): ?>
        <div class="modal fade" id="editModal<?= $q['id'] ?>" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg"> <form action="../../Controllers/QuestionController.php" method="POST">
                        <div class="modal-header border-0 pb-0">
                            <h5 class="modal-title fw-bold text-primary">កែប្រែសំណួរ</h5>
                            <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-4">
                            <input type="hidden" name="question_id" value="<?= $q['id'] ?>">
                            <input type="hidden" name="quiz_id" value="<?= $quiz_id ?>">
                            
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">ប្រធានសំណួរ</label>
                                <textarea name="question_text" class="form-control bg-light border-0" rows="3" required><?= htmlspecialchars($q['question_text']) ?></textarea>
                            </div>
                            <div class="row g-3 mb-4">
                                <?php 
                                $opts = ['a' => $q['option_a'], 'b' => $q['option_b'], 'c' => $q['option_c'], 'd' => $q['option_d']];
                                foreach($opts as $key => $val): ?>
                                <div class="col-12 col-md-6"> <label class="small fw-bold text-muted">ជម្រើស <?= strtoupper($key) ?></label>
                                    <textarea name="option_<?= $key ?>" 
                                            class="form-control bg-light border-0" 
                                            rows="2" 
                                            style="resize: none; font-size: 0.9rem; border-radius: 10px;" 
                                            required><?= htmlspecialchars($val) ?></textarea>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <div>
                                <label class="form-label small fw-bold text-muted">ចម្លើយត្រឹមត្រូវ</label>
                                <select name="correct_option" class="form-select bg-light border-0">
                                    <?php foreach(['A','B','C','D'] as $ans): ?>
                                        <option value="<?= $ans ?>" <?= $q['correct_option'] == $ans ? 'selected' : '' ?>><?= $ans ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer border-0 pt-0">
                            <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">បោះបង់</button>
                            <button type="submit" name="update_question" class="btn btn-primary rounded-pill px-4 shadow-sm">រក្សាទុកការកែប្រែ</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.body.classList.add('loaded');
        });
    </script>
</body>
</html>