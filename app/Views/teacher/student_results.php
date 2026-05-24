<?php
require_once "../../Controllers/ResultController.php";
$controller = new ResultController();
$results = $controller->getStudentResults();

// ១. បង្កើត Stats ទូទៅ
$passedCount = 0;
$quizStats = [];
$groupedResults = []; 
$unique_students = []; // បង្កើត array សម្រាប់ទុក ID សិស្សដែលមិនស្ទួន

foreach($results as $r) {
    $student_identifier = !empty($r['full_name']) ? $r['full_name'] : $r['id'];
    $unique_students[$student_identifier] = true; 

    // គណនាចំនួនអ្នកជាប់
    $q_total = $r['total_questions'] > 0 ? $r['total_questions'] : 100;
    if (($r['score'] / $q_total) * 100 >= 50) {
        $passedCount++;
    }

    // ២. រៀបចំសម្រាប់ Chart និង Grouping (ទុកកូដដដែល)
    $title = $r['title'];
    if (!isset($quizStats[$title])) {
        $quizStats[$title] = ['total_points' => 0, 'max_points' => 0, 'count' => 0];
    }
    $quizStats[$title]['total_points'] += $r['score'];
    $quizStats[$title]['max_points'] += $q_total; 
    $quizStats[$title]['count'] += 1;

    $className = !empty($r['class_name']) ? $r['class_name'] : "មិនទាន់មានថ្នាក់";
    $groupedResults[$className][] = $r;
}
// ដំណោះស្រាយ៖ រាប់ចំនួន Key ក្នុង array $unique_students (វានឹងបង្ហាញ 5 នាក់)
$totalStudents = count($unique_students);

// អត្រាជាប់មធ្យម គិតលើការប្រឡងសរុប
$totalAttempts = count($results);
$passRate = $totalAttempts > 0 ? round(($passedCount / $totalAttempts) * 100) : 0;
// ៣. រៀបចំទិន្នន័យសម្រាប់ Chart (ទុកកូដដដែល)
$quizLabels = [];
$avgScores = [];
foreach($quizStats as $title => $stat) {
    $quizLabels[] = $title;
    $avgScores[] = $stat['max_points'] > 0 ? round(($stat['total_points'] / $stat['max_points']) * 100, 2) : 0;
}
// --- កូដ PHP បន្ថែមសម្រាប់ Chart តាមថ្នាក់ ---
$classLabels = [];
$classAvgScores = [];

foreach ($groupedResults as $className => $students) {
    $classLabels[] = $className;
    $totalClassScore = 0;
    $totalClassMax = 0;
    
    foreach ($students as $s) {
        $totalClassScore += $s['score'];
        $totalClassMax += ($s['total_questions'] > 0 ? $s['total_questions'] : 100);
    }
    
    $avg = $totalClassMax > 0 ? round(($totalClassScore / $totalClassMax) * 100, 2) : 0;
    $classAvgScores[] = $avg;
}
?>

<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>លទ្ធផលតាមថ្នាក់ - QuizMaster</title>
    <link rel="stylesheet" href="../../../../node_modules/bootstrap/dist/css/bootstrap.min.css">

    <!-- Line 9 - fix this -->
    <script src="../../../../node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="../../../public/fontawesome-free-7.2.0-web/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Kantumruy+Pro:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../../public/css/teacherstyle/result.css">
