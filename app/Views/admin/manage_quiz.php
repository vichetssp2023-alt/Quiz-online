<?php
session_start();

// ១. ការពារ Security
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../../public/index.php");
    exit();
}

// ២. តភ្ជាប់ Database
require_once "../../../config/database.php"; 
$database = new Database();
$db = $database->getConnection();

// ៣. ទាញយកទិន្នន័យ Quiz
// ៣. ទាញយកទិន្នន័យ Quiz ដោយភ្ជាប់ជាមួយតារាង Users ដើម្បីយកឈ្មោះគ្រូ
$query = "SELECT quizzes.*, users.full_name as teacher_name, users.profile_image 
          FROM quizzes 
          LEFT JOIN users ON quizzes.teacher_id = users.id 
          ORDER BY quizzes.id DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);
// count 
$count_classes = $db->query("SELECT COUNT(*) FROM classes")->fetchColumn();

// ២. រាប់ចំនួនវិញ្ញាសាសរុប
$count_quizzes = $db->query("SELECT COUNT(*) FROM quizzes")->fetchColumn();

// ៣. រាប់ចំនួនវិញ្ញាសាដែល Active (ឧទាហរណ៍ status = 1 ឬ 'active')
// សូមប្តូរ 'status' តាមឈ្មោះ Column ក្នុង DB របស់អ្នក
$count_active = $db->query("SELECT COUNT(*) FROM quizzes WHERE status = 'active'")->fetchColumn(); 

// --- បញ្ចប់ការរាប់ ---

// ទាញយកទិន្នន័យ Quiz បង្ហាញក្នុង Table
$query = "SELECT quizzes.*, users.full_name as teacher_name, users.profile_image 
          FROM quizzes 
          LEFT JOIN users ON quizzes.teacher_id = users.id 
          ORDER BY quizzes.id DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <title>គ្រប់គ្រង Quiz - QuizMaster</title>
    <link rel="stylesheet" href="../../../../node_modules/bootstrap/dist/css/bootstrap.min.css">

    <!-- Line 9 - fix this -->
    <script src="../../../../node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="../../../public/fontawesome-free-7.2.0-web/css/all.min.css">
    <link rel="stylesheet" href="../../../public/css/adminstyle/dashboard.css"> 

</head>
<body>

