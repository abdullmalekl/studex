<!DOCTYPE html>
<html lang="ar">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>إنشاء حساب جديد</title>
  <style>
    :root {
      --primary-color: #1b325f;
      --accent-color: #f15623;
      --bg-color: #f0f4f8;
      --text-color: #333;
    
    }

    body {
        
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: var(--bg-color);
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      direction: rtl;
    }

    .register-container {
      margin-top: 2.4%;
      background-color: #fff;
      padding: 2rem;
      border-radius: 12px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
      width: 90%;
      max-width: 500px;
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

    input, select {
      width: 100%;
      padding: 0.7rem;
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

    .error, .success {
      text-align: center;
      margin-bottom: 1rem;
    }

    .error {
      color: red;
    }
     .toregister {
        width: 30%;
      color: white;
      text-color: white;
      border: none;
      border-radius: 8px;
      padding: 0.5rem 1rem;
      cursor: pointer;
      margin-right: 10%;
      text-decoration: none;
    }
    .tologin {
      margin-right: 4%;
      width: 30%;
      color: white;
      text-color: white;
      border: none;
      border-radius: 8px;
      padding: 0.5rem 1rem;
      cursor: pointer;
      text-decoration: none;
    }
    #sem {
      color: black;
      font-size: 1rem;
      font-weight: bold;
      font-family: 'markazi_text';
     
    }
    #log, #reg {
      color: white;
      text-decoration: none;
       font-weight: bold;
      font-family: 'markazi_text';
    }
    #aw {
      color: black;
      font-size: 1.6rem;
      font-weight: bold;
      font-family: 'markazi_text';
      margin-right: 14%;
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

    .success {
      color: green;
    }

    @media (max-width: 480px) {
      .register-container {
        padding: 1rem;
      }
    }
  </style>
  <script>
    function toggleFields() {
      const role = document.getElementById("role").value;
      document.getElementById("student_fields").style.display = role === "student" ? "block" : "none";
      document.getElementById("teacher_fields").style.display = role === "teacher" ? "block" : "none";
    }
  </script>
</head>
<body>
  <div class="register-container">
    <h2>إنشاء حساب جديد</h2>
    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
      $role = $_POST["role"];
      $name = $_POST["name"];
      $email = $_POST["email"];
      $password = $_POST["password"];
      $confirm = $_POST["confirm_password"];
      $phone = $_POST["phone"];
      $address = $_POST["address"];

      $conn = new mysqli("localhost", "root", "", "student_registration_system");
      if ($conn->connect_error) {
        die("<div class='error'>فشل الاتصال بقاعدة البيانات</div>");
      }

      if ($password !== $confirm) {
        echo "<div class='error'>كلمة المرور وتأكيدها غير متطابقين.</div>";
      } else {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // تحقق من عدم تكرار البريد
        $check = $conn->prepare("SELECT email FROM Students WHERE email = ? UNION SELECT email FROM Teachers WHERE email = ? UNION SELECT email FROM Users WHERE email = ?");
        $check->bind_param("sss", $email, $email, $email);
        $check->execute();
        $res = $check->get_result();
        
        if ($res->num_rows > 0) {
          echo "<div class='error'>البريد الإلكتروني مستخدم مسبقًا.</div> </br>
          <button class='tologin'> <a id='log' href='login.php'>تسجيل الدخول</a> </button> <span id='aw'> أو </span><button class='toregister'> <a id='reg' href='register.php'>إنشاء حساب جديد</a></button>";
          $conn->close();
          exit;
        } else {
          if ($role === "student") {
            $student_id = $_POST["student_id"];
            $check = $conn->prepare("SELECT 1 FROM Students WHERE student_id = ?");
            $check->bind_param("s", $student_id);
            $check->execute();
            $res = $check->get_result();
            
            if ($res->num_rows > 0) {
              echo "<div class='error'>رقم القيد مستخدم مسبقًا.</div> </br>
              <button class='tologin'> <a id='log' href='login.php'>تسجيل الدخول</a> </button> <span id='aw'> أو </span><button class='toregister'> <a id='reg' href='register.php'>إنشاء حساب جديد</a></button>";
              $conn->close();
              exit;
            } else {
              $stmt = $conn->prepare("INSERT INTO Students (student_id, name, email, phone, address, password, status, deleted) VALUES (?, ?, ?, ?, ?, ?, 'inactive', false)");
              $stmt->bind_param("ssssss", $student_id, $name, $email, $phone, $address, $password_hash);
            }
          } else {
            // تحقق من عدم تكرار الرقم الوظيفي
            $employee_number = $_POST["employee_number"];
            $check = $conn->prepare("SELECT 1 FROM Teachers WHERE teacher_id = ?");
            $check->bind_param("s", $employee_number);
            $check->execute();
            $res = $check->get_result();
            
            if ($res->num_rows > 0) {
              echo "<div class='error'>الرقم الوظيفي مستخدم مسبقًا.</div> </br>
              <button class='tologin'> <a id='log' href='login.php'>تسجيل الدخول</a> </button> <span id='aw'> أو </span><button class='toregister'> <a id='reg' href='register.php'>إنشاء حساب جديد</a></button>";
              $conn->close();
              exit;
            } else {
              $specialization = $_POST["specialization"];
              $stmt = $conn->prepare("INSERT INTO Teachers (teacher_id, name, email, phone, address, specialization, password, role, status, deleted) VALUES (?, ?, ?, ?, ?, ?, ?, 'teacher', 'inactive', false)");
              $stmt->bind_param("sssssss", $employee_number, $name, $email, $phone, $address, $specialization, $password_hash);
            }
          }
          if ($stmt->execute()) {
            header("Location: login.php?success=1");
          } else {
            echo "<div class='error'>حدث خطأ أثناء إنشاء الحساب.</div>";
          }
        }

        $check->close();
        $stmt->close();
      }

      $conn->close();
    }
    ?>
                                                   
    <div class="background-image"></div>

    <form  method="POST" action="">
      <label for="role">نوع المستخدم</label>
      <select name="role" id="role" onchange="toggleFields()" required>
        <option value="">اختر النوع</option>
        <option value="student">طالب</option>
        <option value="teacher">أستاذ</option>
      </select>

      <label for="name">الاسم الثلاثي</label>
      <input type="text" name="name" id="name" required />

      <label for="email">البريد الإلكتروني</label>
      <input type="email" name="email" id="email" required />

      <label for="password">كلمة المرور</label>
      <input type="password" name="password" id="password" required />

      <label for="confirm_password">تأكيد كلمة المرور</label>
      <input type="password" name="confirm_password" id="confirm_password" required />

      <label for="phone">رقم الهاتف</label>
      <input type="text" name="phone" id="phone" required />

      <label for="address">عنوان السكن</label>
      <input type="text" name="address" id="address" required />

      <div id="student_fields" style="display:none;">
        <label for="student_id">رقم القيد</label>
        <input type="text" name="student_id" id="student_id" />
      </div>

      <div id="teacher_fields" style="display:none;">
        <label for="employee_number">الرقم الوظيفي</label>
        <input type="text" name="employee_number" id="employee_number" />

        <label for="specialization">التخصص</label>
        <input type="text" name="specialization" id="specialization" />
      </div>
      <div id="links">
        <span id='sem' >   لديك حساب؟ <a href="login.php" > تسجيل الدخول </a></span>
        
        <!-- <span id="aw">أو</span>
        <a href="forgot_password.php" class="forgot-password">نسيت كلمة المرور؟</a> -->
    </div>
</br>

      <button type="submit">إنشاء الحساب</button>
    </form>
  </div>
</body>
</html>