</head>
<body>

    <?php include 'partials/sidebar.php'; ?>

    <main class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4 no-print">
            <div>
                <h3 class="fw-bold text-dark">វិភាគលទ្ធផលតាមថ្នាក់</h3>
                <p class="text-muted mb-0">ពិនិត្យមើលវឌ្ឍនភាពសិស្សបែងចែកតាមក្រុមថ្នាក់នីមួយៗ</p>
            </div>
            <button onclick="window.print()" class="btn btn-primary rounded-pill px-4 shadow-sm">
                <i class="fas fa-print me-2"></i>បោះពុម្ពរបាយការណ៍
            </button>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="stat-card shadow-sm border-start border-primary border-4 p-4 bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div><p class="text-muted mb-1">សិស្សសរុបដែលបានប្រឡង</p><h2 class="fw-bold mb-0"><?= number_format($totalStudents) ?> នាក់</h2></div>
                        <div class="text-primary fs-1 opacity-50"><i class="fas fa-user-graduate"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card shadow-sm border-start border-success border-4 p-4 bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div><p class="text-muted mb-1">អត្រាជាប់មធ្យម</p><h2 class="fw-bold mb-0"><?= $passRate ?>%</h2></div>
                        <div class="text-success fs-1 opacity-50"><i class="fas fa-chart-line"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card shadow-sm border-start border-info border-4 p-4 bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div><p class="text-muted mb-1">ចំនួនថ្នាក់រៀនដែលបានប្រឡង</p><h2 class="fw-bold mb-0"><?= count($groupedResults) ?> ថ្នាក់</h2></div>
                        <div class="text-info fs-1 opacity-50"><i class="fas fa-school"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="accordion shadow-sm rounded-3 overflow-hidden" id="classAccordion">
        <?php if (empty($groupedResults)): ?>
            <div class="bg-white p-5 text-center"><p class="text-muted">មិនទាន់មានទិន្នន័យសិស្សឡើយ</p></div>
        <?php else: ?>
            <?php $idx = 0; foreach ($groupedResults as $className => $classStudents): $idx++; ?>
                <div class="accordion-item border-0 border-bottom">
                    <h2 class="accordion-header">
                        <button class="accordion-button fw-bold py-3" type="button" data-bs-toggle="collapse" data-bs-target="#class_<?= $idx ?>">
                            <i class="fas fa-chalkboard-user me-2 text-primary"></i> 
                            ថ្នាក់រៀន៖ <?= htmlspecialchars($className) ?> 
                        </button>
                    </h2>
                    <div id="class_<?= $idx ?>" class="accordion-collapse collapse show">
                        <div class="accordion-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light small text-uppercase">
                                        <tr>
                                            <th class="ps-4">សិស្ស</th>
                                            <th>វិញ្ញាសា</th>
                                            <th style="width: 200px;">លទ្ធផល</th>
                                            <th class="text-center">ស្ថានភាព</th>
                                            <th class="text-end pe-4">សកម្មភាព</th>
                                        </tr>
                                    </thead>
                                    <tbody class="studentTableBody">
                                        <?php foreach ($classStudents as $row): 
                                            $score = $row['score']; 
                                            $max = $row['total_questions'] > 0 ? $row['total_questions'] : 100;
                                            $percent = ($score / $max) * 100;
                                            $isPassed = ($percent >= 50);
                                            $user_img = $row['profile_image'] ?? ''; 
                                            $display_img = (!empty($user_img) && file_exists("../../../public/images/profiles/" . $user_img)) 
                                                ? "../../../public/images/profiles/" . $user_img 
                                                : "https://ui-avatars.com/api/?name=" . urlencode($row['full_name']) . "&background=6366f1&color=fff";
                                        ?>
                                        <tr>
                                            <td class="ps-4">
                                                <div class="d-flex align-items-center">
                                                    <img src="<?= $display_img ?>" class="rounded-circle me-3" style="width: 38px; height: 38px; object-fit: cover; border: 1px solid #eee;">
                                                    <div>
                                                        <div class="fw-bold small">
                                                        <?= !empty($row['full_name']) ? htmlspecialchars($row['full_name']) : htmlspecialchars($row['username']) ?>
                                                    </div>
                                                        <small class="text-muted" style="font-size: 0.7rem;">ID: #<?= str_pad($row['id'], 4, '0', STR_PAD_LEFT) ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><span class="small text-dark"><?= htmlspecialchars($row['title']) ?></span></td>
                                            <td>
                                                <div class="d-flex justify-content-between mb-1 small" style="font-size: 0.75rem;">
                                                    <span class="fw-bold"><?= $score ?>/<?= $max ?></span>
                                                    <span><?= round($percent) ?>%</span>
                                                </div>
                                                <div class="progress" style="height: 6px;">
                                                    <div class="progress-bar <?= $isPassed ? 'bg-success' : 'bg-danger' ?>" style="width: <?= $percent ?>%"></div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge <?= $isPassed ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' ?> rounded-pill px-3" style="font-size: 0.7rem;">
                                                    <?= $isPassed ? 'ជាប់' : 'ធ្លាក់' ?>
                                                </span>
                                            </td>
                                            <td class="text-end pe-4">
                                                <a href="view_review_student.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-light border rounded-circle"><i class="fas fa-eye text-primary"></i></a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
        <!-- chart  -->
    <div class="row mt-5 no-print">
        <div class="col-md-6 mb-4">
            <div class="custom-card p-4 shadow-sm bg-white rounded-3">
                <h5 class="fw-bold mb-4"><i class="fas fa-chart-bar me-2 text-primary"></i>ពិន្ទុមធ្យមតាមវិញ្ញាសា (%)</h5>
                <div style="height: 300px;"><canvas id="quizComparisonChart"></canvas></div>
        </div>
    </div>

    <div class="col-md-6 mb-4">
        <div class="custom-card p-4 shadow-sm bg-white rounded-3">
            <h5 class="fw-bold mb-4"><i class="fas fa-graduation-cap me-2 text-success"></i>ពិន្ទុមធ្យមតាមថ្នាក់រៀន (%)</h5>
            <div style="height: 300px;"><canvas id="classComparisonChart"></canvas></div>
        </div>
    </div>
