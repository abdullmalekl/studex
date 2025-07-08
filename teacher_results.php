<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "teacher") {
    header("Location: login.php");
    exit();
}

include "includes/db_connection.php";

$teacher_id = $_SESSION["user_id"];
$teacher_name = $_SESSION["name"];
$message = "";

// معالجة النماذج
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // إضافة أو تحديث النتائج
    if (isset($_POST["save_results"])) {
        $subject_id = $_POST["subject_id"];
        $group_id = $_POST["group_id"];
        
        // التحقق من أن المعلم يدرس هذه المادة لهذه المجموعة
        $verify_query = "SELECT COUNT(*) as count FROM Lectures WHERE teachr_id = ? AND sbjct_id = ? AND group_id = ?";
        $verify_stmt = $conn->prepare($verify_query);
        $verify_stmt->bind_param("iii", $teacher_id, $subject_id, $group_id);
        $verify_stmt->execute();
        $verify_result = $verify_stmt->get_result();
        $verify_data = $verify_result->fetch_assoc();
        
        if ($verify_data["count"] > 0) {
            $success_count = 0;
            $error_count = 0;
            
            foreach ($_POST["students"] as $student_id => $grades) {
                $midterm_grade = !empty($grades["midterm"]) ? $grades["midterm"] : null;
                $final_grade = !empty($grades["final"]) ? $grades["final"] : null;
                
                // التحقق من وجود سجل سابق
                $check_query = "SELECT result_id FROM Results WHERE stdnt_id = ? AND sbjct_id = ?";
                $check_stmt = $conn->prepare($check_query);
                $check_stmt->bind_param("ii", $student_id, $subject_id);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                
                if ($check_result->num_rows > 0) {
                    // تحديث السجل الموجود
                    $update_query = "UPDATE Results SET midterm_grade = ?, final_grade = ? WHERE stdnt_id = ? AND sbjct_id = ?";
                    $update_stmt = $conn->prepare($update_query);
                    $update_stmt->bind_param("ddii", $midterm_grade, $final_grade, $student_id, $subject_id);
                    
                    if ($update_stmt->execute()) {
                        $success_count++;
                    } else {
                        $error_count++;
                    }
                    $update_stmt->close();
                } else {
                    // إدراج سجل جديد
                    if ($midterm_grade !== null || $final_grade !== null) {
                        $insert_query = "INSERT INTO Results (student_id, subject_id, midterm_grade, final_grade) VALUES (?, ?, ?, ?)";
                        $insert_stmt = $conn->prepare($insert_query);
                        $insert_stmt->bind_param("iidd", $student_id, $subject_id, $midterm_grade, $final_grade);
                        
                        if ($insert_stmt->execute()) {
                            $success_count++;
                        } else {
                            $error_count++;
                        }
                        $insert_stmt->close();
                    }
                }
                $check_stmt->close();
            }
            
            if ($error_count == 0) {
                $message = "<div class=\"alert alert-success\">✅ تم حفظ النتائج بنجاح! ($success_count طالب)</div>";
            } else {
                $message = "<div class=\"alert alert-danger\">⚠️ تم حفظ $success_count نتيجة، فشل في حفظ $error_count نتيجة.</div>";
            }
        } else {
            $message = "<div class=\"alert alert-danger\">❌ ليس لديك صلاحية لإدخال نتائج هذه المادة لهذه المجموعة.</div>";
        }
        $verify_stmt->close();
    }
}

// جلب المواد التي يدرسها المعلم مع المجموعات
$subjects_query = "SELECT DISTINCT s.subject_id, s.name AS subject_name, se.name AS semester_name, 
                          g.group_id, g.name AS group_name
                   FROM Lectures l
                   JOIN Subjects s ON l.sbjct_id = s.subject_id
                   JOIN Semesters se ON s.sem_id = se.semester_id
                   JOIN Groups g ON l.group_id = g.group_id
                   WHERE l.teachr_id = ?
                   ORDER BY s.name, g.name";
$subjects_stmt = $conn->prepare($subjects_query);
$subjects_stmt->bind_param("i", $teacher_id);
$subjects_stmt->execute();
$subjects_result = $subjects_stmt->get_result();

// متغيرات لعرض النتائج
$selected_subject_id = isset($_GET["subject_id"]) ? $_GET["subject_id"] : null;
$selected_group_id = isset($_GET["group_id"]) ? $_GET["group_id"] : null;
$students_result = null;

if ($selected_subject_id && $selected_group_id) {
    // التحقق من صلاحية المعلم
    $verify_query = "SELECT COUNT(*) as count FROM Lectures WHERE teachr_id = ? AND sbjct_id = ? AND group_id = ?";
    $verify_stmt = $conn->prepare($verify_query);
    $verify_stmt->bind_param("iii", $teacher_id, $selected_subject_id, $selected_group_id);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();
    $verify_data = $verify_result->fetch_assoc();
    
 if ($selected_subject_id) {
    // جلب الطلاب الذين لديهم نتائج مسجلة في جدول Results لهذه المادة
    $students_query = "SELECT s.student_id, s.name AS student_name, s.email,
                             r.midterm_grade, r.final_grade
                      FROM Students s
                      JOIN Results r ON s.student_id = r.stdnt_id
                      WHERE r.sbjct_id = ? AND s.status = 'active' AND s.deleted = false
                      ORDER BY s.name";
    $students_stmt = $conn->prepare($students_query);
    $students_stmt->bind_param("i", $selected_subject_id);
    $students_stmt->execute();
    $students_result = $students_stmt->get_result();
}

    $verify_stmt->close();
}

