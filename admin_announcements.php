<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include 'includes/db_connection.php';

$message = '';

// تفعيل أو إلغاء تفعيل الإعلان
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action'])) {
    $announcement_id = $_POST['announcement_id'];
    $action = $_POST['action'];
    
    $new_status = ($action === 'activate') ? 'active' : 'inactive';
    
    $stmt = $conn->prepare("UPDATE Announcements SET status = ? WHERE announcement_id = ?");
    $stmt->bind_param("si", $new_status, $announcement_id);
    
    if ($stmt->execute()) {
        $message = "<div class='alert alert-success'>✅ تم تحديث حالة الإعلان بنجاح!</div>";
    } else {
        $message = "<div class='alert alert-danger'>❌ حدث خطأ أثناء تحديث حالة الإعلان.</div>";
    }
    $stmt->close();
}

// حذف الإعلان (حذف منطقي)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['delete'])) {
    $announcement_id = $_POST['announcement_id'];
    
    $stmt = $conn->prepare("UPDATE Announcements SET deleted = TRUE WHERE announcement_id = ?");
    $stmt->bind_param("i", $announcement_id);
    
    if ($stmt->execute()) {
        $message = "<div class='alert alert-success'>✅ تم حذف الإعلان بنجاح!</div>";
    } else {
        $message = "<div class='alert alert-danger'>❌ حدث خطأ أثناء حذف الإعلان.</div>";
    }
    $stmt->close();
}

// جلب جميع الإعلانات غير المحذوفة
$announcements = $conn->query("
    SELECT a.*, t.name as teacher_name 
    FROM Announcements a 
    LEFT JOIN Teachers t ON a.teacher_id = t.teacher_id 
    WHERE a.deleted = FALSE 
    ORDER BY a.created_at DESC
");
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة الإعلانات</title>
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
        .announcement-content {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
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
            <div class="nav-item active">
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
                <h4 class="mb-0">📢 إدارة الإعلانات</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>العنوان</th>
                                <th>المحتوى</th>
                                <th>الأستاذ</th>
                                <th>تاريخ الإنشاء</th>
                                <th>الحالة</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($announcement = $announcements->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($announcement['title']) ?></td>
                                <td>
                                    <div class="announcement-content" title="<?= htmlspecialchars($announcement['content']) ?>">
                                        <?= htmlspecialchars($announcement['content']) ?>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($announcement['teacher_name'] ?? 'غير محدد') ?></td>
                                <td><?= date('Y-m-d H:i', strtotime($announcement['created_at'])) ?></td>
                                <td>
                                    <span class="status-<?= $announcement['status'] ?>">
                                        <?= $announcement['status'] === 'active' ? 'مفعل' : 'غير مفعل' ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="announcement_id" value="<?= $announcement['announcement_id'] ?>">
                                            <?php if ($announcement['status'] === 'active'): ?>
                                                <button type="submit" name="action" value="deactivate" class="btn btn-warning btn-sm">إلغاء التفعيل</button>
                                            <?php else: ?>
                                                <button type="submit" name="action" value="activate" class="btn btn-success btn-sm">تفعيل</button>
                                            <?php endif; ?>
                                        </form>
                                        
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('هل أنت متأكد من حذف هذا الإعلان؟')">
                                            <input type="hidden" name="announcement_id" value="<?= $announcement['announcement_id'] ?>">
                                            <button type="submit" name="delete" value="1" class="btn btn-danger btn-sm">حذف</button>
                                        </form>
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

