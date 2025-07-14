<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include 'includes/db_connection.php';

$message = '';

// تحديث تخصص المعلم
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_specialization'])) {
    $teacher_id = $_POST['teacher_id'];
    $new_specialization = $_POST['new_specialization'];
    $new_department = $_POST['new_department'];
    
    $stmt = $conn->prepare("UPDATE Teachers SET specialization = ?, department_id = ? WHERE teacher_id = ?");
    $stmt->bind_param("sii", $new_specialization, $new_department, $teacher_id);
    
    if ($stmt->execute()) {
        $message = "<div class='alert alert-success'>✅ تم تحديث بيانات المعلم بنجاح!</div>";
    } else {
        $message = "<div class='alert alert-danger'>❌ حدث خطأ أثناء تحديث البيانات.</div>";
    }
    $stmt->close();
}

// حذف المعلم (حذف منطقي)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['delete_teacher'])) {
    $teacher_id = $_POST['teacher_id'];
    
    $stmt = $conn->prepare("UPDATE Teachers SET deleted = TRUE WHERE teacher_id = ?");
    $stmt->bind_param("i", $teacher_id);
    
    if ($stmt->execute()) {
        $message = "<div class='alert alert-success'>✅ تم حذف المعلم من النظام بنجاح!</div>";
    } else {
        $message = "<div class='alert alert-danger'>❌ حدث خطأ أثناء حذف المعلم.</div>";
    }
    $stmt->close();
}

// تفعيل/إلغاء تفعيل المعلم
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['toggle_status'])) {
    $teacher_id = $_POST['teacher_id'];
    $new_status = $_POST['new_status'];
    
    $stmt = $conn->prepare("UPDATE Teachers SET status = ? WHERE teacher_id = ?");
    $stmt->bind_param("si", $new_status, $teacher_id);
    
    if ($stmt->execute()) {
        $message = "<div class='alert alert-success'>✅ تم تحديث حالة المعلم بنجاح!</div>";
    } else {
        $message = "<div class='alert alert-danger'>❌ حدث خطأ أثناء تحديث الحالة.</div>";
    }
    $stmt->close();
}

// جلب جميع المعلمين غير المحذوفين
$teachers = $conn->query("
    SELECT t.*, d.name as department_name 
    FROM Teachers t 
    LEFT JOIN Departments d ON t.department_id = d.department_id 
    WHERE t.deleted = FALSE 
    ORDER BY t.name
");

// جلب جميع الأقسام
$departments = $conn->query("SELECT department_id, name FROM Departments ORDER BY name");
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة أعضاء هيئة التدريس</title>
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
        }
        .card-header {
            background-color: #1b325f;
            color: white;
            border-radius: 10px 10px 0 0 !important;
        }
        .status-active {
            color: #28a745;
            font-weight: bold;
        }
        .status-inactive {
            color: #dc3545;
            font-weight: bold;
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
                <a href="index.php">🏠 الرئيسية</a>
            </div>
            <div class="nav-item">
                <a href="dashboard.php">📚 قاعات دراسية</a>
            </div>
            <div class="nav-item">
                <a href="admin_students_management.php">👨‍🎓 الطلاب</a>
            </div>
            <div class="nav-item">
                <a href="semester.php">📅 الفصول الدراسية</a>
            </div>
            <div class="nav-item active">
                <a href="admin_teachers_management.php">👨‍🏫 أعضاء هيئة التدريس</a>
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
        
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">👨‍🏫 إدارة أعضاء هيئة التدريس</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>رقم المعلم</th>
                                <th>الاسم</th>
                                <th>البريد الإلكتروني</th>
                                <th>الهاتف</th>
                                <th>التخصص</th>
                                <th>القسم</th>
                                <th>الحالة</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($teacher = $teachers->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($teacher['teacher_id']) ?></td>
                                <td><?= htmlspecialchars($teacher['name']) ?></td>
                                <td><?= htmlspecialchars($teacher['email']) ?></td>
                                <td><?= htmlspecialchars($teacher['phone']) ?></td>
                                <td><?= htmlspecialchars($teacher['specialization'] ?? 'غير محدد') ?></td>
                                <td><?= htmlspecialchars($teacher['department_name'] ?? 'غير محدد') ?></td>
                                <td>
                                    <span class="status-<?= $teacher['status'] ?>">
                                        <?= $teacher['status'] === 'active' ? 'مفعل' : 'غير مفعل' ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <!-- تغيير التخصص -->
                                        <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#changeSpecModal<?= $teacher['teacher_id'] ?>">
                                            تعديل البيانات
                                        </button>
                                        
                                        <!-- تفعيل/إلغاء تفعيل -->
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="teacher_id" value="<?= $teacher['teacher_id'] ?>">
                                            <input type="hidden" name="new_status" value="<?= $teacher['status'] === 'active' ? 'inactive' : 'active' ?>">
                                            <button type="submit" name="toggle_status" class="btn btn-<?= $teacher['status'] === 'active' ? 'warning' : 'success' ?> btn-sm">
                                                <?= $teacher['status'] === 'active' ? 'إلغاء التفعيل' : 'تفعيل' ?>
                                            </button>
                                        </form>
                                        
                                        <!-- حذف -->
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('هل أنت متأكد من حذف هذا المعلم؟')">
                                            <input type="hidden" name="teacher_id" value="<?= $teacher['teacher_id'] ?>">
                                            <button type="submit" name="delete_teacher" class="btn btn-danger btn-sm">حذف</button>
                                        </form>
                                    </div>

                                    <!-- Modal لتغيير التخصص -->
                                    <div class="modal fade" id="changeSpecModal<?= $teacher['teacher_id'] ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">تعديل بيانات المعلم</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="teacher_id" value="<?= $teacher['teacher_id'] ?>">
                                                        <div class="mb-3">
                                                            <label class="form-label">المعلم: <?= htmlspecialchars($teacher['name']) ?></label>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">التخصص</label>
                                                            <input type="text" name="new_specialization" class="form-control" 
                                                                   value="<?= htmlspecialchars($teacher['specialization'] ?? '') ?>" 
                                                                   placeholder="أدخل التخصص">
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">القسم</label>
                                                            <select name="new_department" class="form-control">
                                                                <option value="">اختر القسم</option>
                                                                <?php 
                                                                $departments->data_seek(0);
                                                                while($dept = $departments->fetch_assoc()): 
                                                                ?>
                                                                    <option value="<?= $dept['department_id'] ?>" 
                                                                        <?= $teacher['department_id'] == $dept['department_id'] ? 'selected' : '' ?>>
                                                                        <?= htmlspecialchars($dept['name']) ?>
                                                                    </option>
                                                                <?php endwhile; ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                                        <button type="submit" name="update_specialization" class="btn btn-primary">حفظ التغييرات</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="attatchments/bootstrap.bundle.min.js"></script>
</body>
</html>

