<?php include 'get_site_name.php'; $site_name = getSiteName();
session_start();
$role = isset($_SESSION['role']) ? $_SESSION['role'] : '';

$conn = new mysqli("localhost", "root", "", "student_registration_system");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// جلب عدد الطلاب
$result_students = $conn->query("SELECT COUNT(*) AS total FROM students");
$row_students = $result_students->fetch_assoc();
$total_students = $row_students['total'];

// جلب عدد أعضاء هيئة التدريس
$result_teachers = $conn->query("SELECT COUNT(*) AS total FROM teachers");
$row_teachers = $result_teachers->fetch_assoc();
$total_teachers = $row_teachers['total'];

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
  <head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />

    <title><?= htmlspecialchars($site_name) ?></title>
    <meta content="" name="description" content="" />
    <meta content="" name="keywords" />

    <!-- Favicons -->
    <link href="./attatchments/logo.jpeg" rel="icon">
    <link href="https://www.histr.ly/assets/img/apple-touch-icon.png" rel="apple-touch-icon">

    <script
      src="https://kit.fontawesome.com/a076d05399.js"
      crossorigin="anonymous"
    ></script>

    <!-- Google Fonts -->
    <link
      href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Raleway:300,300i,400,400i,500,500i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i"
      rel="stylesheet"
    />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Almarai:wght@300;400;700;800&display=swap"
      rel="stylesheet"
    />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Tajawal:wght@200;300;400;500;700;800;900&display=swap"
      rel="stylesheet"
    />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=El+Messiri:wght@400;500;600;700&display=swap"
      rel="stylesheet"
    />

    <!-- Vendor CSS Files -->
    <link href="./attatchments/animate.min.css" rel="stylesheet">
    <link href="./attatchments/aos.css" rel="stylesheet">
    <link href="./attatchments/bootstrap.min.css" rel="stylesheet">
    <link href="./attatchments/bootstrap-icons.css" rel="stylesheet">
    <link href="./attatchments/boxicons.min.css" rel="stylesheet">
    <link href="./attatchments/glightbox.min.css" rel="stylesheet">
    <link href="./attatchments/remixicon.css" rel="stylesheet">
    <link href="./attatchments/swiper-bundle.min.css" rel="stylesheet">

    <!-- Template Main CSS File -->
    <link href="./attatchments/style.css" rel="stylesheet">
  </head>

  <body>
    <!-- ======= Header ======= -->
    <header id="header" class="fixed-top d-flex align-items-center header-transparent" dir="ltr">
      <div class="container-fluid d-flex align-items-center justify-content-between px-5">
        
    <div class="logo d-flex flex-row justify-content-center align-items-center d-lg-none d-xl-flex">
<a href="home.php"><img src="attatchments/logo.jpeg" alt="" class="img-fluid me-2"></a>
          <h1><a href="index.php"><?= htmlspecialchars($site_name) ?></a></h1>
        </div>

        <nav id="navbar" class="navbar" dir="rtl">
          <ul>
            <li class="">
              <a class="nav-link scrollto active p-1" href="index.php">الرئيسية
              </a>
            </li>
            

            <li class="dropdown me-3">
              <a href="index.php#services" class="fs-5 p-1"><span>التخصصات </span>
               </a>
               <ul>
                <li><a href="index.php#services"> قسم هندسة الحاسب الآلى</a></li>
                <li><a href="index.php#services">قسم هندسة علوم المواد  </a></li>
                <li><a href="index.php#services">قسم هندسة البناء والتشييد</a></li>
                <li><a href="index.php#services">قسم الهندسة الكهربائية</a></li>
                <li><a href="index.php#services">قسم الهندسة الميكانيكية</a></li>
                <li><a href="index.php#services">قسم لهندسة التقنية الكيميائية</a></li>
              </ul>
            </li>
            <li class="me-3">
              <a class="nav-link scrollto fs-5 p-1" href="index.php#about">حول المعهد</a>
            </li>
            <li class="me-3">
              <a class="nav-link scrollto fs-5 p-1" href="index.php#footer">اتصل بنا </a>
            </li>
