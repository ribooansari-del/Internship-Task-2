<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'blog');
define('DB_USER', 'root');
define('DB_PASS', '');

// Create connection
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Check if ID is provided
if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$id = $_GET['id'];

// First, get the file name to delete it
$stmt = $pdo->prepare("SELECT file_name FROM posts WHERE id = ?");
$stmt->execute([$id]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

// Delete the file if it exists
if ($post && $post['file_name'] && file_exists("uploads/" . $post['file_name'])) {
    unlink("uploads/" . $post['file_name']);
}

// Delete post
$stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
if ($stmt->execute([$id])) {
    header("Location: dashboard.php?message=deleted");
} else {
    header("Location: dashboard.php?error=delete_failed");
}
exit();
?>