<aside class="sidebar shadow">
    <div class="p-4 text-center">
        <h4 class="fw-bold text-white mb-0">QuizMaster</h4>
        <small class="text-info">ផ្នែកសិស្ស (Student)</small>
    </div>
    <hr class="mx-3 opacity-25 text-white">
    <nav class="nav flex-column mt-3">
    <?php 
        $current_page = basename($_SERVER['PHP_SELF']); 
    ?>
    
    <a href="dashboard.php" class="nav-link <?= ($current_page == 'dashboard.php') ? 'active' : '' ?>">
        <i class="fa-solid fa-house me-2"></i> ផ្ទាំងគ្រប់គ្រង
    </a>
    
    <a href="available_quizzes.php" class="nav-link <?= ($current_page == 'available_quizzes.php') ? 'active' : '' ?>">
        <i class="fa-solid fa-pen-to-square me-2"></i> វិញ្ញាសាប្រឡង
    </a>

    <a href="history.php" class="nav-link <?= ($current_page == 'history.php') ? 'active' : '' ?> text-white">
        <i class="fa-solid fa-clock-rotate-left me-2"></i> ប្រវត្តិប្រឡង
    </a>

    <a href="result_student.php" class="nav-link <?= ($current_page == 'result_student.php') ? 'active' : '' ?>">
        <i class="fa-solid fa-award me-2"></i> លទ្ធផលរបស់ខ្ញុំ
    </a>
    
    <hr class="mx-3 opacity-25 text-white">

    <div class="mt-auto pt-5">
        <a href="student_profile.php" class="nav-link <?= ($current_page == 'student_profile.php') ? 'active' : '' ?>">
            <i class="fa-solid fa-user me-2"></i> ព័ត៌មានផ្ទាល់ខ្លួន
        </a>

        <a href="../../../public/index.php" class="nav-link text-danger">
            <i class="fa-solid fa-power-off me-2"></i> ចាកចេញ
        </a>
    </div>
</nav>
</aside>