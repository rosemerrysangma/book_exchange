<?php include 'session.php'; ?>

<?php


// Redirect to login page if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$success = '';
$error = '';
$search_query = '';

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "book_portal";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission for offering a book for exchange
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $price = $_POST['price'];
    $user_id = $_SESSION['user_id'];

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image_path = 'uploads/' . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $image_path);
    } else {
        $image_path = 'https://images.unsplash.com/photo-1519681393784-d120267933ba'; // Default image if none uploaded
    }

    // Insert into DB as an exchange request
    $stmt = $conn->prepare("INSERT INTO books (title, price, image_url, user_id, status) VALUES (?, ?, ?, ?, 'requested')");
    $stmt->bind_param("sdsi", $title, $price, $image_path, $user_id);

    if ($stmt->execute()) {
        $success = "Book offered for exchange successfully!";
    } else {
        $error = "Error adding exchange request: " . $stmt->error;
    }

    $stmt->close();
}

// Search functionality
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_query = $_GET['search'];
    $sql = "SELECT * FROM books WHERE status = 'requested' AND user_id != ? AND title LIKE ?";
    $stmt = $conn->prepare($sql);
    $search_query = "%$search_query%"; // Add wildcards for partial matching
    $stmt->bind_param("is", $_SESSION['user_id'], $search_query);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // Fetch available books for exchange
    $sql = "SELECT * FROM books WHERE status = 'requested' AND user_id != ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
}

// Fetch user's uploaded books
$user_books_sql = "SELECT * FROM books WHERE status = 'requested' AND user_id = ?";
$user_books_stmt = $conn->prepare($user_books_sql);
$user_books_stmt->bind_param("i", $_SESSION['user_id']);
$user_books_stmt->execute();
$user_books_result = $user_books_stmt->get_result();

// Handle accepting or rejecting a book offer
if (isset($_GET['action']) && isset($_GET['book_id'])) {
    $action = $_GET['action'];
    $book_id = $_GET['book_id'];

    if ($action == 'accept') {
        $update = $conn->prepare("UPDATE books SET status = 'exchanged' WHERE id = ?");
        $update->bind_param("i", $book_id);
        $update->execute();
        $success = "Book exchange accepted!";
    } elseif ($action == 'reject') {
        $update = $conn->prepare("UPDATE books SET status = 'rejected' WHERE id = ?");
        $update->bind_param("i", $book_id);
        $update->execute();
        $success = "Book exchange rejected.";
    }
}

// Handle removing a book
if (isset($_GET['remove_id'])) {
    $remove_id = $_GET['remove_id'];
    $remove = $conn->prepare("DELETE FROM books WHERE id = ?");
    $remove->bind_param("i", $remove_id);
    if ($remove->execute()) {
        $success = "Book removed successfully!";
    } else {
        $error = "Error removing book: " . $remove->error;
    }
}

