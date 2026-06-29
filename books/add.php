<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
// input handling
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $author = $_POST['author'];
    $isbn = $_POST['isbn'];
    $category_id = $_POST['category_id'] ?: null;
    $publication_year = $_POST['publication_year'];
    $copies_available = $_POST['copies_available'];
    $description = $_POST['description'];

    try {
        $stmt = $pdo->prepare("INSERT INTO books (title, author, isbn, category_id, publication_year, copies_available, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $author, $isbn, $category_id, $publication_year, $copies_available, $description]);
        
        $_SESSION['success'] = "Book added successfully!";
        header("Location: index.php");
        exit();
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            $error = "A book with this ISBN already exists.";
        } else {
            $error = "An error occurred while adding the book.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Book</title>
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
                    <li class="active"><a href="index.php">Books Management</a></li>
                    <li><a href="../members/">Members Management</a></li>
                    <li><a href="../borrow/">Borrow/Return</a></li>
                    <li><a href="../auth/logout.php">Logout</a></li>
                </ul>
            </nav>
        </aside>
        
        <main class="main-content">
            <header>
                <h1>Add New Book</h1>
                <div class="actions">
                    <a href="index.php" class="btn">Back to Books</a>
                </div>
            </header>
            
            <div class="content">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form action="add.php" method="post" class="book-form">
                    <div class="form-group">
                        <label for="title">Title:</label>
                        <input type="text" id="title" name="title" required>
                    </div>
                    <div class="form-group">
                        <label for="author">Author:</label>
                        <input type="text" id="author" name="author" required>
                    </div>
                    <div class="form-group">
                        <label for="isbn">ISBN:</label>
                        <input type="text" id="isbn" name="isbn" required>
                    </div>
                    <div class="form-group">
                        <label for="category_id">Category:</label>
                        <select id="category_id" name="category_id">
                            <option value="">-- Select Category --</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="publication_year">Publication Year:</label>
                        <input type="number" id="publication_year" name="publication_year" min="1000" max="<?php echo date('Y'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="copies_available">Copies Available:</label>
                        <input type="number" id="copies_available" name="copies_available" min="1" value="1" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Description:</label>
                        <textarea id="description" name="description" rows="4"></textarea>
                    </div>
                    <button type="submit" class="btn">Add Book</button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>