<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../../../public/index.php");
    exit();
}

require_once "../../../config/database.php"; 

try {
    $database = new Database();
    $db = $database->getConnection();
    $teacher_id = $_SESSION['user_id'];

    // ១. ទាញយកព័ត៌មាន Profile
    $stmt_profile = $db->prepare("SELECT * FROM users WHERE id = :id");
    $stmt_profile->execute(['id' => $teacher_id]);
    $user_data = $stmt_profile->fetch(PDO::FETCH_ASSOC);

    // ២. រាប់ចំនួនទិន្នន័យសម្រាប់ Stat Cards (Table quizzes)
    $stmt1 = $db->prepare("SELECT COUNT(*) FROM quizzes WHERE teacher_id = :tid");
    $stmt1->execute(['tid' => $teacher_id]);
    $count_quizzes = $stmt1->fetchColumn();
    
    $stmt2 = $db->prepare("SELECT COUNT(DISTINCT r.user_id) FROM results r 
                           INNER JOIN quizzes q ON r.quiz_id = q.id 
                           WHERE q.teacher_id = :tid");
    $stmt2->execute(['tid' => $teacher_id]);
    $count_students_joined = (int)$stmt2->fetchColumn();
    
    $stmt3 = $db->prepare("SELECT COUNT(r.id) FROM results r 
                           INNER JOIN quizzes q ON r.quiz_id = q.id 
                           WHERE q.teacher_id = :tid");
    $stmt3->execute(['tid' => $teacher_id]);
    $count_results = (int)$stmt3->fetchColumn();

    // ៣. ទាញយកវិញ្ញាសាថ្មីៗ
   // ៣. ទាញយកវិញ្ញាសាថ្មីៗ (Updated to include status)
    $stmt4 = $db->prepare("SELECT * FROM quizzes WHERE teacher_id = :tid ORDER BY created_at DESC LIMIT 5");
    $stmt4->execute(['tid' => $teacher_id]);
    $recent_quizzes = $stmt4->fetchAll(PDO::FETCH_ASSOC);

    // ៤. ទាញយកពិន្ទុមធ្យមតាមវិញ្ញាសា (ប្រើ title តាម Database របស់អ្នក)
    $stmt_quiz_avg = $db->prepare("SELECT q.title, IFNULL(AVG(r.score), 0) as avg_score 
                                   FROM quizzes q 
                                   LEFT JOIN results r ON q.id = r.quiz_id 
                                   WHERE q.teacher_id = :tid 
                                   GROUP BY q.id ORDER BY q.created_at DESC LIMIT 5");
    $stmt_quiz_avg->execute(['tid' => $teacher_id]);
    $quiz_stats = $stmt_quiz_avg->fetchAll(PDO::FETCH_ASSOC);

    $quiz_labels = array_column($quiz_stats, 'title');
    $quiz_scores = array_map(fn($val) => round($val, 1), array_column($quiz_stats, 'avg_score'));
    // ៥. ទាញយកពិន្ទុមធ្យមតាមថ្នាក់ (Fixed version)
// ៥. ទាញយកពិន្ទុមធ្យមតាមថ្នាក់ (Filtered by Teacher ID)
// ៥. ទាញយកពិន្ទុមធ្យមតាមថ្នាក់ (Corrected for all classes)
$stmt_class_avg = $db->prepare("
    SELECT 
        c.class_name, 
        COALESCE(AVG(r.score), 0) as avg_score
    FROM classes c
    LEFT JOIN quizzes q ON c.id = q.class_id AND q.teacher_id = :tid
    LEFT JOIN results r ON q.id = r.quiz_id 
    GROUP BY c.id, c.class_name
    ORDER BY c.class_name ASC
");

$stmt_class_avg->execute(['tid' => $teacher_id]);
$class_stats = $stmt_class_avg->fetchAll(PDO::FETCH_ASSOC);

$class_labels = array_column($class_stats, 'class_name');
$class_scores = array_map(fn($val) => round((float)$val, 2), array_column($class_stats, 'avg_score'));

// Fix for the Warnings: Initialize these so the foreach loops don't crash
$quiz_stats = $quiz_stats ?? []; 
$groupedResults = $groupedResults ?? [];

 
    $stmt_notif = $db->prepare("SELECT * FROM notifications 
                                WHERE user_id = :tid 
                                ORDER BY created_at DESC LIMIT 5");
    $stmt_notif->execute(['tid' => $teacher_id]);
    $notifications = $stmt_notif->fetchAll(PDO::FETCH_ASSOC);

    // រាប់ចំនួនដំណឹងដែលមិនទាន់បានអាន (is_read = 0) ដើម្បីបង្ហាញលើកណ្ដឹង
    $unread_stmt = $db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $unread_stmt->execute([$teacher_id]);
    $unread_count = $unread_stmt->fetchColumn();

    // ឧទាហរណ៍៖ បន្ថែម link ទៅកាន់ទំព័រមើលលទ្ធផល

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard - QuizMaster</title>
    <link rel="stylesheet" href="../../../../node_modules/bootstrap/dist/css/bootstrap.min.css">

    <!-- Line 9 - fix this -->
  
    <link rel="stylesheet" href="../../../public/fontawesome-free-7.2.0-web/css/all.min.css">
    <link rel="stylesheet" href="../../../public/css/adminstyle/dashboard.css"> 
    <style>
        :root { --primary-color: #6366f1; --bg-light: #f8fafc; }
        body { background-color: var(--bg-light); font-family: 'Kantumruy Pro', sans-serif; }
        .main-content { padding: 30px; }
        .custom-card { border: none !important; border-radius: 20px !important; box-shadow: 0 10px 30px rgba(0,0,0,0.05) !important; background: #fff; height: 100%; }
        .stat-card { transition: transform 0.2s; border-radius: 15px; }
        .stat-card:hover { transform: translateY(-5px); }
        .chart-container { position: relative; height: 350px; width: 100%; }
        .modal-content { border-radius: 25px !important; border: none; overflow: hidden; }
        .profile-img-wrapper { width: 110px; height: 110px; background: white; border-radius: 50%; padding: 5px; margin: -55px auto 10px; position: relative; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .profile-img-wrapper img { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; }
        .placeholder-avatar { width: 100%; height: 100%; border-radius: 50%; background: var(--primary-color); color: white; display: flex; align-items: center; justify-content: center; font-size: 32px; font-weight: bold; }
    </style>
</head>
<body>

<div class="d-flex">
    <?php include 'partials/sidebar.php'; ?>

    <main class="main-content w-100">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h3 class="fw-bold mb-0 text-dark">ផ្ទាំងគ្រប់គ្រង</h3>
                <p class="text-muted">សួស្តី, <?php echo htmlspecialchars($_SESSION['user'] ?? 'Admin'); ?>!</p>
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
                                        <a class="dropdown-item py-3 border-bottom <?= $notif['is_read'] == 0 ? 'bg-light' : '' ?>" 
                                        href="student_results.php" 
                                        style="white-space: normal; word-wrap: break-word;">
                                            
                                            <div class="d-flex align-items-start">
                                                <div class="flex-grow-1" style="min-width: 0;"> <p class="mb-1 small fw-bold text-dark text-wrap">
                                                        <?= htmlspecialchars($notif['title']) ?>
                                                    </p>
                                                    <p class="mb-1 text-muted extra-small text-wrap" style="line-height: 1.4;">
                                                        <?= htmlspecialchars($notif['message']) ?>
                                                    </p>
                                                    <small class="text-primary opacity-75" style="font-size: 0.65rem;">
                                                        <i class="fa-regular fa-clock me-1"></i>
                                                        <?= date('d M, h:i A', strtotime($notif['created_at'])) ?>
                                                    </small>
                                                </div>

                                                <?php if ($notif['is_read'] == 0): ?>
                                                    <div class="ms-2 mt-1">
                                                        <span class="p-1 bg-primary rounded-circle d-block"></span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </a>
                                    </li>
                                    <li class="text-center">
                                        <a href="student_results.php" class="dropdown-item py-2 small fw-bold text-primary">ពិនិត្យមើល</a>
                                    </li>

                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </ul>
                </div>
                <div class="bg-white p-2 rounded-pill shadow-sm px-4 fw-bold text-primary hover-profile" 
                     style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#teacherProfileModal">
                    <i class="fa-solid fa-user-shield me-2"></i>Teacher Profile
                </div>
            </div>
        </div>
        
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card stat-card shadow-sm p-4 border-0 border-start border-danger border-5 bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div><p class="text-muted mb-1 small">វិញ្ញាសាសរុប</p><h2 class="fw-bold mb-0"><?php echo $count_quizzes; ?></h2></div>
                        <div class="fs-1 text-danger opacity-25"><i class="fa-solid fa-file-invoice"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card shadow-sm p-4 border-0 border-start border-primary border-5 bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div><p class="text-muted mb-1 small">សិស្សដែលបានប្រឡង</p><h2 class="fw-bold mb-0"><?php echo $count_students_joined; ?></h2></div>
                        <div class="fs-1 text-primary opacity-25"><i class="fa-solid fa-users"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card shadow-sm p-4 border-0 border-start border-success border-5 bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div><p class="text-muted mb-1 small">លទ្ធផលសរុប</p><h2 class="fw-bold mb-0"><?php echo $count_results; ?></h2></div>
                        <div class="fs-1 text-success opacity-25"><i class="fa-solid fa-chart-simple"></i></div>
                    </div>
                </div>
            </div>
        </div> 

        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
            <h5 class="fw-bold mb-4">វិញ្ញាសាថ្មីៗ</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ឈ្មោះវិញ្ញាសា</th>
                            <th>កាលបរិច្ឆេទ</th>
                            <th>ស្ថានភាព</th>
                            <th class="text-end">សកម្មភាព</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recent_quizzes)): ?>
                            <?php foreach ($recent_quizzes as $quiz): ?>
                                <?php 
                                    // Determine CSS class based on status
                                    $status = strtolower($quiz['status']);
                                    $badge_class = 'bg-success-subtle text-success'; // Default Active
                                    
                                    if ($status === 'inactive') {
                                        $badge_class = 'bg-secondary-subtle text-secondary';
                                    } elseif ($status === 'closed') {
                                        $badge_class = 'bg-danger-subtle text-danger';
                                    }
                                ?>
                                <tr>
                                    <td class="fw-bold text-dark"><?php echo htmlspecialchars($quiz['title']); ?></td>
                                    <td class="text-muted small"><?php echo date('d-m-Y', strtotime($quiz['created_at'])); ?></td>
                                    <td>
                                        <span class="badge <?php echo $badge_class; ?> rounded-pill px-3 text-capitalize">
                                            <?php echo $status; ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a href="manage_quiz.php?id=<?php echo $quiz['id']; ?>" class="btn btn-light btn-sm rounded-circle shadow-sm">
                                            <i class="fa-solid fa-eye text-primary"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="text-center py-4 text-muted">មិនទាន់មានទិន្នន័យ</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-xl-7">
                <div class="card custom-card p-4">
                    <h6 class="fw-bold mb-4"><i class="fa-solid fa-chart-bar me-2 text-primary"></i>ពិន្ទុមធ្យមតាមវិញ្ញាសា (%)</h6>
                    <div class="chart-container"><canvas id="quizChart"></canvas></div>
                </div>
            </div>
            <div class="col-xl-5">
                <div class="card custom-card p-4">
                    <h6 class="fw-bold mb-4"><i class="fa-solid fa-graduation-cap me-2 text-success"></i>ពិន្ទុមធ្យមតាមថ្នាក់ (%)</h6>
                    <div class="chart-container"><canvas id="classChart"></canvas></div>
                </div>
            </div>
        </div>
    </main>
</div>

<div class="modal fade" id="teacherProfileModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div style="height: 100px; background: linear-gradient(45deg, #6366f1, #a855f7);"></div>
            <div class="modal-body text-center pt-0">
                <form action="../../Controllers/UserController.php?action=update_profile" method="POST" enctype="multipart/form-data">
                    
                    <input type="hidden" name="user_id" value="<?php echo $user_data['id']; ?>">

                    <div class="profile-img-wrapper position-relative d-inline-block shadow" 
                        style="margin-top: -55px; border-radius: 50%; padding: 5px; background-color: #fff; width: 120px; height: 120px;">
                        
                        <?php 
                            $img_name = $user_data['profile_image'] ?? '';
                            $img_path = "../../../public/images/profiles/" . $img_name;
                            
                            // Check if the image exists in DB and the actual file exists in the folder
                            if (!empty($img_name) && file_exists($img_path)): 
                        ?>
                            <img src="<?php echo $img_path; ?>" 
                                id="previewImg" 
                                style="width: 110px; height: 110px; border-radius: 50%; object-fit: cover; display: block;" 
                                alt="Teacher Profile">
                        
                        <?php else: ?>
                            <div class="placeholder-avatar bg-primary text-white d-flex align-items-center justify-content-center fw-bold" 
                                style="width: 110px; height: 110px; border-radius: 50%; font-size: 35px;">
                                <?php echo strtoupper(substr($user_data['full_name'] ?? 'U', 0, 2)); ?>
                            </div>
                        <?php endif; ?>
                        
                        <label for="uploadInput" 
                            class="position-absolute bg-white rounded-circle shadow-sm border d-flex align-items-center justify-content-center" 
                            style="width: 32px; height: 32px; cursor: pointer; bottom: 5px; right: 5px; transform: translate(10%, 10%);">
                            <i class="fa-solid fa-camera text-primary" style="font-size: 12px;"></i>
                            <input type="file" id="uploadInput" name="profile_image" class="d-none" accept="image/*" onchange="previewImage(this)">
                        </label>
                    </div>

                    <h4 class="fw-bold mb-1"><?php echo htmlspecialchars($user_data['full_name'] ?? 'Teacher'); ?></h4>
                    <span class="badge bg-primary-subtle text-primary rounded-pill px-3 mb-3">Teacher Panel</span>
                    
                    <div class="row g-3 text-start mb-4 mt-1">
                        <div class="col-12">
                            <div class="bg-light p-2 px-3 rounded-3">
                                <small class="text-muted">ឈ្មោះពេញ (Full Name)</small>
                                <input type="text" name="full_name" class="form-control border-0 bg-transparent p-0 fw-bold shadow-none" value="<?php echo htmlspecialchars($user_data['full_name'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="bg-light p-2 px-3 rounded-3">
                                <small class="text-muted">អ៊ីមែល (Email)</small>
                                <input type="email" name="email" class="form-control border-0 bg-transparent p-0 fw-bold shadow-none" value="<?php echo htmlspecialchars($user_data['email'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="bg-light p-2 px-3 rounded-3">
                                <small class="text-muted">ភេទ (Gender)</small>
                                <select name="gender" class="form-select border-0 bg-transparent p-0 fw-bold shadow-none">
                                    <option value="M" <?php echo (($user_data['gender'] ?? '') == 'M') ? 'selected' : ''; ?>>ប្រុស (Male)</option>
                                    <option value="F" <?php echo (($user_data['gender'] ?? '') == 'F') ? 'selected' : ''; ?>>ស្រី (Female)</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="bg-light p-2 px-3 rounded-3">
                                <small class="text-muted">ថ្ងៃកំណើត (DOB)</small>
                                <input type="date" name="dob" class="form-control border-0 bg-transparent p-0 fw-bold shadow-none" value="<?php echo $user_data['dob'] ?? ''; ?>">
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="bg-light p-2 px-3 rounded-3">
                                <small class="text-muted">អាសយដ្ឋាន (Address)</small>
                                <input type="text" name="address" class="form-control border-0 bg-transparent p-0 fw-bold shadow-none" value="<?php echo htmlspecialchars($user_data['address'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary rounded-pill py-2 fw-bold shadow-sm">
                            <i class="fa-solid fa-floppy-disk me-2"></i>រក្សាទុកការផ្លាស់ប្តូរ
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Quiz Chart
    new Chart(document.getElementById('quizChart'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($quiz_labels); ?>,
            datasets: [{
                label: 'មធ្យមភាគ (%)',
                data: <?php echo json_encode($quiz_scores); ?>,
                backgroundColor: '#9892ff',
                borderRadius: 6
            }]
        },
        options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, scales: { x: { max: 100, beginAtZero: true } } }
    });

// Get data from PHP
// Get data from PHP
const classLabels = <?php echo json_encode($class_labels); ?>;
const classScores = <?php echo json_encode($class_scores); ?>;

new Chart(document.getElementById('classChart'), {
    type: 'bar',
    data: {
        labels: classLabels,
        datasets: [{
            label: 'Average Score (%)',
            data: classScores,
            // Uses your preferred blue for active classes
            backgroundColor: '#6366f1', 
            borderRadius: 8,
            barPercentage: 0.5, // Makes bars thinner like in your screenshot
        }]
    },
    options: { 
        responsive: true, 
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false }
        },
        scales: { 
            y: { 
                beginAtZero: true,
                max: 100,
                ticks: { 
                    stepSize: 10,
                    callback: (value) => value + "%" 
                },
                grid: {
                    drawBorder: false,
                    color: '#e2e8f0'
                }
            },
            x: {
                grid: { display: false } // Hides vertical lines for a cleaner look
            }
        }
    }
});



    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) { document.getElementById('previewImg').src = e.target.result; }
            reader.readAsDataURL(input.files[0]);
        }
    }

    document.getElementById('bellDropdown').addEventListener('shown.bs.dropdown', function() {
    const badge = document.getElementById('unread-badge');
    if (badge) badge.style.display = 'none';

    const xhr = new XMLHttpRequest();
    xhr.open('GET', 'dashboard.php?mark_read=1', true);
    xhr.send();
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>