$stmt->close();
$user_books_stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Book Exchange | Exchange Book</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <style>
    body { background-color: #f8f9fa; }
    .form-container { max-width: 500px; margin: auto; }
    .book-list { margin-top: 30px; }
    .user-books { display: none; margin-top: 30px; }
    .back-button {
      position: fixed;
      top: 10px;
      right: 20px;
      z-index: 1000;
    }
  </style>
</head>
<body>

  <!-- Back Button -->
  <a href="book.php" class="btn btn-secondary back-button">Go Back</a>

  <div class="container my-5">
    <h2 class="text-center mb-4">Offer Your Book for Exchange</h2>

    <?php if ($success): ?>
      <div class="alert alert-success text-center"><?= $success ?></div>
    <?php elseif ($error): ?>
      <div class="alert alert-danger text-center"><?= $error ?></div>
    <?php endif; ?>

    <!-- Search Bar -->
    <form method="GET" class="mb-4">
      <input type="text" name="search" class="form-control" placeholder="Search books..." value="<?= htmlspecialchars($search_query) ?>" />
      <button type="submit" class="btn btn-primary mt-2 w-100">Search</button>
    </form>

    <!-- Toggle for the "Upload Book for Exchange" form -->
    <div class="text-center mb-4">
      <button class="btn btn-primary" id="uploadToggle">Upload Book for Exchange</button>
    </div>

    <!-- Form to submit a book for exchange -->
    <div id="uploadForm" class="upload-form" style="display: none;">
      <form method="POST" enctype="multipart/form-data" class="form-container p-4 bg-white shadow rounded">
        <div class="mb-3">
          <label for="title" class="form-label">Book Title</label>
          <input type="text" name="title" id="title" class="form-control" required>
        </div>
        <div class="mb-3">
          <label for="price" class="form-label">Estimated Value ($)</label>
          <input type="number" name="price" id="price" class="form-control" step="0.01" min="0" required>
        </div>
        <div class="mb-3">
          <label for="image" class="form-label">Book Image (Upload)</label>
          <input type="file" name="image" id="image" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Submit</button>
      </form>
    </div>

    <!-- Button to toggle the user's uploaded books -->
    <div class="text-center mt-5">
      <button class="btn btn-secondary" id="userBooksToggle">View My Uploads</button>
    </div>

    <!-- Display the user's uploaded books -->
    <div id="userBooks" class="user-books">
      <h3 class="text-center">Your Uploaded Books</h3>
      <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <?php while ($row = $user_books_result->fetch_assoc()): ?>
          <div class="col">
            <div class="card h-100">
              <div class="card-body">
                <h5 class="card-title"><?= htmlspecialchars($row['title']) ?></h5>
                <p class="card-text">Estimated Value: $<?= number_format($row['price'], 2) ?></p>
                <img src="<?= htmlspecialchars($row['image_url']) ?>" class="img-fluid card-img-top" alt="Book Image">
              </div>
              <div class="card-footer">
                <a href="exchange_book.php?remove_id=<?= $row['id'] ?>" class="btn btn-danger">Remove</a>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      </div>

      <?php if ($user_books_result->num_rows == 0): ?>
        <p>You haven't uploaded any books yet.</p>
      <?php endif; ?>
    </div>

    <!-- Display available books for exchange -->
    <h3 class="text-center my-5">Available Books for Exchange</h3>

    <!-- Grid Layout for Books -->
    <div class="book-list row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
      <?php while ($row = $result->fetch_assoc()): ?>
        <div class="col">
          <div class="card h-100">
            <div class="card-body">
              <h5 class="card-title"><?= htmlspecialchars($row['title']) ?></h5>
              <p class="card-text">Estimated Value: $<?= number_format($row['price'], 2) ?></p>
              <img src="<?= htmlspecialchars($row['image_url']) ?>" class="img-fluid card-img-top" alt="Book Image">
            </div>
            <div class="card-footer">
              <a href="exchange_book.php?action=accept&book_id=<?= $row['id'] ?>" class="btn btn-success">Accept</a>
              <a href="exchange_book.php?action=reject&book_id=<?= $row['id'] ?>" class="btn btn-danger">Reject</a>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>

    <?php if ($result->num_rows == 0): ?>
      <p>No books available for exchange at the moment.</p>
    <?php endif; ?>

  </div>

  <script>
    // Toggle the book upload form visibility
    document.getElementById('uploadToggle').addEventListener('click', function() {
      const form = document.getElementById('uploadForm');
      form.style.display = form.style.display === 'none' || form.style.display === '' ? 'block' : 'none';
    });

    // Toggle the user's uploaded books visibility
    document.getElementById('userBooksToggle').addEventListener('click', function() {
      const userBooks = document.getElementById('userBooks');
      userBooks.style.display = userBooks.style.display === 'none' || userBooks.style.display === '' ? 'block' : 'none';
    });
  </script>
</body>
</html>
