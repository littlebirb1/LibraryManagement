<?php
session_start();
require_once 'includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

//statistics for dashboard
$books_count = $pdo->query("SELECT COUNT(*) FROM books")->fetchColumn();
$members_count = $pdo->query("SELECT COUNT(*) FROM members")->fetchColumn();
$borrowed_books = $pdo->query("SELECT COUNT(*) FROM borrowings WHERE status = 'borrowed'")->fetchColumn();
$overdue_books = $pdo->query("SELECT COUNT(*) FROM borrowings WHERE status = 'overdue'")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
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
                    <li class="active"><a href="index.php">Dashboard</a></li>
                    <li><a href="books/">Books Management</a></li>
                    <li><a href="members/">Members Management</a></li>
                    <li><a href="borrow/">Borrow/Return</a></li>
                    <li><a href="auth/logout.php">Logout</a></li>
                </ul>
            </nav>
        </aside>
        
        <main class="main-content">
            <header>
                <h1>Dashboard</h1>
                <div class="search-bar">
                    <form action="books/" method="get">
                        <input type="text" name="search" placeholder="Search books...">
                        <button type="submit">Search</button>
                    </form>
                </div>
            </header>
            
            <div class="stats">
                <div class="stat-card">
                    <h3>Total Books</h3>
                    <p><?php echo $books_count; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Total Members</h3>
                    <p><?php echo $members_count; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Books Borrowed</h3>
                    <p><?php echo $borrowed_books; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Overdue Books</h3>
                    <p><?php echo $overdue_books; ?></p>
                </div>
            </div>
            
            <div class="recent-activity">
                <h2>Recent Activity</h2>
                <?php
                $stmt = $pdo->query("
                    SELECT b.title, m.first_name, m.last_name, br.borrowed_date, br.due_date, br.status 
                    FROM borrowings br
                    JOIN books b ON br.book_id = b.id
                    JOIN members m ON br.member_id = m.id
                    ORDER BY br.borrowed_date DESC
                    LIMIT 5
                ");
                $activities = $stmt->fetchAll();
                
                if ($activities): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Book</th>
                                <th>Member</th>
                                <th>Borrowed Date</th>
                                <th>Due Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($activities as $activity): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($activity['title']); ?></td>
                                    <td><?php echo htmlspecialchars($activity['first_name'] . ' ' . $activity['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($activity['borrowed_date']); ?></td>
                                    <td><?php echo htmlspecialchars($activity['due_date']); ?></td>
                                    <td class="<?php echo htmlspecialchars($activity['status']); ?>">
                                        <?php echo htmlspecialchars(ucfirst($activity['status'])); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No recent activity found.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>