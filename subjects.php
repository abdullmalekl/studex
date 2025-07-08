<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit();
}

include "includes/db_connection.php";

$message = "";

// إضافة مادة جديدة
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_subject"])) {
    $name = $_POST["name"];
    $credit_hours = $_POST["credit_hours"];
    $num_units = $_POST["num_units"];
    $status = isset($_POST["status"]) ? 1 : 0; // Boolean
    $sem_id = $_POST["sem_id"];
    $deprt_id = $_POST["deprt_id"];

    $stmt = $conn->prepare("INSERT INTO Subjects (name, credit_hours, units_count, status, sem_id, department_id) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("siiiii", $name, $credit_hours, $num_units, $status, $sem_id, $deprt_id);

    if ($stmt->execute()) {
        $message = "<div class=\'alert alert-success\
angle✅ تم إضافة المادة بنجاح!</div>";
    } else {
        $message = "<div class=\'alert alert-danger\
angle❌ حدث خطأ أثناء إضافة المادة: " . $stmt->error . "</div>";
    }
    $stmt->close();
}

// تعديل مادة
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["edit_subject"])) {
    $subject_id = $_POST["subject_id"];
    $name = $_POST["name"];
    $credit_hours = $_POST["credit_hours"];
    $num_units = $_POST["num_units"];
    $status = isset($_POST["status"]) ? 1 : 0; // Boolean
    $sem_id = $_POST["sem_id"];
    $deprt_id = $_POST["deprt_id"];

    $stmt = $conn->prepare("UPDATE Subjects SET name = ?, credit_hours = ?, units_count = ?, status = ?, sem_id = ?, department_id = ? WHERE subject_id = ?");
    $stmt->bind_param("siiiiii", $name, $credit_hours, $num_units, $status, $sem_id, $deprt_id, $subject_id);

    if ($stmt->execute()) {
        $message = "<div class=\'alert alert-success\
angle✅ تم تعديل المادة بنجاح!</div>";
    } else {
        $message = "<div class=\'alert alert-danger\
angle❌ حدث خطأ أثناء تعديل المادة: " . $stmt->error . "</div>";
    }
    $stmt->close();
}

// حذف مادة
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_subject"])) {
    $subject_id = $_POST["subject_id"];

    $stmt = $conn->prepare("DELETE FROM Subjects WHERE subject_id = ?");
    $stmt->bind_param("i", $subject_id);

    if ($stmt->execute()) {
        $message = "<div class=\'alert alert-success\
angle✅ تم حذف المادة بنجاح!</div>";
    } else {
        $message = "<div class=\'alert alert-danger\
angle❌ حدث خطأ أثناء حذف المادة: " . $stmt->error . "</div>";
    }
    $stmt->close();
}

// جلب جميع المواد
$subjects_query = $conn->query("SELECT s.*, sem.name as semester_name, dep.name as department_name FROM Subjects s LEFT JOIN Semesters sem ON s.sem_id = sem.semester_id LEFT JOIN Departments dep ON s.department_id = dep.department_id ORDER BY s.name");

// جلب الفصول الدراسية والأقسام لملء القوائم المنسدلة
$semesters = $conn->query("SELECT semester_id, name FROM Semesters ORDER BY name");
$departments = $conn->query("SELECT department_id, name FROM Departments ORDER BY name");

