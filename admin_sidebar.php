
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تعديل بيانات القاعة الدراسية</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/style2.css"> 
    <style>
        .main-content {
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-top: 20px;
        }
        .form-container {
            max-width: 600px;
            margin: 30px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .form-control[readonly] {
            background-color: #e9ecef;
            opacity: 1;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light navbar-custom">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">نظام الإدارة الأكاديمية</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle"></i> Raja@studex.lyx
                        </a>
                        <ul class="dropdown-menu dropdown-menu-custom" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="#"><i class="fas fa-cog"></i> الإعدادات</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-sign-out-alt"></i> تسجيل الخروج</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid main-layout">
        <div class="row w-100 g-0">
            <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <h5 class="sidebar-title">
                        <i class="fas fa-heartbeat"></i> الدراسة والامتحانات
                    </h5>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">
                                <i class="fas fa-home"></i> الرئيسية
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="classrooms.php">
                                <i class="fas fa-chalkboard"></i> قاعات دراسية
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="students.php">
                                <i class="fas fa-user-graduate"></i> الطلاب
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="semester.php">
                                <i class="fas fa-calendar-alt"></i> الفصول الدراسية
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="lectures.php">
                                <i class="fas fa-users-class"></i> المجموعات
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="teachers.php">
                                <i class="fas fa-chalkboard-teacher"></i> أعضاء هيئة التدريس
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="subjects.php">
                                <i class="fas fa-book"></i> المواد
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="display_grades.php">
                                <i class="fas fa-user-friends"></i> مجموعات الطلبة
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="users.php">
                                <i class="fas fa-users"></i> المستخدمين
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin_users_management.php">
                                <i class="fas fa-user-check"></i> إدارة المستخدمين
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin_site_settings.php">
                                <i class="fas fa-cog"></i> إعدادات الموقع
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php"><i class="fas fa-home"></i> الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="classrooms.php">القاعات الدراسية</a></li>
                        <li class="breadcrumb-item active" aria-current="page">تعديل قاعة دراسية</li>
                    </ol>
                </nav>

                <h2 class="mb-4 text-center">تعديل بيانات القاعة الدراسية</h2>

                <div class="form-container">
                    <?php if ($status_message): ?>
                        <div class="alert alert-<?php echo htmlspecialchars($status_type); ?> alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($status_message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($classroom_data): // اعرض النموذج فقط إذا وجدت بيانات للقاعة ?>
                        <form action="edit_classroom.php" method="POST">
                            <input type="hidden" name="classroom_id" value="<?php echo htmlspecialchars($classroom_data['id_classroom']); ?>">
                            
                            <div class="mb-3">
                                <label for="classroom_id_display" class="form-label">ID القاعة:</label>
                                <input type="text" class="form-control" id="classroom_id_display" 
                                       value="<?php echo htmlspecialchars($classroom_data['id_classroom']); ?>" readonly>
                            </div>

                            <div class="mb-3">
                                <label for="classroom_name" class="form-label">اسم القاعة:</label>
                                <input type="text" class="form-control" id="classroom_name" name="classroom_name" 
                                       value="<?php echo htmlspecialchars($classroom_data['name']); ?>" required>
                            </div>
                            <div class="d-flex justify-content-between">
                                <a href="classrooms.php" class="btn btn-secondary">العودة للقائمة</a>
                                <button type="submit" class="btn btn-primary">حفظ التغييرات</button>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="text-center no-data-message">
                            <p>لا يمكن عرض نموذج التعديل. <?php echo htmlspecialchars($status_message); ?></p>
                            <a href="classrooms.php" class="btn btn-info">العودة إلى قائمة القاعات</a>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

</body>
</html>