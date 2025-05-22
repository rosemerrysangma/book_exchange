<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "book_portal";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$register_error = "";
$register_success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $register_username = trim($_POST['register_username']);
    $register_password = $_POST['register_password'];
    $register_confirm_password = $_POST['register_confirm_password'];

    if ($register_password !== $register_confirm_password) {
        $register_error = "Passwords do not match.";
    } else {
        $hashed_password = password_hash($register_password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $register_username, $hashed_password);

        try {
          if ($stmt->execute()) {
              $register_success = "Registration successful! <a href='login.php'>Login</a>";
          }
      } catch (mysqli_sql_exception $e) {
          if ($e->getCode() == 1062) { // Duplicate entry error code
              $register_error = "Username already exists. Please choose another.";
          } else {
              $register_error = "Registration failed: " . $e->getMessage();
          }
      }
      
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <style>
    body {
      margin: 0;
      padding: 0;
      height: 100vh;
      background: radial-gradient(circle at top left, #1e3c72, #2a5298);
      display: flex;
      justify-content: center;
      align-items: center;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      overflow: hidden;
      animation: gradientShift 20s infinite alternate ease-in-out;
    }

    @keyframes gradientShift {
      0% {
        background: radial-gradient(circle at top left, #1e3c72, #2a5298);
      }
      100% {
        background: radial-gradient(circle at bottom right, #2c5364, #0f2027);
      }
    }

    .register-box {
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(15px);
      -webkit-backdrop-filter: blur(15px);
      border-radius: 20px;
      padding: 40px;
      box-shadow: 0 0 20px rgba(255, 255, 255, 0.1);
      width: 100%;
      max-width: 420px;
      color: #fff;
      animation: fadeInUp 1s ease-out;
    }

    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(40px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    h2 {
      text-align: center;
      margin-bottom: 30px;
      font-weight: bold;
      color: #00eaff;
    }

    .form-control {
      background: rgba(255, 255, 255, 0.15);
      border: none;
      border-radius: 12px;
      padding: 12px;
      color: #fff;
    }

    .form-control::placeholder {
      color: #ccc;
    }

    .form-control:focus {
      background: rgba(255, 255, 255, 0.2);
      box-shadow: 0 0 8px #00eaff;
      outline: none;
      color: #fff;
    }

    .btn-primary {
      background: linear-gradient(135deg, #00eaff, #0077ff);
      border: none;
      border-radius: 12px;
      font-weight: bold;
      padding: 12px;
      margin-top: 10px;
      box-shadow: 0 5px 20px rgba(0, 234, 255, 0.3);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 24px rgba(0, 234, 255, 0.6);
    }

    .alert {
      border: none;
      border-radius: 12px;
      font-weight: bold;
      text-align: center;
    }

    .alert-danger {
      background: rgba(255, 0, 0, 0.2);
      color: #ffaaaa;
    }

    .alert-success {
      background: rgba(0, 255, 0, 0.15);
      color: #aaffaa;
    }

    p {
      text-align: center;
      margin-top: 20px;
      color: #eee;
    }

    a {
      color: #00eaff;
      text-decoration: none;
    }

    a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="register-box">
    <h2><i class="fas fa-user-plus"></i> Register</h2>
    
    <?php if ($register_success): ?>
      <div class="alert alert-success"><?= $register_success ?></div>
    <?php endif; ?>
    
    <?php if ($register_error): ?>
      <div class="alert alert-danger"><?= $register_error ?></div>
    <?php endif; ?>
    
    <form method="POST">
      <div class="mb-3">
        <label for="register_username" class="form-label">Username</label>
        <input type="text" name="register_username" id="register_username" class="form-control" placeholder="Enter username" required>
      </div>
      <div class="mb-3">
        <label for="register_password" class="form-label">Password</label>
        <input type="password" name="register_password" id="register_password" class="form-control" placeholder="Enter password" required>
      </div>
      <div class="mb-3">
        <label for="register_confirm_password" class="form-label">Confirm Password</label>
        <input type="password" name="register_confirm_password" id="register_confirm_password" class="form-control" placeholder="Confirm password" required>
      </div>
      <button type="submit" class="btn btn-primary w-100">Register</button>
    </form>

    <p>Already have an account? <a href="login.php">Login</a></p>
  </div>
</body>
</html>
