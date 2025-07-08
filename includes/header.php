<?php
// includes/header.php

// ابدأ الجلسة إذا لم تكن قد بدأت بالفعل (لضمان عمل الجلسات في كل الصفحات)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// تحديد اسم الصفحة الحالية لتفعيل الرابط في الشريط الجانبي
$current_page = basename($_SERVER['PHP_SELF']);

// للتبسيط، سنعتمد على متغيرات الجلسة التي تم تعيينها في login.php
$user_role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'guest';
$user_email = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : 'Guest';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نظام إدارة الدراسة والامتحانات - لوحة التحكم</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/style2.css"> 
    <style>
        /*
         * ملاحظة: يفضل وضع جميع هذه التنسيقات في ملف style2.css
         * ولكنها وضعت هنا مؤقتًا لضمان تطبيقها ولسهولة المراجعة.
         *
         * بعد التأكد من عمل التصميم، يرجى نقلها إلى style2.css
         * وتضمين style2.css فقط في هذا الملف.
         */
        body {
            display: flex;
            min-height: 100vh;
            flex-direction: column;
            background-color: #f4f7f6; /* لون خلفية خفيف */
            color: #333; /* لون نص أساسي */
            padding-top: 56px; /* مسافة من الأعلى لتعويض ارتفاع شريط التنقل العلوي الثابت */
        }
        /* لتجاوز تداخل النافبار مع الشريط الجانبي والمحتوى */
        .navbar {
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1030; /* تأكد من أن الـ Navbar فوق كل شيء */
        }

        #wrapper {
            display: flex; /* مهم جدا: لجعل الشريط الجانبي والمحتوى يجلسان جنبا إلى جنب */
            width: 100%;
            flex-grow: 1; /* لجعل الـ wrapper يمتد ويملأ المساحة المتاحة */
        }

        #sidebar-wrapper {
            min-width: 250px;
            max-width: 250px;
            background-color: #343a40; /* لون الشريط الجانبي (داكن) */
            color: #ffffff;
            transition: margin .25s ease-out;
            padding-top: 20px;
            position: fixed; /* تثبيت الشريط الجانبي */
            height: 100vh; /* ارتفاع كامل للشاشة */
            overflow-y: auto; /* لإضافة شريط تمرير إذا كان المحتوى طويلاً */
            z-index: 999; /* أقل من الـ Navbar بقليل */
            right: 0; /* لجعله يلتصق باليمين في RTL */
            top: 56px; /* ليأتي بعد الـ Navbar */
            box-shadow: 0 0 10px rgba(0,0,0,0.2); /* إضافة ظل لإعطاء عمق */
        }

        .main-content {
            flex-grow: 1; /* لجعل المحتوى الرئيسي يملأ المساحة المتبقية */
            padding: 20px;
            background-color: #ffffff; /* لون خلفية للمحتوى الرئيسي */
            border-radius: 8px;
            margin: 20px; /* هامش حول المحتوى الرئيسي */
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
            /* الهامش والعرض هنا مهمان لتعويض الشريط الجانبي */
            margin-right: 270px; /* 250px عرض الشريط + 20px هامش إضافي */
            width: calc(100% - 270px); /* عرض المحتوى بعد طرح عرض الشريط والهامش */
            box-sizing: border-box; /* لضمان أن الـ padding لا يزيد العرض الكلي */
            min-height: calc(100vh - 56px - 40px); /* ارتفاع أقل من الشاشة لترك مسافة للفوتر */
            margin-bottom: 20px; /* لترك مسافة للفوتر */
        }

        .navbar-brand {
            font-weight: bold;
        }
        .navbar {
            background-color: #007bff; /* لون شريط التنقل العلوي */
            color: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .navbar .nav-link {
            color: white !important;
        }
        .navbar .nav-link:hover {
            color: rgba(255, 255, 255, 0.75) !important;
        }
        /* تصميم الشريط الجانبي */
        #sidebar-wrapper .sidebar-heading {
            padding: 0.875rem 1.25rem;
            font-size: 1.2rem;
            color: white;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,.1);
            margin-bottom: 20px;
        }
        #sidebar-wrapper .list-group {
            width: 100%;
        }
        #sidebar-wrapper .list-group-item {
            background-color: transparent;
            color: #adb5bd;
            border: none;
            padding: 10px 20px;
            text-align: right; /* لمحاذاة النص لليمين */
            transition: background-color 0.2s ease, color 0.2s ease;
        }
        #sidebar-wrapper .list-group-item .fas {
            margin-left: 10px; /* مسافة بين الأيقونة والنص */
        }
        #sidebar-wrapper .list-group-item:hover {
            background-color: rgba(255,255,255,0.1);
            color: white;
        }
        #sidebar-wrapper .list-group-item.active {
            background-color: #007bff;
            color: white;
            border-radius: 5px;
            margin: 0 10px;
        }
        .page-title-box {
            background-color: #e9ecef;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            direction: rtl; /* للتأكد من RTL في العنوان والمسار */
            text-align: right;
        }
        .breadcrumb-item + .breadcrumb-item::before {
            float: right; /* للأسهم في Breadcrumb */
            padding-left: 0.5rem;
            padding-right: 0.5rem;
            content: var(--bs-breadcrumb-divider, "/");
        }

        /* تنسيقات البطاقات (Cards) */
        .card {
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            text-align: right; /* لمحاذاة محتوى البطاقة لليمين */
        }
        .card-header {
            background-color: #007bff;
            color: white;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
            padding: 15px 20px;
            border-bottom: 1px solid rgba(0,0,0,.125);
            text-align: right; /* لمحاذاة عنوان البطاقة لليمين */
        }
        .card-title {
            margin-bottom: 0;
            color: white; /* تأكد من لون العنوان */
        }
        /* تنسيقات الجداول */
        .table {
            direction: rtl; /* لضمان عرض الجدول بشكل صحيح لليمين لليسار */
        }
        .table th, .table td {
            vertical-align: middle;
            text-align: center; /* لوسطنة محتوى الجدول */
        }
        .table th {
            background-color: #e9ecef;
            font-weight: bold;
        }
        .table-responsive {
            margin-top: 15px;
        }
        /* تنسيقات الأزرار */
        .btn-success {
            background-color: #28a745;
            border-color: #28a745;
        }
        .btn-success:hover {
            background-color: #218838;
            border-color: #1e7e34;
        }
        .btn-info {
            background-color: #17a2b8;
            border-color: #17a2b8;
        }
        .btn-info:hover {
            background-color: #138496;
            border-color: #117a8b;
        }
        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }
        .btn-danger:hover {
            background-color: #c82333;
            border-color: #bd2130;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
        .btn-primary:hover {
            background-color: #0069d9;
            border-color: #0062cc;
        }
        /* تنسيقات رسائل التنبيه (Alerts) */
        .alert {
            text-align: right; /* لمحاذاة رسائل التنبيه لليمين */
            margin-bottom: 20px;
            border-radius: 5px;
        }
        /* تنسيقات الـ breadcrumb */
        .breadcrumb-item {
            font-size: 0.9em;
        }
        .breadcrumb-item a {
            color: #007bff;
            text-decoration: none;
        }
        .breadcrumb-item a:hover {
            text-decoration: underline;
        }
        .breadcrumb-item.active {
            color: #6c757d;
        }
        /* تنسيقات الفورم */
        .form-label {
            text-align: right;
            width: 100%;
        }
        .form-control, .form-select {
            border-radius: 5px;
        }

        /* Media Queries للتجاوبية (Responsive) */
        @media (max-width: 768px) {
            #sidebar-wrapper {
                margin-right: -250px; /* لإخفاء الشريط الجانبي خارج الشاشة في الشاشات الصغيرة */
                right: auto; /* لإلغاء الـ right: 0; عند إخفائه */
                left: 0; /* لدفعه لليسار عند إخفائه */
            }
            #wrapper.toggled #sidebar-wrapper {
                margin-right: 0; /* لإظهاره عند التبديل */
            }
            .main-content {
                margin-right: 0; /* المحتوى الرئيسي يأخذ كامل العرض */
                width: 100%; /* المحتوى يأخذ 100% من العرض */
            }
            #wrapper.toggled .main-content {
                margin-right: 250px; /* عندما يظهر الشريط الجانبي */
            }
            /* لإضافة زر لتبديل الشريط الجانبي في الشاشات الصغيرة - هذا يحتاج JS */
            .navbar .navbar-toggler {
                display: block; /* أظهره في الشاشات الصغيرة */
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top" id="mainNav">
        <div class="container-fluid">
            <a class="navbar-brand" href="inndex.php">نظام إدارة الطلاب</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle"></i> مرحبا, <?php echo htmlspecialchars($user_email); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="#">الملف الشخصي</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">تسجيل الخروج</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div id="wrapper">
        
        
     <?php include 'sidebar.php'; // بدون 'includes/' لأن sidebar.php في نفس المجلد ?>

<?php
// semesters.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// التحقق مما إذا كان المستخدم مسجل دخول وله صلاحية (admin)
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include 'db_connection.php'; // تضمين ملف الاتصال بقاعدة البيانات
include 'includes/header.php'; // تضمين الهيدر (وهو الآن يضم الشريط الجانبي تلقائيًا)

// *** ملاحظة: سطر `include 'includes/sidebar.php';` يجب أن يتم حذفه من هنا! ***
// القديم كان: include 'includes/sidebar.php';

$status_type = '';
$status_message = '';

// ... باقي الكود الخاص بـ semesters.php كما هو ...

// يجب أن يكون محتوى الصفحة الرئيسية (main-content) يبدأ هنا
?>
<div class="main-content">
    <div class="container-fluid">
        </div>
</div>

<?php include 'includes/footer.php'; ?>