<?php
session_start();

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

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to get file icon based on extension
function getFileIcon($filename) {
    $extension = pathinfo($filename, PATHINFO_EXTENSION);
    $extension = strtolower($extension);
    
    $icons = [
        'jpg' => '📷',
        'jpeg' => '📷',
        'png' => '🖼️',
        'gif' => '🎞️',
        'pdf' => '📄',
        'doc' => '📝',
        'docx' => '📝',
        'txt' => '📄'
    ];
    
    return isset($icons[$extension]) ? $icons[$extension] : '📎';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Management System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="logo">Blog System</a>
            <div class="nav-links">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="dashboard.php">Dashboard</a>
                    <a href="create.php">Create Post</a>
                    <a href="logout.php">Logout (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="register.php">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <div class="container">

    <h1 class="page-title">Latest Blog Posts</h1>

    <?php
    // Fetch all posts
    $stmt = $pdo->query("SELECT * FROM posts ORDER BY created_at DESC");
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <?php if (empty($posts)): ?>
        <div class="alert">
            <p>No posts found. Be the first to create one!</p>
        </div>
    <?php else: ?>
        <div class="posts-grid">
            <?php foreach ($posts as $post): ?>
                <div class="post-card">
                    <div class="post-content">
                        <h2 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h2>
                        <div class="post-meta">
                            Posted on: <?php echo date('F j, Y, g:i a', strtotime($post['created_at'])); ?>
                        </div>
                        
                        <!-- CONTENT FIRST -->
                        <div class="post-excerpt">
                            <?php 
                            $content = htmlspecialchars($post['content']);
                            echo nl2br($content); // Keep line breaks
                            ?>
                        </div>
                        
                        <!-- FILE ATTACHMENT AFTER CONTENT -->
                        <?php if ($post['file_name']): ?>
                            <div class="file-attachment">
                                <div class="file-icon"><?php echo getFileIcon($post['file_original_name']); ?></div>
                                <div class="file-info">
                                    <strong>Attached File:</strong>
                                    <?php
                                    // Check if file is an image
                                    $image_extensions = ['jpg', 'jpeg', 'png', 'gif'];
                                    $extension = pathinfo($post['file_original_name'], PATHINFO_EXTENSION);
                                    $extension = strtolower($extension);
                                    
                                    if (in_array($extension, $image_extensions)): ?>
                                        <div class="post-image">
                                            <img src="uploads/<?php echo htmlspecialchars($post['file_name']); ?>" 
                                                 alt="<?php echo htmlspecialchars($post['file_original_name']); ?>"
                                                 style="max-width: 100%; border-radius: 5px; margin-top: 10px;">
                                            <br>
                                            <a href="uploads/<?php echo htmlspecialchars($post['file_name']); ?>" 
                                               download="<?php echo htmlspecialchars($post['file_original_name']); ?>"
                                               class="download-link">Download <?php echo htmlspecialchars($post['file_original_name']); ?></a>
                                        </div>
                                    <?php else: ?>
                                        <a href="uploads/<?php echo htmlspecialchars($post['file_name']); ?>" 
                                           download="<?php echo htmlspecialchars($post['file_original_name']); ?>"
                                           class="download-link">
                                           Download <?php echo htmlspecialchars($post['file_original_name']); ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (isLoggedIn()): ?>
                        <div class="post-actions">
                            <a href="edit.php?id=<?php echo $post['id']; ?>" class="btn btn-edit">Edit</a>
                            <a href="delete.php?id=<?php echo $post['id']; ?>" 
                               class="btn btn-danger" 
                               onclick="return confirm('Are you sure you want to delete this post? This will also delete any attached file.')">Delete</a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    </div>
    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Blog Management System. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>