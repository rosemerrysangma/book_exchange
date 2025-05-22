<?php

session_start();

$login_error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "book_portal";

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $login_username = trim($_POST['login_username']);
    $login_password = $_POST['login_password'];

    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $login_username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($login_password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            header("Location: book.php");
            exit();
        } else {
            $login_error = "Invalid username or password.";
        }
    } else {
        $login_error = "Invalid username or password.";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <style>
    body {
      margin: 0;
      padding: 0;
      height: 100vh;
      background: linear-gradient(135deg, #1f1c2c, #928dab);
      display: flex;
      justify-content: center;
      align-items: center;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      overflow: hidden;
    }

    .login-container {
      background: rgba(255, 255, 255, 0.1);
      border: 1px solid rgba(255, 255, 255, 0.2);
      backdrop-filter: blur(15px);
      -webkit-backdrop-filter: blur(15px);
      border-radius: 20px;
      box-shadow: 0 0 30px rgba(0, 0, 0, 0.4);
      padding: 40px;
      width: 100%;
      max-width: 400px;
      color: #fff;
      animation: floatUp 1s ease forwards;
    }

    @keyframes floatUp {
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
      font-size: 2rem;
      color: #00d4ff;
    }

    .form-label {
      font-weight: 600;
    }

    .form-control {
      background: rgba(255, 255, 255, 0.15);
      border: none;
      border-radius: 12px;
      padding: 12px;
      color: #fff;
    }

    .form-control::placeholder {
      color: #ddd;
    }

    .form-control:focus {
      outline: none;
      box-shadow: 0 0 5px #00d4ff;
      background-color: rgba(255, 255, 255, 0.25);
      color: #fff;
    }

    .btn-primary {
      background: linear-gradient(135deg, #00d4ff, #0076ff);
      border: none;
      padding: 12px;
      border-radius: 12px;
      font-weight: bold;
      letter-spacing: 1px;
      transition: all 0.3s ease;
      box-shadow: 0 6px 18px rgba(0, 212, 255, 0.3);
    }

    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 24px rgba(0, 212, 255, 0.6);
    }

    .alert {
      background: rgba(255, 0, 0, 0.2);
      border: none;
      color: #ffbbbb;
      font-weight: bold;
      border-radius: 10px;
    }

    p {
      margin-top: 20px;
      text-align: center;
      color: #eee;
    }

    a {
      color: #00d4ff;
      text-decoration: none;
    }

    a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="login-container">
    <h2><i class="fas fa-lock"></i> Login</h2>
    <form method="POST">
      <?php if (!empty($login_error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($login_error) ?></div>
      <?php endif; ?>
      <div class="mb-3">
        <label for="login_username" class="form-label">Username</label>
        <input type="text" name="login_username" id="login_username" class="form-control" placeholder="Username" required>
      </div>
      <div class="mb-3">
        <label for="login_password" class="form-label">Password</label>
        <input type="password" name="login_password" id="login_password" class="form-control" placeholder="Password" required>
      </div>
      <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>
    <p>Don't have an account? <a href="register.php">Sign up</a></p>
  </div>
</body>
</html>
