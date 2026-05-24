<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../../public/index.php");
    exit();
}

require_once "../../../config/database.php"; 
$database = new Database();
$db = $database->getConnection();

// ទាញយកទិន្នន័យសម្រាប់តារាង
$query = "SELECT r.*, u.username, u.profile_image, q.title as quiz_title, c.class_name 
          FROM results r
          JOIN users u ON r.user_id = u.id
          JOIN quizzes q ON r.quiz_id = q.id
          LEFT JOIN classes c ON u.class_id = c.id  -- បន្ថែមការ JOIN ត្រង់នេះ
          ORDER BY r.created_at DESC";

$stmt = $db->prepare($query);
$stmt->execute();
$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- ផ្នែកបន្ថែមថ្មី: ទាញទិន្នន័យសម្រាប់ក្រាហ្វ ---
$pass_count = $db->query("SELECT COUNT(*) FROM results WHERE score >= 50")->fetchColumn() ?: 0;
$fail_count = $db->query("SELECT COUNT(*) FROM results WHERE score < 50")->fetchColumn() ?: 0;
// រាប់សិស្សស្រី: ចាប់យកពាក្យ Female, female, F, หรือ ស្រី
$female_count = $db->query("SELECT COUNT(*) FROM users WHERE role='student' AND (LOWER(gender) = 'female' OR gender = 'F' OR gender = 'ស្រី')")->fetchColumn() ?: 0;

// រាប់សិស្សប្រុស: ចាប់យកពាក្យ Male, male, M, หรือ ប្រុស
$male_count = $db->query("SELECT COUNT(*) FROM users WHERE role='student' AND (LOWER(gender) = 'male' OR gender = 'M' OR gender = 'ប្រុស')")->fetchColumn() ?: 0;

// រាប់សិស្សសរុប
$total_students = $db->query("SELECT COUNT(*) FROM users WHERE role='student'")->fetchColumn() ?: 0;

// សិស្សដែលមិនបានបំពេញ ឬខុសពីលក្ខខណ្ឌខាងលើ
$unknown_gender = $total_students - ($female_count + $male_count);

$total_students = $db->query("SELECT COUNT(*) FROM users WHERE role='student'")->fetchColumn() ?: 0;
$unknown_gender = $total_students - ($female_count + $male_count);

$total_st = $db->query("SELECT COUNT(*) FROM users WHERE role='student'")->fetchColumn();
$total_qz = $db->query("SELECT COUNT(*) FROM quizzes")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>របាយការណ៍ - QuizMaster</title>
    <link rel="stylesheet" href="../../../../node_modules/bootstrap/dist/css/bootstrap.min.css">

    <!-- Line 9 - fix this -->
    <script src="../../../../node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="../../../public/fontawesome-free-7.2.0-web/css/all.min.css">
     <link rel="stylesheet" href="../../../public/css/adminstyle/report.css">
     <script src="../../../public/js/chart.umd.min.js"></script>
</head>
<body>

