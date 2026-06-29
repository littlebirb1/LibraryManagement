<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $book_id = $_POST['book_id'];
    $member_id = $_POST['member_id'];
    $borrowed_date = date('Y-m-d');
    $due_date = date('Y-m-d', strtotime('+14 days')); // 2 weeks borrowing period

    try {
        // Check if book is available
        $stmt = $pdo->prepare("SELECT copies_available FROM books WHERE id = ?");
        $stmt->execute([$book_id]);
        $copies_available = $stmt->fetchColumn();
        
        if ($copies_available < 1) {
            throw new Exception("No copies of this book are currently available.");
        }
        
        // Check if member is active
        $stmt = $pdo->prepare("SELECT status FROM members WHERE id = ?");
        $stmt->execute([$member_id]);
        $member_status = $stmt->fetchColumn();
        
        if ($member_status != 'active') {
            throw new Exception("Member is not active and cannot borrow books.");
        }
        
        // Start transaction
        $pdo->beginTransaction();
        
        // Create borrowing record
        $stmt = $pdo->prepare("INSERT INTO borrowings (book_id, member_id, borrowed_date, due_date, status) VALUES (?, ?, ?, ?, 'borrowed')");
        $stmt->execute([$book_id, $member_id, $borrowed_date, $due_date]);
        
        // Decrement available copies
        $stmt = $pdo->prepare("UPDATE books SET copies_available = copies_available - 1 WHERE id = ?");
        $stmt->execute([$book_id]);
        
        $pdo->commit();
        
        $_SESSION['success'] = "Book borrowed successfully! Due date: " . date('F j, Y', strtotime($due_date));
        header("Location: index.php");
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = $e->getMessage();
    }
}

// Get available books and active members
$books = $pdo->query("SELECT id, title, author FROM books WHERE copies_available > 0 ORDER BY title")->fetchAll();
$members = $pdo->query("SELECT id, first_name, last_name FROM members WHERE status = 'active' ORDER BY last_name, first_name")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrow a Book</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>Library Management</h2>
                <p>Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?></p>
            </div>
            <nav>
                <ul>
                    <li><a href="../index.php">Dashboard</a></li>
                    <li><a href="../books/">Books Management</a></li>
                    <li><a href="../members/">Members Management</a></li>
                    <li class="active"><a href="index.php">Borrow/Return</a></li>
                    <li><a href="../auth/logout.php">Logout</a></li>
                </ul>
            </nav>
        </aside>
        
        <main class="main-content">
            <header>
                <h1>Borrow a Book</h1>
                <div class="actions">
                    <a href="index.php" class="btn">Back to Borrowings</a>
                </div>
            </header>
            
            <div class="content">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form action="borrow.php" method="post" class="borrow-form">
                    <div class="form-group">
                        <label for="book_id">Book:</label>
                        <select id="book_id" name="book_id" required>
                            <option value="">-- Select Book --</option>
                            <?php foreach ($books as $book): ?>
                                <option value="<?php echo $book['id']; ?>">
                                    <?php echo htmlspecialchars($book['title'] . ' by ' . $book['author']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="member_id">Member:</label>
                        <select id="member_id" name="member_id" required>
                            <option value="">-- Select Member --</option>
                            <?php foreach ($members as $member): ?>
                                <option value="<?php echo $member['id']; ?>">
                                    <?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Borrowed Date:</label>
                        <p><?php echo date('F j, Y'); ?></p>
                    </div>
                    <div class="form-group">
                        <label>Due Date:</label>
                        <p><?php echo date('F j, Y', strtotime('+14 days')); ?></p>
                    </div>
                    <button type="submit" class="btn">Borrow Book</button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>