<?php
include "includes/get_announcements.php";
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "student") {
    header("Location: login.php");
    exit();
}

include "includes/db_connection.php";
$student_id = $_SESSION["user_id"];

// جلب جميع الفصول الدراسية التي سجل فيها الطالب مواد
$semesters_query = "
    SELECT DISTINCT
        sem.semester_id,
        sem.name AS semester_name,
        sem.start_date,
        sem.end_date,
        t.name AS term_name,
        d.name AS department_name
    FROM
        enrollment e
    JOIN
        subjects s ON e.sbjct_id = s.subject_id
    JOIN
        semesters sem ON s.sem_id = sem.semester_id
    LEFT JOIN
        terms t ON sem.terms = t.term_id
    LEFT JOIN
        departments d ON s.department_id = d.department_id
    WHERE
        e.std_id = ?
    ORDER BY
        sem.start_date DESC
";
$stmt_semesters = $conn->prepare($semesters_query);
$stmt_semesters->bind_param("i", $student_id);
$stmt_semesters->execute();
$semesters_result = $stmt_semesters->get_result();

$student_semesters_data = [];
while ($semester = $semesters_result->fetch_assoc()) {
    $semester_id = $semester["semester_id"];
    $student_semesters_data[$semester_id] = [
        "info" => $semester,
        "subjects" => []
    ];

    // جلب المواد المسجلة للطالب في هذا الفصل
    $subjects_query = "
        SELECT
            s.subject_id,
            s.name AS subject_name,
            g.name AS group_name,
            t.name AS teacher_name,
            r.midterm_grade,
            r.final_grade,
            r.total_score,
            r.status AS result_status
        FROM
            enrollment e
        JOIN
            subjects s ON e.sbjct_id = s.subject_id
        LEFT JOIN
            groups g ON e.group_id = g.group_id
        LEFT JOIN
            teachers t ON e.tch_id = t.teacher_id
        LEFT JOIN
            results r ON e.std_id = r.stdnt_id AND e.sbjct_id = r.sbjct_id
        WHERE
            e.std_id = ? AND s.sem_id = ?
    ";
    $stmt_subjects = $conn->prepare($subjects_query);
    $stmt_subjects->bind_param("ii", $student_id, $semester_id);
    $stmt_subjects->execute();
    $subjects_result_for_semester = $stmt_subjects->get_result();

    $has_results = false;
    while ($subject = $subjects_result_for_semester->fetch_assoc()) {
        $student_semesters_data[$semester_id]["subjects"][] = $subject;
        if ($subject["midterm_grade"] !== null || $subject["final_grade"] !== null) {
            $has_results = true;
        }
    }
    $stmt_subjects->close();

   
    // if (!$has_results) {
    //     unset($student_semesters_data[$semester_id]);
    // }
}
$stmt_semesters->close();

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>الفصول الدراسية - الطالب</title>
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
        .semester-card {
            margin-bottom: 30px;
        }
        .semester-header {
            background-color: #0d6efd;
            color: white;
            padding: 15px;
            border-radius: 8px 8px 0 0;
            font-size: 1.2rem;
            font-weight: bold;
        }
        .subject-table th,
        .subject-table td {
            text-align: center;
            vertical-align: middle;
        }
        .status-pass {
            color: green;
            font-weight: bold;
        }
        .status-fail {
            color: red;
            font-weight: bold;
        }
/* شريط الإعلانات */
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
    animation: ticker-scroll 20s linear infinite;
    font-size: 16px;
    line-height: 40px;
    position: relative;
    bottom:80%;
}

.news-ticker-content span {
    margin-right: 50px;
    display: inline-block;
    color: #ffffff;
}

/* الحركة */
@keyframes ticker-scroll {
    0%   { transform: translateX(100%); }
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
            $announcements = getLatestAnnouncements($conn);
            if (!empty($announcements)) {
                foreach ($announcements as $announcement) {
                    echo "<span>"  . htmlspecialchars($announcement["title"]) . ": " . htmlspecialchars($announcement["content"]) . "</span>";
                }
            } else {
                echo "<span>لا توجد إعلانات حاليًا.</span>";
            }
            ?>
        </div>
    </div>
</div>

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
                <a href="logout.php">🚪 تسجيل الخروج</a>
            </div>
        </div>
    </div>

    <div class="container mt-4">
        <h1 class="mb-4">الفصول الدراسية </h1>

        <?php if (empty($student_semesters_data)): ?>
            <div class="alert alert-info">لا توجد فصول دراسية أو نتائج متاحة لك حالياً.</div>
        <?php else: ?>
            <?php foreach ($student_semesters_data as $semester_id => $data): ?>
                <div class="card semester-card">
                    <div class="card-header semester-header">
                        <?= htmlspecialchars($data["info"]["semester_name"]) ?> - <?= htmlspecialchars($data["info"]["term_name"]) ?> - قسم <?= htmlspecialchars($data["info"]["department_name"]) ?> (رقم القيد: <?= htmlspecialchars($student_id) ?>)
                    </div>
                    <div class="card-body">
                        <?php if (!empty($data["subjects"])): ?>
                            <div class="table-responsive">
                                <table class="table table-striped subject-table">
                                    <thead>
                                        <tr>
                                            <th>اسم المادة</th>
                                            <th>المجموعة</th>
                                            <th>اسم الأستاذ</th>
                                            <th>النصفي</th>
                                            <th>النهائي</th>
                                            <th>المجموع</th>
                                            <th>الحالة</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($data["subjects"] as $subject): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($subject["subject_name"]) ?></td>
                                                <td><?= htmlspecialchars($subject["group_name"] ?? 'N/A') ?></td>
                                                <td><?= htmlspecialchars($subject["teacher_name"] ?? 'N/A') ?></td>
                                                <td><?= htmlspecialchars($subject["midterm_grade"] ?? '-') ?></td>
                                                <td><?= htmlspecialchars($subject["final_grade"] ?? '-') ?></td>
                                                <td><?= htmlspecialchars($subject["total_score"] ?? '-') ?></td>
                                                <td>
                                                    <?php if ($subject["result_status"] === 'pass'): ?>
                                                        <span class="status-pass">ناجح</span>
                                                        <?php elseif(!$subject["final_grade"]): ?>
                                                            <span class="status-pass">لم يتم إدراج النتيجة</span>
                                                    <?php elseif ($subject["result_status"] === 'fail'): ?>
                                                        <span class="status-fail">راسب</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">لا توجد مواد مسجلة لهذا الفصل الدراسي أو لم يتم رصد النتائج بعد.</div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
</body>
</html>


