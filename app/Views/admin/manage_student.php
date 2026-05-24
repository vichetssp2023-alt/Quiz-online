<?php
session_start();
require_once "../../../config/database.php";

// ១. ពិនិត្យសិទ្ធិចូលប្រើប្រាស់
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../../public/index.php");
    exit();
}

try {
    $database = new Database();
    $db = $database->getConnection();

    // ២. ទាញយកបញ្ជីថ្នាក់ទាំងអស់
    $c_stmt = $db->prepare("SELECT id, class_name FROM classes WHERE status = 'active'");
    $c_stmt->execute();
    $classes = $c_stmt->fetchAll(PDO::FETCH_ASSOC);

    // ៣. ទាញយកទិន្នន័យសិស្សទាំងអស់
    $s_query = "SELECT u.*, c.class_name 
                FROM users u
                LEFT JOIN classes c ON u.class_id = c.id 
                WHERE u.role = 'student' 
                ORDER BY u.username ASC";
    $s_stmt = $db->prepare($s_query);
    $s_stmt->execute();
    $all_students = $s_stmt->fetchAll(PDO::FETCH_ASSOC);

    // ៤. រៀបចំសិស្សជាក្រុមតាមថ្នាក់ (Grouping Logic)
    $students_by_class = [];
    foreach ($classes as $class) {
        $students_by_class[$class['class_name']] = [];
    }
    // បន្ថែមក្រុមសម្រាប់សិស្សដែលមិនទាន់មានថ្នាក់
    $students_by_class['មិនទាន់មានថ្នាក់'] = [];

    foreach ($all_students as $s) {
        $c_name = $s['class_name'] ?? 'មិនទាន់មានថ្នាក់';
        $students_by_class[$c_name][] = $s;
    }

    // ៥. ស្ថិតិសរុប
    $total_students = count($all_students);
    $female_count = 0;
    $male_count = 0;
    foreach ($all_students as $s) {
        if (($s['gender'] ?? '') == 'F') $female_count++;
        elseif (($s['gender'] ?? '') == 'M') $male_count++;
    }

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>គ្រប់គ្រងទិន្នន័យសិស្ស - QuizMaster</title>
    <link rel="stylesheet" href="../../../../node_modules/bootstrap/dist/css/bootstrap.min.css">

    <!-- Line 9 - fix this -->
    <script src="../../../../node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="../../../public/fontawesome-free-7.2.0-web/css/all.min.css">
    <link rel="stylesheet" href="../../../public/css/adminstyle/manage_student.css">

</head>
<body>

<div class="d-flex">
    <?php include 'partials/sidebar.php'; ?>

    <main class="main-content w-100">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h3 class="fw-bold mb-1">គ្រប់គ្រងសិស្សតាមថ្នាក់ 📂</h3>
                <p class="text-muted small">រៀបចំ និងមើលបញ្ជីសិស្សតាមបណ្តាថ្នាក់នីមួយៗ</p>
            </div>
            <button class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm btn-create" data-bs-toggle="modal" data-bs-target="#addStudentModal">
                <i class="fa-solid fa-user-plus me-2"></i> បន្ថែមសិស្សថ្មី
            </button>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="card stat-card p-4 border-start border-primary border-5 bg-white shadow-sm">
                    <p class="text-muted mb-1 fw-medium">សិស្សសរុប</p>
                    <h2 class="fw-bold mb-0 text-primary"><?= $total_students ?> នាក់</h2>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card p-4 border-start border-danger border-5 bg-white shadow-sm">
                    <p class="text-muted mb-1 fw-medium">សិស្សស្រី</p>
                    <h2 class="fw-bold mb-0 text-danger"><?= $female_count ?> នាក់</h2>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card p-4 border-start border-info border-5 bg-white shadow-sm">
                    <p class="text-muted mb-1 fw-medium">សិស្សប្រុស</p>
                    <h2 class="fw-bold mb-0 text-info"><?= $male_count ?> នាក់</h2>
                </div>
            </div>
        </div>
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 gap-3">
                <div class="rean">
                    <h3 class="fw-bold mb-0">បញ្ជីរាយថ្នាក់រៀន 🏫</h3>
                </div>
                
                <div style="width: 100%; max-width: 400px;">
                    <div class="input-group search-container">
                        <span class="input-group-text">
                            <i class="fa-solid fa-magnifying-glass text-primary"></i>
                        </span>
                        <input type="text" id="studentSearch" class="form-control" placeholder="ស្វែងរកឈ្មោះសិស្ស ឬ អ៊ីមែល...">
                    </div>
                </div>
            </div>


        <div class="accordion border-0" id="classAccordion">
            <?php 
            $index = 0;
            foreach ($students_by_class as $class_name => $class_students): 
                // បង្ហាញតែថ្នាក់ដែលមានសិស្ស ឬថ្នាក់ដែលមានក្នុង DB (លើកលែងតែក្រុម 'មិនទាន់មានថ្នាក់' បើគ្មានសិស្ស)
                if (empty($class_students) && $class_name == 'មិនទាន់មានថ្នាក់') continue;
                $index++;
                $collapse_id = "collapse_" . $index;
            ?>
                <div class="card mb-3 border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-header bg-white border-0 p-0">
                        <button class="accordion-button collapsed py-3 px-4 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#<?= $collapse_id ?>" style="background: #fff;">
                            <div class="d-flex align-items-center w-100">
                                <div class="student-avatar me-3" style="width: 40px; height: 40px; background: var(--primary-color);">
                                    <i class="fa-solid fa-graduation-cap text-white"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold text-dark"><?= htmlspecialchars($class_name) ?></h6>
                                    <small class="text-muted">សិស្សសរុប៖ <?= count($class_students) ?> នាក់</small>
                                </div>
                                <span class="badge rounded-pill ms-auto me-3" style="background: rgba(99, 102, 241, 0.1); color: var(--primary-color);">
                                    Active
                                </span>
                            </div>
                        </button>
                    </div>

                    <div id="<?= $collapse_id ?>" class="accordion-collapse collapse" data-bs-parent="#classAccordion">
                        <div class="card-body p-0 border-top">
                            <div class="table-responsive">
                                <table class="table align-middle mb-0 table-hover">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="ps-4 py-3 border-0 small text-uppercase fw-bold text-muted">ឈ្មោះសិស្ស</th>
                                            <th class="border-0 small text-uppercase fw-bold text-muted">ភេទ</th>
                                            <th class="border-0 small text-uppercase fw-bold text-muted">អ៊ីមែល</th>
                                            <th class="text-end pe-4 border-0 small text-uppercase fw-bold text-muted">សកម្មភាព</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($class_students)): ?>
                                            <tr>
                                                <td colspan="4" class="text-center py-4 text-muted small">មិនទាន់មានសិស្សក្នុងថ្នាក់នេះនៅឡើយទេ</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($class_students as $row): 
                                                $display_img = (!empty($row['profile_image'])) ? "../../../public/images/profiles/" . $row['profile_image'] : "https://ui-avatars.com/api/?name=" . urlencode($row['username']) . "&background=6366f1&color=fff";
                                            ?>
                                                <tr>
                                                    <td class="ps-4">
                                                        <div class="d-flex align-items-center">
                                                            <img src="<?= $display_img ?>" class="rounded-circle border me-3 shadow-sm" style="width: 40px; height: 40px; object-fit: cover;">
                                                            <div>
                                                                <div class="fw-bold text-dark mb-0 small text-uppercase"><?= htmlspecialchars($row['username']) ?></div>
                                                                <small class="text-muted">ID: #<?= str_pad($row['id'], 4, '0', STR_PAD_LEFT) ?></small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge rounded-pill px-3" style="background: <?= ($row['gender'] == 'M') ? 'rgba(99, 102, 241, 0.1)' : 'rgba(244, 63, 94, 0.1)' ?>; color: <?= ($row['gender'] == 'M') ? '#6366f1' : '#f43f5e' ?>;">
                                                            <?= ($row['gender'] == 'M') ? 'ប្រុស' : 'ស្រី' ?>
                                                        </span>
                                                    </td>
                                                    <td class="text-muted small"><?= htmlspecialchars($row['email']) ?></td>
                                                    <td class="text-end pe-4">
                                                        <div class="btn-group shadow-sm rounded-3 overflow-hidden">
                                                            <button class="btn btn-white btn-sm px-2 border-end" onclick='viewStudent(<?= json_encode($row) ?>, "<?= $display_img ?>")' title="មើល">
                                                                <i class="fa-solid fa-eye text-primary"></i>
                                                            </button>
                                                            <button class="btn btn-white btn-sm px-2 border-end" onclick='editStudent(<?= json_encode($row) ?>)' title="កែប្រែ">
                                                                <i class="fa-solid fa-pen text-warning"></i>
                                                            </button>
                                                            <button class="btn btn-white btn-sm px-2" onclick="confirmDelete(<?= $row['id'] ?>, '<?= $row['username'] ?>')" title="លុប">
                                                                <i class="fa-solid fa-trash text-danger"></i>
                                                            </button>
                                                        </div>
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
            <?php endforeach; ?>
        </div>

        <div class="modal fade" id="addStudentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form action="../../Controllers/StudentController.php?action=add" method="POST" enctype="multipart/form-data">
                <div class="modal-header bg-primary text-white border-0 py-3">
                    <h5 class="modal-title fw-bold"><i class="fa-solid fa-user-plus me-2"></i>បន្ថែមសិស្សថ្មី</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">ឈ្មោះសិស្ស</label>
                        <input type="text" name="username" class="form-control rounded-3" placeholder="បញ្ចូលឈ្មោះ..." required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">អ៊ីមែល</label>
                        <input type="email" name="email" class="form-control rounded-3" placeholder="example@gmail.com" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small">ជ្រើសរើសថ្នាក់</label>
                            <select name="class_id" class="form-select rounded-3" required>
                                <option value="">--- រើសថ្នាក់ ---</option>
                                <?php foreach($classes as $c): ?>
                                    <option value="<?= $c['id'] ?>"><?= $c['class_name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small">ភេទ</label>
                            <select name="gender" class="form-select rounded-3">
                                <option value="M">ប្រុស</option>
                                <option value="F">ស្រី</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-bold small">រូបថតសិស្ស</label>
                        <input type="file" name="profile_image" class="form-control rounded-3">
                    </div>
                </div>
                <div class="modal-footer border-0 pb-4">
                    <button type="button" class="btn btn-light px-4 rounded-pill" data-bs-dismiss="modal">បោះបង់</button>
                    <button type="submit" class="btn btn-primary px-4 rounded-pill">បង្កើតថ្មី</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="dynamicViewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-body text-center p-0">
                <div class="p-4" style="background: linear-gradient(135deg, #6366f1 0%, #4338ca 100%);">
                    <img id="view-profile-img" src="" class="rounded-circle mb-3 border border-4 border-white shadow-sm" style="width: 110px; height: 110px; object-fit: cover;">
                    <h4 id="view-username" class="fw-bold text-white mb-1 uppercase"></h4>
                    <span id="view-id" class="badge bg-white text-primary rounded-pill px-3"></span>
                </div>
                <div class="p-4 bg-white">
                    <div class="row g-3">
                        <div class="col-6 text-start">
                            <label class="text-muted small d-block">អ៊ីមែល</label>
                            <span id="view-email" class="fw-bold"></span>
                        </div>
                        <div class="col-6 text-start">
                            <label class="text-muted small d-block">ថ្នាក់រៀន</label>
                            <span id="view-class" class="fw-bold text-primary"></span>
                        </div>
                        <div class="col-6 text-start">
                            <label class="text-muted small d-block">ភេទ</label>
                            <span id="view-gender" class="fw-bold"></span>
                        </div>
                        <div class="col-6 text-start">
                            <label class="text-muted small d-block">ថ្ងៃខែឆ្នាំកំណើត</label>
                            <span id="view-dob" class="fw-bold"></span>
                        </div>
                        <div class="col-12 text-start">
                            <label class="text-muted small d-block">អាសយដ្ឋាន</label>
                            <span id="view-address" class="fw-bold text-muted"></span>
                        </div>
                    </div>
                </div>
                <div class="p-3 bg-light border-top">
                    <button class="btn btn-secondary px-5 rounded-pill shadow-sm" data-bs-dismiss="modal">បិទ</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="dynamicEditModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form action="../../Controllers/StudentController.php?action=edit" method="POST">
                <input type="hidden" name="id" id="edit-id">
                <div class="modal-header bg-warning py-3 border-0">
                    <h5 class="modal-title fw-bold text-dark"><i class="fa-solid fa-pen-to-square me-2"></i>កែប្រែព័ត៌មានសិស្ស</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">ឈ្មោះសិស្ស</label>
                        <input type="text" name="username" id="edit-username" class="form-control rounded-3" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">អ៊ីមែល</label>
                        <input type="email" name="email" id="edit-email" class="form-control rounded-3" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small">ថ្នាក់រៀន</label>
                            <select name="class_id" id="edit-class" class="form-select rounded-3">
                                <?php foreach($classes as $c): ?>
                                    <option value="<?= $c['id'] ?>"><?= $c['class_name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small">ភេទ</label>
                            <select name="gender" id="edit-gender" class="form-select rounded-3">
                                <option value="M">ប្រុស</option>
                                <option value="F">ស្រី</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">ថ្ងៃខែឆ្នាំកំណើត</label>
                        <input type="date" name="dob" id="edit-dob" class="form-control rounded-3">
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-bold small">អាសយដ្ឋាន</label>
                        <textarea name="address" id="edit-address" class="form-control rounded-3" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pb-4">
                    <button type="button" class="btn btn-light px-4 rounded-pill" data-bs-dismiss="modal">បោះបង់</button>
                    <button type="submit" class="btn btn-warning px-4 rounded-pill fw-bold">រក្សាទុក</button>
                </div>
            </form>
        </div>
    </div>
</div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // មុខងារបង្ហាញ View Modal
    function viewStudent(data, img) {
        document.getElementById('view-profile-img').src = img;
        document.getElementById('view-username').innerText = data.username;
        document.getElementById('view-id').innerText = 'ID: #' + data.id.toString().padStart(4, '0');
        document.getElementById('view-email').innerText = data.email;
        document.getElementById('view-class').innerText = data.class_name || 'មិនទាន់មានថ្នាក់';
        document.getElementById('view-gender').innerText = data.gender === 'M' ? 'ប្រុស' : 'ស្រី';
        document.getElementById('view-dob').innerText = data.dob || 'N/A';
        document.getElementById('view-address').innerText = data.address || 'N/A';
        
        var myModal = new bootstrap.Modal(document.getElementById('dynamicViewModal'));
        myModal.show();
    }

    // មុខងារបង្ហាញ Edit Modal
    function editStudent(data) {
        document.getElementById('edit-id').value = data.id;
        document.getElementById('edit-username').value = data.username;
        document.getElementById('edit-email').value = data.email;
        document.getElementById('edit-class').value = data.class_id;
        document.getElementById('edit-gender').value = data.gender;
        document.getElementById('edit-dob').value = data.dob;
        document.getElementById('edit-address').value = data.address;

        var myModal = new bootstrap.Modal(document.getElementById('dynamicEditModal'));
        myModal.show();
    }

    // លុបសិស្ស
    function confirmDelete(id, name) {
        Swal.fire({
            title: 'តើអ្នកប្រាកដទេ?',
            text: "ចង់លុបសិស្ស " + name + " ចេញពីប្រព័ន្ធ?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'បាទ លុបចេញ',
            cancelButtonText: 'បោះបង់'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "../../Controllers/StudentController.php?action=delete&id=" + id;
            }
        });
    }

    // Status Notification
    const urlParams = new URLSearchParams(window.location.search);
    const status = urlParams.get('status');
    if (status === 'success') Swal.fire({ icon: 'success', title: 'ជោគជ័យ', text: 'ប្រតិបត្តិការបានជោគជ័យ!', timer: 2000, showConfirmButton: false });
    else if (status === 'error') Swal.fire({ icon: 'error', title: 'បរាជ័យ', text: 'មានបញ្ហាកើតឡើង!' });

    document.getElementById('studentSearch').addEventListener('keyup', function() {
    let filter = this.value.toLowerCase();
    let accordions = document.querySelectorAll('.accordion-item, .card.mb-3'); // Selects your class cards

    accordions.forEach(function(card) {
        let rows = card.querySelectorAll('tbody tr');
        let hasVisibleRow = false;

        rows.forEach(function(row) {
            // Get Username (first column) and Email (third column)
            let username = row.querySelector('.fw-bold.text-dark').innerText.toLowerCase();
            let email = row.querySelector('td:nth-child(3)').innerText.toLowerCase();

            if (username.includes(filter) || email.includes(filter)) {
                row.style.display = "";
                hasVisibleRow = true;
            } else {
                row.style.display = "none";
            }
        });

        // UI Logic: Hide the entire class card if no students match in that class
        if (hasVisibleRow) {
            card.style.display = "";
            
            // Automatically expand the accordion if searching
            if (filter.length > 0) {
                let collapseElement = card.querySelector('.accordion-collapse');
                if (collapseElement && !collapseElement.classList.contains('show')) {
                    let bsCollapse = new bootstrap.Collapse(collapseElement, { toggle: false });
                    bsCollapse.show();
                }
            }
        } else {
            card.style.display = "none";
        }
    });
});
</script>
</body>
</html>