<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include 'includes/db_connection.php';

$message = '';

// تعديل بيانات المستخدم
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['edit_user'])) {
    $user_id = $_POST['user_id'];
    $user_type = $_POST['user_type'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    
    if ($user_type === 'student') {
        $stmt = $conn->prepare("UPDATE Students SET name = ?, email = ?, phone = ?, address = ? WHERE student_id = ?");
        $stmt->bind_param("ssssi", $name, $email, $phone, $address, $user_id);
    } elseif ($user_type === 'teacher') {
        $specialization = $_POST['specialization'];
        $stmt = $conn->prepare("UPDATE Teachers SET name = ?, email = ?, phone = ?, address = ?, specialization = ? WHERE teacher_id = ?");
        $stmt->bind_param("sssssi", $name, $email, $phone, $address, $specialization, $user_id);
    }
    
    if (isset($stmt) && $stmt->execute()) {
        $message = "<div class='alert alert-success'>✅ تم تحديث بيانات المستخدم بنجاح!</div>";
    } else {
        $message = "<div class='alert alert-danger'>❌ حدث خطأ أثناء تحديث البيانات.</div>";
    }
    if (isset($stmt)) $stmt->close();
}

// حذف المستخدم
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];
    $user_type = $_POST['user_type'];
    
    if ($user_type === 'student') {
        $stmt = $conn->prepare("UPDATE Students SET deleted = TRUE WHERE student_id = ?");
    } elseif ($user_type === 'teacher') {
        $stmt = $conn->prepare("UPDATE Teachers SET deleted = TRUE WHERE teacher_id = ?");
    }
    
    if (isset($stmt)) {
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            $message = "<div class='alert alert-success'>✅ تم حذف المستخدم بنجاح!</div>";
        } else {
            $message = "<div class='alert alert-danger'>❌ حدث خطأ أثناء حذف المستخدم.</div>";
        }
        $stmt->close();
    }
}

// تفعيل أو إلغاء تفعيل المستخدم
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['toggle_status'])) {
    $user_id = $_POST['user_id'];
    $user_type = $_POST['user_type'];
    $new_status = $_POST['new_status'];
    
    if ($user_type === 'student') {
        $stmt = $conn->prepare("UPDATE Students SET status = ? WHERE student_id = ?");
    } elseif ($user_type === 'teacher') {
        $stmt = $conn->prepare("UPDATE Teachers SET status = ? WHERE teacher_id = ?");
    }
    
    if (isset($stmt)) {
        $stmt->bind_param("si", $new_status, $user_id);
        if ($stmt->execute()) {
            $message = "<div class='alert alert-success'>✅ تم تحديث حالة المستخدم بنجاح!</div>";
        } else {
            $message = "<div class='alert alert-danger'>❌ حدث خطأ أثناء تحديث الحالة.</div>";
        }
        $stmt->close();
    }
}

// جلب الطلاب غير المحذوفين
$students = $conn->query("SELECT student_id, name, email, phone, address, status FROM Students WHERE deleted = FALSE ORDER BY student_id");

