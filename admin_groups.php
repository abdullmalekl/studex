<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include 'includes/db_connection.php';

$message = '';

// إضافة مجموعة جديدة
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add_group'])) {
    $group_id = $_POST['group_id'];
    $name = $_POST['name'];
    $maximum = $_POST['maximum'];
    
    $stmt = $conn->prepare("INSERT INTO Groups (group_id, name, maximum) VALUES (?, ?, ?)");
    $stmt->bind_param("isi", $group_id, $name, $maximum);
    
    if ($stmt->execute()) {
        $message = "<div class='alert alert-success'>✅ تم إضافة المجموعة بنجاح!</div>";
    } else {
        $message = "<div class='alert alert-danger'>❌ حدث خطأ أثناء إضافة المجموعة. تأكد من أن رقم المجموعة غير مستخدم.</div>";
    }
    $stmt->close();
}

// تعديل مجموعة
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['edit_group'])) {
    $group_id = $_POST['group_id'];
    $name = $_POST['name'];
    $maximum = $_POST['maximum'];
    
    $stmt = $conn->prepare("UPDATE Groups SET name = ?, maximum = ? WHERE group_id = ?");
    $stmt->bind_param("sii", $name, $maximum, $group_id);
    
    if ($stmt->execute()) {
        $message = "<div class='alert alert-success'>✅ تم تعديل المجموعة بنجاح!</div>";
    } else {
        $message = "<div class='alert alert-danger'>❌ حدث خطأ أثناء تعديل المجموعة.</div>";
    }
    $stmt->close();
}

// حذف مجموعة
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['delete_group'])) {
    $group_id = $_POST['group_id'];
    
    $stmt = $conn->prepare("DELETE FROM Groups WHERE group_id = ?");
    $stmt->bind_param("i", $group_id);
    
    if ($stmt->execute()) {
        $message = "<div class='alert alert-success'>✅ تم حذف المجموعة بنجاح!</div>";
    } else {
        $message = "<div class='alert alert-danger'>❌ حدث خطأ أثناء حذف المجموعة.</div>";
    }
    $stmt->close();
}

// جلب جميع المجموعات
$groups = $conn->query("SELECT * FROM Groups ORDER BY group_id");
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة المجموعات</title>
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
            <div class="nav-item">
                <a href="admin_classes.php">🏫 إدارة القاعات</a>
            </div>
            <div class="nav-item">
                <a href="semester.php">📅 الفصول الدراسية</a>
            </div>
            <div class="nav-item active">
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
        
        <!-- إضافة مجموعة جديدة -->
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">➕ إضافة مجموعة جديدة</h4>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="row">
                        <div class="col-md-4">
                            <label class="form-label">رقم المجموعة</label>
                            <input type="number" name="group_id" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">اسم المجموعة</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">العدد الأقصى</label>
                            <input type="number" name="maximum" class="form-control" required>
                        </div>
                    </div>
                    <div class="text-center mt-3">
                        <button type="submit" name="add_group" class="btn btn-primary">➕ إضافة المجموعة</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- عرض المجموعات -->
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">👥 المجموعات الموجودة</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>رقم المجموعة</th>
                                <th>اسم المجموعة</th>
                                <th>العدد الأقصى</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>                            <?php if ($groups->num_rows > 0): ?>
                            <?php while($group = $groups->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($group["group_id"]) ?></td>
                                <td><?= htmlspecialchars($group["name"]) ?></td>
                                <td><?= htmlspecialchars($group["maximum"]) ?></td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <!-- تعديل -->
                                        <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?= $group["group_id"] ?>">
                                            ✏️ تعديل
                                        </button>
                                        
                                        <!-- حذف -->
                                        <form method="POST" style="display: inline;" onsubmit="return confirm(\'هل أنت متأكد من حذف هذه المجموعة؟\')">
                                            <input type="hidden" name="group_id" value="<?= $group["group_id"] ?>">
                                            <button type="submit" name="delete_group" class="btn btn-danger btn-sm">🗑️ حذف</button>
                                        </form>
                                    </div>

                                    <!-- Modal للتعديل -->
                                    <div class="modal fade" id="editModal<?= $group["group_id"] ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">تعديل المجموعة</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="group_id" value="<?= $group["group_id"] ?>">
                                                        <div class="mb-3">
                                                            <label class="form-label">اسم المجموعة</label>
                                                            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($group["name"]) ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">العدد الأقصى</label>
                                                            <input type="number" name="maximum" class="form-control" value="<?= htmlspecialchars($group["maximum"]) ?>" required>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                                        <button type="submit" name="edit_group" class="btn btn-primary">💾 حفظ التغييرات</button>
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
                                <td colspan="4" class="text-center">لا يوجد مجموعات حالياً.</td>
                            </tr>
                            <?php endif; ?>                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="attatchments/bootstrap.bundle.min.js"></script>
</body>
</html>

