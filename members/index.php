<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$search = isset($_GET['search']) ? $_GET['search'] : '';

$members = [];
try {
    $query = "SELECT * FROM members WHERE 1=1";
    $params = [];

    if (!empty($search)) {
        $query .= " AND (first_name LIKE ? OR last_name LIKE ? OR id = ?)";
        $params = array_merge($params, ["%$search%", "%$search%", $search]);
    }

    $query .= " ORDER BY last_name, first_name";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $members = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Database error in members/index.php: " . $e->getMessage());
    $_SESSION['error'] = "An error occurred while loading members. Please try again.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Members Management</title>
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
                    <li class="active"><a href="index.php">Members Management</a></li>
                    <li><a href="../borrow/">Borrow/Return</a></li>
                    <li><a href="../auth/logout.php">Logout</a></li>
                </ul>
            </nav>
        </aside>
        
        <main class="main-content">
            <header>
                <h1>Members Management</h1>
                <div class="actions">
                    <a href="add.php" class="btn">Add New Member</a>
                    <form action="index.php" method="get" class="search-form">
                        <input type="text" name="search" placeholder="Search by name or member ID" value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit">Search</button>
                    </form>
                </div>
            </header>
            
            <div class="content">
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
                <?php endif; ?>
                
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Membership Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($members) > 0): ?>
                            <?php foreach ($members as $member): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($member['id']); ?></td>
                                    <td><?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($member['email'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($member['phone'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($member['membership_date']); ?></td>
                                    <td class="<?php echo htmlspecialchars($member['status']); ?>">
                                        <?php echo htmlspecialchars(ucfirst($member['status'])); ?>
                                    </td>
                                    <td class="actions">
                                        <a href="edit.php?id=<?php echo $member['id']; ?>" class="btn btn-edit">Edit</a>
                                        <a href="delete.php?id=<?php echo $member['id']; ?>" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this member?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7">No members found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>