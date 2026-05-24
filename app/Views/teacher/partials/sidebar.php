<aside class="sidebar shadow">
    <div class="p-4 text-center">
        <h4 class="fw-bold text-white mb-0">QuizMaster</h4>
        <small class="text-secondary">Teacher Panel</small>
    </div>
    <hr class="mx-3 opacity-25 text-white">
    <nav class="nav flex-column mt-3">
    <?php 
        $current_page = basename($_SERVER['PHP_SELF']); 
    ?>
    <a href="dashboard.php" class="nav-link <?= ($current_page == 'dashboard.php') ? 'active' : '' ?>">
        <i class="fa-solid fa-chart-line me-2"></i> ផ្ទាំងគ្រប់គ្រង
    </a>
    
    <a href="manage_quiz.php" class="nav-link <?= ($current_page == 'manage_quiz.php') ? 'active' : '' ?>">
        <i class="fa-solid fa-book-open me-2"></i> គ្រប់គ្រងវិញ្ញាសា
    </a>

    <a href="manage_questions.php" class="nav-link <?= ($current_page == 'manage_questions.php') ? 'active' : '' ?>">
    <i class="fa-solid fa-list-check me-2"></i> បញ្ជីសំណួរ
    </a>

    <a href="student_results.php" class="nav-link <?= ($current_page == 'student_results.php') ? 'active' : '' ?>">
        <i class="fa-solid fa-square-poll-vertical me-2"></i> លទ្ធផលសិស្ស
    </a>
    
    <hr class="mx-3 opacity-25 text-white">

    <div class="mt-auto pt-5">
        <a href="../../../public/index.php" class="nav-link text-danger">
            <i class="fa-solid fa-power-off me-2"></i> ចាកចេញ
        </a>
    </div>
</nav>
</aside>