<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "teacher") {
    header("Location: login.php");
    exit();
}

include "includes/db_connection.php";

$teacher_id = $_SESSION["user_id"];
$message = "";

// جلب بيانات المعلم الحالية
$stmt = $conn->prepare("SELECT name, email, phone FROM Teachers WHERE teacher_id = ?");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$result = $stmt->get_result();
$teacher_data = $result->fetch_assoc();
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST["name"];
    $email = $_POST["email"];
    $phone = $_POST["phone"];
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    // التحقق من البريد الإلكتروني
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "<div class=\"alert alert-danger\">❌ البريد الإلكتروني غير صالح.</div>";
    } else if ($password && $password !== $confirm_password) {
        $message = "<div class=\"alert alert-danger\">❌ كلمة المرور وتأكيد كلمة المرور غير متطابقين.</div>";
    } else {
        // تحديث البيانات
        $update_query = "UPDATE Teachers SET name = ?, email = ?, phone = ?";
        $params = "sss";
        $values = [&$name, &$email, &$phone];

        if ($password) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $update_query .= ", password = ?";
            $params .= "s";
            $values[] = &$hashed_password;
        }

        $update_query .= " WHERE teacher_id = ?";
        $params .= "i";
        $values[] = &$teacher_id;

        $stmt = $conn->prepare($update_query);
        $stmt->bind_param($params, ...$values);

        if ($stmt->execute()) {
            $_SESSION["name"] = $name; // تحديث الاسم في الجلسة
            $message = "<div class=\"alert alert-success\">✅ تم تحديث بياناتك بنجاح!</div>";
            // إعادة جلب البيانات المحدثة لعرضها في النموذج
            $stmt_reget = $conn->prepare("SELECT name, email, phone FROM Teachers WHERE teacher_id = ?");
            $stmt_reget->bind_param("i", $teacher_id);
            $stmt_reget->execute();
            $result_reget = $stmt_reget->get_result();
            $teacher_data = $result_reget->fetch_assoc();
            $stmt_reget->close();
        } else {
            $message = "<div class=\"alert alert-danger\">❌ حدث خطأ أثناء تحديث البيانات.</div>";
        }
        $stmt->close();
    }
}

?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الملف الشخصي</title>
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
    </style>
</head>
<body>
    <?php include 'teacher_navbar.php'; ?>

    <div class="container mt-4">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">👤 الملف الشخصي</h4>
            </div>
            <div class="card-body">
                <?php if (isset($message)): ?>
                    <?= $message ?>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label for="name" class="form-label">الاسم الكامل</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($teacher_data['name']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">البريد الإلكتروني</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($teacher_data['email']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">رقم الهاتف</label>
                        <input type="text" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($teacher_data['phone']) ?>">
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">كلمة المرور الجديدة (اتركها فارغة لعدم التغيير)</label>
                        <input type="password" class="form-control" id="password" name="password">
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">تأكيد كلمة المرور الجديدة</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">💾 حفظ التغييرات</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="attatchments/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
$conn->close();
?>

