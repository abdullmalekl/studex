<?php

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "student_registration_system");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// جلب المواد
$subjects = $conn->query("SELECT s.subject_id, s.name, s.sem_id, se.name AS semester_name FROM Subjects s JOIN Semesters se ON s.sem_id = se.semester_id");
// جلب الأساتذة
$teachers = $conn->query("SELECT teacher_id, name, specialization FROM Teachers WHERE status = 'active' AND deleted = false");
// جلب القاعات
$classes = $conn->query("SELECT class_id, name FROM Classes");
// جلب المجموعات
$groups = $conn->query("SELECT group_id, name FROM Groups");

// عند إرسال النموذج
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    // حذف محاضرة
    if (isset($_POST['delete_lecture']) && isset($_POST['lecture_id'])) {
        $lecture_id = $_POST['lecture_id'];
        $delete = $conn->prepare("DELETE FROM Lectures WHERE lecture_id = ?");
        $delete->bind_param("i", $lecture_id);
        if ($delete->execute()) {
            $success = "✅ تم حذف المحاضرة بنجاح!";
        } else {
            $error = "❌ حدث خطأ أثناء حذف المحاضرة.";
        }
        $delete->close();
    }
    
    // تعديل محاضرة
    elseif (isset($_POST['edit_lecture']) && isset($_POST['lecture_id'])) {
        $lecture_id = $_POST['lecture_id'] ?? '';
        $subject_id = $_POST['subject_id'] ?? '';
        $teacher_id = $_POST['teacher_id'] ?? '';
        $class_id = $_POST['class_id'] ?? '';
        $group_id = $_POST['group_id'] ?? '';
        $day = $_POST['day'] ?? '';
        $start_time = $_POST['start_time'] ?? '';
        $end_time = $_POST['end_time'] ?? '';

        if ($lecture_id && $subject_id && $teacher_id && $class_id && $group_id && $day && $start_time && $end_time) {
            $sem_query = $conn->prepare("SELECT sem_id FROM Subjects WHERE subject_id = ?");
            $sem_query->bind_param("i", $subject_id);
            $sem_query->execute();
            $sem_result = $sem_query->get_result();
            $semester = $sem_result->fetch_assoc();
            
            if ($semester && isset($semester['sem_id'])) {
                $semester_id = $semester['sem_id'];

                $stmt = $conn->prepare("UPDATE Lectures SET teachr_id = ?, sems_id = ?, class_id = ?, sbjct_id = ?, group_id = ?, day_of_week = ?, start_time = ?, end_time = ? WHERE lecture_id = ?");
                $stmt->bind_param("iiiissssi", $teacher_id, $semester_id, $class_id, $subject_id, $group_id, $day, $start_time, $end_time, $lecture_id);
                if ($stmt->execute()) {
                    $success = "✅ تم تعديل المحاضرة بنجاح!";
                } else {
                    $error = "❌ حدث خطأ أثناء تعديل المحاضرة.";
                }
                $stmt->close();
            } else {
                $error = "❌ لم يتم العثور على الفصل الدراسي للمادة المختارة.";
            }
        } else {
            $error = "❌ يرجى ملء جميع الحقول المطلوبة.";
        }
    }
    
    // إضافة محاضرة جديدة
    elseif (isset($_POST['subject_id'], $_POST['teacher_id'], $_POST['class_id'], $_POST['group_id'], $_POST['day'], $_POST['start_time'], $_POST['end_time'])) {
        $subject_id = $_POST['subject_id'];
        $teacher_id = $_POST['teacher_id'];
        $class_id = $_POST['class_id'];
        $group_id = $_POST['group_id'];
        $day = $_POST['day'];
        $start_time = $_POST['start_time'];
        $end_time = $_POST['end_time'];

        // الحصول على semester_id من المادة المختارة
        $sem_query = $conn->prepare("SELECT sem_id FROM Subjects WHERE subject_id = ?");
        $sem_query->bind_param("i", $subject_id);
        $sem_query->execute();
        $sem_result = $sem_query->get_result();
        $semester = $sem_result->fetch_assoc();
        
        if ($semester && isset($semester['sem_id'])) {
            $semester_id = $semester['sem_id'];

            // تحقق من عدم وجود تعارض في الجدول للمكان أو الأستاذ
            $conflict = $conn->prepare("SELECT * FROM Lectures WHERE day_of_week = ? AND ((start_time < ? AND end_time > ?) OR (start_time < ? AND end_time > ?) OR (start_time >= ? AND end_time <= ?)) AND (class_id = ? OR teachr_id = ?)");
            $conflict->bind_param("sssssssii", $day, $end_time, $end_time, $start_time, $start_time, $start_time, $end_time, $class_id, $teacher_id);
            $conflict->execute();
            $result = $conflict->get_result();

            if ($result->num_rows > 0) {
                $error = "⚠️ يوجد تعارض في الجدول للمكان أو وقت الأستاذ.";
            } else {
                $stmt = $conn->prepare("INSERT INTO Lectures (teachr_id, sems_id, class_id, sbjct_id, group_id, day_of_week, start_time, end_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("iiiissss", $teacher_id, $semester_id, $class_id, $subject_id, $group_id, $day, $start_time, $end_time);
                if ($stmt->execute()) {
                    $success = "✅ تم إضافة المحاضرة بنجاح!";
                } else {
                    $error = "❌ حدث خطأ أثناء إضافة المحاضرة.";
                }
                $stmt->close();
            }
            $conflict->close();
        } else {
            $error = "❌ لم يتم العثور على الفصل الدراسي للمادة المختارة.";
        }
    }
}

