<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if (!isset($_GET['id']) || !isset($_GET['action'])) {
    die("Invalid request.");
}

$book_id = (int) $_GET['id'];
$action = $_GET['action'];
$user_id = $_SESSION['user_id'];

if (!in_array($action, ['accept', 'reject'])) {
    die("Invalid action.");
}

$conn = new mysqli("localhost", "root", "", "book_portal");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Perform the action (accept or reject)
if ($action === 'accept') {
    // Update the book's status to accepted and set the owner_id to the current user
    $stmt = $conn->prepare("UPDATE books SET exchange_status = 'accepted', owner_id = ? WHERE id = ?");
    $stmt->bind_param("ii", $user_id, $book_id);
} else {
    // Reject the exchange request
    $stmt = $conn->prepare("UPDATE books SET exchange_status = 'rejected' WHERE id = ?");
    $stmt->bind_param("i", $book_id);
}

$stmt->execute();
$stmt->close();
$conn->close();

header('Location: books.php');
exit();
?>
