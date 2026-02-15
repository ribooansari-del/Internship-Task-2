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

// File upload function
function uploadFile($file) {
    $target_dir = "uploads/";
    
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_extension = pathinfo($file["name"], PATHINFO_EXTENSION);
    $new_filename = uniqid() . "_" . time() . "." . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    if ($file["size"] > 5000000) {
        return ['error' => 'File size too large. Maximum size is 5MB'];
    }
    
    $allowed_types = array('jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'txt');
    if (!in_array(strtolower($file_extension), $allowed_types)) {
        return ['error' => 'Invalid file type. Allowed: JPG, PNG, GIF, PDF, DOC, DOCX, TXT'];
    }
    
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return [
            'success' => true,
            'file_name' => $new_filename,
            'original_name' => $file["name"]
        ];
    }
    
    return ['error' => 'File upload failed'];
}

// Check if ID is provided
if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$id = $_GET['id'];
$error = '';
$success = '';

// Fetch post
$stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
$stmt->execute([$id]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    
    if (empty($title) || empty($content)) {
        $error = 'Please fill in all required fields';
    } else {
        $file_name = $post['file_name'];
        $file_original_name = $post['file_original_name'];
        
        // Handle file upload if new file is provided
        if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
            // Delete old file if exists
            if ($file_name && file_exists("uploads/" . $file_name)) {
                unlink("uploads/" . $file_name);
            }
            
            $upload_result = uploadFile($_FILES['file']);
            if (isset($upload_result['error'])) {
                $error = $upload_result['error'];
            } else {
                $file_name = $upload_result['file_name'];
                $file_original_name = $upload_result['original_name'];
            }
        }
        
        // Handle file removal if checkbox is checked
        if (isset($_POST['remove_file']) && $_POST['remove_file'] == '1') {
            if ($file_name && file_exists("uploads/" . $file_name)) {
                unlink("uploads/" . $file_name);
            }
            $file_name = null;
            $file_original_name = null;
        }
        
        if (!$error) {
            // Update post
            $stmt = $pdo->prepare("UPDATE posts SET title = ?, content = ?, file_name = ?, file_original_name = ? WHERE id = ?");
            
            if ($stmt->execute([$title, $content, $file_name, $file_original_name, $id])) {
                $success = 'Post updated successfully!';
                $post['title'] = $title;
                $post['content'] = $content;
                $post['file_name'] = $file_name;
                $post['file_original_name'] = $file_original_name;
            } else {
                $error = 'Failed to update post. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Post - Blog System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="logo">Blog System</a>
            <div class="nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="create.php">Create Post</a>
                <a href="logout.php">Logout (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a>
            </div>
        </div>
    </nav>
    <div class="container">

    <div class="post-form">
        <h2>Edit Post</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if ($post['file_name']): ?>
            <div class="current-file">
                <h4>Current File:</h4>
                <div class="file-attachment">
                    <div class="file-icon">📎</div>
                    <div class="file-info">
                        <strong><?php echo htmlspecialchars($post['file_original_name']); ?></strong>
                        <br>
                        <?php
                        $image_extensions = ['jpg', 'jpeg', 'png', 'gif'];
                        $extension = pathinfo($post['file_original_name'], PATHINFO_EXTENSION);
                        $extension = strtolower($extension);
                        
                        if (in_array($extension, $image_extensions)): ?>
                            <div class="post-image">
                                <img src="uploads/<?php echo htmlspecialchars($post['file_name']); ?>" 
                                     alt="<?php echo htmlspecialchars($post['file_original_name']); ?>"
                                     style="max-width: 200px; border-radius: 5px; margin-top: 10px;">
                            </div>
                        <?php endif; ?>
                        <a href="uploads/<?php echo htmlspecialchars($post['file_name']); ?>" 
                           target="_blank" class="download-link">View/Download</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">Post Title *</label>
                <input type="text" id="title" name="title" 
                       value="<?php echo htmlspecialchars($post['title']); ?>" 
                       required>
            </div>
            
            <div class="form-group">
                <label for="content">Content *</label>
                <textarea id="content" name="content" rows="8" required><?php echo htmlspecialchars($post['content']); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="file">Upload New File (Optional)</label>
                <input type="file" id="file" name="file" accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.txt">
                <small>This will replace the current file</small>
            </div>
            
            <?php if ($post['file_name']): ?>
                <div class="form-group">
                    <label style="display: flex; align-items: center;">
                        <input type="checkbox" name="remove_file" value="1" style="margin-right: 10px;">
                        Remove current file
                    </label>
                </div>
            <?php endif; ?>
            
            <button type="submit" class="btn">Update Post</button>
            <a href="dashboard.php" class="btn" style="background: #666; margin-left: 10px;">Cancel</a>
        </form>
    </div>

    </div>
    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Blog Management System. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>