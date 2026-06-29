<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = $_GET['id'];

try {
    $stmt = $pdo->prepare("DELETE FROM books WHERE id = ?");
    $stmt->execute([$id]);
    
    $_SESSION['success'] = "Book deleted successfully!";
} catch (PDOException $e) {
    $_SESSION['error'] = "Error deleting book: " . $e->getMessage();
}

header("Location: index.php");
exit();
?>