<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../public/index.php");
    exit();
}

// ទាញយកទិន្នន័យពី Session
$user_id    = $_SESSION['user_id'];
$full_name  = $_SESSION['full_name'] ?? 'មិនមានឈ្មោះ';
$username   = $_SESSION['user'] ?? '';
$email      = $_SESSION['email'] ?? '';
$gender     = $_SESSION['gender'] ?? '';
$dob        = $_SESSION['dob'] ?? '';
$address    = $_SESSION['address'] ?? '';
$user_img   = $_SESSION['profile_image'] ?? ''; // នេះជាឈ្មោះ file ពី DB (ឧទាហរណ៍: profile_9_...JPG)

// --- កំណត់ Path ឱ្យត្រូវជាមួយ Folder ជាក់ស្តែងរបស់អ្នក ---

// ១. ប្រើ Absolute Path សម្រាប់ PHP ឆែកមើលឯកសារក្នុង Disk
// __DIR__ នឹងយកទីតាំងបច្ចុប្បន្ន (app/Views/student/) រួចថយក្រោយទៅរក public/
$server_path = dirname(__DIR__, 3) . "/public/images/profiles/" . $user_img;

// ២. ប្រើ Relative Path សម្រាប់ HTML បង្ហាញរូបភាព
$web_path = "../../../public/images/profiles/" . $user_img;

// ត្រួតពិនិត្យវត្តមានរូបភាព
if (!empty($user_img) && file_exists($server_path)) {
    // បន្ថែម ?t= ជាមួយ Time ដើមី្បបង្ខំឱ្យ Browser បង្ហាញរូបថ្មីជានិច្ចក្រោយពេល Save
    $display_img = $web_path . "?t=" . time();
} else {
    // បើរកមិនឃើញរូបភាព ប្រើ UI Avatars ជាបណ្តោះអាសន្ន
    $display_img = "https://ui-avatars.com/api/?name=" . urlencode($full_name) . "&background=6366f1&color=fff";
}
?>

<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ព័ត៌មានផ្ទាល់ខ្លួន | Quiz System</title>
    <link rel="stylesheet" href="../../../../node_modules/bootstrap/dist/css/bootstrap.min.css">

    <!-- Line 9 - fix this -->
    <script src="../../../../node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="../../../public/fontawesome-free-7.2.0-web/css/all.min.css">
     <link rel="stylesheet" href="../../../public/css/studentstyle/profile.css">
</head>
<body>

    <?php include 'partials/sidebar.php'; ?>

    <div class="main-content">
        <h2 class="fw-bold mb-4">ការកំណត់ព័ត៌មានផ្ទាល់ខ្លួន</h2>

        <form action="../../Controllers/UserController.php?action=update_profile" method="POST" enctype="multipart/form-data">
            <div class="profile-card">
                <div class="banner"></div>
                
                <div class="avatar-container">
                    <img src="<?= $display_img ?>" id="preview" class="avatar-img" alt="Profile Image">
                    
                    <label for="profile_image" class="upload-badge">
                        <i class="fa-solid fa-camera text-primary"></i>
                    </label>
                    <input type="file" name="profile_image" id="profile_image" class="d-none" accept="image/*" onchange="previewFile(this)">
                </div>

                <div class="p-4 pt-2">
                    <div class="ms-4 mb-4">
                        <h4 class="fw-bold m-0"><?= htmlspecialchars($full_name) ?></h4>
                        <p class="text-muted">សិស្ស (Student) • @<?= htmlspecialchars($username) ?></p>
                    </div>

                    <hr class="mx-3 opacity-50">

                    <div class="row g-4 p-3">
                        <div class="col-md-6">
                            <label class="form-label">អ៊ីមែល (Email Address)</label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($email) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">ភេទ (Gender)</label>
                            <select name="gender" class="form-select form-control">
                                <option value="M" <?= ($gender == 'M') ? 'selected' : '' ?>>ប្រុស (Male)</option>
                                <option value="F" <?= ($gender == 'F') ? 'selected' : '' ?>>ស្រី (Female)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">ថ្ងៃខែឆ្នាំកំណើត</label>
                            <input type="date" name="dob" class="form-control" value="<?= $dob ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">អាសយដ្ឋាន</label>
                            <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($address) ?>">
                        </div>
                        <div class="col-12 text-end mt-5">
                            <button type="submit" class="btn btn-primary px-5 shadow-sm" style="border-radius: 12px; padding: 12px;">
                                <i class="fa-solid fa-check-circle me-2"></i> រក្សាទុកការផ្លាស់ប្តូរ
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        function previewFile(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('preview').src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>