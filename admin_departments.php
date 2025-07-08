<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit();
}

include "includes/db_connection.php";

$message = "";

// إضافة قسم جديد
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_department"])) {
    $name = $_POST["name"];
    
    $stmt = $conn->prepare("INSERT INTO departments (name) VALUES (?)");
    $stmt->bind_param("s", $name);
    
    if ($stmt->execute()) {
        $message = "<div class=\"alert alert-success\">✅ تم إضافة القسم بنجاح!</div>";
    } else {
        $message = "<div class=\"alert alert-danger\">❌ حدث خطأ أثناء إضافة القسم.</div>";
    }
    $stmt->close();
}

// تعديل قسم
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["edit_department"])) {
    $department_id = $_POST["department_id"];
    $name = $_POST["name"];
    
    $stmt = $conn->prepare("UPDATE departments SET name = ? WHERE department_id = ?");
    $stmt->bind_param("si", $name, $department_id);
    
    if ($stmt->execute()) {
        $message = "<div class=\"alert alert-success\">✅ تم تعديل القسم بنجاح!</div>";
    } else {
        $message = "<div class=\"alert alert-danger\">❌ حدث خطأ أثناء تعديل القسم.</div>";
    }
    $stmt->close();
}

// حذف قسم
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_department"])) {
    $department_id = $_POST["department_id"];
    
    // التحقق قبل الحذف
$check_stmt = $conn->prepare("SELECT COUNT(*) FROM subjects WHERE department_id = ?");
$check_stmt->bind_param("i", $department_id);
$check_stmt->execute();
$check_stmt->bind_result($count);
$check_stmt->fetch();
$check_stmt->close();

if ($count > 0) {
    $message = "<div class=\"alert alert-danger\">❌ لا يمكن حذف القسم لأنه مرتبط بمواد في جدول المواد.</div>";
} else {
    // نفذ الحذف
    $stmt = $conn->prepare("DELETE FROM departments WHERE department_id = ?");
    $stmt->bind_param("i", $department_id);
    if ($stmt->execute()) {
        $message = "<div class=\"alert alert-success\">✅ تم حذف القسم بنجاح!</div>";
    } else {
        $message = "<div class=\"alert alert-danger\">❌ حدث خطأ أثناء حذف القسم: " . $conn->error . "</div>";
    }
    $stmt->close();
}

}

// جلب جميع الأقسام
$departments = $conn->query("SELECT * FROM departments ORDER BY department_id");
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة الأقسام</title>
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
            <div class="nav-item active">
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
        <?= $message ?>
        
        <!-- إضافة قسم جديد -->
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">➕ إضافة قسم جديد</h4>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="row">
                        <div class="col-md-12">
                            <label class="form-label">اسم القسم</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                    </div>
                    <div class="text-center mt-3">
                        <button type="submit" name="add_department" class="btn btn-primary">➕ إضافة القسم</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- عرض الأقسام -->
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">🏢 الأقسام الموجودة</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>الرقم</th>
                                <th>اسم القسم</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($departments->num_rows > 0): ?>
                                <?php while($department = $departments->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($department["department_id"]) ?></td>
                                    <td><?= htmlspecialchars($department["name"]) ?></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <!-- تعديل -->
                                            <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?= $department["department_id"] ?>">
                                                ✏️ تعديل
                                            </button>
                                            
                                            <!-- حذف -->
                                            <form method="POST" style="display: inline;" onsubmit="return confirm(\'هل أنت متأكد من حذف هذا القسم؟\')">
                                                <input type="hidden" name="department_id" value="<?= $department["department_id"] ?>">
                                                <button type="submit" name="delete_department" class="btn btn-danger btn-sm">🗑️ حذف</button>
                                            </form>
                                        </div>

                                        <!-- Modal للتعديل -->
                                        <div class="modal fade" id="editModal<?= $department["department_id"] ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">تعديل القسم</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form method="POST">
                                                        <div class="modal-body">
                                                            <input type="hidden" name="department_id" value="<?= $department["department_id"] ?>">
                                                            <div class="mb-3">
                                                                <label class="form-label">اسم القسم</label>
                                                                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($department["name"]) ?>" required>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                                            <button type="submit" name="edit_department" class="btn btn-primary">💾 حفظ التغييرات</button>
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
                                    <td colspan="3" class="text-center">لا يوجد أقسام حالياً.</td>
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