<div class="d-flex">
    <?php include 'partials/sidebar.php'; ?>

    <main class="main-content w-100">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h3 class="fw-bold mb-0 text-dark">គ្រប់គ្រងវិញ្ញាសា</h3>
                <p class="text-muted small">បង្កើត និងរៀបចំឯកសារវិញ្ញាសា</p>
            </div>
            <div class="btn-create">
                <button class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#addQuizModal">
                    <i class="fa-solid fa-file-circle-plus"></i> បង្កើតវិញ្ញាសាថ្មី
                </button>
            </div> 
        </div>

        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="card stat-card shadow-sm p-4 border-0 border-start border-danger border-5 bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 fw-medium">ថ្នាក់រៀនសរុប</p>
                            <h2 class="fw-bold mb-0"><?= number_format($count_classes) ?></h2>
                        </div>
                        <i class="fa-solid fa-user-graduate fs-1 text-danger opacity-25"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card stat-card shadow-sm p-4 border-0 border-start border-primary border-5 bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 fw-medium">វិញ្ញាសាសរុប</p>
                            <h2 class="fw-bold mb-0"><?= number_format($count_quizzes) ?></h2>
                        </div>
                        <i class="fa-solid fa-book-open fs-1 text-primary opacity-25"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card stat-card shadow-sm p-4 border-0 border-start border-success border-5 bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 fw-medium">វិញ្ញាសាដែលដំណើរការ (Active)</p>
                            <h2 class="fw-bold mb-0"><?= number_format($count_active) ?></h2>
                        </div>
                        <i class="fa-solid fa-signal fs-1 text-success opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm p-4 overflow-hidden border-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="border-0">ឯកសារ</th>
                            <th class="border-0">ឈ្មោះវិញ្ញាសា</th>
                            <th class="border-0">បង្កើតដោយគ្រូ</th>
                            <th class="border-0 text-center">រយៈពេល</th>
                            <th class="border-0 text-end">សកម្មភាព</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($quizzes as $row): 
                            $ext = pathinfo($row['image_url'], PATHINFO_EXTENSION); ?>
                        <tr>
                            <td>
                                <?php if (in_array($ext, ['jpg', 'png', 'jpeg'])): ?>
                                    <img src="../../../public/uploads/quizzes/<?= $row['image_url']; ?>" style="width: 45px; height: 45px; object-fit: cover; border-radius: 10px;">
                                <?php else: ?>
                                    <div class="bg-primary-subtle text-primary rounded-3 p-2 text-center" style="width: 45px;"><i class="fa-solid fa-file-pdf"></i></div>
                                <?php endif; ?>
                            </td>
                            <td class="fw-bold"><?= htmlspecialchars($row['title']); ?></td>
                            
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="me-2">
                                        <?php if (!empty($row['profile_image'])): ?>
                                            <img src="../../../public/images/profiles/<?= $row['profile_image']; ?>" 
                                                class="rounded-circle shadow-sm" 
                                                style="width: 32px; height: 32px; object-fit: cover; border: 2px solid #fff;">
                                        <?php else: ?>
                                            <div class="bg-secondary-subtle rounded-circle d-flex align-items-center justify-content-center" 
                                                style="width: 32px; height: 32px;">
                                                <i class="fa-solid fa-user text-secondary small"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <span class="badge bg-primary-subtle text-primary rounded-pill px-3 fw-medium">
                                        <?= htmlspecialchars($row['teacher_name'] ?? 'មិនស្គាល់'); ?>
                                    </span>
                                </div>
                            </td>
                            <td class="text-center"><?= $row['duration']; ?> នាទី</td>
                            <td class="text-end">
                                <button class="btn btn-light btn-sm text-primary shadow-sm mx-1" data-bs-toggle="modal" data-bs-target="#viewQuizModal<?= $row['id']; ?>"><i class="fa-solid fa-eye"></i></button>
                                <button class="btn btn-light btn-sm text-warning shadow-sm mx-1" data-bs-toggle="modal" data-bs-target="#editQuizModal<?= $row['id']; ?>"><i class="fa-solid fa-pen-to-square"></i></button>
                                <button class="btn btn-light btn-sm text-danger shadow-sm mx-1 btn-delete-quiz" data-id="<?= $row['id']; ?>" data-title="<?= htmlspecialchars($row['title']); ?>"><i class="fa-solid fa-trash"></i></button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<div class="modal fade" id="addQuizModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 p-4">
            <h4 class="fw-bold mb-4 text-primary"><i class="fa-solid fa-file-circle-plus"></i> បង្កើត Quiz ថ្មី</h4>
            <form action="../../Controllers/QuizController.php" method="POST" enctype="multipart/form-data">
                <div class="mb-4">
                    <div class="file-drop-zone text-center">
                        <input type="file" name="quiz_file" class="file-input" required id="quizFile">
                        <i class="fa-solid fa-cloud-arrow-up fa-2x text-primary mb-2"></i>
                        <p class="small text-muted mb-0" id="fileLabel">ជ្រើសរើសរូបភាព ឬ PDF</p>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">ឈ្មោះវិញ្ញាសា</label>
                    <input type="text" name="title" class="form-control bg-light border-0 py-2 rounded-3" placeholder="ឧទាហរណ៍៖ រៀន PHP" required>
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-6"><label class="form-label small fw-bold">រយៈពេល (នាទី)</label><input type="number" name="duration" class="form-control bg-light border-0 py-2 rounded-3" value="60"></div>
                    <div class="col-6"><label class="form-label small fw-bold">ពិន្ទុសរុប</label><input type="number" name="total_score" class="form-control bg-light border-0 py-2 rounded-3" value="100"></div>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-light w-100 rounded-pill py-2" data-bs-dismiss="modal">បោះបង់</button>
                    <button type="submit" name="save_quiz" class="btn btn-primary w-100 rounded-pill shadow py-2 fw-bold">រក្សាទុក</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php foreach ($quizzes as $row): 
    $ext = pathinfo($row['image_url'], PATHINFO_EXTENSION); ?>
    
    <div class="modal fade" id="viewQuizModal<?= $row['id']; ?>" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="bg-primary p-4 text-center text-white">
                    <h5 class="fw-bold mb-0">ព័ត៌មានវិញ្ញាសា</h5>
                </div>
                <div class="modal-body p-4 text-center">
                    <?php if (in_array($ext, ['jpg', 'png', 'jpeg'])): ?>
                        <img src="../../../public/uploads/quizzes/<?= $row['image_url']; ?>" class="img-fluid rounded-3 mb-4 shadow-sm" style="max-height: 180px; width: 100%; object-fit: contain;">
                    <?php endif; ?>
                    <h3 class="fw-bold text-dark mb-4 text-uppercase"><?= htmlspecialchars($row['title']); ?></h3>
                    <div class="row g-3">
                        <div class="col-6"><div class="bg-light p-3 rounded-4"><small class="text-muted d-block">រយៈពេល</small><span class="fw-bold fs-5"><?= $row['duration']; ?> នាទី</span></div></div>
                        <div class="col-6"><div class="bg-light p-3 rounded-4"><small class="text-muted d-block">ពិន្ទុសរុប</small><span class="fw-bold fs-5"><?= $row['total_score'] ?? '100'; ?></span></div></div>
                    </div>
                    <button class="btn btn-secondary w-100 rounded-pill mt-4 py-2 fw-bold" data-bs-dismiss="modal">យល់ព្រម</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editQuizModal<?= $row['id']; ?>" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 p-4">
                <h4 class="fw-bold mb-4 text-warning"><i class="fa-solid fa-pen-to-square"></i> កែប្រែវិញ្ញាសា</h4>
                <form action="../../Controllers/QuizController.php?action=update" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="quiz_id" value="<?= $row['id']; ?>">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">ឈ្មោះវិញ្ញាសា</label>
                        <input type="text" name="title" class="form-control bg-light border-0 py-2 rounded-3" value="<?= htmlspecialchars($row['title']); ?>" required>
                    </div>
                    <div class="row g-3 mb-4">
                        <div class="col-6"><label class="form-label small fw-bold">រយៈពេល (នាទី)</label><input type="number" name="duration" class="form-control bg-light border-0 py-2 rounded-3" value="<?= $row['duration']; ?>"></div>
                        <div class="col-6"><label class="form-label small fw-bold">ពិន្ទុសរុប</label><input type="number" name="total_score" class="form-control bg-light border-0 py-2 rounded-3" value="<?= $row['total_score'] ?? '100'; ?>"></div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted">ប្តូរឯកសារ (ទុកទំនេរបាន)</label>
                        <input type="file" name="quiz_file" class="form-control bg-light border-0">
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-light w-100 rounded-pill py-2" data-bs-dismiss="modal">បោះបង់</button>
                        <button type="submit" name="update_quiz" class="btn btn-warning w-100 rounded-pill shadow py-2 fw-bold text-white">រក្សាទុក</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // បង្ហាញឈ្មោះ File ពេលរើសរួច
    document.getElementById('quizFile').onchange = function() {
        document.getElementById('fileLabel').innerText = "File: " + this.files[0].name;
        document.getElementById('fileLabel').classList.add('text-primary', 'fw-bold');
    };

    // Delete Logic
    document.querySelectorAll('.btn-delete-quiz').forEach(button => {
        button.onclick = function() {
            const quizId = this.getAttribute('data-id');
            const quizTitle = this.getAttribute('data-title');
            Swal.fire({
                title: 'តើអ្នកប្រាកដទេ?',
                text: "អ្នកនឹងលុបវិញ្ញាសា '" + quizTitle + "' នេះ!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'បាទ, លុបវា!',
                cancelButtonText: 'បោះបង់',
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "../../Controllers/QuizController.php?delete_id=" + quizId;
                }
            });
        };
    });
</script>
</body>
</html>