<div class="d-flex">
    <?php include 'partials/sidebar.php'; ?>

    <main class="main-content w-100">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h3 class="fw-bold mb-0 text-dark">របាយការណ៍លទ្ធផល</h3>
                <p class="text-muted small">ពិនិត្យ និងទាញយកទិន្នន័យពិន្ទុសិស្ស</p>
            </div>
            <div class="no-print">
                <button onclick="window.print()" class="btn btn-primary rounded-pill px-4 shadow-sm">
                <i class="fas fa-print me-2"></i>បោះពុម្ពរបាយការណ៍
                </button>
                <button id="exportExcel" onclick="exportToExcel()" class="btn   btn-success rounded-pill px-4 fw-bold shadow-sm">
                    <i class="fa-solid fa-file-excel"></i> Excel
                </button>
            </div> 
        </div>

        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="card stat-card shadow-sm p-4 border-0 border-start border-danger border-5 bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div><p class="text-muted mb-1 fw-medium">សិស្សសរុប</p><h2 class="fw-bold mb-0"><?= number_format($total_st) ?></h2></div>
                        <i class="fa-solid fa-user-graduate fs-1 text-danger opacity-25"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card shadow-sm p-4 border-0 border-start border-primary border-5 bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div><p class="text-muted mb-1 fw-medium">វិញ្ញាសាសរុប</p><h2 class="fw-bold mb-0"><?= $total_qz ?></h2></div>
                        <i class="fa-solid fa-book-open fs-1 text-primary opacity-25"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card shadow-sm p-4 border-0 border-start border-success border-5 bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div><p class="text-muted mb-1 fw-medium">ជាប់សរុប</p><h2 class="fw-bold mb-0"><?= $pass_count ?></h2></div>
                        <i class="fa-solid fa-check-circle fs-1 text-success opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="rean"><h3>របាយការណ៍លទ្ធផលតាមថ្នាក់</h3></div>
                <div class="accordion rounded-4 overflow-hidden " style ="margin-bottom :10px;" id="reportAccordion">
                    <?php 
                    // ១. រៀបចំ Grouping ទិន្នន័យពី $reports (ដែលទាញចេញពី Database) មកដាក់តាមថ្នាក់
                    $grouped_reports = [];
                    foreach ($reports as $report) {
                        $class_name = $report['class_name'] ?? 'មិនទាន់មានថ្នាក់';
                        $grouped_reports[$class_name][] = $report;
                    }

                    $idx = 0;
                    foreach ($grouped_reports as $class_name => $class_data): 
                        $idx++;
                        $target_id = "collapse_" . $idx;
                    ?>
                        <div class="accordion-item border-0 border-bottom">
                            <h2 class="accordion-header">
                                <button class="accordion-button <?= $idx > 1 ? 'collapsed' : '' ?> py-3 fw-bold" 
                                        type="button" data-bs-toggle="collapse" data-bs-target="#<?= $target_id ?>">
                                    <i class="fa-solid fa-screen-users me-2 text-primary"></i> 
                                    ថ្នាក់រៀន៖ <?= htmlspecialchars($class_name) ?> 
                                    <span class="badge bg-light text-dark ms-2 rounded-pill border small">
                                        <?= count($class_data) ?> នាក់
                                    </span>
                                </button>
                            </h2>
                            
                            <div id="<?= $target_id ?>" class="accordion-collapse collapse <?= $idx == 1 ? 'show' : '' ?>" >
                                <div class="accordion-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th class="border-0 ps-4">ឈ្មោះសិស្ស</th>
                                                    <th class="border-0">វិញ្ញាសា</th>
                                                    <th class="border-0 text-center">ពិន្ទុ</th>
                                                    <th class="border-0 text-center">ស្ថានភាព</th>
                                                    <th class="border-0 text-end pe-4">កាលបរិច្ឆេទ</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($class_data as $row): 
                                                    $is_pass = ($row['score'] >= 50);
                                                    $status_class = $is_pass ? 'bg-pass' : 'bg-fail';
                                                    $status_text = $is_pass ? 'ជាប់' : 'ធ្លាក់';
                                                    
                                                    $user_img = $row['profile_image'];
                                                    $img_path = !empty($user_img) && file_exists("../../../public/images/profiles/" . $user_img) 
                                                                ? "../../../public/images/profiles/" . $user_img 
                                                                : "https://ui-avatars.com/api/?name=" . urlencode($row['username']) . "&background=6366f1&color=fff";
                                                ?>
                                                    <tr>
                                                        <td class="ps-4">
                                                            <div class="d-flex align-items-center">
                                                                <img src="<?= $img_path ?>" class="student-img me-2 shadow-sm border">
                                                                <span class="fw-bold text-dark"><?= htmlspecialchars($row['username']) ?></span>
                                                            </div>
                                                        </td>
                                                        <td><i class="fa-solid fa-file-lines text-muted me-1"></i> <?= htmlspecialchars($row['quiz_title']) ?></td>
                                                        <td class="text-center fw-bold text-primary"><?= $row['score'] ?>/100</td>
                                                        <td class="text-center">
                                                            <span class="status-badge <?= $status_class ?>"><?= $status_text ?></span>
                                                        </td>
                                                        <td class="text-end pe-4 text-muted small">
                                                            <i class="fa-regular fa-calendar-alt me-1"></i>
                                                            <?= date('d M, Y', strtotime($row['created_at'])) ?>
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
                </div>
                <div class="row g-4 mb-5">
                <div class="col-md-6">
                    <div class="card shadow-sm p-4 h-100">
                        <h6 class="fw-bold mb-4 text-center">ស្ថិតិជាប់ និងធ្លាក់</h6>
                        <div style="height: 250px;">
                            <canvas id="passFailChart"></canvas> </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card shadow-sm p-4 h-100">
                        <h6 class="fw-bold mb-4 text-center">ចំនួនសិស្សតាមភេទ (ប្រុស/ស្រី)</h6>
                        <div style="height: 250px;">
                            <canvas id="genderChart"></canvas> 
                        </div>
                    </div>
                </div>
         </div>
    </main>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.3.0/exceljs.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
