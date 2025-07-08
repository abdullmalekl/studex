<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "teacher") {
    header("Location: login.php");
    exit();
}

include "includes/db_connection.php";

$teacher_id = $_SESSION["user_id"];
$teacher_name = $_SESSION["name"];
$message = "";

// معالجة النماذج
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // إضافة إعلان جديد
    if (isset($_POST["add_announcement"])) {
        $title = $_POST["title"];
        $content = $_POST["content"];
        
        $stmt = $conn->prepare("INSERT INTO Announcements (title, content, created_at, teacher_id, status) VALUES (?, ?, NOW(), ?, 'inactive')");
        $stmt->bind_param("ssi", $title, $content, $teacher_id);
        
        if ($stmt->execute()) {
            $message = "<div class=\"alert alert-success\">✅ تم إرسال الإعلان بنجاح! في انتظار موافقة المسؤول.</div>";
        } else {
            $message = "<div class=\"alert alert-danger\">❌ حدث خطأ أثناء إرسال الإعلان.</div>";
        }
        $stmt->close();
    }
    
    // تعديل إعلان
    if (isset($_POST["edit_announcement"])) {
        $announcement_id = $_POST["announcement_id"];
        $title = $_POST["title"];
        $content = $_POST["content"];
        
        // التحقق من أن الإعلان يخص المعلم الحالي وأنه في حالة pending
        $check_stmt = $conn->prepare("SELECT status FROM Announcements WHERE announcement_id = ? AND teacher_id = ?");
        $check_stmt->bind_param("ii", $announcement_id, $teacher_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $announcement = $check_result->fetch_assoc();
            if ($announcement["status"] === "active") {
                $stmt = $conn->prepare("UPDATE Announcements SET title = ?, content = ? WHERE announcement_id = ? AND teacher_id = ?");
                $stmt->bind_param("ssii", $title, $content, $announcement_id, $teacher_id);
                
                if ($stmt->execute()) {
                    $message = "<div class=\"alert alert-success\">✅ تم تعديل الإعلان بنجاح!</div>";
                } else {
                    $message = "<div class=\"alert alert-danger\">❌ حدث خطأ أثناء تعديل الإعلان.</div>";
                }
                $stmt->close();
            } else {
                $message = "<div class=\"alert alert-danger\">❌ لا يمكن تعديل إعلان تمت الموافقة عليه أو رفضه.</div>";
            }
        } else {
            $message = "<div class=\"alert alert-danger\">❌ الإعلان غير موجود أو لا تملك صلاحية تعديله.</div>";
        }
        $check_stmt->close();
    }
    
    // حذف إعلان
    if (isset($_POST["delete_announcement"])) {
        $announcement_id = $_POST["announcement_id"];
        
        // التحقق من أن الإعلان يخص المعلم الحالي وأنه في حالة pending
        $check_stmt = $conn->prepare("SELECT status FROM Announcements WHERE announcement_id = ? AND teacher_id = ?");
        $check_stmt->bind_param("ii", $announcement_id, $teacher_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $announcement = $check_result->fetch_assoc();
            if ($announcement["status"] === "active") {
                $stmt = $conn->prepare("DELETE FROM Announcements WHERE announcement_id = ? AND teacher_id = ?");
                $stmt->bind_param("ii", $announcement_id, $teacher_id);
                
                if ($stmt->execute()) {
                    $message = "<div class=\"alert alert-success\">✅ تم حذف الإعلان بنجاح!</div>";
                } else {
                    $message = "<div class=\"alert alert-danger\">❌ حدث خطأ أثناء حذف الإعلان.</div>";
                }
                $stmt->close();
            } else {
                $message = "<div class=\"alert alert-danger\">❌ لا يمكن حذف إعلان تمت الموافقة عليه أو رفضه.</div>";
            }
        } else {
            $message = "<div class=\"alert alert-danger\">❌ الإعلان غير موجود أو لا تملك صلاحية حذفه.</div>";
        }
        $check_stmt->close();
    }
}

// جلب إعلانات المعلم
$announcements_query = "SELECT a.*, t.name AS teacher_name 
                       FROM Announcements a 
                       JOIN Teachers t ON a.teacher_id = t.teacher_id 
                       WHERE a.teacher_id = ? 
                       ORDER BY a.created_at DESC";
