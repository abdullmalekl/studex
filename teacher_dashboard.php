<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "teacher") {
    header("Location: login.php");
    exit();
}

include "includes/db_connection.php";

$teacher_id = $_SESSION["user_id"];
$teacher_name = $_SESSION["name"];

// جلب المواد التي يدرسها المعلم
$subjects_taught_query = "SELECT DISTINCT s.subject_id, s.name AS subject_name, se.name AS semester_name
                          FROM Lectures l
                          JOIN Subjects s ON l.sbjct_id = s.subject_id
                          JOIN Semesters se ON s.sem_id = se.semester_id
                          WHERE l.teachr_id = ?";
$stmt_subjects = $conn->prepare($subjects_taught_query);
$stmt_subjects->bind_param("i", $teacher_id);
$stmt_subjects->execute();
$subjects_taught_result = $stmt_subjects->get_result();

// جلب المحاضرات الخاصة بالمعلم
$lectures_query = "SELECT 
    L.lecture_id, L.day_of_week, L.start_time, L.end_time,
    S.name AS subject_name, SE.name AS semester_name,
    C.name AS class_name,
    G.name AS group_name
FROM 
    Lectures L
JOIN 
    Subjects S ON L.sbjct_id = S.subject_id
JOIN 
    Semesters SE ON S.sem_id = SE.semester_id
JOIN 
    Classes C ON L.class_id = C.class_id
JOIN 
    Groups G ON L.group_id = G.group_id
WHERE 
    L.teachr_id = ?
ORDER BY 
    L.day_of_week, L.start_time";

$stmt_lectures = $conn->prepare($lectures_query);
$stmt_lectures->bind_param("i", $teacher_id);
$stmt_lectures->execute();
$lectures_result = $stmt_lectures->get_result();

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة تحكم المعلم</title>
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
        .alert-info {
            background-color: #d1ecf1;
            color: #0c5460;
            border-color: #bee5eb;
        }
    </style>
</head>
<body>
    <?php include 'teacher_navbar.php'; ?>
       
    <div class="container mt-4">
        <div class="card">
            
            <div class="card-body">
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">📚 المواد التي تدرسها</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($subjects_taught_result->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>اسم المادة</th>
                                            <th>الفصل الدراسي</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($subject = $subjects_taught_result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($subject['subject_name']) ?></td>
                                                <td><?= htmlspecialchars($subject['semester_name']) ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">لا توجد مواد مسندة إليك حالياً.</div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">🗓️ جدول محاضراتك</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($lectures_result->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>المادة</th>
                                            <th>القاعة</th>
                                            <th>المجموعة</th>
                                            <th>اليوم</th>
                                            <th>وقت البداية</th>
                                            <th>وقت النهاية</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($lecture = $lectures_result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($lecture['subject_name']) ?> (<?= htmlspecialchars($lecture['semester_name']) ?>)</td>
                                                <td><?= htmlspecialchars($lecture['class_name']) ?></td>
                                                <td><?= htmlspecialchars($lecture['group_name']) ?></td>
                                                <td><?= htmlspecialchars($lecture['day_of_week']) ?></td>
                                                <td><?= htmlspecialchars($lecture['start_time']) ?></td>
                                                <td><?= htmlspecialchars($lecture['end_time']) ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">لا توجد محاضرات مجدولة لك حالياً.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="attatchments/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
$conn->close();
?>

