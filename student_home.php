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

<?php include 'navbar.php'; ?>

<div class="container-fluid main-layout">
  <div class="row w-100 h-100 g-0">

    <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block sidebar collapse">
      <div class="position-sticky pt-3 d-flex flex-column align-items-center w-100">
        <h5 class="sidebar-title">
          <i class="fas fa-university"></i> المعهد العالي
        </h5>
        <p class="text-white text-center small px-2">مرحبًا بك في نظام إدارة المواد. يمكنك من خلال هذا النظام إدارة جدولك الدراسي، متابعة الإعلانات، وتنزيل المواد.</p>

        <ul class="nav flex-column w-100">
          <li class="nav-item">
            <a class="nav-link active" href="student_home.php">
              <i class="fas fa-home"></i>
              الرئيسية
            </a>
          </li>

          <li class="nav-item">
            <a class="nav-link" data-bs-toggle="collapse" href="#subjectsMenu" role="button" aria-expanded="false" aria-controls="subjectsMenu">
              <i class="fas fa-book"></i>
              المواد <i class="fas fa-chevron-down float-end"></i>
            </a>
            <div class="collapse" id="subjectsMenu">
              <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
                <li><a href="view_subjects.php" class="nav-link">المواد التي تمت دراستها</a></li>
                <li><a href="register_subjects.php" class="nav-link">تنزيل المواد</a></li>
              </ul>
            </div>
          </li>

          <li class="nav-item">
            <a class="nav-link" href="announcements.php">
              <i class="fas fa-bullhorn"></i>
              الإعلانات
            </a>
          </li>

          <li class="nav-item">
            <a class="nav-link" href="schedule.php">
              <i class="fas fa-calendar-alt"></i>
              جدول المحاضرات
            </a>
          </li>
        </ul>
      </div>
    </nav>

    <main class="col-md-9 ms-sm-auto col-lg-10 main-content">
      <h1 class="mb-4">مرحبًا بك، <?= $_SESSION['name'] ?></h1>

      <div class="card p-4 shadow-sm">
        <h5>عن النظام</h5>
        <p>
          يوفر لك هذا النظام إمكانية إدارة المواد الدراسية بشكل كامل من حيث تنزيل المواد، متابعة الجدول الدراسي، ومراجعة الدرجات والإعلانات.
        </p>
        <p>
          يمكنك البدء باستخدام القائمة الجانبية للتنقل بين مختلف أجزاء النظام.
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