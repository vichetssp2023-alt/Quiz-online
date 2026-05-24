<?php
session_start();
require_once "../../../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../../public/index.php");
    exit();
}

try {
    $database = new Database();
    $db = $database->getConnection();

    // ១. ទាញយកស្ថិតិអាសយដ្ឋាន
    $addr_query = "SELECT address, COUNT(*) as total 
               FROM users 
               WHERE address IS NOT NULL 
               AND address != '' 
               AND role = 'teacher' 
               GROUP BY address";
    $addr_stmt = $db->prepare($addr_query);
    $addr_stmt->execute();
    $address_stats = $addr_stmt->fetchAll(PDO::FETCH_ASSOC);

    // ២. ទាញយកបញ្ជីឈ្មោះគ្រូបង្រៀន
   // ២. ទាញយកបញ្ជីឈ្មោះគ្រូបង្រៀន (បន្ថែម profile_image ក្នុង SELECT)
    $t_query = "SELECT id, username, email, role, gender, dob, address, profile_image, created_at FROM users WHERE role = 'teacher' ORDER BY id DESC";
    $t_stmt = $db->prepare($t_query);
    $t_stmt->execute();
    $teachers = $t_stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    die("Error Connection: " . $e->getMessage());
}

// ៣. គណនាស្ថិតិគ្រូ
$total_teachers = count($teachers);
$female_count = 0;
$male_count = 0;

foreach ($teachers as $t) {
    if (($t['gender'] ?? '') == 'F') {
        $female_count++;
    } elseif (($t['gender'] ?? '') == 'M') {
        $male_count++;
    }
}
?>
<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>គ្រប់គ្រងទិន្នន័យគ្រូ - QuizMaster</title>
    <link rel="stylesheet" href="../../../../node_modules/bootstrap/dist/css/bootstrap.min.css">

    <!-- Line 9 - fix this -->
    <script src="../../../../node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="../../../public/fontawesome-free-7.2.0-web/css/all.min.css">
    <link rel="stylesheet" href="../../../public/css/adminstyle/manage_teacher.css">
    <script src="../../../public/js/sweetalert2.all.min.js"></script>
</head>
<body>

