<?php include 'session.php'; ?>
<?php

// Redirect to login page if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $author = $_POST['author'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $user_id = $_SESSION['user_id'];
    $image_path = '';

    // Handle file upload
    if (isset($_FILES['book_image']) && $_FILES['book_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true); // Create upload folder if it doesn't exist
        }

        $file_tmp_path = $_FILES['book_image']['tmp_name'];
        $file_name = basename($_FILES['book_image']['name']);
        $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
        $new_file_name = uniqid('book_', true) . '.' . $file_ext;
        $destination = $upload_dir . $new_file_name;

        if (move_uploaded_file($file_tmp_path, $destination)) {
            $image_path = $destination;
        } else {
            $error = "Failed to upload image.";
        }
    }

    // Database connection
    if (empty($error)) {
        $conn = new mysqli("localhost", "root", "", "book_portal");
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Insert book data
        $stmt = $conn->prepare("INSERT INTO books (title, author, price, description, image_url, user_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdssi", $title, $author, $price, $description, $image_path, $user_id);

        if ($stmt->execute()) {
            $success = "Book listed for sale successfully!";
        } else {
            $error = "Error: " . $stmt->error;
        }

        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Sell a Book</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <style>
    body {
      font-family: 'Roboto', sans-serif;
      background: linear-gradient(135deg, #2c3e50, #3498db);
      color: #fff;
      background-size: cover;
      padding-top: 50px;
    }

    .container {
      background-color: rgba(0, 0, 0, 0.7);
      border-radius: 15px;
      box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.5);
      padding: 30px;
      max-width: 500px;
      margin: auto;
    }

    h2 {
      text-align: center;
      margin-bottom: 20px;
      color: #ffb84d;
      font-size: 2rem;
    }

    .form-label {
      color: #ffb84d;
      font-size: 1.2rem;
    }

    .form-control {
      border-radius: 25px;
      border: 2px solid #ffb84d;
      background-color: #3e3e3e;
      color: #fff;
      font-size: 1.1rem;
    }

    .btn {
      border-radius: 25px;
      padding: 12px 20px;
      font-size: 18px;
      transition: 0.3s ease;
    }

    .btn-success {
      background-color: #28a745;
      border: none;
    }

    .btn-success:hover {
      background-color: #218838;
      transform: scale(1.05);
    }

    .back-button-container {
      position: fixed;
      top: 10px;
      right: 20px;
      z-index: 1000;
    }

    .back-button {
      background-color: #6c757d;
      color: white;
      padding: 10px 20px;
      border-radius: 50px;
    }

    .back-button:hover {
      background-color: #5a6268;
      transform: scale(1.05);
    }

    .alert {
      font-size: 16px;
    }

    .file-input {
      padding: 10px;
      border-radius: 25px;
      background-color: #3e3e3e;
      color: #fff;
    }
  </style>
</head>
<body>

  <div class="back-button-container">
    <a href="book.php" class="btn back-button">Back</a>
  </div>

  <div class="container my-5">
    <h2>Sell a Book</h2>

    <?php if (!empty($success)): ?>
      <div class="alert alert-success"><?= $success ?></div>
    <?php elseif (!empty($error)): ?>
      <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
      <div class="mb-3">
        <label for="title" class="form-label">Book Title</label>
        <input type="text" name="title" id="title" class="form-control" required>
      </div>

      <div class="mb-3">
        <label for="author" class="form-label">Author</label>
        <input type="text" name="author" id="author" class="form-control" required>
      </div>

      <div class="mb-3">
        <label for="price" class="form-label">Price (₹)</label>
        <input type="number" name="price" id="price" step="0.01" class="form-control" required>
      </div>

      <div class="mb-3">
        <label for="description" class="form-label">Description</label>
        <textarea name="description" id="description" class="form-control" rows="3"></textarea>
      </div>

      <div class="mb-3">
        <label for="book_image" class="form-label">Book Image</label>
        <input type="file" name="book_image" id="book_image" class="form-control file-input" accept="image/*">
      </div>

      <button type="submit" class="btn btn-success w-100">List Book for Sale</button>
    </form>
  </div>

</body>
</html>
