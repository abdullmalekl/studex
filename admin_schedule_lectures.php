<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include 'includes/db_connection.php';

$message = '';
$subjects = [];
$teachers = [];
$classes = [];
$available_times = [];

// جلب جميع المواد مع الفصول الدراسية
$subjects_query = $conn->query("
    SELECT s.subject_id, s.name, sem.name as semester_name, d.name as department_name
    FROM Subjects s 
    LEFT JOIN Semesters sem ON s.sem_id = sem.semester_id 
    LEFT JOIN Departments d ON s.department_id = d.department_id
    WHERE s.status = TRUE
    ORDER BY sem.name, s.name
");

if ($subjects_query) {
    $subjects = $subjects_query->fetch_all(MYSQLI_ASSOC);
}

// إذا تم اختيار مادة، جلب الأساتذة المناسبين
if (isset($_POST['subject_id']) && !empty($_POST['subject_id'])) {
    $subject_id = $_POST['subject_id'];
    
    // جلب قسم المادة
    $subject_dept = $conn->prepare("SELECT department_id FROM Subjects WHERE subject_id = ?");
    $subject_dept->bind_param("i", $subject_id);
    $subject_dept->execute();
    $dept_result = $subject_dept->get_result()->fetch_assoc();
    
    if ($dept_result) {
        // جلب الأساتذة من نفس القسم أو الأساتذة العامين
        $teachers_query = $conn->prepare("
            SELECT teacher_id, name, specialization 
            FROM Teachers 
            WHERE status = 'active' AND deleted = FALSE 
            AND (department_id = ? OR department_id IS NULL)
            ORDER BY name
        ");
        $teachers_query->bind_param("i", $dept_result['department_id']);
        $teachers_query->execute();
        $teachers = $teachers_query->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}

// إذا تم اختيار أستاذ، جلب القاعات المتاحة
if (isset($_POST['teacher_id']) && !empty($_POST['teacher_id'])) {
    $classes_query = $conn->query("SELECT class_id, name FROM Classes ORDER BY name");
    if ($classes_query) {
        $classes = $classes_query->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}

// إذا تم اختيار قاعة، جلب الأوقات المتاحة
if (isset($_POST['class_id']) && !empty($_POST['class_id']) && isset($_POST['teacher_id'])) {
    $class_id = $_POST['class_id'];
    $teacher_id = $_POST['teacher_id'];
    
    // الأيام والأوقات المتاحة
    $days = ['الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'];
    $time_slots = [
        '08:00-09:30', '09:30-11:00', '11:00-12:30', 
        '12:30-14:00', '14:00-15:30', '15:30-17:00'
    ];
    
    foreach ($days as $day) {
        foreach ($time_slots as $slot) {
            $times = explode('-', $slot);
            $start_time = $times[0];
            $end_time = $times[1];
            
            // فحص التضارب في القاعة
            $class_conflict = $conn->prepare("
                SELECT COUNT(*) as count FROM Lectures 
                WHERE class_id = ? AND day_of_week = ? 
                AND ((start_time <= ? AND end_time > ?) OR (start_time < ? AND end_time >= ?))
            ");
            $class_conflict->bind_param("isssss", $class_id, $day, $start_time, $start_time, $end_time, $end_time);
            $class_conflict->execute();
            $class_result = $class_conflict->get_result()->fetch_assoc();
            
            // فحص التضارب مع الأستاذ
            $teacher_conflict = $conn->prepare("
                SELECT COUNT(*) as count FROM Lectures 
                WHERE teacher_id = ? AND day_of_week = ? 
                AND ((start_time <= ? AND end_time > ?) OR (start_time < ? AND end_time >= ?))
            ");
            $teacher_conflict->bind_param("isssss", $teacher_id, $day, $start_time, $start_time, $end_time, $end_time);
            $teacher_conflict->execute();
            $teacher_result = $teacher_conflict->get_result()->fetch_assoc();
            
            if ($class_result['count'] == 0 && $teacher_result['count'] == 0) {
                $available_times[] = [
                    'day' => $day,
                    'start_time' => $start_time,
                    'end_time' => $end_time,
                    'slot' => $slot
                ];
            }
        }
    }
}

// حفظ المحاضرة
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['save_lecture'])) {
    $subject_id = $_POST['subject_id'];
    $teacher_id = $_POST['teacher_id'];
    $class_id = $_POST['class_id'];
    $semester_id = $_POST['semester_id'];
    $group_id = $_POST['group_id'];
    $day = $_POST['day'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    
    $stmt = $conn->prepare("
        INSERT INTO Lectures (subject_id, teacher_id, class_id, semester_id, group_id, day_of_week, start_time, end_time) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("iiiissss", $subject_id, $teacher_id, $class_id, $semester_id, $group_id, $day, $start_time, $end_time);
    
    if ($stmt->execute()) {
        $message = "<div class='alert alert-success'>✅ تم حفظ المحاضرة بنجاح!</div>";
    } else {
        $message = "<div class='alert alert-danger'>❌ حدث خطأ أثناء حفظ المحاضرة.</div>";
    }
    $stmt->close();
}

// جلب الفصول الدراسية والمجموعات
$semesters = $conn->query("SELECT semester_id, name FROM Semesters ORDER BY name");
$groups = $conn->query("SELECT group_id, name FROM Groups ORDER BY name");
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>جدولة المحاضرات</title>
    <link href="attatchments/bootstrap.min.css" rel="stylesheet">
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
        .form-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            max-width: 800px;
            margin: 0 auto;
        }
        .available-time {
            background: #e8f5e8;
            border: 1px solid #28a745;
            border-radius: 5px;
            padding: 10px;
            margin: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .available-time:hover {
            background: #d4edda;
        }
        .available-time.selected {
            background: #28a745;
            color: white;
        }
    </style>
</head>
<body>
<!-- الشريط العلوي -->
    <div class="top-navbar">
        <div class="nav-menu">
            <div class="nav-item">
                <a href="home.php">🏠 الرئيسية</a>
            </div>
            <div class="nav-item">
                <a href="dashboard.php">📚 إضافة محاضرة</a>
            </div>
            <div class="nav-item">
                <a href="admin_departments.php">🏢 إدارة الأقسام</a>
            </div>
            <div class="nav-item">
                <a href="admin_classes.php">🏫 إدارة القاعات</a>
            </div>
            <div class="nav-item">
                <a href="semester.php">📅 الفصول الدراسية</a>
            </div>
            <div class="nav-item">
                <a href="admin_groups.php">👥 إدارة المجموعات</a>
            </div>
            <div class="nav-item">
                <a href="subjects.php">📖 المواد</a>
            </div>
            <div class="nav-item">
                <a href="admin_users_management.php">👤 إدارة المستخدمين</a>
            </div>
            <div class="nav-item">
                <a href="admin_announcements.php">📢 إدارة الإعلانات</a>
            </div>
                 <div class="nav-item">
        <a href="admin_site_settings.php">⚙️ إعدادات الموقع</a>
    </div>
            <div class="nav-item">
                <a href="logout.php">🚪 تسجيل الخروج</a>
            </div>
        </div>
    </div>

    <div class="container mt-4">
        <div class="form-container">
            <h2 class="text-center mb-4">🗓️ جدولة محاضرات</h2>
            
            <?= $message ?>
            
            <form method="POST" id="scheduleForm">
                <!-- اختيار المادة -->
                <div class="mb-3">
                    <label class="form-label">المادة الدراسية</label>
                    <select name="subject_id" class="form-control" onchange="this.form.submit()" required>
                        <option value="">اختر المادة</option>
                        <?php foreach($subjects as $subject): ?>
                            <option value="<?= $subject['subject_id'] ?>" 
                                <?= (isset($_POST['subject_id']) && $_POST['subject_id'] == $subject['subject_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($subject['name']) ?> - <?= htmlspecialchars($subject['semester_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- اختيار الأستاذ -->
                <?php if (!empty($teachers)): ?>
                <div class="mb-3">
                    <label class="form-label">الأستاذ</label>
                    <select name="teacher_id" class="form-control" onchange="this.form.submit()" required>
                        <option value="">اختر الأستاذ</option>
                        <?php foreach($teachers as $teacher): ?>
                            <option value="<?= $teacher['teacher_id'] ?>"
                                <?= (isset($_POST['teacher_id']) && $_POST['teacher_id'] == $teacher['teacher_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($teacher['name']) ?> - <?= htmlspecialchars($teacher['specialization']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>

                <!-- اختيار القاعة -->
                <?php if (!empty($classes)): ?>
                <div class="mb-3">
                    <label class="form-label">القاعة</label>
                    <select name="class_id" class="form-control" onchange="this.form.submit()" required>
                        <option value="">اختر القاعة</option>
                        <?php foreach($classes as $class): ?>
                            <option value="<?= $class['class_id'] ?>"
                                <?= (isset($_POST['class_id']) && $_POST['class_id'] == $class['class_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($class['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>

                <!-- عرض الأوقات المتاحة -->
                <?php if (!empty($available_times)): ?>
                <div class="mb-3">
                    <label class="form-label">الأوقات المتاحة</label>
                    <div class="row">
                        <?php foreach($available_times as $time): ?>
                            <div class="col-md-4">
                                <div class="available-time" onclick="selectTime('<?= $time['day'] ?>', '<?= $time['start_time'] ?>', '<?= $time['end_time'] ?>')">
                                    <strong><?= $time['day'] ?></strong><br>
                                    <?= $time['slot'] ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- الحقول المخفية للوقت المختار -->
                <input type="hidden" name="day" id="selected_day">
                <input type="hidden" name="start_time" id="selected_start_time">
                <input type="hidden" name="end_time" id="selected_end_time">

                <!-- باقي الحقول -->
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">الفصل الدراسي</label>
                        <select name="semester_id" class="form-control" required>
                            <option value="">اختر الفصل</option>
                            <?php while($semester = $semesters->fetch_assoc()): ?>
                                <option value="<?= $semester['semester_id'] ?>"><?= htmlspecialchars($semester['name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">المجموعة</label>
                        <select name="group_id" class="form-control" required>
                            <option value="">اختر المجموعة</option>
                            <?php while($group = $groups->fetch_assoc()): ?>
                                <option value="<?= $group['group_id'] ?>"><?= htmlspecialchars($group['name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <button type="submit" name="save_lecture" class="btn btn-primary btn-lg" id="saveBtn" disabled>
                        💾 حفظ المحاضرة
                    </button>
                </div>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <script src="attatchments/bootstrap.bundle.min.js"></script>
    <script>
        function selectTime(day, startTime, endTime) {
            // إزالة التحديد من جميع الأوقات
            document.querySelectorAll('.available-time').forEach(el => {
                el.classList.remove('selected');
            });
            
            // تحديد الوقت المختار
            event.target.classList.add('selected');
            
            // تعبئة الحقول المخفية
            document.getElementById('selected_day').value = day;
            document.getElementById('selected_start_time').value = startTime;
            document.getElementById('selected_end_time').value = endTime;
            
            // تفعيل زر الحفظ
            document.getElementById('saveBtn').disabled = false;
        }
    </script>
</body>
</html>

