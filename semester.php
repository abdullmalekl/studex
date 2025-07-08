<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit();
}

include "includes/db_connection.php";

$message = "";

// إضافة فصل دراسي جديد
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_semester"])) {
    $name = $_POST["name"];
    $start_date = $_POST["start_date"];
    $end_date = $_POST["end_date"];
    $term_id = $_POST["term_id"];

    $stmt = $conn->prepare("INSERT INTO Semesters (name, start_date, end_date, terms) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $name, $start_date, $end_date, $term_id);

    if ($stmt->execute()) {
        $message = "<div class=\'alert alert-success\
angle✅ تم إضافة الفصل الدراسي بنجاح!</div>";
    } else {
        $message = "<div class=\'alert alert-danger\
angle❌ حدث خطأ أثناء إضافة الفصل الدراسي: " . $stmt->error . "</div>";
    }
    $stmt->close();
}

// تعديل فصل دراسي
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["edit_semester"])) {
    $semester_id = $_POST["semester_id"];
    $name = $_POST["name"];
    $start_date = $_POST["start_date"];
    $end_date = $_POST["end_date"];
    $term_id = $_POST["term_id"];

    $stmt = $conn->prepare("UPDATE Semesters SET name = ?, start_date = ?, end_date = ?, terms = ? WHERE semester_id = ?");
    $stmt->bind_param("sssii", $name, $start_date, $end_date, $term_id, $semester_id);

    if ($stmt->execute()) {
        $message = "<div class=\'alert alert-success\
angle✅ تم تعديل الفصل الدراسي بنجاح!</div>";
    } else {
        $message = "<div class=\'alert alert-danger\
angle❌ حدث خطأ أثناء تعديل الفصل الدراسي: " . $stmt->error . "</div>";
    }
    $stmt->close();
}

// حذف فصل دراسي
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_semester"])) {
    $semester_id = $_POST["semester_id"];

    $stmt = $conn->prepare("DELETE FROM Semesters WHERE semester_id = ?");
    $stmt->bind_param("i", $semester_id);

    if ($stmt->execute()) {
        $message = "<div class=\'alert alert-success\
angle✅ تم حذف الفصل الدراسي بنجاح!</div>";
    } else {
        $message = "<div class=\'alert alert-danger\
angle❌ حدث خطأ أثناء حذف الفصل الدراسي: " . $stmt->error . "</div>";
    }
    $stmt->close();
}

// جلب جميع الفصول الدراسية
$semesters_query = $conn->query("SELECT s.*, t.name as term_name FROM Semesters s LEFT JOIN terms t ON s.terms = t.term_id ORDER BY s.start_date DESC");

// جلب الفترات الدراسية لملء القائمة المنسدلة
$terms = $conn->query("SELECT term_id, name FROM terms ORDER BY name");

?>
<?php include 'admin_navbar.php'; ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة الفصول الدراسية</title>
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
            <div class="nav-item  active">
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
        
        <!-- إضافة فصل دراسي جديد -->
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">➕ إضافة فصل دراسي جديد</h4>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">اسم الفصل الدراسي</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">تاريخ البدء</label>
                            <input type="date" name="start_date" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">تاريخ الانتهاء</label>
                            <input type="date" name="end_date" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">الفترة الدراسية</label>
                            <select name="term_id" class="form-control" required>
                                <option value="">اختر الفترة الدراسية</option>
                                <?php while($term = $terms->fetch_assoc()): ?>
                                    <option value="<?= $term['term_id'] ?>"><?= htmlspecialchars($term['name']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    <div class="text-center mt-3">
                        <button type="submit" name="add_semester" class="btn btn-primary">➕ إضافة الفصل</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- عرض الفصول الدراسية الموجودة -->
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">📅 الفصول الدراسية الموجودة</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>اسم الفصل</th>
                                <th>تاريخ البدء</th>
                                <th>تاريخ الانتهاء</th>
                                <th>الفترة الدراسية</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($semester = $semesters_query->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($semester['semester_id']) ?></td>
                                <td><?= htmlspecialchars($semester['name']) ?></td>
                                <td><?= htmlspecialchars($semester['start_date']) ?></td>
                                <td><?= htmlspecialchars($semester['end_date']) ?></td>
                                <td><?= htmlspecialchars($semester['term_name'] ?? 'غير محدد') ?></td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <!-- تعديل -->
                                        <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editSemesterModal<?= $semester['semester_id'] ?>">
                                            ✏️ تعديل
                                        </button>
                                        
                                        <!-- حذف -->
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('هل أنت متأكد من حذف هذا الفصل الدراسي؟')">
                                            <input type="hidden" name="semester_id" value="<?= $semester['semester_id'] ?>">
                                            <button type="submit" name="delete_semester" class="btn btn-danger btn-sm">🗑️ حذف</button>
                                        </form>
                                    </div>

                                    <!-- Modal للتعديل -->
                                    <div class="modal fade" id="editSemesterModal<?= $semester['semester_id'] ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">تعديل الفصل الدراسي</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="semester_id" value="<?= $semester['semester_id'] ?>">
                                                        <div class="mb-3">
                                                            <label class="form-label">اسم الفصل الدراسي</label>
                                                            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($semester['name']) ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">تاريخ البدء</label>
                                                            <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($semester['start_date']) ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">تاريخ الانتهاء</label>
                                                            <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($semester['end_date']) ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">الفترة الدراسية</label>
                                                            <select name="term_id" class="form-control" required>
                                                                <option value="">اختر الفترة الدراسية</option>
                                                                <?php 
                                                                $terms->data_seek(0);
                                                                while($term = $terms->fetch_assoc()): 
                                                                ?>
                                                                    <option value="<?= $term['term_id'] ?>" <?= $semester['terms'] == $term['term_id'] ? 'selected' : '' ?>><?= htmlspecialchars($term['name']) ?></option>
                                                                <?php endwhile; ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                                        <button type="submit" name="edit_semester" class="btn btn-primary">💾 حفظ التغييرات</button>
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