<div class="d-flex">
    <?php include 'partials/sidebar.php'; ?>

    <main class="main-content w-100">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h3 class="fw-bold mb-0 text-dark">គ្រប់គ្រងទិន្នន័យគ្រូ</h3>
                <p class="text-muted small">បញ្ជីឈ្មោះគ្រូបង្រៀនទាំងអស់ក្នុងប្រព័ន្ធ</p>
            </div>
            <div class="btn-create">
                 <button class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#addTeacherModal">
                <i class="fa-solid fa-user-plus me-2"></i> បន្ថែមគ្រូថ្មី
            </button>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="card stat-card shadow-sm p-4 border-start border-danger border-5 bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 fw-medium">ចំនួនគ្រូ សរុប</p>
                            <h2 class="fw-bold mb-0 text-dark"><?= $total_teachers; ?> នាក់</h2>
                        </div>
                        <div class="fs-1 text-danger opacity-25"><i class="fa-solid fa-users"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card shadow-sm p-4 border-start border-primary border-5 bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 fw-medium">គ្រូបង្រៀន ភេទស្រី</p>
                            <h2 class="fw-bold mb-0 text-dark"><?= $female_count; ?> នាក់</h2>
                        </div>
                        <div class="fs-1 text-primary opacity-25"><i class="fa-solid fa-venus"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card shadow-sm p-4 border-start border-success border-5 bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 fw-medium">គ្រូបង្រៀន ភេទប្រុស</p>
                            <h2 class="fw-bold mb-0 text-dark"><?= $male_count; ?> នាក់</h2>
                        </div>
                        <div class="fs-1 text-success opacity-25"><i class="fa-solid fa-mars"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <!-- SEARCH -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="input-group shadow-sm rounded-pill overflow-hidden border">
                    <span class="input-group-text bg-white border-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                    <input type="text" id="teacherSearch" class="form-control border-0 px-2" placeholder="ស្វែងរកឈ្មោះគ្រូ ឬ អ៊ីមែល...">
                </div>
            </div>
        </div>

        <div class="card shadow-sm p-4 bg-white">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="border-0 ps-4">គ្រូបង្រៀន</th>
                            <th class="border-0 text-center">ភេទ</th>
                            <th class="border-0">អ៊ីមែល</th>
                            <th class="border-0">ស្ថានភាព</th>
                            <th class="border-0 text-end pe-4">សកម្មភាព</th>
                        </tr>
                    </thead>
                    <tbody id="teacherTableBody">
                        <tr id="noResults" style="display: none;">
                            <td colspan="5" class="text-center py-4 text-muted">រកមិនឃើញទិន្នន័យដែលអ្នកស្វែងរកទេ</td>
                        </tr>

                        <?php foreach ($teachers as $row): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="teacher-avatar me-3 shadow-sm border" style="width: 40px; height: 40px; border-radius: 50%; overflow: hidden; display: flex; align-items: center; justify-content: center; background-color: #f8f9fa;">
                                            <?php 
                                                $profile_img = $row['profile_image'] ?? '';
                                                $img_path = "../../../public/images/profiles/" . $profile_img;

                                                if (!empty($profile_img) && file_exists(__DIR__ . "/../../../public/images/profiles/" . $profile_img)): 
                                            ?>
                                                <img src="<?= $img_path; ?>" class="w-100 h-100 object-fit-cover">
                                            <?php else: ?>
                                                <div class="bg-primary text-white w-100 h-100 d-flex align-items-center justify-content-center fw-bold" style="font-size: 14px;">
                                                    <?= strtoupper(substr($row['username'] ?? 'TR', 0, 2)); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($row['username']); ?></div>
                                    </div>
                                </td>
                                <td class="text-center"><?= ($row['gender'] == 'M') ? 'ប្រុស' : 'ស្រី'; ?></td>
                                <td class="text-muted"><?= htmlspecialchars($row['email']); ?></td>
                                <td><span class="badge bg-success-subtle text-success px-3 rounded-pill small">Active</span></td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-light btn-sm shadow-sm me-1" data-bs-toggle="modal" data-bs-target="#viewModal<?= $row['id']; ?>"><i class="fa-solid fa-eye text-primary"></i></button>
                                    <button class="btn btn-light btn-sm shadow-sm me-1" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['id']; ?>"><i class="fa-solid fa-pen text-warning"></i></button>
                                    <button class="btn btn-light btn-sm text-danger shadow-sm" onclick="confirmDelete(<?= $row['id']; ?>, '<?= htmlspecialchars($row['username']); ?>')">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?> 
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-5">
            <h5 class="fw-bold mb-4">ស្ថិតិអ្នកប្រើប្រាស់តាមខេត្ត/ក្រុង</h5>
            <div class="row g-4">
                <?php if (count($address_stats) > 0): ?>
                    <?php foreach ($address_stats as $stat): ?>
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-info border-5">
                                <div class="d-flex align-items-center">
                                    <div class="bg-info bg-opacity-10 p-3 rounded-circle text-info me-3">
                                        <i class="fa-solid fa-location-dot fs-4"></i>
                                    </div>
                                    <div>
                                        <p class="text-muted mb-0 small fw-bold text-uppercase"><?= htmlspecialchars($stat['address']); ?></p>
                                        <h4 class="fw-bold mb-0"><?= $stat['total']; ?> នាក់</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center text-muted">មិនទាន់មានទិន្នន័យអាសយដ្ឋាន</div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<div class="modal fade" id="addTeacherModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form action="../../Controllers/TeacherController.php?action=add" method="POST">
                <div class="modal-header border-0 pb-0 px-4 pt-4">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 p-2 rounded-3 me-3 text-primary"><i class="fa-solid fa-user-plus fs-4"></i></div>
                        <div><h5 class="fw-bold mb-0">បន្ថែមគ្រូថ្មី</h5><p class="text-muted small mb-0">បង្កើតគណនីថ្មី</p></div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="small text-muted fw-bold">ឈ្មោះពេញ</label><input type="text" name="username" class="form-control rounded-pill bg-light border-0 px-3" placeholder="ឈ្មោះគ្រូ" required></div>
                        <div class="col-md-6"><label class="small text-muted fw-bold">អ៊ីមែល</label><input type="email" name="email" class="form-control rounded-pill bg-light border-0 px-3" placeholder="example@gmail.com" required></div>
                        <div class="col-md-6">
                            <label class="small text-muted fw-bold">ភេទ</label>
                            <select name="gender" class="form-select rounded-pill bg-light border-0 px-3">
                                <option value="M">ប្រុស (Male)</option>
                                <option value="F">ស្រី (Female)</option>
                            </select>
                        </div>
                        <div class="col-md-6"><label class="small text-muted fw-bold">ថ្ងៃខែឆ្នាំកំណើត</label><input type="date" name="dob" class="form-control rounded-pill bg-light border-0 px-3" required></div>
                        <div class="col-12"><label class="small text-muted fw-bold">អាសយដ្ឋាន</label><textarea name="address" class="form-control rounded-4 bg-light border-0 px-3" rows="2" placeholder="អាសយដ្ឋានបច្ចុប្បន្ន"></textarea></div>
                        <div class="col-12"><label class="small text-muted fw-bold">លេខសម្ងាត់</label><input type="password" name="password" class="form-control rounded-pill bg-light border-0 px-3" value="123456" required></div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold py-2 shadow-sm">បង្កើតគណនី</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php foreach ($teachers as $row): ?>
    <div class="modal fade" id="viewModal<?= $row['id']; ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0 px-4 pt-4">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 p-2 rounded-3 me-3 text-primary"><i class="fa-solid fa-address-card fs-4"></i></div>
                        <div><h5 class="fw-bold mb-0">ព័ត៌មានលម្អិត</h5><p class="text-muted small mb-0">Profile Overview</p></div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="text-center mb-4">
                        <div class="teacher-avatar mx-auto mb-2 shadow-sm" style="width: 80px; height: 80px; font-size: 1.8rem; overflow: hidden; display: flex; align-items: center; justify-content: center; background-color: #f8f9fa;">
                            <?php 
                                $profile_img = $row['profile_image'] ?? '';
                                // ពិនិត្យមើល Path រូបភាព (ត្រូវប្រាកដថា Path នេះត្រឹមត្រូវតាម Folder របស់អ្នក)
                                $img_path = "../../../public/images/profiles/" . $profile_img;

                                if (!empty($profile_img) && file_exists(__DIR__ . "/../../../public/images/profiles/" . $profile_img)): 
                            ?>
                                <img src="<?= $img_path; ?>" class="w-100 h-100 object-fit-cover rounded-circle">
                            <?php else: ?>
                                <div class="bg-primary text-white w-100 h-100 d-flex align-items-center justify-content-center fw-bold">
                                    <?= strtoupper(substr($row['username'], 0, 2)); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <h4 class="fw-bold text-dark mb-0"><?= htmlspecialchars($row['username']); ?></h4>
                        <span class="badge bg-primary-subtle text-primary rounded-pill px-3">គ្រូបង្រៀន</span>
                    </div>
                    <div class="row g-3 text-start bg-light p-3 rounded-4">
                        <div class="col-6"><label class="small text-muted d-block">អត្តលេខ (ID)</label><span class="fw-bold">#<?= $row['id']; ?></span></div>
                        <div class="col-6"><label class="small text-muted d-block">ភេទ</label><span class="fw-bold"><?= ($row['gender'] == 'M') ? 'ប្រុស' : 'ស្រី'; ?></span></div>
                        <div class="col-6"><label class="small text-muted d-block">ថ្ងៃខែឆ្នាំកំណើត</label><span class="fw-bold"><?= ($row['dob']) ? date('d M Y', strtotime($row['dob'])) : 'N/A'; ?></span></div>
                        <div class="col-6"><label class="small text-muted d-block">អ៊ីមែល</label><span class="fw-bold small text-truncate d-block"><?= htmlspecialchars($row['email']); ?></span></div>
                        <div class="col-12"><label class="small text-muted d-block">អាសយដ្ឋាន</label><span class="fw-bold"><?= htmlspecialchars($row['address'] ?? 'មិនទាន់មាន'); ?></span></div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button class="btn btn-light w-100 rounded-pill fw-bold" data-bs-dismiss="modal">បិទផ្ទាំងនេះ</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editModal<?= $row['id']; ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <form action="../../Controllers/TeacherController.php?action=edit" method="POST">
                    <input type="hidden" name="id" value="<?= $row['id']; ?>">
                    <div class="modal-header border-0 pb-0 px-4 pt-4">
                        <div class="d-flex align-items-center">
                            <div class="bg-warning bg-opacity-10 p-2 rounded-3 me-3 text-warning"><i class="fa-solid fa-user-pen fs-4"></i></div>
                            <div><h5 class="fw-bold mb-0">កែប្រែទិន្នន័យគ្រូ</h5><p class="text-muted small mb-0">Update information</p></div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6"><label class="small text-muted fw-bold">ឈ្មោះពេញ</label><input type="text" name="username" class="form-control rounded-pill bg-light border-0 px-3" value="<?= htmlspecialchars($row['username']); ?>" required></div>
                            <div class="col-md-6"><label class="small text-muted fw-bold">អ៊ីមែល</label><input type="email" name="email" class="form-control rounded-pill bg-light border-0 px-3" value="<?= htmlspecialchars($row['email']); ?>" required></div>
                            <div class="col-md-6">
                                <label class="small text-muted fw-bold">ភេទ</label>
                                <select name="gender" class="form-select rounded-pill bg-light border-0 px-3">
                                    <option value="M" <?= ($row['gender'] == 'M') ? 'selected' : ''; ?>>ប្រុស (Male)</option>
                                    <option value="F" <?= ($row['gender'] == 'F') ? 'selected' : ''; ?>>ស្រី (Female)</option>
                                </select>
                            </div>
                            <div class="col-md-6"><label class="small text-muted fw-bold">ថ្ងៃខែឆ្នាំកំណើត</label><input type="date" name="dob" class="form-control rounded-pill bg-light border-0 px-3" value="<?= $row['dob']; ?>"></div>
                            <div class="col-12"><label class="small text-muted fw-bold">អាសយដ្ឋាន</label><textarea name="address" class="form-control rounded-4 bg-light border-0 px-3" rows="2"><?= htmlspecialchars($row['address']); ?></textarea></div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <div class="row w-100 g-2">
                            <div class="col-6"><button type="button" class="btn btn-light w-100 rounded-pill fw-bold" data-bs-dismiss="modal">បោះបង់</button></div>
                            <div class="col-6"><button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold shadow-sm">រក្សាទុក</button></div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const urlParams = new URLSearchParams(window.location.search);
    const status = urlParams.get('status');
    const toast = (icon, text) => Swal.fire({ icon, title: 'ជោគជ័យ', text, timer: 2000, showConfirmButton: false });
    
    if(status === 'success') toast('success', 'បន្ថែមគ្រូរួចរាល់!');
    if(status === 'updated') toast('success', 'កែប្រែរួចរាល់!');
    if(status === 'deleted') toast('success', 'លុបរួចរាល់!');

    function confirmDelete(id, name) {
        Swal.fire({
            title: 'តើអ្នកប្រាកដទេ?', text: "ចង់លុបគណនី " + name + " ឬ?", icon: 'warning',
            showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'បាទ, លុប!', cancelButtonText: 'បោះបង់'
        }).then((result) => { if (result.isConfirmed) window.location.href = `../../Controllers/TeacherController.php?action=delete&id=${id}`; });
    }

    document.getElementById('teacherSearch').addEventListener('keyup', function() {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll('#teacherTableBody tr:not(#noResults)');
    let found = false;

    rows.forEach(row => {
        let text = row.textContent.toLowerCase();
        if (text.includes(filter)) {
            row.style.display = "";
            found = true;
        } else {
            row.style.display = "none";
        }
    });

    document.getElementById('noResults').style.display = found ? "none" : "";
});
</script>
</body>
</html>