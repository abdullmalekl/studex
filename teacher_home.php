<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login.php");
    exit();
}

include 'includes/db_connection.php';

$teacher_id = $_SESSION['user_id'];
$teacher_name = $_SESSION['name'];
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>صفحة المعلم - <?= htmlspecialchars($teacher_name) ?></title>
    <link href="attatchments/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            direction: rtl;
        }
        .navbar {
            background-color: #1b325f;
        }
        .navbar-brand, .navbar-nav .nav-link {
            color: white !important;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .card-header {
            background-color: #1b325f;
            color: white;
            border-radius: 10px 10px 0 0 !important;
        }
        .btn-primary {
            background-color: #1b325f;
            border-color: #1b325f;
        }
        .btn-primary:hover {
            background-color: #142447;
            border-color: #142447;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="#">نظام إدارة الطلاب</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="profile.php">الملف الشخصي</a>
                <a class="nav-link" href="logout.php">تسجيل الخروج</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">مرحباً، <?= htmlspecialchars($teacher_name) ?></h4>
                    </div>
                    <div class="card-body">
                        <p>مرحباً بك في نظام إدارة الطلاب. يمكنك من هنا إدارة المحاضرات والإعلانات والنتائج.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">📚 المحاضرات</h5>
                    </div>
                    <div class="card-body">
                        <p>عرض وإدارة جدول المحاضرات الخاص بك</p>
                        <a href="teacher_lectures.php" class="btn btn-primary">عرض المحاضرات</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">📢 الإعلانات</h5>
                    </div>
                    <div class="card-body">
                        <p>إنشاء وإدارة الإعلانات للطلاب</p>
                        <a href="teacher_announcements.php" class="btn btn-primary">إدارة الإعلانات</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">📊 النتائج</h5>
                    </div>
                    <div class="card-body">
                        <p>إدخال وتعديل نتائج الطلاب</p>
                        <a href="teacher_results.php" class="btn btn-primary">إدارة النتائج</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="attatchments/bootstrap.bundle.min.js"></script>
</body>
</html>