// جلب جميع المحاضرات مع التفاصيل
$lectures_query = "
    SELECT 
        l.lecture_id,
        l.day_of_week,
        l.start_time,
        l.end_time,
        s.name AS subject_name,
        t.name AS teacher_name,
        c.name AS class_name,
        g.name AS group_name,
        sem.name AS semester_name,
        l.teachr_id,
        l.sems_id,
        l.class_id,
        l.sbjct_id,
        l.group_id
    FROM Lectures l
    JOIN Subjects s ON l.sbjct_id = s.subject_id
    JOIN Teachers t ON l.teachr_id = t.teacher_id
    JOIN Classes c ON l.class_id = c.class_id
    JOIN Groups g ON l.group_id = g.group_id
    JOIN Semesters sem ON l.sems_id = sem.semester_id
    ORDER BY l.day_of_week, l.start_time
";
$lectures_result = $conn->query($lectures_query);

// إعادة جلب البيانات للنماذج
$subjects = $conn->query("SELECT s.subject_id, s.name, s.sem_id, se.name AS semester_name FROM Subjects s JOIN Semesters se ON s.sem_id = se.semester_id");
$teachers = $conn->query("SELECT teacher_id, name, specialization FROM Teachers WHERE status = 'active' AND deleted = false");
$classes = $conn->query("SELECT class_id, name FROM Classes");
$groups = $conn->query("SELECT group_id, name FROM Groups");
?>
<?php include 'admin_navbar.php'; ?>
<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>إضافة محاضرة</title>
    <link href="attatchments/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
            direction: rtl;
            margin: 0;
            padding-top: 80px; /* مساحة للشريط العلوي */
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
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .nav-item.active {
            background: #0d6efd;
        }
        .main-content {
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .form-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            max-width: 800px;
            margin: 0 auto 30px auto;
        }
        .lectures-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-top: 30px;
        }
        .form-title {
            color: #1b325f;
            margin-bottom: 30px;
            text-align: center;
            font-size: 24px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        select, input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: 6px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }
        select:focus, input:focus {
            outline: none;
            border-color: #1b325f;
        }
        .btn-submit {
            background: #1b325f;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s ease;
            width: 100%;
        }
        .btn-submit:hover {
            background: #142447;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 6px;
            text-align: center;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .lectures-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .lectures-table th,
        .lectures-table td {
            padding: 12px;
            text-align: center;
            border: 1px solid #dee2e6;
        }
        .lectures-table th {
            background: #1b325f;
            color: white;
            font-weight: 600;
        }
        .lectures-table tr:nth-child(even) {
            background: #f8f9fa;
        }
        .lectures-table tr:hover {
            background: #e9ecef;
        }
        .btn-action {
            padding: 6px 12px;
            margin: 2px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.3s ease;
        }
        .btn-edit {
            background: #28a745;
            color: white;
        }
        .btn-edit:hover {
            background: #218838;
        }
        .btn-delete {
            background: #dc3545;
            color: white;
        }
        .btn-delete:hover {
            background: #c82333;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border-radius: 10px;
            width: 80%;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
        }
        .close {
            color: #aaa;
            float: left;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover {
            color: black;
        }
        .modal-form {
            margin-top: 20px;
        }
        .modal-form .form-group {
            margin-bottom: 15px;
        }
        .modal-form select,
        .modal-form input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .modal-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 20px;
        }
        .btn-modal {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        .btn-save {
            background: #28a745;
            color: white;
        }
        .btn-cancel {
            background: #6c757d;
            color: white;
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
            <div class="nav-item active">
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
    
        <!-- جدول عرض المحاضرات -->
        <div class="lectures-container">
            <h2 class="form-title">📋 جدول المحاضرات</h2>
            
            <?php if ($lectures_result && $lectures_result->num_rows > 0): ?>
                <table class="lectures-table">
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
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($lecture = $lectures_result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($lecture['lecture_id']) ?></td>
                                <td><?= htmlspecialchars($lecture['subject_name']) ?></td>
                                <td><?= htmlspecialchars($lecture['teacher_name']) ?></td>
                                <td><?= htmlspecialchars($lecture['class_name']) ?></td>
                                <td><?= htmlspecialchars($lecture['group_name']) ?></td>
                                <td><?= htmlspecialchars($lecture['semester_name']) ?></td>
                                <td><?= htmlspecialchars($lecture['day_of_week']) ?></td>
                                <td><?= htmlspecialchars($lecture['start_time']) ?></td>
                                <td><?= htmlspecialchars($lecture['end_time']) ?></td>
                                <td>
                                    <button class="btn-action btn-edit" onclick="openEditModal(<?= htmlspecialchars(json_encode($lecture)) ?>)">
                                        ✏️ تعديل
                                    </button>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('هل أنت متأكد من حذف هذه المحاضرة؟')">
                                        <input type="hidden" name="lecture_id" value="<?= htmlspecialchars($lecture['lecture_id']) ?>">
                                        <button type="submit" name="delete_lecture" class="btn-action btn-delete">
                                            🗑️ حذف
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="alert alert-error">لا توجد محاضرات مسجلة حالياً</div>
            <?php endif; ?>
        </div>
    </div>
    <br>
    <div class="main-content">
        <div class="form-container">
            <h2 class="form-title">📚 إضافة محاضرة جديدة</h2>
            
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="subject_id">المادة</label>
                    <select name="subject_id" required>
                        <option disabled selected>اختر مادة</option>
                        <?php while($sub = $subjects->fetch_assoc()): ?>
                            <option value="<?= $sub['subject_id'] ?>"> <?= $sub['name'] ?> (<?= $sub['semester_name'] ?>)</option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="teacher_id">الأستاذ</label>
                    <select name="teacher_id" required>
                        <option disabled selected>اختر أستاذ</option>
                        <?php while($tea = $teachers->fetch_assoc()): ?>
                            <option value="<?= $tea['teacher_id'] ?>"> <?= $tea['name'] ?> - <?= $tea['specialization'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="class_id">القاعة</label>
                    <select name="class_id" required>
                        <option disabled selected>اختر قاعة</option>
                        <?php while($class = $classes->fetch_assoc()): ?>
                            <option value="<?= $class['class_id'] ?>"> <?= $class['name'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="group_id">المجموعة</label>
                    <select name="group_id" required>
                        <option disabled selected>اختر مجموعة</option>
                        <?php while($group = $groups->fetch_assoc()): ?>
                            <option value="<?= $group['group_id'] ?>"> <?= $group['name'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="day">اليوم</label>
                    <select name="day" required>
                        <option disabled selected>اختر اليوم</option>
                        <option value="الأحد">الأحد</option>
                        <option value="الاثنين">الاثنين</option>
                        <option value="الثلاثاء">الثلاثاء</option>
                        <option value="الأربعاء">الأربعاء</option>
                        <option value="الخميس">الخميس</option>
                        <option value="الجمعة">الجمعة</option>
                        <option value="السبت">السبت</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="start_time">وقت البداية</label>
                    <input type="time" name="start_time" required>
                </div>

                <div class="form-group">
                    <label for="end_time">وقت النهاية</label>
                    <input type="time" name="end_time" required>
                </div>

                <button type="submit" class="btn-submit">💾 حفظ المحاضرة</button>
            </form>
        </div>


    <!-- نافذة تعديل المحاضرة -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditModal()">&times;</span>
            <h3>تعديل المحاضرة</h3>
            <form method="POST" class="modal-form">
                <input type="hidden" name="lecture_id" id="edit_lecture_id">
                
                <div class="form-group">
                    <label for="edit_subject_id">المادة</label>
                    <select name="subject_id" id="edit_subject_id" required>
                        <option disabled>اختر مادة</option>
                        <?php 
                        $subjects_edit = $conn->query("SELECT s.subject_id, s.name, s.sem_id, se.name AS semester_name FROM Subjects s JOIN Semesters se ON s.sem_id = se.semester_id");
                        while($sub = $subjects_edit->fetch_assoc()): ?>
                            <option value="<?= $sub['subject_id'] ?>"> <?= htmlspecialchars($sub['name']) ?> (<?= htmlspecialchars($sub['semester_name']) ?>)</option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="edit_teacher_id">الأستاذ</label>
                    <select name="teacher_id" id="edit_teacher_id" required>
                        <option disabled>اختر أستاذ</option>
                        <?php 
                        $teachers_edit = $conn->query("SELECT teacher_id, name, specialization FROM Teachers WHERE status = 'active' AND deleted = false");
                        while($tea = $teachers_edit->fetch_assoc()): ?>
                            <option value="<?= $tea['teacher_id'] ?>"> <?= htmlspecialchars($tea['name']) ?> - <?= htmlspecialchars($tea['specialization']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="edit_class_id">القاعة</label>
                    <select name="class_id" id="edit_class_id" required>
                        <option disabled>اختر قاعة</option>
                        <?php 
                        $classes_edit = $conn->query("SELECT class_id, name FROM Classes");
                        while($class = $classes_edit->fetch_assoc()): ?>
                            <option value="<?= $class['class_id'] ?>"> <?= htmlspecialchars($class['name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="edit_group_id">المجموعة</label>
                    <select name="group_id" id="edit_group_id" required>
                        <option disabled>اختر مجموعة</option>
                        <?php 
                        $groups_edit = $conn->query("SELECT group_id, name FROM Groups");
                        while($group = $groups_edit->fetch_assoc()): ?>
                            <option value="<?= $group['group_id'] ?>"> <?= htmlspecialchars($group['name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="edit_day">اليوم</label>
                    <select name="day" id="edit_day" required>
                        <option disabled>اختر اليوم</option>
                        <option value="الأحد">الأحد</option>
                        <option value="الاثنين">الاثنين</option>
                        <option value="الثلاثاء">الثلاثاء</option>
                        <option value="الأربعاء">الأربعاء</option>
                        <option value="الخميس">الخميس</option>
                        <option value="الجمعة">الجمعة</option>
                        <option value="السبت">السبت</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="edit_start_time">وقت البداية</label>
                    <input type="time" name="start_time" id="edit_start_time" required>
                </div>

                <div class="form-group">
                    <label for="edit_end_time">وقت النهاية</label>
                    <input type="time" name="end_time" id="edit_end_time" required>
                </div>

                <div class="modal-buttons">
                    <button type="submit" name="edit_lecture" class="btn-modal btn-save">💾 حفظ التعديلات</button>
                    <button type="button" class="btn-modal btn-cancel" onclick="closeEditModal()">❌ إلغاء</button>
                </div>
            </form>
        </div>
    </div>

    <script src="attatchments/bootstrap.bundle.min.js"></script>
    <script>
        function openEditModal(lecture) {
            document.getElementById('edit_lecture_id').value = lecture.lecture_id;
            document.getElementById('edit_subject_id').value = lecture.sbjct_id;
            document.getElementById('edit_teacher_id').value = lecture.teachr_id;
            document.getElementById('edit_class_id').value = lecture.class_id;
            document.getElementById('edit_group_id').value = lecture.group_id;
            document.getElementById('edit_day').value = lecture.day_of_week;
            document.getElementById('edit_start_time').value = lecture.start_time;
            document.getElementById('edit_end_time').value = lecture.end_time;
            
            document.getElementById('editModal').style.display = 'block';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        // إغلاق النافذة عند النقر خارجها
        window.onclick = function(event) {
            var modal = document.getElementById('editModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>
