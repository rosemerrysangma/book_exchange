<?php include 'session.php'; ?>

<?php


// Redirect to login page if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "book_portal";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle book purchase (if a book is bought)
if (isset($_GET['buy'])) {
    $book_id = $_GET['buy'];
    $user_id = $_SESSION['user_id'];

    // Fetch the selected book details
    $book_sql = "SELECT title, author, description, price, image_url FROM books WHERE id = ? AND status = 'available'";
    $book_stmt = $conn->prepare($book_sql);
    $book_stmt->bind_param("i", $book_id);
    $book_stmt->execute();
    $book_result = $book_stmt->get_result();

    if ($book_result->num_rows > 0) {
        // Get the book details
        $book = $book_result->fetch_assoc();

        // Insert the book into the user's library (with status 'owned')
        $insert_sql = "INSERT INTO books (title, author, description, price, status, user_id, image_url) VALUES (?, ?, ?, ?, 'owned', ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("ssssds", $book['title'], $book['author'], $book['description'], $book['price'], $user_id, $book['image_url']);
        $insert_stmt->execute();

        // Update the original book's status to 'bought'
        $update_sql = "UPDATE books SET status = 'bought' WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("i", $book_id);
        $update_stmt->execute();

        // Check if the book was successfully bought
        if ($insert_stmt->affected_rows > 0) {
            $_SESSION['message'] = "Book bought successfully!";
        } else {
            $_SESSION['message'] = "Error processing your purchase.";
        }
    } else {
        $_SESSION['message'] = "The book is no longer available.";
    }

    // Redirect to the current page to refresh the book list
    header("Location: buy.php");
    exit();
}

// Fetch all books that are available for sale (status = 'available')
$sql = "SELECT * FROM books WHERE status = 'available'";
$result = $conn->query($sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Buy Books</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <style>
    .back-button-container {
        position: fixed;
        top: 10px;
        right: 20px;
        z-index: 1000; /* Ensures it stays on top of other content */
    }

    .back-button {
        background-color: #6c757d;
        color: white;
    }

    .search-container {
        margin-bottom: 30px;
    }
  </style>
</head>
<body class="bg-light">
  <div class="back-button-container">
    <a href="book.php" class="btn btn-secondary back-button">Go Back</a>
  </div>

  <div class="container my-5">
    <h2 class="text-center mb-4">Search Books to Buy</h2>

    <!-- Display success/error message -->
    <?php if (isset($_SESSION['message'])): ?>
      <div class="alert alert-info text-center">
        <?= htmlspecialchars($_SESSION['message']) ?>
        <?php unset($_SESSION['message']); ?>
      </div>
    <?php endif; ?>

    <!-- Search and Filter Section -->
    <div class="search-container">
      <form class="d-flex mb-4">
        <input type="text" class="form-control me-2" id="searchInput" placeholder="Search books by title">
        <select class="form-control" id="priceFilter">
          <option value="">Filter by Price</option>
          <option value="0-1000">Under ₹1000</option>
          <option value="1000-2000">₹1000 - ₹2000</option>
          <option value="2000-5000">₹2000 - ₹5000</option>
          <option value="5000-99999">Above ₹5000</option>
        </select>
      </form>
    </div>

    <div id="bookList" class="row">
      <?php
      if ($result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
              // Check if the book is not listed by the user
              $disable_buy = '';
              $remove_button = '';

              if ($row['user_id'] == $_SESSION['user_id']) {
                  $disable_buy = 'disabled'; // Disable buy button for the user's own books
                  $remove_button = '<a href="?remove=' . $row['id'] . '" class="btn btn-danger">Remove from Sale</a>';
              }

              // Convert price to INR format (using the PHP number_format function)
              $formatted_price = '₹' . number_format($row['price'], 0, '.', ',');

              echo '
              <div class="col-md-4 mb-4 book-item" data-price="' . $row['price'] . '">
                  <div class="card">
                      <img src="' . $row['image_url'] . '" class="card-img-top" alt="' . $row['title'] . '">
                      <div class="card-body">
                          <h5 class="card-title">' . $row['title'] . '</h5>
                          <p class="card-text">Author: ' . $row['author'] . '</p>
                          <p class="card-text">Price: ' . $formatted_price . '</p>
                          <p class="card-text">' . $row['description'] . '</p>' . 
                          // Show buttons
                          ($disable_buy ? '<button class="btn btn-secondary" ' . $disable_buy . '>You Cannot Buy Your Own Book</button>' . $remove_button 
                          : '<a href="?buy=' . $row['id'] . '" class="btn btn-primary">Buy Now</a>') . 
                      '</div>
                  </div>
              </div>';
          }
      } else {
          echo "<p>No books available at the moment.</p>";
      }
      ?>
    </div>
  </div>

  <script>
    // Add event listeners for filtering
    const searchInput = document.getElementById('searchInput');
    const priceFilter = document.getElementById('priceFilter');
    const bookItems = document.querySelectorAll('.book-item');

    function filterBooks() {
      const searchText = searchInput.value.toLowerCase();
      const priceRange = priceFilter.value;

      bookItems.forEach(item => {
        const title = item.querySelector('.card-title').textContent.toLowerCase();
        const price = parseFloat(item.getAttribute('data-price'));

        // Search filter
        const matchesSearch = title.includes(searchText);

        // Price filter
        let matchesPrice = true;
        if (priceRange) {
          const [min, max] = priceRange.split('-').map(Number);
          matchesPrice = price >= min && (max === 999 ? true : price <= max);
        }

        // Show or hide book
        if (matchesSearch && matchesPrice) {
          item.style.display = 'block';
        } else {
          item.style.display = 'none';
        }
      });
    }

    searchInput.addEventListener('input', filterBooks);
    priceFilter.addEventListener('change', filterBooks);
  </script>
</body>
</html>
