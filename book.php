<?php include 'session.php'; ?>

<?php


if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// DB connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "book_portal";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Book Portal</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(135deg, #1e3c72, #2a5298);
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      color: #fff;
      margin: 0;
      padding: 0;
      height: 100vh;
      overflow: hidden;
      display: flex;
      justify-content: center;
      align-items: center;
      backdrop-filter: blur(10px);
    }

    .logout-button-container {
      position: fixed;
      top: 20px;
      right: 30px;
      z-index: 1000;
    }

    .logout-button {
      background-color: rgba(220, 53, 69, 0.9);
      color: white;
      padding: 10px 20px;
      border-radius: 8px;
      transition: background-color 0.3s ease;
      font-weight: 600;
    }

    .logout-button:hover {
      background-color: rgba(200, 35, 51, 0.95);
    }

    .main-card {
      background: rgba(0, 123, 255, 0.15);
      border: 1px solid rgba(255, 255, 255, 0.2);
      border-radius: 25px;
      box-shadow: 0 12px 30px rgba(0, 0, 0, 0.3);
      backdrop-filter: blur(18px);
      -webkit-backdrop-filter: blur(18px);
      padding: 50px 40px;
      max-width: 580px;
      width: 90%;
      text-align: center;
      animation: fadeInUp 0.8s ease-out;
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
      font-weight: 700;
      color: #ffffff;
    }

    .btn-action {
      position: relative;
      padding: 16px 22px;
      font-size: 1.1rem;
      font-weight: 600;
      border-radius: 14px;
      text-transform: uppercase;
      overflow: hidden;
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 12px;
      border: none;
      transition: all 0.3s ease-in-out;
      box-shadow: 0 6px 15px rgba(0,0,0,0.25);
      background-size: 200% 200%;
      animation: pulse 5s infinite;
    }

    @keyframes pulse {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }

    .btn-action i {
      font-size: 1.2rem;
      transition: transform 0.3s ease;
    }

    .btn-action:hover i {
      transform: scale(1.2) rotate(10deg);
    }

    .btn-buy {
      background: linear-gradient(135deg, #1d8cf8, #3358f4);
    }

    .btn-sell {
      background: linear-gradient(135deg, #f5365c, #f56036);
    }

    .btn-exchange {
      background: linear-gradient(135deg, #2dce89, #2ebf91);
    }

    .btn-library {
      background: linear-gradient(135deg, #11cdef, #1171ef);
    }

    .btn-action:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
    }

    @media (max-width: 600px) {
      .main-card {
        padding: 30px 20px;
      }

      .btn-action {
        font-size: 1rem;
        padding: 12px 18px;
      }
    }
  </style>
</head>
<body>

  <!-- Logout Button -->
  <div class="logout-button-container">
    <a href="logout.php" class="btn logout-button">Logout</a>
  </div>

  <div class="main-card">
    <h2 class="mb-3">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
    <p class="mb-4">Choose what you want to do:</p>

    <div class="d-grid gap-3">
      <a href="buy.php" class="btn-action btn-buy"><i class="fas fa-book"></i> Buy </a>
      <a href="sellbook.php" class="btn-action btn-sell"><i class="fas fa-dollar-sign"></i> Sell </a>
      <a href="exchange_book.php" class="btn-action btn-exchange"><i class="fas fa-exchange-alt"></i> Exchange </a>
    </div>
  </div>

</body>
</html>
