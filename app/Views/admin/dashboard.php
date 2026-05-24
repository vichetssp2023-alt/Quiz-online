<?php
session_start();

if (isset($_GET['mark_read']) && $_GET['mark_read'] == 1) {
    require_once "../../../config/database.php";
    $database = new Database();
    $db = $database->getConnection();
    $admin_id = $_SESSION['user_id'] ?? 0;
    $db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?")->execute([$admin_id]);
    echo json_encode(['status' => 'success']);
    exit();
}

// 1. Security Check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../../public/index.php");
    exit();
}

require_once "../../../config/database.php"; 

// Get the logged-in Admin's ID from the session
$admin_id = $_SESSION['user_id'] ?? 0; // Ensure you set 'user_id' during login

// Initialize variables
$count_students = 0;
$count_teachers = 0;
$count_quizzes = 0;
$unread_count = 0;
$users = [];
$notifications = [];

try {
    $database = new Database();
    $db = $database->getConnection();

    // 2. Optimized Counting
    $count_students = $db->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
    $count_teachers = $db->query("SELECT COUNT(*) FROM users WHERE role = 'teacher'")->fetchColumn();
    $count_quizzes = $db->query("SELECT COUNT(*) FROM quizzes")->fetchColumn();

    // 3. Fetch Recent Users
    $query = "SELECT * FROM users ORDER BY id DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. Fetch Notifications for the logged-in Admin
    // We use $admin_id instead of the undefined $student_id
    $unread_stmt = $db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $unread_stmt->execute([$admin_id]);
    $unread_count = $unread_stmt->fetchColumn();

    $notif_list_stmt = $db->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
    $notif_list_stmt->execute([$admin_id]);
    $notifications = $notif_list_stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $error_msg = $e->getMessage();
}