// جلب المعلمين غير المحذوفين
$teachers = $conn->query("SELECT teacher_id, name, email, phone, address, specialization, status FROM Teachers WHERE deleted = FALSE ORDER BY teacher_id");
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة المستخدمين</title>
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
            <div class="nav-item">
                <a href="admin_groups.php">👥 إدارة المجموعات</a>
            </div>
            <div class="nav-item">
                <a href="subjects.php">📖 المواد</a>
            </div>
            <div class="nav-item active">
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
        
        <!-- إدارة الطلاب -->
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">👨‍🎓 إدارة الطلاب</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>رقم القيد</th>
                                <th>الاسم</th>
                                <th>البريد الإلكتروني</th>
                                <th>الهاتف</th>
                                <th>الحالة</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($student = $students->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($student['student_id']) ?></td>
                                <td><?= htmlspecialchars($student['name']) ?></td>
                                <td><?= htmlspecialchars($student['email']) ?></td>
                                <td><?= htmlspecialchars($student['phone']) ?></td>
                                <td>
                                    <span class="status-<?= $student['status'] ?>">
                                        <?= $student['status'] === 'active' ? 'مفعل' : 'غير مفعل' ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <!-- تعديل -->
                                        <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editStudentModal<?= $student['student_id'] ?>">
                                            ✏️ تعديل
                                        </button>
                                        
                                        <!-- تفعيل/إلغاء تفعيل -->
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="user_id" value="<?= $student['student_id'] ?>">
                                            <input type="hidden" name="user_type" value="student">
                                            <input type="hidden" name="new_status" value="<?= $student['status'] === 'active' ? 'inactive' : 'active' ?>">
                                            <button type="submit" name="toggle_status" class="btn btn-<?= $student['status'] === 'active' ? 'warning' : 'success' ?> btn-sm">
                                                <?= $student['status'] === 'active' ? '⏸️ إلغاء التفعيل' : '▶️ تفعيل' ?>
                                            </button>
                                        </form>
                                        
                                        <!-- حذف -->
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('هل أنت متأكد من حذف هذا الطالب؟')">
                                            <input type="hidden" name="user_id" value="<?= $student['student_id'] ?>">
                                            <input type="hidden" name="user_type" value="student">
                                            <button type="submit" name="delete_user" class="btn btn-danger btn-sm">🗑️ حذف</button>
                                        </form>
                                    </div>

                                    <!-- Modal للتعديل -->
                                    <div class="modal fade" id="editStudentModal<?= $student['student_id'] ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">تعديل بيانات الطالب</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="user_id" value="<?= $student['student_id'] ?>">
                                                        <input type="hidden" name="user_type" value="student">
                                                        <div class="mb-3">
                                                            <label class="form-label">الاسم</label>
                                                            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($student['name']) ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">البريد الإلكتروني</label>
                                                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($student['email']) ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">الهاتف</label>
                                                            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($student['phone']) ?>">
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">العنوان</label>
                                                            <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($student['address']) ?>">
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                                        <button type="submit" name="edit_user" class="btn btn-primary">💾 حفظ التغييرات</button>
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

        <!-- إدارة المعلمين -->
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">👨‍🏫 إدارة المعلمين</h4>
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
                                <td>
                                    <span class="status-<?= $teacher['status'] ?>">
                                        <?= $teacher['status'] === 'active' ? 'مفعل' : 'غير مفعل' ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <!-- تعديل -->
                                        <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editTeacherModal<?= $teacher['teacher_id'] ?>">
                                            ✏️ تعديل
                                        </button>
                                        
                                        <!-- تفعيل/إلغاء تفعيل -->
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="user_id" value="<?= $teacher['teacher_id'] ?>">
                                            <input type="hidden" name="user_type" value="teacher">
                                            <input type="hidden" name="new_status" value="<?= $teacher['status'] === 'active' ? 'inactive' : 'active' ?>">
                                            <button type="submit" name="toggle_status" class="btn btn-<?= $teacher['status'] === 'active' ? 'warning' : 'success' ?> btn-sm">
                                                <?= $teacher['status'] === 'active' ? '⏸️ إلغاء التفعيل' : '▶️ تفعيل' ?>
                                            </button>
                                        </form>
                                        
                                        <!-- حذف -->
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('هل أنت متأكد من حذف هذا المعلم؟')">
                                            <input type="hidden" name="user_id" value="<?= $teacher['teacher_id'] ?>">
                                            <input type="hidden" name="user_type" value="teacher">
                                            <button type="submit" name="delete_user" class="btn btn-danger btn-sm">🗑️ حذف</button>
                                        </form>
                                    </div>

                                    <!-- Modal للتعديل -->
                                    <div class="modal fade" id="editTeacherModal<?= $teacher['teacher_id'] ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">تعديل بيانات المعلم</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="user_id" value="<?= $teacher['teacher_id'] ?>">
                                                        <input type="hidden" name="user_type" value="teacher">
                                                        <div class="mb-3">
                                                            <label class="form-label">الاسم</label>
                                                            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($teacher['name']) ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">البريد الإلكتروني</label>
                                                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($teacher['email']) ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">الهاتف</label>
                                                            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($teacher['phone']) ?>">
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">العنوان</label>
                                                            <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($teacher['address']) ?>">
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">التخصص</label>
                                                            <input type="text" name="specialization" class="form-control" value="<?= htmlspecialchars($teacher["specialization"] ?? "") ?>">
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                                        <button type="submit" name="edit_user" class="btn btn-primary">💾 حفظ التغييرات</button>
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

        <!-- إضافة مسؤول جديد -->
        <div class="card mt-4">
            <div class="card-header">
                <h4 class="mb-0">➕ إضافة مسؤول جديد</h4>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">الاسم</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">البريد الإلكتروني</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">كلمة المرور</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الهاتف</label>
                        <input type="text" name="phone" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">العنوان</label>
                        <input type="text" name="address" class="form-control">
                    </div>
                    <button type="submit" name="add_admin" class="btn btn-success">➕ إضافة مسؤول</button>
                </form>
            </div>
        </div>

        <!-- إعادة تعيين كلمة المرور -->
        <div class="card mt-4">
            <div class="card-header">
                <h4 class="mb-0">🔑 إعادة تعيين كلمة المرور</h4>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">نوع المستخدم</label>
                        <select name="user_type" class="form-control" required>
                            <option value="">اختر نوع المستخدم</option>
                            <option value="student">طالب</option>
                            <option value="teacher">معلم</option>
                            <option value="user">مسؤول</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">معرف المستخدم (ID)</label>
                        <input type="number" name="user_id" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">كلمة المرور الجديدة</label>
                        <input type="password" name="new_password" class="form-control" required>
                    </div>
                    <button type="submit" name="reset_password" class="btn btn-info">🔑 إعادة تعيين</button>
                </form>
            </div>
        </div>
    </div>

    <script src="attatchments/bootstrap.bundle.min.js"></script>
</body>
</html>

