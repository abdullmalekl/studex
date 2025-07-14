<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include 'includes/db_connection.php';
include 'get_site_name.php';

$current_site_name = getSiteName();
$message = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $new_site_name = trim($_POST['site_name']);
    
    if (!empty($new_site_name)) {
        $stmt = $conn->prepare("UPDATE Site_name SET site_name = ? WHERE id = (SELECT id FROM (SELECT id FROM Site_name ORDER BY id DESC LIMIT 1) AS temp)");
        $stmt->bind_param("s", $new_site_name);
        
        if ($stmt->execute()) {
            $message = "<div class='alert alert-success'>✅ تم تحديث اسم الموقع بنجاح!</div>";
            $current_site_name = $new_site_name;
        } else {
            $message = "<div class='alert alert-danger'>❌ حدث خطأ أثناء تحديث اسم الموقع.</div>";
        }
        $stmt->close();
    } else {
        $message = "<div class='alert alert-warning'>⚠️ يرجى إدخال اسم الموقع.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إعدادات الموقع</title>
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
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .nav-item.active {
            background: #0d6efd;
        }
        .navbar {
            background-color: #1b325f;
        }
        .navbar-brand, .navbar-nav .nav-link {
            color: white !important;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            background-color: #1b325f;
            color: white;
            border-radius: 10px 10px 0 0 !important;
        }
        .btn-primary {
            background-color: #1b325f;
            border-color: #1b325f;
        }
        .btn-primary:hover {
            background-color: #142447;
            border-color: #142447;
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
                <div class="nav-item active">
        <a href="admin_site_settings.php">⚙️ إعدادات الموقع</a>
    </div>
            <div class="nav-item">
                <a href="logout.php">🚪 تسجيل الخروج</a>
            </div>
        </div>
    </div>
<br>
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">⚙️ إعدادات الموقع</h4>
                    </div>
                    <div class="card-body">
                        <?= $message ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label for="site_name" class="form-label">اسم الموقع</label>
                                <input type="text" class="form-control" id="site_name" name="site_name" 
                                       value="<?= htmlspecialchars($current_site_name) ?>" required>
                                <div class="form-text">هذا الاسم سيظهر في عنوان الصفحة والشعار</div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">💾 حفظ التغييرات</button>
                        </form>
                        
                        <hr class="my-4">
                        
                        <div class="alert alert-info">
                            <h6>📋 معاينة:</h6>
                            <p><strong>الاسم الحالي:</strong> <?= htmlspecialchars($current_site_name) ?></p>
                            <p><small class="text-muted">سيظهر هذا الاسم في جميع صفحات الموقع</small></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="attatchments/bootstrap.bundle.min.js"></script>
</body>
</html>

