<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "student") {
    header("Location: login.php");
    exit();
}

include "includes/db_connection.php";
$student_id = $_SESSION["user_id"];
$message = "";

// Get student's department ID
$student_department_query = "SELECT dep_id FROM students WHERE student_id = ?";
$stmt_student_dept = $conn->prepare($student_department_query);
$stmt_student_dept->bind_param("i", $student_id);
$stmt_student_dept->execute();
$result_student_dept = $stmt_student_dept->get_result();
$student_dept_row = $result_student_dept->fetch_assoc();
$student_department_id = $student_dept_row['dep_id'] ?? null;
$stmt_student_dept->close();

// Get current open semester
$current_open_semester = null;
$current_open_semester_query = "
    SELECT
        semester_id,
        semesters.name AS semester_name,
        start_date,
        end_date,
        terms.name AS term_name
    FROM
        semesters
    JOIN
        terms ON semesters.terms = terms.term_id
    WHERE
        NOW() BETWEEN start_date AND end_date
    ORDER BY
        start_date DESC
    LIMIT 1
";
$stmt_current_semester = $conn->prepare($current_open_semester_query);
if ($stmt_current_semester) {
    $stmt_current_semester->execute();
    $current_semester_result = $stmt_current_semester->get_result();
    $current_open_semester = $current_semester_result->fetch_assoc();
    $stmt_current_semester->close();
}

