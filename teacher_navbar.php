<?php
// التحقق من تسجيل الدخول
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include "includes/db_connection.php";

$teacher_id = $_SESSION["user_id"];
$teacher_name = $_SESSION["name"];

// تأكد من أن المستخدم مسجل الدخول ولديه دور المعلم
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "teacher") {
    header("Location: login.php");
    exit();
}
?>

<div class="top-navbar">
    <div class="nav-menu">
            
            <div class="">
            <h4 class="mb-0">مرحباً بك، <?= htmlspecialchars($teacher_name) ?>!</h4>
        </div>
        <div class="nav-item">
            <a href="home.php">🏠 الصفحة الرئيسية </a>
        </div>
        <div class="nav-item">
            <a href="teacher_dashboard.php">⚙️ لوحة التحكم </a>
        </div>
        <div class="nav-item">
            <a href="teacher_announcements.php">📢 إدارة الإعلانات</a>
        </div>
        <div class="nav-item">
            <a href="teacher_results.php">📊 إدارة النتائج</a>
        </div>
        <div class="nav-item">
            <a href="profile.php">👤 الملف الشخصي</a>
        </div>
        <div class="nav-item">
            <a href="logout.php">🚪 تسجيل الخروج</a>
        </div>
    </div>
</div>

