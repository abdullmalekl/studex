<?php
// includes/sidebar.php

// المتغيرات $user_role و $current_page يتم تعريفها في header.php
// لا داعي لإعادة تعريفها هنا ما لم تكن تستخدم sidebar.php بشكل مستقل

?>
<div class="bg-dark border-right" id="sidebar-wrapper">
    <div class="sidebar-heading text-white">القائمة الرئيسية</div>
    <div class="list-group list-group-flush">
        <li class="list-group-item <?php echo ($current_page == 'inndex.php') ? 'active' : ''; ?>">
            <a class="nav-link text-white" href="inndex.php">
                <i class="fas fa-tachometer-alt"></i> لوحة القيادة
            </a>
        </li>

        <?php if ($user_role === 'admin'): ?>
            <li class="list-group-item <?php echo ($current_page == 'semesters.php' || $current_page == 'add_semester.php' || $current_page == 'edit_semester.php') ? 'active' : ''; ?>">
                <a class="nav-link text-white" href="semesters.php">
                    <i class="fas fa-calendar-alt"></i> الفصول الدراسية
                </a>
            </li>
            <li class="list-group-item <?php echo ($current_page == 'subjects.php' || $current_page == 'add_subject.php' || $current_page == 'edit_subject.php') ? 'active' : ''; ?>">
                <a class="nav-link text-white" href="subjects.php">
                    <i class="fas fa-book"></i> المواد الدراسية
                </a>
            </li>
            <li class="list-group-item <?php echo ($current_page == 'teachers.php' || $current_page == 'add_teacher.php' || $current_page == 'edit_teacher.php') ? 'active' : ''; ?>">
                <a class="nav-link text-white" href="teachers.php">
                    <i class="fas fa-chalkboard-teacher"></i> الأساتذة
                </a>
            </li>
            <li class="list-group-item <?php echo ($current_page == 'students.php' || $current_page == 'add_student.php' || $current_page == 'edit_student.php') ? 'active' : ''; ?>">
                <a class="nav-link text-white" href="students.php">
                    <i class="fas fa-users"></i> الطلاب
                </a>
            </li>
            <li class="list-group-item <?php echo ($current_page == 'lectures.php' || $current_page == 'add_lecture.php' || $current_page == 'edit_lecture.php') ? 'active' : ''; ?>">
                <a class="nav-link text-white" href="lectures.php">
                    <i class="fas fa-book-reader"></i> جداول المحاضرات
                </a>
            </li>
            <li class="list-group-item <?php echo ($current_page == 'classrooms.php' || $current_page == 'add_classroom.php' || $current_page == 'edit_classroom.php') ? 'active' : ''; ?>">
                <a class="nav-link text-white" href="classrooms.php">
                    <i class="fas fa-building"></i> القاعات الدراسية
                </a>
            </li>
            <li class="list-group-item <?php echo ($current_page == 'users.php' || $current_page == 'add_user.php' || $current_page == 'edit_user.php') ? 'active' : ''; ?>">
                <a class="nav-link text-white" href="users.php">
                    <i class="fas fa-user-cog"></i> إدارة المستخدمين
                </a>
            </li>
             <li class="list-group-item <?php echo ($current_page == 'advertisements.php' || $current_page == 'add_advertisement.php' || $current_page == 'edit_advertisement.php') ? 'active' : ''; ?>">
                <a class="nav-link text-white" href="advertisements.php">
                    <i class="fas fa-bullhorn"></i> الإعلانات
                </a>
            </li>
        <?php elseif ($user_role === 'teacher'): ?>
            <li class="list-group-item <?php echo ($current_page == 'teacher_lectures.php' || $current_page == 'add_teacher_lecture.php' || $current_page == 'edit_teacher_lecture.php') ? 'active' : ''; ?>">
                <a class="nav-link text-white" href="teacher_lectures.php">
                    <i class="fas fa-book-open"></i> محاضراتي
                </a>
            </li>
            <li class="list-group-item <?php echo ($current_page == 'enter_grades.php') ? 'active' : ''; ?>">
                <a class="nav-link text-white" href="enter_grades.php">
                    <i class="fas fa-tasks"></i> إدخال درجات الطلاب
                </a>
            </li>
            <li class="list-group-item <?php echo ($current_page == 'teacher_advertisements.php' || $current_page == 'add_teacher_advertisement.php' || $current_page == 'edit_teacher_advertisement.php') ? 'active' : ''; ?>">
                <a class="nav-link text-white" href="teacher_advertisements.php">
                    <i class="fas fa-bullhorn"></i> إعلاناتي
                </a>
            </li>
        <?php elseif ($user_role === 'student'): ?>
            <li class="list-group-item">
                <a class="nav-link text-white" href="student_courses.php">
                    <i class="fas fa-clipboard-list"></i> المواد المسجلة
                </a>
            </li>
            <li class="list-group-item">
                <a class="nav-link text-white" href="student_grades.php">
                    <i class="fas fa-percent"></i> نتائجي
                </a>
            </li>
            <li class="list-group-item">
                <a class="nav-link text-white" href="student_schedule.php">
                    <i class="fas fa-calendar-check"></i> جدولي الدراسي
                </a>
            </li>
        <?php endif; ?>

        <li class="list-group-item">
            <a class="nav-link text-white" href="logout.php">
                <i class="fas fa-sign-out-alt"></i> تسجيل الخروج
            </a>
        </li>
    </div>
</div>