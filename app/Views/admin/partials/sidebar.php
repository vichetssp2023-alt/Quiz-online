<aside class="sidebar shadow">

    <div class="p-4 text-center">

        <h4 class="fw-bold text-white mb-0">QuizMaster</h4>

        <small class="text-secondary">Administrator</small>

    </div>

    <hr class="mx-3 opacity-25">

    <nav class="nav flex-column mt-3">

        <?php 

            $current_page = basename($_SERVER['PHP_SELF']); 

        ?>
        <a href="dashboard.php" class="nav-link <?= ($current_page == 'dashboard.php') ? 'active' : '' ?>">

            <i class="fa-solid fa-chart-line me-2"></i> ផ្ទាំងគ្រប់គ្រង

        </a>
        <a href="manage_quiz.php" class="nav-link <?= ($current_page == 'manage_quiz.php') ? 'active' : '' ?>">

            <i class="fa-solid fa-edit me-2"></i> គ្រប់គ្រង Quiz

        </a>
        <a href="manage_teacher.php" class="nav-link <?= ($current_page == 'manage_questions.php') ? 'active' : '' ?>">

            <i class="fa-solid fa-list-check me-2"></i>ទិន្នន័យរបស់គ្រូ

        </a>
        <a href="manage_student.php" class="nav-link <?= ($current_page == 'student_results.php') ? 'active' : '' ?>">

            <i class="fa-solid fa-graduation-cap me-2"></i> ទិន្នន័យរបស់សិស្ស

        </a>
        <a href="manage_report.php" class="nav-link <?= ($current_page == 'daily_report.php') ? 'active' : '' ?>">

            <i class="fa-solid fa-file-export me-2"></i> ទាញយក Report
        </a>
        <hr class="mx-3 opacity-25">

        <div class="mt-auto pt-5">

            <a href="../../../public/index.php" class="nav-link text-danger"><i class="fa-solid fa-power-off me-2"></i> ចាកចេញ</a>

        </div>

    </nav>

</aside> 