<?php

if ($role === "admin") {
    $link = "dashboard.php";
    $text = "لوحة التحكم";
} elseif ($role === "teacher") {
    $link = "teacher_dashboard.php";
    $text = "لوحة التحكم";
} elseif ($role === "student") {
    $link = "semesters_profile.php";
    $text = "نظام الإدارة";
} else {
    $link = "login.php";
    $text = "تسجيل الدخول";
}
?>

<li class="me-3">
  <a href="<?= $link ?>" class="btlogo d-inline-block px-3 py-2" style="text-decoration: none;">
    <span class="my-auto">⚙️   <?= $text ?></span>
  </a>
</li>
<?php if ($role): ?>
<!-- زر تسجيل الخروج -->
<li class="me-3">
  <a href="logout.php" class="btlogo d-inline-block px-3 py-2" style="text-decoration: none;">
    <span class="my-auto">🚪 تسجيل الخروج</span>
  </a>
</li>
<?php endif; ?>

            
          </ul>
          <i class="bi bi-list mobile-nav-toggle"></i>
        </nav>
        <!-- .navbar -->
      </div>
    </header>
    <!-- End Header -->
    <!-- ======= Hero Section ======= -->
    <section
      id="hero"
      class="d-flex flex-column justify-content-end align-items-center"
      style="margin-top: 7px !important"
    >
      <div
        class="container-fluid hero-single"
        style="
          background-image: url('attatchments/slid0.jpg');
          background-position: center;
          background-size: cover;
          height: 100vh;
          display: flex;
          align-items: center;
          justify-content: center;
          position: relative;
        "
      >
        <div
          class="hero-container text-center"
          style="
            position: relative;
            font-family: 'Tajawal', sans-serif !important;
            color: white;
            z-index: 2;
          "
        >
          <h2 class="animate__animated animate__fadeInDown" style="font-size: 3rem; margin-bottom: 20px;">مرحبا بكم في</h2>
          <p class="animate__animated animate__fadeInDown" style="font-size: 1.5rem;">
            الموقع الألكتروني لـ<?= htmlspecialchars($site_name) ?>
          </p>
        </div>
        <!-- طبقة شفافة للخلفية -->
        <div style="
          position: absolute;
          top: 0;
          left: 0;
          right: 0;
          bottom: 0;
          background: rgba(0, 0, 0, 0.4);
          z-index: 1;
        "></div>
      </div>

      <svg class="hero-waves" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 24 150 28 " preserveAspectRatio="none">
      <defs>
        <path id="wave-path" d="M-160 44c30 0 58-18 88-18s 58 18 88 18 58-18 88-18 58 18 88 18 v44h-352z">
      </defs>
      <g class="wave1">
        <use xlink:href="#wave-path" x="50" y="3" fill="rgba(255,255,255, .1)">
      </g>
      <g class="wave2">
        <use xlink:href="#wave-path" x="50" y="0" fill="rgba(255,255,255, .2)">
      </g>
      <g class="wave3">
        <use xlink:href="#wave-path" x="50" y="9" fill="#fff">
      </g>
    </svg>
    </section>
    <!-- End Hero -->
    <main id="main">
      <!-- ======= About Section ======= -->
      <section id="about" class="about">
        <div class="container">
          <div
            class="section-title text-center"
            data-aos="zoom-out"
            style="font-family: 'El Messiri', sans-serif"
          >
            <h2>نبذة عن <?= htmlspecialchars($site_name) ?></h2>
          </div>

           <div class="row content aos-init aos-animate" data-aos="fade-up">
            <div class="col-lg-12">
              <p>
                في العام 1992 وبموجب قرار/ أمين اللجنة الشعبية العامة للتعليم
                والبحث العلمي رقم (277) سابقا تم افتتاح <?= htmlspecialchars($site_name) ?>
                باسم (المعهد العالي للمهن الشاملة - غريان )
                ، بحيث يكون مقره في الجزء الشرقي من مدينة غريان، ويتمتع بالشخصية
                الاعتبارية والذمة المالية المستقلة، وتكون مدة الدراسة بالمعهد
                ثلاث سنوات دراسية بعد اتمام مرحلة التعليم الثانوي العام أو ما
                يعادلها.
                <br><span class="fw-bold">في بداية افتتاح المعهد كان يضم الأقسام الاتية :-
                </span>
              </p>
              <div class="row justify-content-center">
                <div class="col-md-4">
                  <ul>
                    <li>
                      <i class="ri-check-double-line"></i> قسم الهندسة
                      الكهربائية والالكترونية
                    </li>
                    <li>
                      <i class="ri-check-double-line"></i>
                      قسم هندسة الحاسب الآلى
                    </li>
                    <li>
                      <i class="ri-check-double-line"></i>
                      قسم هندسة التقنية الكيميائية
                    </li>
                    
                  </ul>
                </div>
                <div class="col-md-4">
                  <ul>
                    <li>
                      <i class="ri-check-double-line"></i> 
                    تم إضافة قسم الهندسة الميكانيكية 1993/1994م
                    </li>
                    <li>
                      <i class="ri-check-double-line"></i> 
                      تم إضافة قسم البناء والتشييد 1995/1994م
                    </li>
                    <li>
                      <i class="ri-check-double-line"></i>
                     تم إضافة قسم الهندسة وعلوم المواد  1995/1994م
                    </li>
                  </ul>
                </div>
              </div>
            </div>
            <div class="col-lg-12 pt-4 pt-lg-0">
              <p>
                <span>وقد كان من اساسيات المعهد تخريج دفعات عدة من المدربين
                  المتميزين في كافة الأقسام سالفة الذكر بعد ذلك تم تغيير اسم
                  المعهد  (المعهد العالى للعلوم والتقنية غريان 
                 ). </span>
                 <br>
                <span>وقد كان لهذا المعهد الصيت الجيد في كافة المنطقة الغربية حيث
                  درس في هذا المعهد ومنذ تأسيسه عدد كبير من الطلبة والطالبات من
                  مختلف المناطق المجاورة وغير المجاورة لمدينة غريان بل كان
                  هنالك طلبة من اقصي الجنوب الليبي من مدينة سبها يدرسون في هذا
                  المعهد وكان هنالك طلبة من عدة دول عربية وافريقية مثل مصر تونس
                  السودان والعراق و جزر القمر علي سبيل المثال وهذا ان دل علي شيء
                  انما يدل علي المكانة العلمية والصيت الجيد لهذه القلعة العلمية
                  وأعضاء هيئة التدريس و المعيدين والمدربين والكادر الوظيفي بهذا
                  المعهد .</span><br><span>قام المعهد بتخريج الاف الكوادر من المدربين والمهندسين في
                  مختلف التخصصات التقنية والهندسية . حيث بلغ اجمالي عدد الخريجين
                  3143 طالب وطالبة حتى خريف 2022.
                </span>
              </p>
            </div>
          </div>
        </div>
      </section>
      <!-- about2 start -->
      <section id="about2" class="about2">
        <div class="container-fluid aos-init aos-animate" data-aos="fade-up">
          <div class="row gx-0 justify-content-center">
            <div
              class="col-lg-7 d-flex flex-column justify-content-center g aos-init aos-animate"
              data-aos="fade-up"
              data-aos-delay="200"
            >
              <div class="content my-5">
                <h3>الرسالة</h3>
                <h5 style="font-family: 'El Messiri', sans-serif">
                  الارتقاء بمستوى التعليم التقني، وتزويد سوق العمل والمجتمع
                  بخريجين ذوي كفاءة عالية وبالكوادر المتخصصة والخبرات المؤهلة
                  القادرة على التطوير والإبداع ، ومواكبة التطور التكنولوجي بما
                  يعزز إنجازات المعهد العلمية والبحثية والمعرفية ويساهم في خدمة
                  وتطوير وتقدم المجتمع.
                </h5>
                <br />
                <h3>الرؤية</h3>
                <h5 style="font-family: 'El Messiri', sans-serif">
                  الوصول بالمعهد إلى مستوى عالي من الكفاءة المهنية والفاعلية بين
                  المؤسسات التعليمية المناظرة محليا ودوليا في برامجها التعليمية
                  وأنشطتها البحثية وخدمة المجتمع في إطار القيم الثقافية
                  والأخلاقية والاجتماعية ومعايير الجودة العالمية.
                </h5>
              </div>
            </div>

            <div
              class="d-none d-md-flex col-lg-4 d-flex align-items-center aos-init aos-animate"
              data-aos="zoom-out"
              data-aos-delay="200"
              style="overflow: hidden"
            >
              <img
                src="attatchments
                /about.jpg"
                class="img-fluid"
                alt=""
                style="height: 430px; width: 450px"
              />
            </div>
          </div>
        </div>
        <!--  -->
        <div class="container-fluid aos-init aos-animate" data-aos="fade-up">
          <div class="row gx-0 justify-content-center">
            <div
              class="col-lg-4 d-flex align-items-center aos-init aos-animate"
              data-aos="zoom-out"
              data-aos-delay="200"
              style="overflow: hidden"
            >
              <img src="attatchments
              /golas.jpg" class="img-fluid" alt="" />
            </div>
            <div
              class="col-lg-6 d-flex flex-column justify-content-center g aos-init aos-animate"
              data-aos="fade-up"
              data-aos-delay="200"
            >
              <div class="content my-5">
                <h3>الأهداف الإستراتيجية</h3>
                <h5 style="font-family: 'El Messiri', sans-serif">
                  1. تعزيز وتطوير التعليم والتعلم التقني بما يتوافق مع متطلبات
                  سوق العمل وخدمة المجتمع. <br />
                  2. تعزيز وتطوير البحث العلمي بما يساهم في تقدم ورخاء المجتمع.
                  <br />
                  3. تعزيز وتطوير خدمة المجتمع والبيئة. <br />
                  4. تنمية الموارد الذاتية للمعهد لدعم العملية التعليمية
                  والتدريبية والبحثية داخله.<br />
                  5. المحافظة على الثقافة وترسيخ القيم والتقاليد الحضارية
                  للمجتمع.<br />
                  6. تنمية القدرات الإدارية والتنظيمية بما يحقق رؤية ورسالة
                  المعهد.<br />
                  7. الحصول على الاعتماد المؤسسي والبرامجي.<br />
                  8. تعزيز وتطوير البنية التحتية للمعهد<br />
                </h5>

                <!-- <div class="text-center text-lg-start">
                  <a
                    href="#"
                    class="btn-read-more d-inline-flex align-items-center justify-content-center align-self-center"
                  >
                    <span>Read More</span>
                    <i class="bi bi-arrow-right"></i>
                  </a>
                </div> -->
              </div>
            </div>
          </div>
        </div>
        <!--  -->
        <div class="container-fluid aos-init aos-animate" data-aos="fade-up">
          <div class="row gx-0 justify-content-center">
            <div
              class="col-lg-7 d-flex flex-column justify-content-center g aos-init aos-animate"
              data-aos="fade-up"
              data-aos-delay="200"
            >
              <div class="content my-5">
                <h3>القيم</h3>
                <h5 style="font-family: 'El Messiri', sans-serif">
                  الإخلاص والنزاهة - القابلية للتعلم المستمر - الجودة والتميز -
                  الحرية الأكاديمية - الشفافية والمساءلة - القيادة والعمل بروح
                  الفريق - العدالة في التقييم - الريادة والابتكار - العمل بصدق
                  والتعامل باحترام مع الآخرين - الالتزام بتحقيق تكافؤ الفرص
                  والمساواة بين الجميع
                </h5>

                <!-- <div class="text-center text-lg-start">
                  <a
                    href="#"
                    class="btn-read-more d-inline-flex align-items-center justify-content-center align-self-center"
                  >
                    <span>Read More</span>
                    <i class="bi bi-arrow-right"></i>
                  </a>
                </div> -->
              </div>
            </div>

            <div
              class="col-lg-4 d-flex align-items-center aos-init aos-animate"
              data-aos="zoom-out"
              data-aos-delay="200"
              style="overflow: hidden"
            >
              <img src="attatchments
              /vios.jpg" class="img-fluid" alt="" />
            </div>
          </div>
        </div>
      </section>

       
      <!-- ======= counters Section ======= -->

      <section class="facts section-bg mb-5" data-aos="fade-up">
        <div
          class="section-title text-center mt-5"
          data-aos="zoom-out"
          style="font-family: 'El Messiri', sans-serif"
        >
          <h2>الإحصائيات</h2>
        </div>
       <div class="container">
  <div class="row text-center mb-3" style="color: white"></div>
 <div class="row counters justify-content-center">
  <div class="col-lg-3 col-6 text-center">
    <img class="img-fluid mb-1" alt="" style="width: 40%" />
    <span
      data-purecounter-start="0"
      data-purecounter-end="<?= $total_teachers ?>"
      data-purecounter-duration="2"
      class="purecounter"
    ></span>
    <p>أعضاء هيئة التدريس</p>
  </div>
  <div class="col-lg-3 col-6 text-center">
    <img class="img-fluid mb-1" alt="" style="width: 40%" />
    <span
      data-purecounter-start="0"
      data-purecounter-end="<?= $total_students ?>"
      data-purecounter-duration="2"
      class="purecounter"
    ></span>
    <p>الطلبة</p>
  </div>
