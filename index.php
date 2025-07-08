<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$role = $_SESSION['role'];
if ($role === 'admin') {
    header("Location: dashboard.php");
} elseif ($role === 'student') {
    header("Location: student_home.php");
} elseif ($role === 'teacher') {
    header("Location: teacher_home.php");
} else {
    header("Location: home.php");
}
?>