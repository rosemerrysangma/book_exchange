<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$conn = new mysqli("localhost", "root", "", "book_portal");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT * FROM books WHERE exchange_status = 'pending' AND user_id != ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Available Exchange Books</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
      <a class="navbar-brand" href="index.php">BookExchange</a>
      <div>
        <a class="btn btn-outline-light" href="books.php">Books</a>
        <a class="btn btn-outline-light" href="logout.php">Logout</a>
      </div>
    </div>
  </nav>

  <div class="container my-5">
    <h2 class="text-center mb-4">Available Books for Exchange</h2>

    <div class="row">
      <?php while ($row = $result->fetch_assoc()): ?>
        <div class="col-md-4 mb-4">
          <div class="card">
            <img src="<?= $row['image_url'] ?>" class="card-img-top" alt="<?= $row['title'] ?>">
            <div class="card-body">
              <h5 class="card-title"><?= $row['title'] ?></h5>
              <p class="card-text">Price: $<?= number_format($row['price'], 2) ?></p>
              <p class="card-text"><?= $row['description'] ?></p>
              <a href="accept_reject_exchange.php?id=<?= $row['id'] ?>&action=accept" class="btn btn-success">Accept</a>
              <a href="accept_reject_exchange.php?id=<?= $row['id'] ?>&action=reject" class="btn btn-danger">Reject</a>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  </div>
</body>
</html>

<?php $conn->close(); ?>
