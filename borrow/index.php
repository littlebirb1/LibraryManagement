<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Handle return action
if (isset($_GET['return']) && isset($_GET['id'])) {
    $borrowing_id = $_GET['id'];
    $return_date = date('Y-m-d');
    
    // Check if the book is already returned
    $stmt = $pdo->prepare("SELECT status FROM borrowings WHERE id = ?");
    $stmt->execute([$borrowing_id]);
    $status = $stmt->fetchColumn();
    
    if ($status == 'borrowed' || $status == 'overdue') {
        // Update the borrowing record
        $stmt = $pdo->prepare("UPDATE borrowings SET returned_date = ?, status = 'returned' WHERE id = ?");
        $stmt->execute([$return_date, $borrowing_id]);
        
        // Increment the available copies of the book
        $stmt = $pdo->prepare("UPDATE books b JOIN borrowings br ON b.id = br.book_id SET b.copies_available = b.copies_available + 1 WHERE br.id = ?");
        $stmt->execute([$borrowing_id]);
        
        $_SESSION['success'] = "Book returned successfully!";
    } else {
        $_SESSION['error'] = "This book is already returned.";
    }
    
    header("Location: index.php");
    exit();
}

// Get current borrowings
$query = "SELECT br.id, b.title, m.first_name, m.last_name, br.borrowed_date, br.due_date, br.status 
          FROM borrowings br
          JOIN books b ON br.book_id = b.id
          JOIN members m ON br.member_id = m.id
          ORDER BY br.status, br.due_date";

$borrowings = $pdo->query($query)->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrow/Return Books</title>
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
                <h1>Borrow/Return Books</h1>
                <div class="actions">
                    <a href="borrow.php" class="btn">Borrow a Book</a>
                </div>
            </header>
            
            <div class="content">
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                <?php endif; ?>
                
                <table>
                    <thead>
                        <tr>
                            <th>Book</th>
                            <th>Member</th>
                            <th>Borrowed Date</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($borrowings) > 0): ?>
                            <?php foreach ($borrowings as $borrowing): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($borrowing['title']); ?></td>
                                    <td><?php echo htmlspecialchars($borrowing['first_name'] . ' ' . $borrowing['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($borrowing['borrowed_date']); ?></td>
                                    <td><?php echo htmlspecialchars($borrowing['due_date']); ?></td>
                                    <td class="<?php echo htmlspecialchars($borrowing['status']); ?>">
                                        <?php echo htmlspecialchars(ucfirst($borrowing['status'])); ?>
                                    </td>
                                    <td class="actions">
                                        <?php if ($borrowing['status'] == 'borrowed' || $borrowing['status'] == 'overdue'): ?>
                                            <a href="index.php?return=1&id=<?php echo $borrowing['id']; ?>" class="btn btn-return" onclick="return confirm('Are you sure you want to mark this book as returned?')">Return</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6">No borrowings found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>