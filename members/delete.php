<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Verify POST method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Invalid request method";
    header("Location: index.php");
    exit();
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['error'] = "Invalid security token";
    header("Location: index.php");
    exit();
}

// Verify ID exists and is numeric
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    $_SESSION['error'] = "Invalid member ID";
    header("Location: index.php");
    exit();
}

$id = (int)$_POST['id'];

try {
    // Check if member has any active borrowings
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM borrowings WHERE member_id = ? AND status IN ('borrowed', 'overdue')");
    $stmt->execute([$id]);
    $active_borrowings = $stmt->fetchColumn();

    if ($active_borrowings > 0) {
        $_SESSION['error'] = "Cannot delete member with active book borrowings.";
    } else {
        $stmt = $pdo->prepare("DELETE FROM members WHERE id = ?");
        $stmt->execute([$id]);

        $_SESSION['success'] = "Member deleted successfully!";
    }
} catch (PDOException $e) {
    error_log("Database error in delete.php: " . $e->getMessage());
    $_SESSION['error'] = "An error occurred while deleting the member. Please try again.";
}

header("Location: index.php");
exit();