?>
<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - QuizMaster</title>
    <link rel="stylesheet" href="../../../../node_modules/bootstrap/dist/css/bootstrap.min.css">

    <!-- Line 9 - fix this -->
    <!-- <script src="../../../../node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script> -->
  <link rel="stylesheet" href="../../../public/fontawesome-free-7.2.0-web/css/all.min.css">
    <link rel="stylesheet" href="../../../public/css/adminstyle/dashboard.css">
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
                        data-bs-auto-close="outside"
                        aria-expanded="false"
                        aria-haspopup="true"
                        role="button"
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
                                        href="manage_quiz.php" 
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
                                        <a href="manage_quiz.php" class="dropdown-item py-2 small fw-bold text-primary">ពិនិត្យមើល</a>
                                    </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </ul>
                </div>
                <div class="bg-white p-2 rounded-pill shadow-sm px-4 fw-bold text-primary hover-profile" 
                     style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#adminProfileModal">
                    <i class="fa-solid fa-user-shield me-2"></i>Admin Profile
                </div>
            </div>
        </div>
        
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="card stat-card shadow-sm p-4 border-0 border-start border-danger border-5 bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 fw-medium">សិស្សសរុប</p>
                            <h2 class="fw-bold mb-0 text-dark"><?php echo number_format($count_students); ?></h2>
                        </div>
                        <div class="fs-1 text-danger opacity-25"><i class="fa-solid fa-user-graduate"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card shadow-sm p-4 border-0 border-start border-primary border-5 bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 fw-medium">វិញ្ញាសាសរុប</p>
                            <h2 class="fw-bold mb-0 text-dark"><?php echo number_format($count_quizzes); ?></h2>
                        </div>
                        <div class="fs-1 text-primary opacity-25"><i class="fa-solid fa-book-open"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card shadow-sm p-4 border-0 border-start border-success border-5 bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 fw-medium">គ្រូបង្រៀនសរុប</p>
                            <h2 class="fw-bold mb-0 text-dark"><?php echo number_format($count_teachers); ?></h2>
                        </div>
                        <div class="fs-1 text-success opacity-25"><i class="fa-solid fa-chalkboard-user"></i></div>
                    </div>
                </div>
            </div>
        </div>         

        <div class="card border-0 shadow-sm rounded-4 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                <h5 class="fw-bold mb-0">អ្នកប្រើប្រាស់ចុះឈ្មោះថ្មីៗ</h5>
                
                <div class="position-relative" style="width: 300px;">
                    <span class="position-absolute top-50 start-0 translate-middle-y ms-3 text-muted">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>
                    <input type="text" id="userSearchInput" class="form-control rounded-pill ps-5 border-0 bg-light shadow-none" 
                        placeholder="ស្វែងរកឈ្មោះ ឬអ៊ីមែល...">
                </div>

                <button id="btnToggleView" class="btn btn-primary btn-sm rounded-pill px-4">មើលទាំងអស់</button>
            </div>
            <div class="table-responsive">
                <table id="userTable" class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="border-0">ឈ្មោះ</th>
                            <th class="border-0">តួនាទី</th>
                            <th class="border-0">ស្ថានភាព</th>
                            <th class="border-0 text-end">សកម្មភាព</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $index => $row): ?>
                        <tr class="user-row <?= $index >= 5 ? 'd-none' : '' ?>">
                            <td>
                               <div class="d-flex align-items-center">
                                <div class="rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm border" style="width: 40px; height: 40px; overflow: hidden; background-color: #f8f9fa;">
                                    <?php 
                                        $profile_img = $row['profile_image'] ?? '';
                                        // ផ្លូវ Path ចេញពី Views/admin/ ទៅកាន់ public/images/profiles/
                                        $img_path = "../../../public/images/profiles/" . $profile_img;

                                        // ពិនិត្យថា តើមានរូបភាពក្នុង DB និងមាន File ពិតប្រាកដក្នុង Folder ឬទេ
                                        if (!empty($profile_img) && file_exists(__DIR__ . "/../../../public/images/profiles/" . $profile_img)): 
                                    ?>
                                        <img src="<?php echo $img_path; ?>" class="w-100 h-100 object-fit-cover">
                                    <?php else: ?>
                                        <div class="bg-primary text-white w-100 h-100 d-flex align-items-center justify-content-center fw-bold" style="font-size: 14px;">
                                            <?php echo strtoupper(substr($row['username'], 0, 2)); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark"><?php echo htmlspecialchars($row['username']); ?></div>
                                    <div class="small text-muted" style="font-size: 0.75rem;"><?php echo htmlspecialchars($row['email']); ?></div>
                                </div>
                            </div>
                            </td>
                            <td><span class="badge bg-primary-subtle text-primary px-3 rounded-pill"><?php echo ucfirst($row['role']); ?></span></td>
                            <td><span class="text-success small fw-bold">● Active</span></td>
                            <td class="text-end">
                                <button class="btn btn-light btn-sm rounded-pill border shadow-none" data-bs-toggle="modal" data-bs-target="#viewUserModal<?php echo $row['id']; ?>"><i class="fa-solid fa-eye text-primary"></i></button>
                                <button class="btn btn-light btn-sm rounded-pill border shadow-none" data-bs-toggle="modal" data-bs-target="#editUserModal<?php echo $row['id']; ?>"><i class="fa-solid fa-pen text-warning"></i></button>
                                <button class="btn btn-light btn-sm rounded-pill border shadow-none btn-delete" data-id="<?php echo $row['id']; ?>" data-name="<?php echo htmlspecialchars($row['username']); ?>"><i class="fa-solid fa-trash text-danger"></i></button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<div class="modal fade" id="adminProfileModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="position-relative" style="height: 120px; background: linear-gradient(45deg, #6366f1, #a855f7);">
                <button type="button" class="btn-close btn-close-white position-absolute end-0 top-0 m-3 shadow-none" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body pt-0 text-center">
                <form action="../../Controllers/UserController.php?action=update_profile" method="POST" enctype="multipart/form-data">
                    <div class="position-relative" style="margin-top: -55px;">
                        <div class="bg-white rounded-circle d-inline-flex align-items-center justify-content-center shadow" style="width: 110px; height: 110px; padding: 5px;">
                            <div class="position-relative w-100 h-100">
                                <?php 
                            $profile_img = $_SESSION['profile_image'] ?? '';
                            // បើនៅក្នុង Views/admin/dashboard.php ត្រូវថយក្រោយ ៣ ដងដើម្បីដល់ root
                            $img_path = "../../../public/images/profiles/" . $profile_img;

                            if (!empty($profile_img) && file_exists(__DIR__ . "/../../../public/images/profiles/" . $profile_img)): 
                            ?>
                                <img src="<?php echo $img_path; ?>" id="previewImg" class="rounded-circle w-100 h-100 object-fit-cover shadow-sm">
                            <?php else: ?>
                                <div id="previewPlaceholder" class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center w-100 h-100" style="font-size: 35px; font-weight: bold;">
                                    <?php echo strtoupper(substr($_SESSION['user'] ?? 'AD', 0, 2)); ?>
                                </div>
                                <?php endif; ?>
                                        <label for="profileUpload" class="position-absolute bottom-0 end-0 bg-white rounded-circle shadow-sm d-flex align-items-center justify-content-center border" style="width: 34px; height: 34px; cursor: pointer; transition: 0.3s;" onmouseover="this.style.backgroundColor='#f8fafc'" onmouseout="this.style.backgroundColor='#fff'">
                                    <i class="fa-solid fa-camera text-primary" style="font-size: 14px;"></i>
                                    <input type="file" id="profileUpload" name="profile_image" class="d-none" accept="image/*" onchange="previewImage(this)">
                                </label>
                            </div>
                        </div>
                    </div>

                    <h4 class="fw-bold mt-3 mb-1"><?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Administrator'); ?></h4>
                    <p class="text-muted small mb-4">
                        <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2 fw-medium">
                            <i class="fa-solid fa-shield-halved me-1"></i> System Admin
                        </span>
                    </p>

                    <div class="row g-3 text-start mb-4">
                        <div class="col-12">
                            <div class="p-2 px-3 bg-light rounded-3 border-0">
                                <label class="small text-muted d-block" style="font-size: 0.75rem;">អ៊ីមែល</label>
                                <input type="email" name="email" class="form-control border-0 bg-transparent p-0 fw-bold shadow-none" value="<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="p-2 px-3 bg-light rounded-3 border-0">
                                <label class="small text-muted d-block" style="font-size: 0.75rem;">ភេទ</label>
                                <select name="gender" class="form-select border-0 bg-transparent p-0 fw-bold shadow-none cursor-pointer">
                                    <option value="M" <?php echo ($_SESSION['gender'] ?? '') == 'M' ? 'selected' : ''; ?>>ប្រុស</option>
                                    <option value="F" <?php echo ($_SESSION['gender'] ?? '') == 'F' ? 'selected' : ''; ?>>ស្រី</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="p-2 px-3 bg-light rounded-3 border-0">
                                <label class="small text-muted d-block" style="font-size: 0.75rem;">ថ្ងៃខែឆ្នាំកំណើត</label>
                                <input type="date" name="dob" class="form-control border-0 bg-transparent p-0 fw-bold shadow-none" value="<?php echo $_SESSION['dob'] ?? ''; ?>">
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="p-2 px-3 bg-light rounded-3 border-0">
                                <label class="small text-muted d-block" style="font-size: 0.75rem;">អាសយដ្ឋាន</label>
                                <input type="text" name="address" class="form-control border-0 bg-transparent p-0 fw-bold shadow-none" placeholder="ខេត្ត/ក្រុង..." value="<?php echo htmlspecialchars($_SESSION['address'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary rounded-pill py-2 fw-bold shadow-sm border-0" style="background: #6366f1;">
                            <i class="fa-solid fa-check-circle me-2"></i>រក្សាទុកការផ្លាស់ប្តូរ
                        </button>
                        <a href="dashboard.php" class="btn btn-link text-danger text-decoration-none fw-bold small mt-1 btn-logout-modal" >
                            <i class="fa-solid fa-arrow-right-from-bracket me-1"></i> ចាកចេញពីប្រព័ន្ធ
                        </a>
                    </div>
                </form>
            </div>
            <div class="pb-3"></div>
        </div>
    </div>
</div>

<?php foreach ($users as $row): ?>
    <div class="modal fade" id="viewUserModal<?php echo $row['id']; ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="position-relative" style="height: 100px; background: linear-gradient(45deg, #6366f1, #818cf8);">
                    <button type="button" class="btn-close btn-close-white position-absolute end-0 top-0 m-3 shadow-none" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-0 text-center">
                    <div class="position-relative" style="margin-top: -45px;">
                        <div class="bg-white rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 90px; height: 90px; padding: 5px; overflow: hidden;">
                            <div class="w-100 h-100 rounded-circle d-flex align-items-center justify-content-center overflow-hidden">
                                <?php 
                                    $profile_img = $row['profile_image'] ?? '';
                                    // កែសម្រួល Path ឱ្យត្រូវតាមទីតាំង Folder របស់អ្នក (ឧទាហរណ៍: public/images/profiles/)
                                    $img_path = "../../../public/images/profiles/" . $profile_img;

                                    if (!empty($profile_img) && file_exists(__DIR__ . "/../../../public/images/profiles/" . $profile_img)): 
                                ?>
                                    <img src="<?= $img_path; ?>" class="w-100 h-100 object-fit-cover">
                                <?php else: ?>
                                    <div class="bg-primary text-white w-100 h-100 d-flex align-items-center justify-content-center fw-bold" style="font-size: 30px;">
                                        <?= strtoupper(substr($row['username'], 0, 2)); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <h4 class="fw-bold mt-3 mb-1"><?php echo htmlspecialchars($row['username']); ?></h4>
                    <p class="text-muted small mb-4"><?php echo htmlspecialchars($row['email']); ?></p>
                    <div class="row g-2 mb-4">
                        <div class="col-6"><div class="p-3 bg-light rounded-4 text-start"><small class="text-muted d-block mb-1">តួនាទី</small><span class="badge bg-primary-subtle text-primary rounded-pill"><?php echo ucfirst($row['role']); ?></span></div></div>
                        <div class="col-6"><div class="p-3 bg-light rounded-4 text-start"><small class="text-muted d-block mb-1">ស្ថានភាព</small><span class="text-success small fw-bold">● Active</span></div></div>
                    </div>
                    <button type="button" class="btn btn-secondary w-100 rounded-pill py-2 border-0n btn-view" data-bs-dismiss="modal">បិទ</button>
                </div>
                <div class="pb-4"></div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editUserModal<?php echo $row['id']; ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <form action="../../Controllers/UserController.php?action=update" method="POST">
                    <div class="modal-header border-0 bg-light p-4">
                        <h5 class="fw-bold mb-0">កែប្រែព័ត៌មាន</h5>
                        <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">ឈ្មោះអ្នកប្រើប្រាស់</label>
                            <input type="text" name="username" class="form-control rounded-3 bg-light border-0 py-2" value="<?php echo htmlspecialchars($row['username']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">អ៊ីមែល</label>
                            <input type="email" name="email" class="form-control rounded-3 bg-light border-0 py-2" value="<?php echo htmlspecialchars($row['email']); ?>" required>
                        </div>
                        <div class="mb-0">
                            <label class="form-label small fw-bold">តួនាទី</label>
                            <select name="role" class="form-select rounded-3 bg-light border-0 py-2">
                                <option value="admin" <?php echo $row['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                <option value="teacher" <?php echo $row['role'] == 'teacher' ? 'selected' : ''; ?>>Teacher</option>
                                <option value="student" <?php echo $row['role'] == 'student' ? 'selected' : ''; ?>>Student</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="submit" name="btn_update" class="btn btn-primary w-100 rounded-pill py-2 fw-bold shadow-sm">រក្សាទុកការផ្លាស់ប្តូរ</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<script src="../../../../node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../../public/js/theme-loader.js"></script>
<script src="../../../public/js/sweetalert2.all.min.js"></script>
<script>
    // ✅ Use Bootstrap dropdown event instead of click (fixes conflict)
    const bellEl = document.getElementById('bellDropdown');
    bellEl.addEventListener('shown.bs.dropdown', function() {
        const badge = document.getElementById('unread-badge');
        if (badge) badge.style.display = 'none';

        const xhr = new XMLHttpRequest();
        xhr.open('GET', 'dashboard.php?mark_read=1', true);
        xhr.send();
    });

    // Delete User
    document.querySelectorAll('.btn-delete').forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.getAttribute('data-id');
            const userName = this.getAttribute('data-name');
            Swal.fire({
                title: 'តើអ្នកប្រាកដទេ?',
                text: "អ្នកនឹងលុបអ្នកប្រើប្រាស់ '" + userName + "' នេះ!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'បាទ, លុបវា!',
                cancelButtonText: 'បោះបង់',
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "../../Controllers/UserController.php?action=delete&id=" + userId;
                }
            });
        });
    });

    // Search
    document.getElementById('userSearchInput').addEventListener('keyup', function() {
        const search = this.value.toLowerCase();
        document.querySelectorAll('.user-row').forEach(row => {
            row.classList.toggle('d-none', !row.innerText.toLowerCase().includes(search));
        });
    });

    // Toggle View All
    let showAll = false;
    document.getElementById('btnToggleView').addEventListener('click', function() {
        showAll = !showAll;
        document.querySelectorAll('.user-row').forEach((row, i) => {
            if (i >= 5) row.classList.toggle('d-none', !showAll);
        });
        this.textContent = showAll ? 'បង្រួម' : 'មើលទាំងអស់';
    });
</script>
</body>
</html>