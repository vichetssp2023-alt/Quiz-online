<?php
session_start();

// ត្រួតពិនិត្យ Role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: ../../../public/index.php");
    exit();
}

require_once "../../../config/database.php"; 

try {
    $database = new Database();
    $db = $database->getConnection();
    $student_id = $_SESSION['user_id'];
    
    // ទាញយក Class ID ពី Session (ត្រូវប្រាកដថាអ្នកបានដាកវាពេល Login)
    $student_class = $_SESSION['class_id'] ?? ''; 

    // ១. រាប់ចំនួនវិញ្ញាសាដែលត្រូវប្រឡង (យោងតាមថ្នាក់របស់សិស្ស)
    $count_available_stmt = $db->prepare("SELECT COUNT(*) FROM quizzes WHERE status = 'active' AND class_id = ?");
    $count_available_stmt->execute([$student_class]);
    $count_available = $count_available_stmt->fetchColumn();
    
    // ២. រាប់ចំនួនវិញ្ញាសាដែលសិស្សម្នាក់នេះបានប្រឡងរួច
    $count_completed = $db->prepare("SELECT COUNT(*) FROM results WHERE user_id = ?");
    $count_completed->execute([$student_id]);
    $done_quizzes = $count_completed->fetchColumn();
    
    // ៣. រកពិន្ទុមធ្យមភាគ
    $avg_score = $db->prepare("SELECT AVG(score) FROM results WHERE user_id = ?");
    $avg_score->execute([$student_id]);
    $performance = round($avg_score->fetchColumn() ?? 0);

    // ៤. ទាញយកការជូនដំណឹង (Notifications)
    $unread_stmt = $db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $unread_stmt->execute([$student_id]);
    $unread_count = $unread_stmt->fetchColumn();

    $notif_list_stmt = $db->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
    $notif_list_stmt->execute([$student_id]);
    $notifications = $notif_list_stmt->fetchAll(PDO::FETCH_ASSOC);

    // ទាញយកលទ្ធផល ៥ ចុងក្រោយ
    $recent_results = $db->prepare("
        SELECT r.*, q.title 
        FROM results r 
        JOIN quizzes q ON r.quiz_id = q.id 
        WHERE r.user_id = ? 
        ORDER BY r.created_at DESC LIMIT 5
    ");
    $recent_results->execute([$student_id]);
    $results_list = $recent_results->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    // បង្ហាញ Error បើមានបញ្ហា Database
    // echo $e->getMessage(); 
}
?>

<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ផ្ទាំងគ្រប់គ្រងសិស្ស - QuizMaster</title>
    <link rel="stylesheet" href="../../../../node_modules/bootstrap/dist/css/bootstrap.min.css">

    <!-- Line 9 - fix this -->

    <link rel="stylesheet" href="../../../public/fontawesome-free-7.2.0-web/css/all.min.css">
    <link rel="stylesheet" href="../../../public/css/adminstyle/dashboard.css">    
    <style>
        .extra-small { font-size: 0.75rem; }
        .hover-bell:hover { background-color: #f8f9fa !important; }
    </style>
</head>
<body>

<div class="d-flex">
    <?php include 'partials/sidebar.php'; ?>

    <main class="main-content w-100 p-4">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h3 class="fw-bold mb-0 text-dark">ផ្ទាំងគ្រប់គ្រងសិស្ស</h3>
                <p class="text-muted">
                    <?php 
                        // កែសម្រួលការប្រើពាក្យ លោក ឬ អ្នកនាង តាមភេទ
                        $gender = $_SESSION['gender'] ?? 'Male';
                        $prefix = ($gender === 'Female') ? 'អ្នកនាង' : 'លោក'; 
                        echo "សួស្តី, " . $prefix . " " . htmlspecialchars($_SESSION['user'] ?? 'សិស្ស') . "!"; 
                    ?>
                </p>
            </div>
            
            <div class="d-flex align-items-center gap-3">
                <div class="dropdown">
                    <div class="bg-white p-2 rounded-circle shadow-sm px-3 hover-bell position-relative" 
                        id="bellDropdown" 
                        data-bs-toggle="dropdown" 
                        aria-expanded="false" 
                        style="cursor: pointer;">
                        
                        <i class="fa-regular fa-bell"></i>

                        <?php if ($unread_count > 0): ?>
                            <span id="unread-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                                <?= $unread_count ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-4 mt-3" aria-labelledby="bellDropdown" style="width: 300px;">
                        <li class="px-3 py-2 border-bottom">
                            <h6 class="mb-0 fw-bold">ការជូនដំណឹង</h6>
                        </li>
                        
                        <div class="notification-items" style="max-height: 350px; overflow-y: auto;">
                            <?php if (empty($notifications)): ?>
                                <li class="text-center py-4 text-muted small">
                                    <i class="fa-solid fa-bell-slash d-block mb-2 opacity-25" style="font-size: 2rem;"></i>
                                    មិនមានការជូនដំណឹងទេ
                                </li>
                            <?php else: ?>
                                <?php foreach ($notifications as $notif): ?>
                                    <li>
                                        <a class="dropdown-item py-3 border-bottom <?= $notif['is_read'] == 0 ? 'bg-light' : '' ?>" href="available_quizzes.php">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-grow-1">
                                                    <p class="mb-0 small fw-bold text-dark"><?= htmlspecialchars($notif['title']) ?></p>
                                                    <p class="mb-1 text-muted extra-small">
                                                        <?= htmlspecialchars($notif['message']) ?>
                                                    </p>
                                                    <small class="text-primary opacity-75" style="font-size: 0.65rem;">
                                                        <i class="fa-regular fa-clock me-1"></i>
                                                        <?= date('d M, h:i A', strtotime($notif['created_at'])) ?>
                                                    </small>
                                                </div>
                                                <?php if ($notif['is_read'] == 0): ?>
                                                    <div class="ms-2">
                                                        <span class="p-1 bg-primary rounded-circle d-block"></span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <li class="text-center">
                            <a href="available_quizzes.php" class="dropdown-item py-2 small fw-bold text-primary">មើលទាំងអស់</a>
                        </li>
                    </ul>
                </div>

                <a href="student_profile.php" class="text-decoration-none">
                    <div class="bg-white p-2 rounded-pill shadow-sm px-4 fw-bold text-primary hover-profile">
                        <i class="fa-solid fa-graduation-cap me-2"></i>ប្រវត្តិរូប
                    </div>
                </a>
            </div>
        </div>
        
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="card stat-card shadow-sm p-4 border-start border-primary border-5 border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 fw-medium">វិញ្ញាសាត្រូវប្រឡង</p>
                            <h2 class="fw-bold mb-0 text-dark"><?= number_format($count_available) ?></h2>
                        </div>
                        <div class="fs-1 text-primary opacity-25"><i class="fa-solid fa-book-open"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card shadow-sm p-4 border-start border-success border-5 border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 fw-medium">បានបញ្ចប់</p>
                            <h2 class="fw-bold mb-0 text-dark"><?= number_format($done_quizzes) ?></h2>
                        </div>
                        <div class="fs-1 text-success opacity-25"><i class="fa-solid fa-circle-check"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card shadow-sm p-4 border-start border-warning border-5 border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 fw-medium">សមត្ថភាពរួម</p>
                            <h2 class="fw-bold mb-0 text-dark"><?= $performance ?>%</h2>
                        </div>
                        <div class="fs-1 text-warning opacity-25"><i class="fa-solid fa-chart-line"></i></div>
                    </div>
                </div>
            </div>
        </div> 

        <div class="card border-0 shadow-sm rounded-4 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold"><i class="fa-solid fa-history me-2"></i>លទ្ធផលប្រឡងថ្មីៗ</h5>
                <a href="my_results.php" class="btn btn-primary btn-sm rounded-pill px-4">មើលទាំងអស់</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="border-0">វិញ្ញាសា</th>
                            <th class="border-0 text-center">ពិន្ទុ</th>
                            <th class="border-0">កម្រិតមធ្យមភាគ</th>
                            <th class="border-0">កាលបរិច្ឆេទ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($results_list) > 0): ?>
                            <?php foreach ($results_list as $res): 
                                $pct = ($res['score'] > 100) ? 100 : $res['score'];
                            ?>
                            <tr>
                                <td class="fw-bold text-dark"><?= htmlspecialchars($res['title']) ?></td>
                                <td class="text-center">
                                    <span class="badge bg-<?= $pct >= 50 ? 'success' : 'danger' ?>-subtle text-<?= $pct >= 50 ? 'success' : 'danger' ?> px-3">
                                        <?= $res['score'] ?> / 100
                                    </span>
                                </td>
                                <td style="width: 200px;">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress progress-sm flex-grow-1" style="height: 8px;">
                                            <div class="progress-bar bg-<?= $pct >= 50 ? 'primary' : 'danger' ?>" style="width: <?= $pct ?>%"></div>
                                        </div>
                                        <small class="fw-bold"><?= round($pct) ?>%</small>
                                    </div>
                                </td>
                                <td class="text-muted small"><?= date('d M, Y', strtotime($res['created_at'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">មិនទាន់មានទិន្នន័យប្រឡងនៅឡើយទេ</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// មុខងារ Update Notification ទៅជា Read នៅពេលចុចលើ Bell
document.getElementById('bellDropdown').addEventListener('click', function() {
    const badge = document.getElementById('unread-badge');
    if (badge) {
        badge.style.display = 'none';
    }

    // បញ្ជូន Request ទៅកាន់ mark_read.php ដើម្បី Update ក្នុង Database
    fetch('mark_read.php')
        .then(response => response.json())
        .then(data => {
            if(data.status === 'success') {
                console.log('Notifications marked as read');
            }
        })
        .catch(err => console.error('Error:', err));
});
</script>
</body>
</html>