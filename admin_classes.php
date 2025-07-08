<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit();
}

include "includes/db_connection.php";

$message = "";

// إضافة قاعة جديدة
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_class"])) {
    $class_id = $_POST["class_id"];
    $name = $_POST["name"];
    
    $stmt = $conn->prepare("INSERT INTO classes (class_id, name) VALUES (?, ?)");
    $stmt->bind_param("is", $class_id, $name);
    
    if ($stmt->execute()) {
        $message = "<div class=\"alert alert-success\">✅ تم إضافة القاعة بنجاح!</div>";
    } else {
        $message = "<div class=\"alert alert-danger\">❌ حدث خطأ أثناء إضافة القاعة. تأكد من أن رقم القاعة غير مستخدم.</div>";
    }
    $stmt->close();
}

// تعديل قاعة
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["edit_class"])) {
    $original_class_id = $_POST["original_class_id"]; // Store original class_id
    $new_class_id = $_POST["new_class_id"]; // New class_id from form
    $name = $_POST["name"];
    
    $stmt = $conn->prepare("UPDATE classes SET class_id = ?, name = ? WHERE class_id = ?");
    $stmt->bind_param("isi", $new_class_id, $name, $original_class_id);
    
    if ($stmt->execute()) {
        $message = "<div class=\"alert alert-success\">✅ تم تحديث القاعة بنجاح!</div>";
    } else {
        $message = "<div class=\"alert alert-danger\">❌ حدث خطأ أثناء تحديث القاعة.</div>";
    }
    $stmt->close();
}

// حذف قاعة
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_class"])) {
    $class_id = $_POST["class_id"];
    
    $stmt = $conn->prepare("DELETE FROM classes WHERE class_id = ?");
    $stmt->bind_param("i", $class_id);
    
    if ($stmt->execute()) {
        $message = "<div class=\"alert alert-success\">✅ تم حذف القاعة بنجاح!</div>";
    } else {
        $message = "<div class=\"alert alert-danger\">❌ حدث خطأ أثناء حذف القاعة.</div>";
    }
    $stmt->close();
}

// جلب جميع القاعات
$classes = $conn->query("SELECT * FROM classes ORDER BY class_id");
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة القاعات</title>
    <link href="attatchments/bootstrap.min.css" rel="stylesheet">
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
            <div class="nav-item active">
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
        <?= $message ?>
        
        <!-- إضافة قاعة جديدة -->
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">➕ إضافة قاعة جديدة</h4>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">رقم القاعة</label>
                            <input type="number" name="class_id" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">اسم القاعة</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                    </div>
                    <div class="text-center mt-3">
                        <button type="submit" name="add_class" class="btn btn-primary">➕ إضافة القاعة</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- عرض القاعات -->
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">🏫 القاعات الموجودة</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>الرقم</th>
                                <th>اسم القاعة</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($classes->num_rows > 0): ?>
                                <?php while($class = $classes->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($class["class_id"]) ?></td>
                                    <td><?= htmlspecialchars($class["name"]) ?></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <!-- تعديل -->
                                            <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?= $class["class_id"] ?>">
                                                ✏️ تعديل
                                            </button>
                                            
                                            <!-- حذف -->
                                            <form method="POST" style="display: inline;" onsubmit="return confirm(\'هل أنت متأكد من حذف هذه القاعة؟\')">
                                                <input type="hidden" name="class_id" value="<?= $class["class_id"] ?>">
                                                <button type="submit" name="delete_class" class="btn btn-danger btn-sm">🗑️ حذف</button>
                                            </form>
                                        </div>

                                        <!-- Modal للتعديل -->
                                        <div class="modal fade" id="editModal<?= $class["class_id"] ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">تعديل القاعة</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form method="POST">
                                                        <div class="modal-body">
                                                            <input type="hidden" name="original_class_id" value="<?= $class["class_id"] ?>"> <!-- Original class_id -->
                                                            <div class="mb-3">
                                                                <label class="form-label">رقم القاعة الجديد</label>
                                                                <input type="number" name="new_class_id" class="form-control" value="<?= htmlspecialchars($class["class_id"]) ?>" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">اسم القاعة</label>
                                                                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($class["name"]) ?>" required>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                                            <button type="submit" name="edit_class" class="btn btn-primary">💾 حفظ التغييرات</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center">لا يوجد قاعات حالياً.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="attatchments/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conn->close();
include "includes/footer.php";
?>

