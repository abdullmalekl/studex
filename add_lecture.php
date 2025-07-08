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
// $teachers = $conn->query("SELECT teacher_id, name, specialization FROM Teachers WHERE status = 'active' AND deleted = false");
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $subject_id = $_POST['subject_id'];
    $teacher_id = $_POST['teacher_id'];
    $room = $_POST['room'];
    $day = $_POST['day'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];

    // تحقق من عدم وجود تعارض في الجدول للمكان أو الأستاذ
    $conflict = $conn->prepare("SELECT * FROM lectures WHERE day = ? AND ((start_time < ? AND end_time > ?) OR (start_time < ? AND end_time > ?) OR (start_time >= ? AND end_time <= ?)) AND (room = ? OR teacher_id = ?)");
    $conflict->bind_param("ssssssssi", $day, $end_time, $end_time, $start_time, $start_time, $start_time, $end_time, $room, $teacher_id);
    $conflict->execute();
    $result = $conflict->get_result();

    if ($result->num_rows > 0) {
        $error = "⚠️ يوجد تعارض في الجدول للمكان أو وقت الأستاذ.";
    } else {
        $stmt = $conn->prepare("INSERT INTO lectures (subject_id, teacher_id, room, day, start_time, end_time) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iissss", $subject_id, $teacher_id, $room, $day, $start_time, $end_time);
        $stmt->execute();
        $success = "✅ تم إضافة المحاضرة بنجاح!";
    }
}
?>
<?php include 'admin_sidebar.php'; ?>
<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>إضافة محاضرة</title>
    <style>
        body { font-family: Arial; background: #f5f5f5; direction: rtl; padding: 20px; }
        form { background: white; padding: 20px; max-width: 600px; margin: auto; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        label { display: block; margin: 10px 0 5px; }
        select, input { width: 100%; padding: 8px; margin-bottom: 10px; }
        button { padding: 10px 20px; background: #1b325f; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .message { margin-bottom: 15px; padding: 10px; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <form method="POST">
        <h2>📅 إنشاء محاضرة جديدة</h2>

        <?php if (isset($success)) echo "<div class='message success'>$success</div>"; ?>
        <?php if (isset($error)) echo "<div class='message error'>$error</div>"; ?>

        <label for="subject_id">المادة</label>
        <select name="subject_id" required>
            <option disabled selected>اختر مادة</option>
            <?php while($sub = $subjects->fetch_assoc()): ?>
                <option value="<?= $sub['subject_id'] ?>"> <?= $sub['name'] ?> (<?= $sub['semester_name'] ?>)</option>
            <?php endwhile; ?>
        </select>

        <label for="teacher_id">الأستاذ</label>
        <select name="teacher_id" required>
            <option disabled selected>اختر أستاذ</option>
            <?php while($tea = $teachers->fetch_assoc()): ?>
                <option value="<?= $tea['user_id'] ?>"> <?= $tea['name'] ?> - <?= $tea['specialization'] ?></option>
            <?php endwhile; ?>
        </select>

        <label for="room">رقم القاعة</label>
        <input type="text" name="room" required>

        <label for="day">اليوم</label>
        <select name="day" required>
            <option disabled selected>اختر اليوم</option>
            <option value="السبت">السبت</option>
            <option value="الأحد">الأحد</option>
            <option value="الإثنين">الإثنين</option>
            <option value="الثلاثاء">الثلاثاء</option>
            <option value="الأربعاء">الأربعاء</option>
            <option value="الخميس">الخميس</option>
        </select>

        <label for="start_time">وقت البداية</label>
        <input type="time" name="start_time" required>

        <label for="end_time">وقت النهاية</label>
        <input type="time" name="end_time" required>

        <button type="submit">📥 حفظ المحاضرة</button>
    </form>
</body>
</html>