// Handle subject enrollment on POST request
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["enroll_subjects"])) {
    if ($current_open_semester) {
        $semester_to_enroll_in = $current_open_semester['semester_id'];

        if (isset($_POST["selected_subjects"]) && !empty($_POST["selected_subjects"])) {
            $selected_subjects_data = $_POST["selected_subjects"];
            $success_count = 0;
            $error_count = 0;

            // Check current enrolled subjects for the target semester to enforce 7-subject limit
            $current_enrolled_count_query = "SELECT COUNT(*) FROM enrollment WHERE std_id = ? AND semes_id = ?";
            $stmt = $conn->prepare($current_enrolled_count_query);
            $stmt->bind_param("ii", $student_id, $semester_to_enroll_in);
            $stmt->execute();
            $current_enrolled_subjects_count = $stmt->get_result()->fetch_row()[0];
            $stmt->close();

            $max_subjects_allowed = 7;
            $can_enroll_count = $max_subjects_allowed - $current_enrolled_subjects_count;

            if (count($selected_subjects_data) > $can_enroll_count) {
                $message .= "<div class='alert alert-danger'>❌ لا يمكنك تسجيل أكثر من " . $max_subjects_allowed . " مواد في هذا الفصل الدراسي. لقد اخترت " . count($selected_subjects_data) . " مواد ولديك " . $current_enrolled_subjects_count . " مواد مسجلة بالفعل.</div>";
            } else {
                foreach ($selected_subjects_data as $subject_data_string) {
                    list($subject_id, $group_id, $teacher_id) = array_map('intval', explode("_", $subject_data_string));

                    if ($group_id <= 0 || $teacher_id <= 0) {
                        $message .= "<div class='alert alert-danger'>❌ بيانات غير صالحة للمادة المحددة.</div>";
                        $error_count++;
                        continue;
                    }

                    // Check group capacity
                    $capacity_query = "SELECT maximum FROM groups WHERE group_id = ?";
                    $stmt = $conn->prepare($capacity_query);
                    $stmt->bind_param("i", $group_id);
                    $stmt->execute();
                    $group_maximum = $stmt->get_result()->fetch_row()[0] ?? 0;
                    $stmt->close();

                    $enrolled_count_query = "SELECT COUNT(*) FROM enrollment WHERE sbjct_id = ? AND group_id = ? AND semes_id = ?";
                    $stmt = $conn->prepare($enrolled_count_query);
                    $stmt->bind_param("iii", $subject_id, $group_id, $semester_to_enroll_in);
                    $stmt->execute();
                    $current_enrollment_count = $stmt->get_result()->fetch_row()[0] ?? 0;
                    $stmt->close();

                    if ($current_enrollment_count >= $group_maximum) {
                        $message .= "<div class='alert alert-danger'>❌ المجموعة للمادة ممتلئة.</div>";
                        $error_count++;
                        continue;
                    }

                    // Check if already enrolled
                    $check_query = "SELECT COUNT(*) FROM enrollment WHERE std_id = ? AND sbjct_id = ? AND semes_id = ?";
                    $stmt = $conn->prepare($check_query);
                    $stmt->bind_param("iii", $student_id, $subject_id, $semester_to_enroll_in);
                    $stmt->execute();
                    $exists = $stmt->get_result()->fetch_row()[0] > 0;
                    $stmt->close();

                    if (!$exists) {
                        $insert_query = "INSERT INTO enrollment (std_id, semes_id, sbjct_id, tch_id, group_id) VALUES (?, ?, ?, ?, ?)";
                        $stmt = $conn->prepare($insert_query);
                        $stmt->bind_param("iiiii", $student_id, $semester_to_enroll_in, $subject_id, $teacher_id, $group_id);
                        if ($stmt->execute()) {
                            $success_count++;
                            $insert_result_query = "INSERT INTO results (stdnt_id, sbjct_id, sem_id, tcher_id) VALUES (?, ?, ?, ?)";
                            $stmt_result = $conn->prepare($insert_result_query);
                            $stmt_result->bind_param("iiii", $student_id, $subject_id, $semester_to_enroll_in, $teacher_id);
                            $stmt_result->execute();
                            $stmt_result->close();
                        } else {
                            $message .= "<div class='alert alert-danger'>❌ خطأ في تسجيل المادة: " . $stmt->error . "</div>";
                            $error_count++;
                        }
                        $stmt->close();
                    } else {
                        $message .= "<div class='alert alert-warning'>⚠️ المادة مسجلة بالفعل.</div>";
                        $error_count++;
                    }
                }

                if ($success_count > 0) {
                    $message .= "<div class='alert alert-success'>✅ تم تسجيل " . $success_count . " مادة بنجاح!</div>";
                }
                if ($error_count > 0) {
                    $message .= "<div class='alert alert-danger'>❌ فشل تسجيل " . $error_count . " مادة.</div>";
                }
            }
        } else {
            $message .= "<div class='alert alert-warning'>⚠️ الرجاء اختيار مادة واحدة على الأقل.</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>❌ لا يوجد فصل دراسي متاح للتسجيل حاليًا.</div>";
    }
}

// --- Logic for displaying available subjects --- //
$available_subjects = [];
$enrollment_status_message = "";

if (!$current_open_semester) {
    $enrollment_status_message = "لا يوجد فصل دراسي مفتوح حاليًا لتسجيل المواد.";
} else {
    $current_semester_id = $current_open_semester["semester_id"];
    $enrollment_status_message = "يمكنك تسجيل مواد للفصل الدراسي الحالي: " . htmlspecialchars($current_open_semester["term_name"]) . " " . htmlspecialchars($current_open_semester["semester_name"]) . ".";

    // Get failed subjects for the student
    $failed_subjects_query = "
        SELECT r.sbjct_id
        FROM results r
        WHERE r.stdnt_id = ? AND r.status = 'fail'
    ";
    $stmt_failed_subjects = $conn->prepare($failed_subjects_query);
    $stmt_failed_subjects->bind_param("i", $student_id);
    $stmt_failed_subjects->execute();
    $failed_subjects_result = $stmt_failed_subjects->get_result();
    $failed_subject_ids = [];
    while ($row = $failed_subjects_result->fetch_assoc()) {
        $failed_subject_ids[] = $row['sbjct_id'];
    }
    $stmt_failed_subjects->close();

    // Fetch all available lectures for the current semester and student's department
    $all_available_lectures_query = "
        SELECT
            s.subject_id,
            s.name AS subject_name,
            s.credit_hours,
            s.units_count,
            sem.name AS semester_name,
            t.name AS term_name,
            dep.name AS department_name,
            l.teachr_id,
            tchr.name AS teacher_name,
            l.group_id,
            g.name AS group_name,
            g.maximum AS group_maximum,
            (SELECT COUNT(*) FROM enrollment WHERE sbjct_id = l.sbjct_id AND group_id = l.group_id AND semes_id = l.sems_id) AS current_enrollment
        FROM lectures l
        JOIN subjects s ON l.sbjct_id = s.subject_id
        JOIN teachers tchr ON l.teachr_id = tchr.teacher_id
        JOIN groups g ON l.group_id = g.group_id
        JOIN departments dep ON s.department_id = dep.department_id
        JOIN semesters sem ON l.sems_id = sem.semester_id
        JOIN terms t ON sem.terms = t.term_id
        WHERE l.sems_id = ? 
          AND s.department_id = ? 
          AND s.status = 1
          AND l.sbjct_id NOT IN (SELECT sbjct_id FROM enrollment WHERE std_id = ? AND semes_id = ?)
        ORDER BY FIELD(s.subject_id, " . implode(',', $failed_subject_ids) . ") DESC, s.name ASC
    ";
    $stmt_all_lectures = $conn->prepare($all_available_lectures_query);
    $stmt_all_lectures->bind_param("iiii", $current_semester_id, $student_department_id, $student_id, $current_semester_id);
    $stmt_all_lectures->execute();
    $all_lectures_result = $stmt_all_lectures->get_result();
    $all_lectures_for_display = [];
    while ($row = $all_lectures_result->fetch_assoc()) {
        $all_lectures_for_display[] = $row;
    }
    $stmt_all_lectures->close();

    // Process subjects for display
    $failed_subjects_to_display = [];
    $other_subjects_to_display = [];

    foreach ($all_lectures_for_display as $lecture) {
        $is_failed = in_array($lecture['subject_id'], $failed_subject_ids);
        $is_full = ($lecture['current_enrollment'] >= $lecture['group_maximum']);

        if ($is_full) {
            $lecture['disabled'] = true;
            $lecture['message'] = "المجموعة ممتلئة";
        } else {
            $lecture['disabled'] = false;
        }

        if ($is_failed) {
            $failed_subjects_to_display[] = $lecture;
        } else {
            $other_subjects_to_display[] = $lecture;
        }
    }

    // If there are failed subjects, only show them until they are registered
    if (!empty($failed_subjects_to_display)) {
        $available_subjects = $failed_subjects_to_display;
        if (count($failed_subjects_to_display) < 7) {
             $available_subjects = array_merge($failed_subjects_to_display, array_slice($other_subjects_to_display, 0, 7 - count($failed_subjects_to_display)));
        }
    } else {
        $available_subjects = $other_subjects_to_display;
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
   <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الملف الشخصي</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/style2.css">
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
        .container {
            margin-top: 20px;
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
        .form-label {
            font-weight: bold;
        }
        .btn-primary {
            background-color: #1b325f;
            border-color: #1b325f;
        }
        .btn-primary:hover {
            background-color: #142447;
            border-color: #142447;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }
        .form-control[readonly] {
            background-color: #e9ecef;
            opacity: 1;
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
            <div class="nav-item active">
                <a href="subjects_assighn.php">📖 تنزيل مواد</a>
            </div>
            <div class="nav-item">
                <a href="student_timetable.php">📅 الجدول الدراسي</a>
            </div>
            <div class="nav-item">
                <a href="results_display.php">📅 عرض النتيجة</a>
            </div>
        <div class="nav-item">
            <a href="profile.php">👤 الملف الشخصي</a>
        </div>
            <div class="nav-item">
                <a href="logout.php">🚪 تسجيل الخروج</a>
            </div>
        </div>
    </div>

    <div class="container mt-4">
        <h1 class="mb-4">تسجيل المواد</h1>
        <?= $message ?>

        <?php if (!empty($enrollment_status_message)): ?>
            <div class="alert alert-info">
                <?= htmlspecialchars($enrollment_status_message) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($available_subjects)): ?>
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">المواد المتاحة للتسجيل</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="subjects_assighn.php">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>اختيار</th>
                                        <th>اسم المادة</th>
                                        <th>الأستاذ</th>
                                        <th>المجموعة</th>
                                        <th>الساعات المعتمدة</th>
                                        <th>الوحدات</th>
                                        <th>الفصل الدراسي</th>
                                        <th>القسم</th>
                                        <th>ملاحظات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($available_subjects as $subject): ?>
                                        <tr class="<?= isset($subject['disabled']) && $subject['disabled'] ? 'table-secondary' : '' ?>">
                                            <td>
                                                <input type="checkbox" name="selected_subjects[]" 
                                                       value="<?= htmlspecialchars($subject["subject_id"]) ?>_<?= htmlspecialchars($subject["group_id"]) ?>_<?= htmlspecialchars($subject["teachr_id"]) ?>" 
                                                       <?= isset($subject['disabled']) && $subject['disabled'] ? 'disabled' : '' ?>>
                                            </td>
                                            <td><?= htmlspecialchars($subject["subject_name"] ?? "") ?></td>
                                            <td><?= htmlspecialchars($subject["teacher_name"] ?? "غير محدد") ?></td>
                                            <td><?= htmlspecialchars($subject["group_name"] ?? "غير محدد") ?></td>
                                            <td><?= htmlspecialchars($subject["credit_hours"] ?? "") ?></td>
                                            <td><?= htmlspecialchars($subject["units_count"] ?? "") ?></td>
                                            <td><?= htmlspecialchars($subject["term_name"] ?? "") . ' ' . htmlspecialchars($subject["semester_name"] ?? "") ?></td>
                                            <td><?= htmlspecialchars($subject["department_name"] ?? "") ?></td>
                                            <td>
                                                <?php if(isset($subject['disabled']) && $subject['disabled']): ?>
                                                    <span class="text-danger"><?= htmlspecialchars($subject['message']) ?></span>
                                                <?php elseif(in_array($subject['subject_id'], $failed_subject_ids)): ?>
                                                    <span class="text-warning">مادة راسبة</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center mt-3">
                            <button type="submit" name="enroll_subjects" class="btn btn-primary">تسجيل المواد المختارة</button>
                        </div>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">
                لا توجد مواد متاحة للتسجيل حالياً بناءً على حالتك الأكاديمية.
            </div>
        <?php endif; ?>

    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
</body>
</html>