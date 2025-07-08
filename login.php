<!DOCTYPE html>
<html lang="ar">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>تسجيل الدخول - نظام إدارة الطلبة</title>
  <style>
    :root {
      --primary-color: #1b325f;
      --accent-color: #f15623;
      --bg-color: #f0f4f8;
      --text-color: #333;
    }

    body {
      margin: 0;
      font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
      background-color: var(--bg-color);
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      direction: rtl;
    }

    .login-container {
      background-color: #fff;
      padding: 2rem;
      border-radius: 12px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
      width: 90%;
      max-width: 400px;
    }

    h2 {
      color: var(--primary-color);
      text-align: center;
      margin-bottom: 1.5rem;
    }

    label {
      display: block;
      margin-bottom: 0.5rem;
      color: var(--text-color);
    }

    input[type="email"],
    input[type="password"] {
      width: 100%;
      padding:0.7rem;
      border: 1px solid #ccc;
      border-radius: 8px;
      margin-bottom: 1rem;
      font-size: 1rem;
    }

    button {
      width: 100%;
      padding: 0.75rem;
      background-color: var(--primary-color);
      color: white;
      border: none;
      border-radius: 8px;
      font-size: 1rem;
      cursor: pointer;
      transition: background-color 0.3s;
    }

    button:hover {
      background-color: #142447;
    }
    .background-image {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-image: url('login_background.jpg'); 
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    z-index: -1; 
}

    .error {
      color: red;
      text-align: center;
      margin-bottom: 1rem;
    }
    

    @media (max-width: 480px) {
      .login-container {
        padding: 1rem;
      }
    }
  </style>
</head>
<body>
  <div class="login-container">
    <h2>تسجيل الدخول</h2>
    <?php
    session_start();
    if (isset($_GET['success']) && $_GET['success'] == 1) {
    echo "<div class='success'>✅ تم إنشاء الحساب بنجاح! يمكنك تسجيل الدخول الآن.</div>";
}
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
      $email = $_POST["email"];
      $password = $_POST["password"];

      // الاتصال بقاعدة البيانات
      $conn = new mysqli("localhost", "root", "", "student_registration_system");
      if ($conn->connect_error) {
        die("<div class='error'>فشل الاتصال بقاعدة البيانات</div>");
      }

      // استعلام موحد للطلاب والمعلمين والمستخدمين
      $sql = "
        SELECT 'student' AS role_type, student_id AS id, name, email, password, status, deleted 
        FROM Students WHERE email = ?
        UNION
        SELECT 'teacher' AS role_type, teacher_id AS id, name, email, password, status, deleted 
        FROM Teachers WHERE email = ?
        UNION
        SELECT role AS role_type, user_id AS id, name, email, password, 'active' AS status, FALSE AS deleted 
        FROM Users WHERE email = ?
      ";

      $stmt = $conn->prepare($sql);
      $stmt->bind_param("sss", $email, $email, $email);
      $stmt->execute();
      $result = $stmt->get_result();

      if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if ($user["deleted"]) {
          echo "<div class='error'>تم إيقاف حسابك من قبل الإدارة.</div>";
        } elseif ($user["status"] !== "active") {
          echo "<div class='error'>الحساب غير مفعل. يرجى انتظار موافقة الإدارة.</div>";
        } elseif (password_verify($password, $user["password"])) {
          // تسجيل دخول ناجح
          $_SESSION["user_id"] = $user["id"];
          $_SESSION["name"] = $user["name"];
          $_SESSION["email"] = $user["email"];
          $_SESSION["role"] = $user["role_type"];
          header("Location: index.php");
          exit();
        } else {
          echo "<div class='error'>كلمة المرور غير صحيحة.</div>";
        }
      } else {
        echo "<div class='error'>البريد الإلكتروني غير مسجل.</div>";
      }

      $stmt->close();
      $conn->close();
    }
    ?>
      <div class="background-image"></div>
    <form method="POST" action="">
      <label for="email">البريد الإلكتروني</label>
      <input type="email" name="email" id="email" required />

      <label for="password">كلمة المرور</label>
      <input type="password" name="password" id="password" required />
      <div id="links">
        <span> ليس لديك حساب؟</span>
        <a href="register.php" class="toregister">إنشاء حساب جديد</a>

        <!-- <span id="aw">أو</span>
        <a href="forgot_password.php" class="forgot-password">نسيت كلمة المرور؟</a> -->
      </div>
  </br>

      <button type="submit">تسجيل الدخول</button>
    </form>
  </div>
</body>
</html>