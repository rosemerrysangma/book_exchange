<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "book_portal";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ensure that the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$book_id = isset($_GET['book_id']) ? $_GET['book_id'] : null;
if ($book_id) {
    $sql = "SELECT * FROM books WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $book = $result->fetch_assoc();
} else {
    echo "Invalid book ID!";
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Purchase Confirmation</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<div class="container my-5">
    <h2 class="text-center mb-4">Purchase Confirmation</h2>
    <?php if ($book): ?>
        <div class="alert alert-success">
            <p>Congratulations! You've successfully bought the book:</p>
            <ul>
                <li>Title: <?= htmlspecialchars($book['title']) ?></li>
                <li>Author: <?= htmlspecialchars($book['author']) ?></li>
                <li>Price: $<?= number_format($book['price'], 2) ?></li>
            </ul>
            <a href="buy.php" class="btn btn-primary">Go Back to Book Listings</a>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
