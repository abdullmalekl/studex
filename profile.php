<?php
session_start();
include 'includes/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$message = '';

// جلب بيانات المستخدم
if ($role === 'admin') {
    $stmt = $conn->prepare("SELECT * FROM Users WHERE user_id = ?");
} elseif ($role === 'student') {
    $stmt = $conn->prepare("SELECT * FROM Students WHERE student_id = ?");
} elseif ($role === 'teacher') {
    $stmt = $conn->prepare("SELECT * FROM Teachers WHERE teacher_id = ?");
}

$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit();
}

// تحديث البيانات الشخصية
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    if ($role === 'admin') {
        $stmt = $conn->prepare("UPDATE Users SET name = ?, email = ?, phone = ?, address = ? WHERE user_id = ?");
        $stmt->bind_param('sssii', $name, $email, $phone, $address, $user_id);
    } elseif ($role === 'student') {
        $stmt = $conn->prepare("UPDATE Students SET name = ?, email = ?, phone = ?, address = ? WHERE student_id = ?");
        $stmt->bind_param('sssii', $name, $email, $phone, $address, $user_id);
    } elseif ($role === 'teacher') {
        $stmt = $conn->prepare("UPDATE Teachers SET name = ?, email = ?, phone = ?, address = ? WHERE teacher_id = ?");
        $stmt->bind_param('sssii', $name, $email, $phone, $address, $user_id);
    }

    if ($stmt->execute()) {
        $message = '<div class="alert alert-success">✅ تم تحديث البيانات بنجاح!</div>';
        // تحديث بيانات الجلسة إذا كان الاسم قد تغير
        $_SESSION['user_name'] = $name;
        // إعادة جلب البيانات المحدثة
        if ($role === 'admin') {
            $stmt = $conn->prepare("SELECT * FROM Users WHERE user_id = ?");
        } elseif ($role === 'student') {
            $stmt = $conn->prepare("SELECT * FROM Students WHERE student_id = ?");
        } elseif ($role === 'teacher') {
            $stmt = $conn->prepare("SELECT * FROM Teachers WHERE teacher_id = ?");
        }
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

    } else {
        $message = '<div class="alert alert-danger">❌ حدث خطأ أثناء تحديث البيانات: ' . $stmt->error . '</div>';
    }
}

// تغيير كلمة المرور
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (!password_verify($current_password, $user['password'])) {
        $message = '<div class="alert alert-danger">❌ كلمة المرور الحالية غير صحيحة.</div>';
    } elseif ($new_password !== $confirm_password) {
        $message = '<div class="alert alert-danger">❌ كلمة المرور الجديدة وتأكيدها غير متطابقين.</div>';
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        if ($role === 'admin') {
            $stmt = $conn->prepare("UPDATE Users SET password = ? WHERE user_id = ?");
        } elseif ($role === 'student') {
            $stmt = $conn->prepare("UPDATE Students SET password = ? WHERE student_id = ?");
        } elseif ($role === 'teacher') {
            $stmt = $conn->prepare("UPDATE Teachers SET password = ? WHERE teacher_id = ?");
        }
        $stmt->bind_param('si', $hashed_password, $user_id);

        if ($stmt->execute()) {
            $message = '<div class="alert alert-success">✅ تم تغيير كلمة المرور بنجاح!</div>';
        } else {
            $message = '<div class="alert alert-danger">❌ حدث خطأ أثناء تغيير كلمة المرور: ' . $stmt->error . '</div>';
        }
    }
}

$conn->close();
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
            padding-top: 80px; /* لتجنب تداخل الشريط العلوي */
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
    </style>
</head>
<body>
    <?php 
    if ($_SESSION['role'] === 'admin') {
        include 'admin_navbar.php';
    } elseif ($_SESSION['role'] === 'student') {
        include 'student_navbar.php'; // افترض وجود شريط تنقل للطلاب
    } elseif ($_SESSION['role'] === 'teacher') {
        include 'teacher_navbar.php'; // افترض وجود شريط تنقل للمعلمين
    }
    ?>

    <div class="container mt-4">
        <h2 class="mb-4">الملف الشخصي</h2>
        <?= $message ?>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">تعديل البيانات الشخصية</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label for="name" class="form-label">الاسم</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">البريد الإلكتروني</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">رقم الهاتف</label>
                        <input type="text" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($user['phone']) ?>">
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">العنوان</label>
                        <input type="text" class="form-control" id="address" name="address" value="<?= htmlspecialchars($user['address']) ?>">
                    </div>
                    <button type="submit" name="update_profile" class="btn btn-primary">💾 حفظ التغييرات</button>
                </form>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">تغيير كلمة المرور</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label for="current_password" class="form-label">كلمة المرور الحالية</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="new_password" class="form-label">كلمة المرور الجديدة</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">تأكيد كلمة المرور الجديدة</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    <button type="submit" name="change_password" class="btn btn-warning">🔒 تغيير كلمة المرور</button>
                </form>
            </div>
        </div>
    </div>

    <script src="attatchments/bootstrap.bundle.min.js"></script>
</body>
</html>

