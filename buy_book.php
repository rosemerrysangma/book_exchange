<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if (!isset($_GET['id'])) {
    die("Invalid request.");
}

$book_id = (int) $_GET['id'];
$user_id = $_SESSION['user_id'];

$conn = new mysqli("localhost", "root", "", "book_portal");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the book is already sold
$check = $conn->prepare("SELECT sold FROM books WHERE id = ?");
$check->bind_param("i", $book_id);
$check->execute();
$check->bind_result($sold);
$check->fetch();
$check->close();

if ($sold) {
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Book Purchase</title>
        <style>
            body {
                background-color: #f8f9fa;
                font-family: Arial, sans-serif;
                padding: 50px;
                text-align: center;
            }
            .message {
                background-color: #fff3cd;
                color: #856404;
                padding: 20px;
                border: 1px solid #ffeeba;
                border-radius: 10px;
                display: inline-block;
            }
            a {
                display: inline-block;
                margin-top: 15px;
                color: #007bff;
                text-decoration: none;
            }
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

// Mark book as sold and assign it to the current user
$update = $conn->prepare("UPDATE books SET sold = 1, status = 'owned', user_id = ? WHERE id = ?");
$update->bind_param("ii", $user_id, $book_id);

if ($update->execute()) {
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Purchase Success</title>
        <style>
            body {
                background-color: #f0fff4;
                font-family: Arial, sans-serif;
                padding: 50px;
                text-align: center;
            }
            .success {
                background-color: #d4edda;
                color: #155724;
                padding: 20px;
                border: 1px solid #c3e6cb;
                border-radius: 10px;
                display: inline-block;
            }
            a {
                display: inline-block;
                margin-top: 15px;
                color: #155724;
                text-decoration: none;
                font-weight: bold;
            }
        </style>
    </head>
    <body>
        <div class='success'>
            <h3>Purchase successful!</h3>
            <a href='books.php'>Back to books</a>
        </div>
    </body>
    </html>";
} else {
    echo "Error processing purchase.";
}

$update->close();
$conn->close();
?>