?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة النتائج - المعلم</title>
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
        .table th {
            background-color: #f8f9fa;
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
        .alert-info {
            background-color: #d1ecf1;
            color: #0c5460;
            border-color: #bee5eb;
        }
        .results-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .results-table th,
        .results-table td {
            padding: 12px;
            text-align: center;
            border: 1px solid #dee2e6;
        }
        .results-table th {
            background: #1b325f;
            color: white;
            font-weight: 600;
        }
        .results-table tr:nth-child(even) {
            background: #f8f9fa;
        }
        .results-table tr:hover {
            background: #e9ecef;
        }
        .grade-input {
            width: 80px;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-align: center;
        }
        .subject-group-selector {
            display: flex;
            gap: 20px;
            align-items: end;
            margin-bottom: 20px;
        }
        .subject-group-selector > div {
            flex: 1;
        }
    </style>
</head>
<body>
    <?php include 'teacher_navbar.php'; ?>

    <div class="container mt-4">
        <?= $message ?>
        
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">📊 إدارة النتائج</h4>
            </div>
            <div class="card-body">
                <form method="GET">
                    <div class="subject-group-selector">
                        <div class="mb-3 w-100">
                            <label for="subject_group" class="form-label">اختر المادة والمجموعة</label>
                            <select name="subject_group" id="subject_group" class="form-select" required onchange="this.form.submit()">
                                <option value="">اختر المادة والمجموعة</option>
                                <?php while($subject = $subjects_result->fetch_assoc()): ?>
                                    <option value="<?= $subject['subject_id'] ?>_<?= $subject['group_id'] ?>" 
                                            <?= ($selected_subject_id == $subject['subject_id'] && $selected_group_id == $subject['group_id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($subject['subject_name']) ?> - <?= htmlspecialchars($subject['group_name']) ?> (<?= htmlspecialchars($subject['semester_name']) ?>)
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    <input type="hidden" name="subject_id" value="<?= htmlspecialchars($selected_subject_id) ?>">
                    <input type="hidden" name="group_id" value="<?= htmlspecialchars($selected_group_id) ?>">
                </form>

                <script>
                    document.getElementById('subject_group').addEventListener('change', function() {
                        const value = this.value;
                        if (value) {
                            const [subjectId, groupId] = value.split('_');
                            window.location.href = `teacher_results.php?subject_id=${subjectId}&group_id=${groupId}`;
                        }
                    });
                </script>
            </div>
        </div>

        <?php if ($students_result && $students_result->num_rows > 0): ?>
            <div class="card mt-4">
                <div class="card-header">
                    <h4 class="mb-0">📝 إدخال النتائج</h4>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="subject_id" value="<?= htmlspecialchars($selected_subject_id) ?>">
                        <input type="hidden" name="group_id" value="<?= htmlspecialchars($selected_group_id) ?>">
                        
                        <div class="table-responsive">
                            <table class="table table-striped results-table">
                                <thead>
                                    <tr>
                                        <th>اسم الطالب</th>
                                        <th> رقم القيد</th>
                                        <th>درجة منتصف الفصل (Midterm)</th>
                                        <th>درجة نهاية الفصل (Final)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($student = $students_result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($student['student_name']) ?></td>
                                            <td><?= htmlspecialchars($student['student_id']) ?></td>
                                            <td>
                                                <input type="number" 
                                                       name="students[<?= $student['student_id'] ?>][midterm]" 
                                                       class="form-control grade-input" 
                                                       min="0" 
                                                       max="100" 
                                                       step="0.01"
                                                       value="<?= htmlspecialchars($student['midterm_grade']) ?>"
                                                       placeholder="0-100">
                                            </td>
                                            <td>
                                                <input type="number" 
                                                       name="students[<?= $student['student_id'] ?>][final]" 
                                                       class="form-control grade-input" 
                                                       min="0" 
                                                       max="100" 
                                                       step="0.01"
                                                       value="<?= htmlspecialchars($student['final_grade']) ?>"
                                                       placeholder="0-100">
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="text-center mt-3">
                            <button type="submit" name="save_results" class="btn btn-primary">💾 حفظ النتائج</button>
                        </div>
                    </form>
                </div>
            </div>
        <?php elseif ($selected_subject_id && $selected_group_id): ?>
            <div class="card mt-4">
                <div class="card-body">
                    <div class="alert alert-info">لا يوجد طلاب مسجلون في هذه المجموعة أو ليس لديك صلاحية لإدخال نتائج هذه المادة.</div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($subjects_result->num_rows == 0): ?>
            <div class="card mt-4">
                <div class="card-body">
                    <div class="alert alert-info">لا توجد مواد مسندة إليك حالياً.</div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="attatchments/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
$conn->close();
?>

