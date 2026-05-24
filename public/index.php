<?php require_once "../config/lang_setup.php"; ?>
<!DOCTYPE html>
<html lang="<?= $current_lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= __('login_title') ?></title>
    <!-- Line 8 - fix this -->
    <link rel="stylesheet" href="../../node_modules/bootstrap/dist/css/bootstrap.min.css">

    <!-- Line 9 - fix this -->
    <script src="../../node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <div class="login-wrapper">
        <div class="login-card p-4 shadow">
            <div class="login-header text-center mb-4">
                <h1>QuizMaster</h1>
                <p><?= __('subtitle') ?></p> </div>

            <form action="../app/Controllers/UserController.php" method="POST">
                <div class="mb-3">
                    <label class="form-label"><?= __('username') ?></label> <input type="text" name="username" class="form-control" placeholder="<?= __('username') ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label"><?= __('password') ?></label> <input type="password" name="password" class="form-control" placeholder="123" required>
                </div>

                <button type="submit" name="login" class="login-btn w-100 btn btn-primary mb-3">
                    <?= __('btn_login') ?> </button>

                <div class="mb-3 border-top pt-3">
                    <label class="form-label small text-muted">ជ្រើសរើសភាសា / Language</label>
                    <select class="form-select form-select-sm" onchange="window.location.href='?lang=' + this.value">
                        <option value="kh" <?= $current_lang == 'kh' ? 'selected' : '' ?>>ខ្មែរ (Khmer)</option>
                        <option value="en" <?= $current_lang == 'en' ? 'selected' : '' ?>>English</option>
                    </select>
                </div>
            </form>

            <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-danger mt-3 small text-center">
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>