</div>

  
 
</div>
      </section>

      <!-- End counters Section -->

      <!-- ======= Services Section ======= -->
      <section id="services" class="services mb-5">
        <div class="container">
          <div
            class="section-title text-center mb-5"
            data-aos="zoom-out"
            style="font-family: 'El Messiri', sans-serif !important"
          >
            <h2>التخصصات</h2>
          </div>
          <div
            class="row mt-5 justify-content-center"
            style="margin-top: 160px !important"
          >
            <div class="col-lg-4 col-md-6 mb-5">
              <div class="icon-box overlay-wrapper" data-aos="zoom-in-left">
                <div class="icon ">
                  <img
                    src="attatchments
                    /computer.png"
                    class="img-fluid"
                    alt=""
                  />
                </div>
                <h4 class="title text-center"><a href="">قسم هندسة الحاسب الآلى</a></h4>
                <p class="description">
هو قسم يهتم بتعليم وتدريب الطلاب على مفاهيم وتقنيات الحاسوب وتطبيقاتها في مختلف المجالات. ويشمل هذا القسم دراسة البرمجة، وقواعد البيانات، وأنظمة التشغيل، والشبكات، والأمن المعلوماتي، وتطوير تطبيقات الويب، والذكاء الاصطناعي، والتعلم الآلي، وغيرها من المجالات المتعلقة بتكنولوجيا المعلومات والحاسوب.                </p>
              </div>
            </div>
            <div class="col-lg-4 col-md-6 mt-5 mt-md-0 mb-5">
              <div
                class="icon-box"
                data-aos="zoom-in-left"
                data-aos-delay="100"
              >
                <div class="icon">
                  <img src="attatchments
                  /besnis.png" class="img-fluid" alt="" />
                </div>
                <h4 class="title">
                  <a href="">قسم الهندسة وعلوم المواد</a>
                </h4>
                <p class="description">
                  هذا القسم يمثل نقطة التقاء بين الهندسة بتطبيقاتها العملية وعلوم المواد بفهمها العميق لتركيب وخصائص المواد المختلفة. إنه مجال حيوي ومثير يهتم بتصميم وتطوير وتصنيع واستخدام المواد الهندسية التي تشكل أساس كل شيء من حولنا، بدءًا من الأجهزة الإلكترونية الدقيقة وصولًا إلى الهياكل العملاقة.
                </p>
              </div>
            </div>

            <div class="col-lg-4 col-md-6 mt-5 mt-lg-0 mb-5">
              <div
                class="icon-box"
                data-aos="zoom-in-left"
                data-aos-delay="200"
              >
                <div class="icon">
                  <img src="attatchments
                  /art.png" class="img-fluid" alt="" />
                </div>
               <h4 class="title"><a href="">قسم هندسة البناء والتشييد</a></h4>
                <p class="description">
                  هذا القسم يركز على كل ما يتعلق بتصميم وتنفيذ وإدارة مشاريع البناء والتشييد المختلفة. إنه مجال حيوي يساهم بشكل مباشر في تطوير البنية التحتية للمجتمعات وتلبية احتياجاتها من المباني السكنية والتجارية والصناعية، بالإضافة إلى الطرق والجسور والأنفاق والمطارات وغيرها من المشاريع الحيوية.
                </p>
              </div>
            </div>
            <div
              class="col-lg-4 col-md-6 mt-5 mb-4"
              style="margin-top: 120px !important"
            >
              <div
                class="icon-box"
                data-aos="zoom-in-left"
                data-aos-delay="300"
              >
                <div class="icon" style="margin-top: 20px !important">
                  <img src="./attatchments
                  /elect.png" class="img-fluid" alt="" />
                </div>
                  <h4 class="title"><a href="">قسم الهندسة الكهربائية</a></h4>
                <p class="description">
                هو قسم أكاديمي يهتم بدراسة الكهرباء وتطبيقاتها في مجالات مختلفة مثل الصناعة والطاقة والاتصالات والإلكترونيات والتحكم والأتمتة. ويشمل هذا القسم دراسة الدوائر الكهربائية، والمكونات الإلكترونية، وتوليد الطاقة الكهربائية، ونقلها وتوزيعها، وتصميم وتشغيل وصيانة الأنظمة الكهربائية.
                </p>
              </div>
            </div>

            <div
              class="col-lg-4 col-md-6 mt-5 mb-4"
              style="margin-top: 120px !important"
            >
              <div
                class="icon-box"
                data-aos="zoom-in-left"
                data-aos-delay="400"
              >
                <div class="icon">
                 <img src="./attatchments
                 /oil.png" class="img-fluid" alt="">
                </div>
                <h4 class="title"><a href="">قسم الهندسة الميكانيكية</a></h4>
                <p class="description">
                  الهندسة التقنية الكيميائية هي فرع هندسي يركز على تصميم وتطوير وتشغيل وإدارة العمليات الصناعية التي تحول المواد الخام إلى منتجات ذات قيمة. يجمع هذا المجال بين مبادئ الهندسة والكيمياء والفيزياء والأحياء والاقتصاد لتطوير عمليات آمنة وفعالة واقتصادية ومستدامة لإنتاج مجموعة واسعة من المنتجات التي نستخدمها يوميًا
                </p>
              </div>
            </div>
          <div class="col-lg-4 col-md-6 mt-5 mb-4" style="margin-top: 120px !important">
              <div class="icon-box aos-init" data-aos="zoom-in-left" data-aos-delay="400">
                <div class="icon">
                  <img src="./attatchments
                  /co.png" class="img-fluid" alt="">
                </div>
                <h4 class="title"><a href="">قسم هندسة التقنية الكيميائية</a></h4>
                <p class="description">
                  الهندسة التقنية الكيميائية هي فرع هندسي يركز على تصميم وتطوير وتشغيل وإدارة العمليات الصناعية التي تحول المواد الخام إلى منتجات ذات قيمة. يجمع هذا المجال بين مبادئ الهندسة والكيمياء والفيزياء والأحياء والاقتصاد لتطوير عمليات آمنة وفعالة واقتصادية ومستدامة لإنتاج مجموعة واسعة من المنتجات التي نستخدمها يوميًا،
                </p>
              </div>
            </div>
          </div>
        </div>
      </section>
      <!-- End Services Section -->


    </main>
    <!-- End #main -->

     <!-- ======= Footer ======= -->
    <footer id="footer">
      <div class="container">
        <div class="row text-end">
          <div class="col-lg-3 justify-content-center text-center">
            <img class="my-5 img-fluid" src="./attatchments
            /logo.jpeg" alt="" />
            <!-- هنا اللوقو -->
          </div>
          <div class="col-lg-3 text-end">
            <h5 style="font-family: 'El Messiri', sans-serif">تواصل معنا</h5>

            <div class="btn text-center mt-3">
              <p>
               ليبيا -
                غريان
              </p>
            </div>
            <!-- <div class="btn">
              <p>
                <i class="bx bxs-phone ms-2" style="font-size: 22px"></i>
                091-0000000 092-00000
              </p>
            </div>
            <div class="btn">
              <p>
                <i class="bx bxs-phone-call ms-2" style="font-size: 22px"></i>
                21821-0000000
              </p>
            </div> -->
          <br>
            <div class="btn">
              <a href="mailto:histrg1993@gmail.com"><p>
              
                rsh07573@gmail.com
              </p></a>
            </div>
           
          </div>
          <div class="col-lg-3">
            <h5 style="font-family: 'El Messiri', sans-serif">روابط قد تهمك</h5>
            <div class="btn mt-3">
              <a href="https://moe.gov.ly/"
                ><p>
                  وزارة التعليم الليبية
                 </p
              ></a>
            </div>
            <div class="btn">
              <a href="https://qaa.ly/"
                ><p>
                  المركز الوطني لضمان الجودة</p
              ></a>
            </div>
            <div class="btn">
              <a href="https://uot.edu.ly/"
                ><p>
                  جامعة طرابلس</p
              ></a>
            </div>
            <div class="btn">
              <a href="https://www.tve.gov.ly/"
                ><p>
                  وزارة التعليم التقني والفني</p
              ></a>
            </div>
            <div class="btn">
              <a href="http://admtech.tve.gov.ly/"
                ><p>
                  إدارة الكليات التقنية</p
              ></a>
            </div>
          </div>
          <div class="col-lg-3 mt-3 mt-md-0">
            <h5 style="font-family: 'El Messiri', sans-serif">
              موقعنا علي الخريطة
            </h5>
            <section class="map mt-3">
              <div class="container-fluid">
              <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3377.0842533949!2d13.023485775433825!3d32.1750026739211!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x13af3b02ed000001%3A0xdfe0a0f64120aa68!2sHigher%20Institute%20of%20Science%20and%20Technology%20Gharyan!5e0!3m2!1sen!2sly!4v1752043811151!5m2!1sen!2sly" width="700" height="400" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe></iframe>  
              
          
              </div>
            </section>
          </div>
        </div>

        <div class="container" style="font-size: 16px">
          <div class="copyright">
            جميع الحقوق محفوظة لإدارة المعلوماتية والتوثيق بالمعهد © 2025
          </div>
        </div>
      </div>
    </footer>
    <!-- End Footer -->

    <a
      href="#"
      class="back-to-top d-flex align-items-center justify-content-center"
      ><i class="bi bi-arrow-up-short"></i
    ></a>

    <!-- Vendor JS Files -->
    <script src="attatchments/purecounter_vanilla.js"></script>
    <script src="attatchments/aos.js"></script>
    <script src="attatchments/bootstrap.bundle.min.js"></script>
    <script src="attatchments/glightbox.min.js"></script>
    <script src="attatchments/isotope.pkgd.min.js"></script>
    <script src="attatchments/swiper-bundle.min.js"></script>
    <script src="attatchments/validate.js"></script>

    <!-- Template Main JS File -->
    <script src="attatchments/main.js"></script>
    <script>
    // تهيئة Swiper
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof Swiper !== 'undefined') {
            var swiper = new Swiper('.swiper', {
                loop: true,
                autoplay: {
                    delay: 3000,
                    disableOnInteraction: false,
                },
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true,
                },
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
                },
                breakpoints: {
                    640: {
                        slidesPerView: 1,
                        spaceBetween: 20,
                    },
                    768: {
                        slidesPerView: 2,
                        spaceBetween: 40,
                    },
                    1024: {
                        slidesPerView: 3,
                        spaceBetween: 50,
                    },
                }
            });
        }
    });

    $('#login').click(function(ev) {
        console.log("I'm clicked");
          window.location.replace("user/login.php");
        });


    </script>
  </body>
</html>
