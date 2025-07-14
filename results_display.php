<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "student") {
    header("Location: login.php");
    exit();
}

include "includes/db_connection.php";
$student_id = $_SESSION["user_id"];
$message = "";

// Fetch student results
$results_query = "
    SELECT
        s.name AS subject_name,
        sem.name AS semester_name,
        t.name AS term_name,
        r.total_score,
        r.status,
        tchr.name AS teacher_name,
        g.name AS group_name
    FROM
        results r
    JOIN
        subjects s ON r.sbjct_id = s.subject_id
    JOIN
        semesters sem ON s.sem_id = sem.semester_id
    JOIN
        terms t ON sem.terms = t.term_id
    LEFT JOIN
        enrollment e ON r.stdnt_id = e.std_id AND r.sbjct_id = e.sbjct_id AND s.sem_id = e.semes_id
    LEFT JOIN
        teachers tchr ON e.tch_id = tchr.teacher_id
    LEFT JOIN
        groups g ON e.group_id = g.group_id
    WHERE
        r.stdnt_id = ?
    ORDER BY
        sem.semester_id DESC, s.name ASC
";

$stmt = $conn->prepare($results_query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$results_result = $stmt->get_result();
$student_results = [];
while ($row = $results_result->fetch_assoc()) {
    $student_results[] = $row;
}
$stmt->close();
// $conn->close();

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>عرض النتائج - الطالب</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <link rel="stylesheet" href="css/style2.css">
  <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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
        .alert-info {
            background-color: #d1ecf1;
            color: #0c5460;
            border-color: #bee5eb;
        }
        .alert-warning {
            background-color: #fff3cd;
            color: #664d03;
            border-color: #ffecb5;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }
        .status-pass {
            color: green;
            font-weight: bold;
        }
        .status-fail {
            color: red;
            font-weight: bold;
        }
        .news-ticker-container {
            background-color: #1b325f;
            padding: 10px 0;
            overflow: hidden;
            position: relative;
            height: 40px;
            border-bottom: 2px solid #0d6efd;
            color: white;
        }

        .news-ticker {
            width: 100%;
            height: 100%;
            overflow: hidden;
            position: relative;
            direction: rtl;
        }

        .news-ticker-content {
            display: inline-block;
            white-space: nowrap;
            animation: ticker-scroll-left 20s linear infinite;
            font-size: 16px;
            line-height: 40px;
            position: relative;
            bottom:80%;
        }

        .news-ticker-content span {
            margin-left: 50px;
            display: inline-block;
            color: #ffffff;
        }

        @keyframes ticker-scroll-left {
            0% { transform: translateX(100%); }
            100% { transform: translateX(-100%); }
        }

        .news-ticker-content:hover {
            animation-play-state: paused;
        }
    </style>
</head>
<body>
  
    <!-- الشريط العلوي -->
    <div class="top-navbar">
        <div class="nav-menu">
            <div class="nav-item">
                <a href="index.php">🏠 الرئيسية</a>
            </div>
            <div class="nav-item">
                <a href="semesters_profile.php">📚 الفصول الدراسية</a>
            </div>
            <div class="nav-item">
                <a href="subjects_assighn.php">📖 تنزيل مواد</a>
            </div>
            <div class="nav-item">
                <a href="student_timetable.php">📅 الجدول الدراسي</a>
            </div>
            <div class="nav-item active">
                <a href="results_display.php">📅 عرض النتيجة</a>
            </div>
            <div class="nav-item">
                <a href="user_profile.php">👤 الملف الشخصي</a>
            </div>
            <div class="nav-item">
                <a href="logout.php">🚪 تسجيل الخروج</a>
            </div>
        </div>
    </div>

    <!-- شريط الإعلانات -->
    <div class="news-ticker-container">
      <div class="news-ticker">
        <div class="news-ticker-content">
          <?php
          include "includes/get_announcements.php";
          $announcements = getLatestAnnouncements($conn);
          if (!empty($announcements)) {
              foreach ($announcements as $announcement) {
                  echo "<span>" . htmlspecialchars($announcement["title"]) . ": " . htmlspecialchars($announcement["content"]) . "</span>";
              }
          } else {
              echo "<span>لا توجد إعلانات حالياً</span>";
          }
          ?>
        </div>
      </div>
    </div> 
    
    <div class="container mt-4">
        <h1 class="mb-4">عرض النتائج</h1>
        <?= $message ?>

        <?php if (!empty($student_results)): ?>
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0" style='color: white;'>نتائجك الأكاديمية</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>المادة</th>
                                    <th>الفصل الدراسي</th>
                                    <th>الأستاذ</th>
                                    <th>المجموعة</th>
                                    <th>الدرجة الكلية</th>
                                    <th>الحالة</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($student_results as $result): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($result["subject_name"] ?? "") ?></td>
                                        <td><?= htmlspecialchars($result["term_name"] ?? "") . ' ' . htmlspecialchars($result["semester_name"] ?? "") ?></td>
                                        <td><?= htmlspecialchars($result["teacher_name"] ?? "غير محدد") ?></td>
                                        <td><?= htmlspecialchars($result["group_name"] ?? "غير محدد") ?></td>
                                        <td><?= htmlspecialchars($result["total_score"] ?? "لم يتم الإدراج") ?></td>
                                        <td>
                                            <?php
                                            $status_class = ($result["status"] == "pass") ? "status-pass" : "status-fail";
                                            if(!$result["total_score"] ){
                                                $status_class = $result["total_score"] ? "status-fail" : "status-pass";
                                            }
                                            $status_text = $result["total_score"] ? (($result["status"] == "pass") ? "ناجح" : "راسب") : " -";
                                            ?>
                                            <span class="<?= $status_class  ?>"><?= htmlspecialchars($status_text) ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                لا توجد نتائج لعرضها حالياً.
            </div>
        <?php endif; ?>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<?php $conn->close(); ?>
</body>
</html>