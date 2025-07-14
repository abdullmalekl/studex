<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "student") {
    header("Location: login.php");
    exit();
}

include "includes/db_connection.php";
$student_id = $_SESSION["user_id"];

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>الجدول الدراسي - الطالب</title>
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
            <div class="nav-item active">
                <a href="student_timetable.php">📅 الجدول الدراسي</a>
            </div>
            <div class="nav-item">
                <a href="results_display.php">📊 عرض النتيجة</a>
            </div>
            <div class="nav-item">
                <a href="user_profile.php">👤 الملف الشخصي</a>
            </div>
            <div class="nav-item">
                <a href="logout.php">🚪 تسجيل الخروج</a>
            </div>
        </div>
    </div>

    <div class="container mt-4">
        

            <h1 class="mb-4">جدول المحاضرات</h1>
        

        <div class="card">
            <div class="card-header">
                <h4 class="mb-0" style='color:white;'>المحاضرات المسجلة</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>رقم المحاضرة</th>
                                <th>المادة</th>
                                <th>الأستاذ</th>
                                <th>القاعة</th>
                                <th>المجموعة</th>
                                <th>الفصل الدراسي</th>
                                <th>اليوم</th>
                                <th>وقت البداية</th>
                                <th>وقت النهاية</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $timetable_query = "
                                SELECT
                                    l.lecture_id,
                                    s.name AS subject_name,
                                    tchr.name AS teacher_name,
                                    c.name AS room_name,
                                    g.name AS group_name,
                                    sem.name AS semester_name,
                                    tm.name AS term_name,
                                    l.day_of_week,
                                    l.start_time,
                                    l.end_time
                                FROM
                                    enrollment e
                                JOIN
                                    lectures l ON e.sbjct_id = l.sbjct_id AND e.group_id = l.group_id AND e.tch_id = l.teachr_id AND e.semes_id = l.sems_id
                                JOIN
                                    subjects s ON e.sbjct_id = s.subject_id
                                JOIN
                                    teachers tchr ON e.tch_id = tchr.teacher_id
                                JOIN
                                    groups g ON e.group_id = g.group_id
                                JOIN
                                    semesters sem ON e.semes_id = sem.semester_id
                                JOIN
                                    terms tm ON sem.terms = tm.term_id
                                LEFT JOIN classes c ON l.class_id = c.class_id
                                WHERE
                                    e.std_id = ?
                                ORDER BY
                                    FIELD(l.day_of_week, 'الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'),
                                    l.start_time ASC
                            ";
                            $stmt_timetable = $conn->prepare($timetable_query);
                            $stmt_timetable->bind_param("i", $student_id);
                            $stmt_timetable->execute();
                            $timetable_result = $stmt_timetable->get_result();

                            if ($timetable_result->num_rows > 0) {
                                while ($row = $timetable_result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row["lecture_id"]) . "</td>";
                                    echo "<td>" . htmlspecialchars($row["subject_name"]) . "</td>";
                                    echo "<td>" . htmlspecialchars($row["teacher_name"]) . "</td>";
                                    echo "<td>" . htmlspecialchars($row["room_name"] ?? "غير محدد") . "</td>";
                                    echo "<td>" . htmlspecialchars($row["group_name"]) . "</td>";
                                    echo "<td>" . htmlspecialchars($row["term_name"]) . " " . htmlspecialchars($row["semester_name"]) . "</td>";
                                    echo "<td>" . htmlspecialchars($row["day_of_week"]) . "</td>";
                                    echo "<td>" . htmlspecialchars(date("H:i", strtotime($row["start_time"]))) . "</td>";
                                    echo "<td>" . htmlspecialchars(date("H:i", strtotime($row["end_time"]))) . "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan=\"9\" class=\"text-center\">لا توجد مواد مسجلة لعرض الجدول الدراسي.</td></tr>";
                            }
                            $stmt_timetable->close();
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
</body>
</html>