$stmt_announcements = $conn->prepare($announcements_query);
$stmt_announcements->bind_param("i", $teacher_id);
$stmt_announcements->execute();
$announcements_result = $stmt_announcements->get_result();

?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة الإعلانات - المعلم</title>
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
        .container {
            margin-top: 20px;
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
        .btn-primary {
            background-color: #1b325f;
            border-color: #1b325f;
        }
        .btn-primary:hover {
            background-color: #142447;
            border-color: #142447;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        .status-approved {
            background: #d4edda;
            color: #155724;
        }
        .status-rejected {
            background: #f8d7da;
            color: #721c24;
        }
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border-radius: 10px;
            width: 80%;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <?php include 'teacher_navbar.php'; ?>

    <div class="container mt-4">
        <?= $message ?>
        
        <!-- إضافة إعلان جديد -->
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">➕ إضافة إعلان جديد</h4>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label for="title" class="form-label">عنوان الإعلان</label>
                        <input type="text" name="title" id="title" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="content" class="form-label">محتوى الإعلان</label>
                        <textarea name="content" id="content" class="form-control" rows="5" required></textarea>
                    </div>
                    <div class="text-center">
                        <button type="submit" name="add_announcement" class="btn btn-primary">📤 إرسال الإعلان</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- عرض الإعلانات -->
        <div class="card mt-4">
            <div class="card-header">
                <h4 class="mb-0">📋 إعلاناتي</h4>
            </div>
            <div class="card-body">
                <?php if ($announcements_result->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>العنوان</th>
                                    <th>المحتوى</th>
                                    <th>تاريخ الإنشاء</th>
                                    <th>الحالة</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($announcement = $announcements_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($announcement['title']) ?></td>
                                        <td><?= htmlspecialchars(substr($announcement['content'], 0, 100)) ?>...</td>
                                        <td><?= date('Y-m-d H:i', strtotime($announcement['created_at'])) ?></td>
                                        <td>
                                            <span class="status-badge status-<?= $announcement['status'] ?>">
                                                <?php
                                                switch($announcement['status']) {
                                                    case 'pending': echo 'في الانتظار'; break;
                                                    case 'approved': echo 'مقبول'; break;
                                                    case 'rejected': echo 'مرفوض'; break;
                                                    default: echo $announcement['status'];
                                                }
                                                ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($announcement['status'] === 'active'): ?>
                                                <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?= $announcement['announcement_id'] ?>">
                                                    ✏️ تعديل
                                                </button>
                                                <form method="POST" style="display: inline;" onsubmit="return confirm('هل أنت متأكد من حذف هذا الإعلان؟')">
                                                    <input type="hidden" name="announcement_id" value="<?= $announcement['announcement_id'] ?>">
                                                    <button type="submit" name="delete_announcement" class="btn btn-danger btn-sm">
                                                        🗑️ حذف
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <span class="text-muted">لا يمكن التعديل</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>

                                    <!-- Modal للتعديل -->
                                    <div class="modal fade" id="editModal<?= $announcement['announcement_id'] ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">تعديل الإعلان</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="announcement_id" value="<?= $announcement['announcement_id'] ?>">
                                                        
                                                        <div class="mb-3">
                                                            <label for="edit_title<?= $announcement['announcement_id'] ?>" class="form-label">عنوان الإعلان</label>
                                                            <input type="text" name="title" id="edit_title<?= $announcement['announcement_id'] ?>" class="form-control" value="<?= htmlspecialchars($announcement['title']) ?>" required>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label for="edit_content<?= $announcement['announcement_id'] ?>" class="form-label">محتوى الإعلان</label>
                                                            <textarea name="content" id="edit_content<?= $announcement['announcement_id'] ?>" class="form-control" rows="5" required><?= htmlspecialchars($announcement['content']) ?></textarea>
                                                        </div>

                                                    
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                                        <button type="submit" name="edit_announcement" class="btn btn-primary">💾 حفظ التعديلات</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">لم تقم بإنشاء أي إعلانات بعد.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="attatchments/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
$conn->close();
?>

