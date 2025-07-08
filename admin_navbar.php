<?php
// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
?>

<!-- الشريط العلوي -->
<!-- <div class="top-navbar">
    <div class="nav-menu">
            <a href="home.php">🏠 الرئيسية</a>

        <div class="nav-item">
            <a href="semester.php">📅 الفصول الدراسية</a>
        </div>
        <div class="nav-item">
            <a href="admin_groups.php">👥 إدارة المجموعات</a>
        </div>
        <div class="nav-item">
            <a href="subjects.php">📖 المواد</a>
        </div>
        <div class="nav-item">
            <a href="admin_schedule_lectures.php">🗓️ جدولة المحاضرات</a>
        
        <div class="nav-item">
            <a href="admin_users_management.php">👤 إدارة المستخدمين</a>
        </div>
        <div class="nav-item">
            <a href="admin_announcements.php">📢 إدارة الإعلانات</a>
        </div>
        <div class="nav-item">
            <a href="admin_site_settings.php">⚙️ إعدادات الموقع</a>
        </div>
        <div class="nav-item">
            <a href="logout.php">🚪 تسجيل الخروج</a>
        </div>
    </div>
</div> -->