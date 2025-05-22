<?php
session_start();

// Ensure the user is logged in
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

// Connect to the database
$conn = new mysqli("localhost", "root", "", "book_portal");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle the purchase action
if ($action === 'purchase') {
    // Check if the book is already sold
    $check = $conn->prepare("SELECT sold FROM books WHERE id = ?");
    $check->bind_param("i", $book_id);
    $check->execute();
    $check->bind_result($sold);
    $check->fetch();
    $check->close();

    // If sold, display message
    if ($sold) {
        echo "<!DOCTYPE html>
        <html>
        <head>
            <title>Book Purchase</title>
            <style>
                body { background-color: #f8f9fa; font-family: Arial, sans-serif; padding: 50px; text-align: center; }
                .message { background-color: #fff3cd; color: #856404; padding: 20px; border: 1px solid #ffeeba; border-radius: 10px; display: inline-block; }
            </style>
        </head>
        <body>
            <div class='message'>
                <h3>Sorry, this book has already been sold.</h3>
                <a href='books.php'>Back to books</a>
            </div>
        </body>
        </html>";
        exit();
    }

    // Mark book as sold and add to user's library
    $stmt = $conn->prepare("UPDATE books SET sold = 1, owner_id = ? WHERE id = ?");
    $stmt->bind_param("ii", $user_id, $book_id);
    if ($stmt->execute()) {
        echo "<!DOCTYPE html>
        <html>
        <head>
            <title>Purchase Success</title>
            <style>
                body { background-color: #f0fff4; font-family: Arial, sans-serif; padding: 50px; text-align: center; }
                .success { background-color: #d4edda; color: #155724; padding: 20px; border: 1px solid #c3e6cb; border-radius: 10px; display: inline-block; }
                a { display: inline-block; margin-top: 15px; color: #155724; text-decoration: none; font-weight: bold; }
            </style>
        </head>
        <body>
            <div class='success'>
                <h3>Purchase successful! The book has been added to your library.</h3>
                <a href='books.php'>Back to books</a>
            </div>
        </body>
        </html>";
    } else {
        echo "Error processing purchase.";
    }
    $stmt->close();
}

// Handle exchange acceptance
if ($action === 'accept') {
    // Update exchange status and ownership
    $stmt = $conn->prepare("UPDATE books SET exchange_status = 'accepted', owner_id = ? WHERE id = ?");
    $stmt->bind_param("ii", $user_id, $book_id);
    $stmt->execute();
    $stmt->close();
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Exchange Accepted</title>
        <style>
            body { background-color: #d4edda; font-family: Arial, sans-serif; padding: 50px; text-align: center; }
            .success { background-color: #d4edda; color: #155724; padding: 20px; border: 1px solid #c3e6cb; border-radius: 10px; display: inline-block; }
            a { display: inline-block; margin-top: 15px; color: #155724; text-decoration: none; font-weight: bold; }
        </style>
    </head>
    <body>
        <div class='success'>
            <h3>Exchange accepted! The book has been added to your library.</h3>
            <a href='books.php'>Back to books</a>
        </div>
    </body>
    </html>";
}

$conn->close();
?>
