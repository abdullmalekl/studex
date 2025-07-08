<?php
$servername = "localhost";
$username = "root"; // اسم المستخدم الافتراضي لـ XAMPP/WAMP
$password = "";     // كلمة المرور الافتراضية لـ XAMPP/WAMP (عادة فارغة)
$dbname = "student_registration_system";

// إنشاء اتصال
$conn = new mysqli($servername, $username, $password, $dbname);

// التحقق من الاتصال
if ($conn->connect_error) {
    die("فشل الاتصال: " . $conn->connect_error);
}

// تعيين ترميز الأحرف إلى UTF-8 لدعم اللغة العربية
$conn->set_charset("utf8mb4");
?>