?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة المواد الدراسية</title>
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
            <div class="nav-item">
                <a href="admin_groups.php">👥 إدارة المجموعات</a>
            </div>
            <div class="nav-item active">
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
        
        <!-- إضافة مادة جديدة -->
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">➕ إضافة مادة دراسية جديدة</h4>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">اسم المادة</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">عدد الساعات المعتمدة</label>
                            <input type="number" name="credit_hours" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">عدد الوحدات</label>
                            <input type="number" name="num_units" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">الفصل الدراسي</label>
                            <select name="sem_id" class="form-control" required>
                                <option value="">اختر الفصل الدراسي</option>
                                <?php while($sem = $semesters->fetch_assoc()): ?>
                                    <option value="<?= $sem['semester_id'] ?>"><?= htmlspecialchars($sem['name']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">القسم</label>
                            <select name="deprt_id" class="form-control" required>
                                <option value="">اختر القسم</option>
                                <?php while($dep = $departments->fetch_assoc()): ?>
                                    <option value="<?= $dep['department_id'] ?>"><?= htmlspecialchars($dep['name']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3 form-check">
                            <input type="checkbox" name="status" class="form-check-input" id="subjectStatus">
                            <label class="form-check-label" for="subjectStatus">مفعلة</label>
                        </div>
                    </div>
                    <div class="text-center mt-3">
                        <button type="submit" name="add_subject" class="btn btn-primary">➕ إضافة المادة</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- عرض المواد الموجودة -->
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">📖 المواد الدراسية الموجودة</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>اسم المادة</th>
                                <th>ساعات معتمدة</th>
                                <th>وحدات</th>
                                <th>الحالة</th>
                                <th>الفصل الدراسي</th>
                                <th>القسم</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($subjects_query->num_rows > 0): ?>
                                <?php while($subject = $subjects_query->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($subject['subject_id']) ?></td>
                                    <td><?= htmlspecialchars($subject['name']) ?></td>
                                    <td><?= htmlspecialchars($subject['credit_hours']) ?></td>
                                    <td><?= htmlspecialchars($subject['units_count']) ?></td>
                                    <td><?= $subject['status'] ? 'مفعلة' : 'غير مفعلة' ?></td>
                                    <td><?= htmlspecialchars($subject['semester_name'] ?? 'غير محدد') ?></td>
                                    <td><?= htmlspecialchars($subject['department_name'] ?? 'غير محدد') ?></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <!-- تعديل -->
                                            <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editSubjectModal<?= $subject['subject_id'] ?>">
                                                ✏️ تعديل
                                            </button>
                                            
                                            <!-- حذف -->
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('هل أنت متأكد من حذف هذه المادة؟')">
                                                <input type="hidden" name="subject_id" value="<?= $subject['subject_id'] ?>">
                                                <button type="submit" name="delete_subject" class="btn btn-danger btn-sm">🗑️ حذف</button>
                                            </form>
                                        </div>

                                        <!-- Modal للتعديل -->
                                        <div class="modal fade" id="editSubjectModal<?= $subject['subject_id'] ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">تعديل المادة الدراسية</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form method="POST">
                                                        <div class="modal-body">
                                                            <input type="hidden" name="subject_id" value="<?= $subject['subject_id'] ?>">
                                                            <div class="mb-3">
                                                                <label class="form-label">اسم المادة</label>
                                                                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($subject['name']) ?>" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">عدد الساعات المعتمدة</label>
                                                                <input type="number" name="credit_hours" class="form-control" value="<?= htmlspecialchars($subject['credit_hours']) ?>" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">عدد الوحدات</label>
                                                                <input type="number" name="num_units" class="form-control" value="<?= htmlspecialchars($subject['units_count']) ?>" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">الفصل الدراسي</label>
                                                                <select name="sem_id" class="form-control" required>
                                                                    <option value="">اختر الفصل الدراسي</option>
                                                                    <?php 
                                                                    $semesters->data_seek(0);
                                                                    while($sem = $semesters->fetch_assoc()): 
                                                                    ?>
                                                                        <option value="<?= $sem['semester_id'] ?>" <?= $subject['sem_id'] == $sem['semester_id'] ? 'selected' : '' ?>><?= htmlspecialchars($sem['name']) ?></option>
                                                                    <?php endwhile; ?>
                                                                </select>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">القسم</label>
                                                                <select name="deprt_id" class="form-control" required>
                                                                    <option value="">اختر القسم</option>
                                                                    <?php 
                                                                    $departments->data_seek(0);
                                                                    while($dep = $departments->fetch_assoc()): 
                                                                    ?>
                                                                        <option value="<?= $dep['department_id'] ?>" <?= $subject['department_id'] == $dep['department_id'] ? 'selected' : '' ?>><?= htmlspecialchars($dep['name']) ?></option>
                                                                    <?php endwhile; ?>
                                                                </select>
                                                            </div>
                                                            <div class="mb-3 form-check">
                                                                <input type="checkbox" name="status" class="form-check-input" id="editSubjectStatus<?= $subject['subject_id'] ?>" <?= $subject['status'] ? 'checked' : '' ?>>
                                                                <label class="form-check-label" for="editSubjectStatus<?= $subject['subject_id'] ?>">مفعلة</label>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                                            <button type="submit" name="edit_subject" class="btn btn-primary">💾 حفظ التغييرات</button>
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
                                    <td colspan="8" class="text-center">لا يوجد مواد دراسية حالياً.</td>
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

