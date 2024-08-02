<?php
require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Ensure only lecturers can access this page
check_role('lecturer');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $course_id = $_POST['course_id'];
    $title = $_POST['title'];
    $content = $_POST['content'];
    
    // File upload handling
    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        $file_name = $_FILES['file']['name'];
        $file_tmp = $_FILES['file']['tmp_name'];
        $file_path = "uploads/materials/" . time() . "_" . $file_name;
        
        if (move_uploaded_file($file_tmp, $file_path)) {
            // File uploaded successfully, now insert into database
            $stmt = $pdo->prepare("INSERT INTO materials (course_id, title, content, file_path) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$course_id, $title, $content, $file_path])) {
                $success = "Material uploaded successfully";
            } else {
                $error = "Error uploading material";
            }
        } else {
            $error = "Error uploading file";
        }
    } else {
        // No file uploaded, just insert text content
        $stmt = $pdo->prepare("INSERT INTO materials (course_id, title, content) VALUES (?, ?, ?)");
        if ($stmt->execute([$course_id, $title, $content])) {
            $success = "Material uploaded successfully";
        } else {
            $error = "Error uploading material";
        }
    }
}

// Fetch courses assigned to the lecturer
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT c.* FROM courses c JOIN enrollments e ON c.id = e.course_id WHERE e.user_id = ? AND e.status = 'approved'");
$stmt->execute([$user_id]);
$courses = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Material - LMS</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <h1>Upload Material</h1>
    <?php if (isset($success)): ?>
        <p style="color: green;"><?php echo $success; ?></p>
    <?php endif; ?>
    <?php if (isset($error)): ?>
        <p style="color: red;"><?php echo $error; ?></p>
    <?php endif; ?>
    <form method="POST" enctype="multipart/form-data">
        <label for="course_id">Course:</label>
        <select id="course_id" name="course_id" required>
            <?php foreach ($courses as $course): ?>
                <option value="<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['name']); ?></option>
            <?php endforeach; ?>
        </select><br>
        <label for="title">Title:</label>
        <input type="text" id="title" name="title" required><br>
        <label for="content">Content:</label>
        <textarea id="content" name="content" rows="4" cols="50"></textarea><br>
        <label for="file">File (optional):</label>
        <input type="file" id="file" name="file"><br>
        <input type="submit" value="Upload Material">
    </form>
    <script src="assets/js/main.js"></script>
</body>
</html>