</div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // ១. ទិន្នន័យពី PHP
        const classLabels = <?= json_encode($classLabels) ?>;
        const classAvgScores = <?= json_encode($classAvgScores) ?>;

        // ២. បង្កើត Class Chart
        if (classLabels.length > 0) {
            const ctxClass = document.getElementById('classComparisonChart').getContext('2d');
            new Chart(ctxClass, {
                type: 'bar', // អ្នកអាចប្តូរជា 'doughnut' ឬ 'pie' បើចង់បានរាងមូល
                data: {
                    labels: classLabels,
                    datasets: [{
                        label: 'ភាគរយមធ្យម (%)',
                        data: classAvgScores,
                        backgroundColor: [
                            'rgba(16, 185, 129, 0.8)', // ពណ៌បៃតង
                            'rgba(59, 130, 246, 0.8)', // ពណ៌ខៀវ
                            'rgba(245, 158, 11, 0.8)', // ពណ៌លឿង
                            'rgba(239, 68, 68, 0.8)'   // ពណ៌ក្រហម
                        ],
                        borderRadius: 5,
                        barThickness: 40
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { max: 100, beginAtZero: true }
                    },
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
        }
        // Chart Config (Keep your existing chart logic)
        const quizLabels = <?= json_encode($quizLabels) ?>;
        const avgScores = <?= json_encode($avgScores) ?>;
        if (quizLabels.length > 0) {
            const ctx = document.getElementById('quizComparisonChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: quizLabels,
                    datasets: [{
                        label: 'ពិន្ទុមធ្យម (%)',
                        data: avgScores,
                        backgroundColor: 'rgba(99, 102, 241, 0.8)',
                        borderRadius: 5,
                        barThickness: 30
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: { x: { max: 100, beginAtZero: true } },
                    plugins: { legend: { display: false } }
                }
            });
        }

        // មុខងារទាញទិន្នន័យពី API មកបង្ហាញក្នុងតារាង
async function loadStudentResults() {
    try {
        const response = await fetch('api/get_results.php');
        const result = await response.json();

        if (result.status === 'success') {
            const tableBody = document.getElementById('studentTableBody');
            tableBody.innerHTML = ''; // សម្អាតតារាងមុននឹងដាក់ទិន្នន័យថ្មី

            result.data.forEach(row => {
                const max = row.total_questions > 0 ? row.total_questions : 100;
                const percent = Math.round((row.score / max) * 100);
                const isPassed = percent >= 50;

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="ps-4">
                        <div class="d-flex align-items-center">
                            <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(row.full_name || row.username)}&background=6366f1&color=fff" class="rounded-circle me-3" style="width: 38px; height: 38px;">
                            <div>
                                <div class="fw-bold small">${row.full_name || row.username}</div>
                                <small class="text-muted" style="font-size: 0.7rem;">ID: #${String(row.id).padStart(4, '0')}</small>
                            </div>
                        </div>
                    </td>
                    <td><span class="small text-dark">${row.title}</span></td>
                    <td>
                        <div class="d-flex justify-content-between mb-1 small" style="font-size: 0.75rem;">
                            <span class="fw-bold">${row.score}/${max}</span>
                            <span>${percent}%</span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar ${isPassed ? 'bg-success' : 'bg-danger'}" style="width: ${percent}%"></div>
                        </div>
                    </td>
                    <td class="text-center">
                        <span class="badge ${isPassed ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger'} rounded-pill px-3" style="font-size: 0.7rem;">
                            ${isPassed ? 'ជាប់' : 'ធ្លាក់'}
                        </span>
                    </td>
                    <td class="text-end pe-4">
                        <a href="view_review_student.php?id=${row.id}" class="btn btn-sm btn-light border rounded-circle">
                            <i class="fas fa-eye text-primary"></i>
                        </a>
                    </td>
                `;
                tableBody.appendChild(tr);
            });
        }
    } catch (error) {
        console.error('Error loading data:', error);
    }
}
    </script>
</body>
</html>