<script>
    async function exportToExcel() {
    const workbook = new ExcelJS.Workbook();
    const worksheet = workbook.addWorksheet('របាយការណ៍លទ្ធផល');

    // ១. កំណត់ Header
    const headerRow = worksheet.addRow(["ថ្នាក់", "ឈ្មោះសិស្ស", "វិញ្ញាសា", "ពិន្ទុ", "និទ្ទេស", "ស្ថានភាព", "កាលបរិច្ឆេទ", "មធ្យមភាគ"]);
    headerRow.eachCell((cell) => {
        cell.font = { name: 'Kantumruy Pro', bold: true, color: { argb: 'FFFFFFFF' }, size: 12 };
        cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF6366F1' } };
        cell.alignment = { vertical: 'middle', horizontal: 'center' };
        cell.border = { top: {style:'thin'}, left: {style:'thin'}, bottom: {style:'thin'}, right: {style:'thin'} };
    });

    // ២. រៀបចំទិន្នន័យ (Grouping by Class Name First)
    const classData = {};
    const accordionItems = document.querySelectorAll("#reportAccordion .accordion-item");
    
    accordionItems.forEach(item => {
        const className = item.querySelector(".accordion-button").innerText.split('៖')[1]?.split('\n')[0].trim() || "មិនស្គាល់ថ្នាក់";
        if (!classData[className]) classData[className] = { students: {} };

        const rows = item.querySelectorAll("table tbody tr");
        rows.forEach(row => {
            const cols = row.querySelectorAll("td");
            const name = cols[0].innerText.trim();
            const score = parseFloat(cols[2].innerText.split('/')[0]);

            if (!classData[className].students[name]) {
                classData[className].students[name] = { records: [], totalScore: 0 };
            }
            classData[className].students[name].records.push({
                quiz: cols[1].innerText.trim(),
                score: score,
                status: cols[3].innerText.trim(),
                date: cols[4].innerText.trim()
            });
            classData[className].students[name].totalScore += score;
        });
    });

    // ៣. បញ្ចូលទិន្នន័យ និង Merge Cells
    let currentRow = 2;
    const gradeColors = { 'A': 'FF00B050', 'B': 'FF7030A0', 'C': 'FFFF33CC', 'D': 'FFFF6600', 'E': 'FFFFCC00', 'F': 'FFFF0000' };

    Object.keys(classData).forEach(className => {
        const classStartRow = currentRow; // ចំណុចចាប់ផ្ដើមនៃថ្នាក់នីមួយៗ
        const students = classData[className].students;

        Object.keys(students).forEach(name => {
            const student = students[name];
            const avg = (student.totalScore / student.records.length).toFixed(2);
            const studentStartRow = currentRow; // ចំណុចចាប់ផ្ដើមនៃសិស្សម្នាក់ៗ

            student.records.forEach(rec => {
                let grade = rec.score >= 90 ? 'A' : rec.score >= 80 ? 'B' : rec.score >= 70 ? 'C' : rec.score >= 60 ? 'D' : rec.score >= 50 ? 'E' : 'F';
                const row = worksheet.addRow([className, name, rec.quiz, rec.score, grade, rec.status, rec.date, avg]);

                row.eachCell((cell, colNumber) => {
                    cell.font = { name: 'Kantumruy Pro', size: 11 };
                    cell.alignment = { vertical: 'middle', horizontal: 'center' };
                    cell.border = { top: {style:'thin'}, left: {style:'thin'}, bottom: {style:'thin'}, right: {style:'thin'} };
                    if (colNumber === 5) cell.font = { name: 'Kantumruy Pro', bold: true, color: { argb: gradeColors[grade] } };
                    if (colNumber === 6 && rec.status === 'ជាប់') cell.font.color = { argb: 'FF00B050' };
                    if (colNumber === 6 && rec.status === 'ធ្លាក់') cell.font.color = { argb: 'FFFF0000' };
                    if (colNumber === 8) cell.font = { name: 'Kantumruy Pro', bold: true, color: { argb: 'FF0000FF' } };
                });
                currentRow++;
            });

            // Merge ឈ្មោះសិស្ស និងមធ្យមភាគ
            if (studentStartRow !== currentRow - 1) {
                worksheet.mergeCells(`B${studentStartRow}:B${currentRow - 1}`);
                worksheet.mergeCells(`H${studentStartRow}:H${currentRow - 1}`);
            }
        });

        // Merge ឈ្មោះថ្នាក់ (ឱ្យមានតែមួយក្រឡាសម្រាប់គ្រប់សិស្សក្នុងថ្នាក់នោះ)
        if (classStartRow !== currentRow - 1) {
            worksheet.mergeCells(`A${classStartRow}:A${currentRow - 1}`);
        }
    });

    worksheet.columns = [{ width: 22 }, { width: 25 }, { width: 35 }, { width: 10 }, { width: 10 }, { width: 12 }, { width: 20 }, { width: 15 }];
    const buffer = await workbook.xlsx.writeBuffer();
    saveAs(new Blob([buffer]), `របាយការណ៍_តាមថ្នាក់_${new Date().toLocaleDateString()}.xlsx`);
}
document.addEventListener("DOMContentLoaded", function() {
    // កំណត់ Font ជាសកលសម្រាប់ Chart.js ទាំងអស់ក្នុងទំព័រនេះ
    Chart.defaults.font.family = '"Inter", "Kantumruy Pro", sans-serif';

    // ឆែកមើលក្នុង Console
    console.log("Female Count:", <?= (int)$female_count ?>);
    console.log("Male Count:", <?= (int)$male_count ?>);

    const centerTextPlugin = {
        id: 'centerText',
        afterDraw: (chart) => {
            const { ctx, chartArea: { left, top, width, height } } = chart;
            ctx.save();
            // កែសម្រួល Font នៅទីនេះ ដើម្បីឱ្យលេខនៅកណ្តាលរង្វង់មាន Font ស្អាត
            ctx.font = 'bold 24px "Inter", "Kantumruy Pro", sans-serif';
            ctx.fillStyle = '#333';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            const total = chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
            ctx.fillText(total, left + width / 2, top + height / 2);
            ctx.restore();
        }
    };

    const options = {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '75%',
        plugins: { 
            legend: { 
                position: 'bottom',
                labels: {
                    // កំណត់ Font សម្រាប់ Labels (ជាប់, ធ្លាក់, ស្រី, ប្រុស)
                    font: {
                        family: '"Inter", "Kantumruy Pro", sans-serif',
                        size: 13
                    }
                }
            } 
        }
    };

    // ក្រាហ្វលទ្ធផល (Pass/Fail)
    new Chart(document.getElementById('passFailChart'), {
        type: 'doughnut',
        data: {
            labels: ['ជាប់', 'ធ្លាក់'],
            datasets: [{
                data: [<?= (int)$pass_count ?>, <?= (int)$fail_count ?>],
                backgroundColor: ['#10b981', '#f43f5e']
            }]
        },
        options: options,
        plugins: [centerTextPlugin]
    });

    // ក្រាហ្វភេទ (Gender)
    new Chart(document.getElementById('genderChart'), {
        type: 'doughnut',
        data: {
            labels: ['ស្រី', 'ប្រុស', 'ផ្សេងៗ/មិនបញ្ជាក់'],
            datasets: [{
                data: [
                    <?= (int)$female_count ?>, 
                    <?= (int)$male_count ?>, 
                    <?= (int)$unknown_gender ?>
                ],
                backgroundColor: ['#6366f1', '#f59e0b', '#94a3b8'] 
            }]
        },
        options: options,
        plugins: [centerTextPlugin]
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>