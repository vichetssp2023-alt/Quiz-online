<?php
session_start();
require_once __DIR__ . "/../../../config/database.php"; 

$database = new Database();
$conn = $database->getConnection();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'] ?? 'teacher'; 
$class_filter = isset($_GET['class_id']) ? (int)$_GET['class_id'] : null; 
$quizzes = [];

try {
    // 1. Fetch classes for filter (Common for both)
    $stmt_classes = $conn->query("SELECT * FROM classes WHERE status = 'active'");
    $classes = $stmt_classes->fetchAll(PDO::FETCH_ASSOC);

    // 2. Logic for Teacher vs Student
    if ($user_role === 'teacher') {
        // Teachers see all quizzes they created, including attempts_limit configuration
        $query = "SELECT q.*, c.class_name 
                  FROM quizzes q 
                  LEFT JOIN classes c ON q.class_id = c.id 
                  WHERE q.teacher_id = ?";
        
        $params = [$user_id];
        if ($class_filter) {
            $query .= " AND q.class_id = ?";
            $params[] = $class_filter;
        }
        $query .= " ORDER BY q.id DESC";
        $stmt = $conn->prepare($query);
        $stmt->execute($params);
    } else {
        // For Students: We fetch the quiz AND count how many times this specific student has taken it
        // This allows us to show "Attempts: 1/3" on the frontend.
        $query = "SELECT q.*, c.class_name, 
                  (SELECT COUNT(*) FROM results r WHERE r.quiz_id = q.id AND r.user_id = ?) as used_attempts
                  FROM quizzes q 
                  JOIN classes c ON q.class_id = c.id 
                  JOIN users u ON u.class_id = q.class_id
                  WHERE u.id = ? 
                  AND q.status IN ('active', 'inactive') 
                  ORDER BY q.id DESC";
                  
        $stmt = $conn->prepare($query);
        // We pass user_id twice: once for the subquery count and once for the WHERE clause
        $stmt->execute([$user_id, $user_id]);
    }
    
    $quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>គ្រប់គ្រងវិញ្ញាសា | QuizMaster</title>
    <link rel="stylesheet" href="../../../../node_modules/bootstrap/dist/css/bootstrap.min.css">

    <!-- Line 9 - fix this -->
    <script src="../../../../node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="../../../public/fontawesome-free-7.2.0-web/css/all.min.css">
    <link rel="stylesheet" href="../../../public/css/teacherstyle/quiz.css"> 
</head>
<body>
    <?php include 'partials/sidebar.php'; ?>

    <main class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-dark">គ្រប់គ្រងវិញ្ញាសា</h3>
                <p class="text-muted mb-0">បង្កើត និងកែសម្រួលវិញ្ញាសារបស់អ្នក</p>
            </div>
            <?php if($user_role === 'teacher'): ?>
            <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addQuizModal">
                <i class="fas fa-plus-circle me-2"></i>បង្កើតវិញ្ញាសាថ្មី
            </button>
            <?php endif; ?>
        </div>

        <div class="mb-4 mt-3">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="fw-bold me-2"><i class="fas fa-filter text-primary me-1"></i> ចម្រាញ់តាមថ្នាក់:</span>
                <a href="manage_quiz.php" class="btn btn-sm rounded-pill px-4 <?= !$class_filter ? 'btn-primary' : 'btn-outline-primary' ?>">ទាំងអស់</a>
                <?php foreach ($classes as $class): ?>
                    <a href="manage_quiz.php?class_id=<?= $class['id'] ?>" class="btn btn-sm rounded-pill px-4 <?= ($class_filter == $class['id']) ? 'btn-primary' : 'btn-outline-primary' ?>">
                        <?= htmlspecialchars($class['class_name']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="custom-card shadow-sm bg-white">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">ឈ្មោះវិញ្ញាសា</th>
                            <th>រយៈពេល/ចំនួនដង</th> <th>ថ្នាក់</th>
                            <th>ថ្ងៃបើក - ផុតកំណត់</th>
                            <th>ស្ថានភាព</th>
                            <th>ថ្ងៃបង្កើត</th>
                            <th class="text-end pe-4">សកម្មភាព</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($quizzes)): ?>
                            <tr><td colspan="7" class="text-center py-5 text-muted">មិនទាន់មានទិន្នន័យនៅឡើយទេ</td></tr>
                        <?php else: foreach($quizzes as $quiz): 
                            $status = $quiz['status']; 
                        ?>
                        <tr>
                            <td class="ps-4 fw-bold text-primary"><?= htmlspecialchars($quiz['title']) ?></td>
                            <td>
                                <div class="small">
                                    <span class="badge bg-light text-dark border px-2 py-1 mb-1 d-block"><i class="fas fa-stopwatch me-1"></i> <?= $quiz['duration'] ?> នាទី</span>
                                    <span class="badge bg-secondary-subtle text-secondary border px-2 py-1 d-block"><i class="fas fa-redo me-1"></i> <?= $quiz['attempts_limit'] ?? 1 ?> ដង</span>
                                </div>
                            </td>
                            <td><span class="badge bg-info-subtle text-info rounded-pill px-3"><?= htmlspecialchars($quiz['class_name'] ?? 'N/A') ?></span></td>
                            <td>
                                <div class="small">
                                    <span class="text-success"><i class="fas fa-door-open me-1"></i> <?= ($quiz['open_date']) ? date('d/m/Y H:i', strtotime($quiz['open_date'])) : 'N/A' ?></span><br>
                                    <span class="text-danger"><i class="fas fa-clock me-1"></i> <?= ($quiz['due_date']) ? date('d/m/Y H:i', strtotime($quiz['due_date'])) : 'N/A' ?></span>
                                </div>
                            </td>
                            <td>
                                <?php 
                                    switch($status) {
                                        case 'active':
                                            echo '<span class="badge bg-success-subtle text-success rounded-pill px-3"><i class="fas fa-check-circle me-1"></i> Active</span>';
                                            break;
                                        case 'overdue':
                                            echo '<span class="badge bg-danger text-white rounded-pill px-3"><i class="fas fa-calendar-times me-1"></i> Overdue</span>';
                                            break;
                                        case 'not_open':
                                            echo '<span class="badge bg-warning text-dark rounded-pill px-3"><i class="fas fa-lock me-1"></i> Not Open</span>';
                                            break;
                                        case 'inactive':
                                            echo '<span class="badge bg-secondary text-white rounded-pill px-3"><i class="fas fa-times-circle me-1"></i> Closed</span>';
                                            break;
                                        default:
                                            echo '<span class="badge bg-secondary-subtle text-secondary rounded-pill px-3">' . ucfirst($status) . '</span>';
                                    }
                                ?>
                            </td>
                            <td class="text-muted small"><?= date('d-M-Y', strtotime($quiz['created_at'])) ?></td>
                            <td class="text-end pe-4">
                                <div class="btn-group shadow-sm bg-white rounded-pill border overflow-hidden">
                                    <button class="btn btn-sm btn-view text-info px-3 border-end" data-bs-toggle="modal" data-bs-target="#viewModal<?= $quiz['id'] ?>">
                                        <i class="fas fa-eye"></i>
                                    </button>

                                    <?php if($user_role === 'teacher'): ?>
                                        <button class="btn btn-sm btn-view text-warning px-3 border-end" data-bs-toggle="modal" data-bs-target="#editModal<?= $quiz['id'] ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="../../Controllers/QuizController.php?delete_id=<?= $quiz['id'] ?>" class="btn btn-sm btn-view text-danger px-3" onclick="return confirm('តើអ្នកប្រាកដថាចង់លុបមែនទេ?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    <?php else: ?>
                                        <?php 
                                            $is_expired = strtotime($quiz['due_date']) <= time();
                                            $is_active = ($quiz['status'] === 'active' && !$is_expired);
                                        ?>
                                        <?php if ($is_active): ?>
                                            <a href="take_quiz.php?id=<?= $quiz['id'] ?>" class="btn btn-sm btn-view text-success px-3">
                                                <i class="fas fa-play"></i>
                                            </a>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-view text-muted px-3" disabled title="ហួសពេលកំណត់">
                                                <i class="fas fa-lock"></i>
                                            </button>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <div class="modal fade" id="addQuizModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title fw-bold">បង្កើតវិញ្ញាសាថ្មី</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="../../Controllers/QuizController.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold">ចំណងជើងវិញ្ញាសា</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">ការពិពណ៌នា (Description)</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="បញ្ចូលព័ត៌មានលម្អិត..."></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">ថ្ងៃបើក (Open Date)</label>
                                <input type="datetime-local" name="open_date" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">ថ្ងៃផុតកំណត់ (Due Date)</label>
                                <input type="datetime-local" name="due_date" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">ជ្រើសរើសថ្នាក់</label>
                            <select name="class_id" class="form-select" required>
                                <option value="">--- ជ្រើសរើសថ្នាក់ ---</option>
                                <?php foreach ($classes as $class): ?>
                                    <option value="<?= $class['id'] ?>"><?= htmlspecialchars($class['class_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">រយៈពេល (នាទី)</label>
                                <input type="number" name="duration" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">ចំនួនដងប្រឡង</label>
                                <input type="number" name="attempts_limit" class="form-control" value="1" min="1" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">ឯកសារវិញ្ញាសា</label>
                            <input type="file" name="quiz_file" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="submit" name="save_quiz" class="btn btn-primary rounded-pill px-4">បង្កើត</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php foreach($quizzes as $quiz): ?>
        <div class="modal fade" id="viewModal<?= $quiz['id'] ?>" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 rounded-4">
                    <div class="modal-header bg-info text-white border-0">
                        <h5 class="modal-title fw-bold">ព័ត៌មានវិញ្ញាសា</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <h4 class="fw-bold text-center"><?= htmlspecialchars($quiz['title']) ?></h4>
                        <p class="text-center text-muted">ថ្នាក់៖ <?= htmlspecialchars($quiz['class_name'] ?? 'N/A') ?></p>
                        <hr>
                        <div class="mb-3">
                            <label class="fw-bold text-primary">ការពិពណ៌នា៖</label>
                            <p class="bg-light p-3 rounded shadow-sm"><?= nl2br(htmlspecialchars($quiz['description'] ?? 'មិនមានការពិពណ៌នាឡើយ')) ?></p>
                        </div>
                        <div class="row text-center">
                            <div class="col-4 border-end"><strong>រយៈពេល</strong><br><?= $quiz['duration'] ?> នាទី</div>
                            <div class="col-4 border-end"><strong>ចំនួនដង</strong><br><?= $quiz['attempts_limit'] ?? 1 ?> ដង</div>
                            <div class="col-4"><strong>ស្ថានភាព</strong><br><span class="badge bg-success"><?= $quiz['status'] ?></span></div>
                        </div>
                        <?php if($user_role === 'teacher'): ?>
                        <div class="mt-4">
                            <a href="manage_questions.php?quiz_id=<?= $quiz['id'] ?>" class="btn btn-success w-100 rounded-pill shadow">គ្រប់គ្រងសំណួរ</a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="editModal<?= $quiz['id'] ?>" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 rounded-4">
                    <div class="modal-header bg-warning text-dark border-0">
                        <h5 class="modal-title fw-bold">កែសម្រួលវិញ្ញាសា</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="../../Controllers/QuizController.php" method="POST" enctype="multipart/form-data">
                        <div class="modal-body p-4">
                            <input type="hidden" name="quiz_id" value="<?= $quiz['id'] ?>">
                            <div class="mb-3">
                                <label class="form-label fw-bold">ចំណងជើង</label>
                                <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($quiz['title']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">ការពិពណ៌នា (Description)</label>
                                <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($quiz['description'] ?? '') ?></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">ថ្ងៃបើក</label>
                                    <input type="datetime-local" name="open_date" class="form-control" value="<?= date('Y-m-d\TH:i', strtotime($quiz['open_date'])) ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">ថ្ងៃផុតកំណត់</label>
                                    <input type="datetime-local" name="due_date" class="form-control" value="<?= date('Y-m-d\TH:i', strtotime($quiz['due_date'])) ?>" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">ជ្រើសរើសថ្នាក់</label>
                                <select name="class_id" class="form-select" required>
                                    <?php foreach ($classes as $class): ?>
                                        <option value="<?= $class['id'] ?>" <?= ($quiz['class_id'] == $class['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($class['class_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">រយៈពេល (នាទី)</label>
                                    <input type="number" name="duration" class="form-control" value="<?= $quiz['duration'] ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">ចំនួនដងប្រឡង</label>
                                    <input type="number" name="attempts_limit" class="form-control" value="<?= $quiz['attempts_limit'] ?? 1 ?>" min="1" required>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-0">
                            <button type="submit" name="update_quiz" class="btn btn-warning rounded-pill px-4">រក្សាទុកការកែប្រែ</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>