<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

include 'includes/db_connection.php';
$student_id = $_SESSION['user_id'];

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>الرئيسية - الطالب</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <link rel="stylesheet" href="css/style2.css">
</head>
<body>

    <link href="attatchments/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            direction: rtl;
            margin: 0;
            padding-top: 80px;
        }
        .top-navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: #1b325f;
            color: white;
            padding: 10px 0;
            z-index: 1000;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .nav-menu {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 10px;
            padding: 0 20px;
        }
        .nav-item {
            background: #2c477a;
            border-radius: 6px;
            padding: 8px 15px;
            transition: all 0.3s ease;
        }
        .nav-item:hover {
            background: #3c5a9a;
            transform: translateY(-2px);
        }
        .nav-item a {
            color: white;
            text-decoration: none;
            font-size: 14px;
        }
        .nav-item.active {
            background: #0d6efd;
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
        .table th {
            background-color: #f8f9fa;
        }
    </style>

    <!-- الشريط العلوي -->
    <div class="top-navbar">
        <div class="nav-menu">
            <div class="nav-item">
                <a href="index.php">🏠 الرئيسية</a>
            </div>
            <div class="nav-item active">
                <a href="semesters_profile.php">📚 الفصول الدراسية</a>
            </div>
            <div class="nav-item">
                <a href="subjects_assighn.php">📖 تنزيل مواد</a>
            </div>
            <div class="nav-item">
                <a href="student_timetable.php">📅 الجدول الدراسي</a>
            </div>
            <div class="nav-item">
                <a href="results_display.php">📅 عرض النتيجة</a>
            </div>
        <div class="nav-item">
            <a href="user_profile.php">👤 الملف الشخصي</a>
        </div>
            <div class="nav-item">
                <a href="logout.php">🚪 تسجيل الخروج</a>x
            </div>
        </div>
    </div>


<div class="container-fluid main-layout">
  <div class="row w-100 h-100 g-0">

   

    <main class="col-md-9 ms-sm-auto col-lg-10 main-content">
      <h1 class="mb-4">مرحبًا بك، <?= $_SESSION['name'] ?></h1>

      <div class="card p-4 shadow-sm">
        <h5>عن النظام</h5>
        <p>
          يوفر لك هذا النظام إمكانية إدارة المواد الدراسية بشكل كامل من حيث تنزيل المواد، متابعة الجدول الدراسي، ومراجعة الدرجات والإعلانات.
        </p>
        <p>
     
        </p>
      </div>
    </main>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="js/scripts2.js"></script>
</body>
</html>