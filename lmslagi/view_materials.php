<?php
require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Ensure user is logged in
if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$course_id = $_GET['course_id'] ?? null;

if (!$course_id) {
    die("Course ID is required");
}

// Check if the student is enrolled in the course
if (get_user_role() == 'student' && !is_enrolled($user_id, $course_id)) {
    header('Location: unauthorized.php');
    exit;
}

// Fetch materials for the course
$stmt = $pdo->prepare("SELECT * FROM materials WHERE course_id = ? ORDER BY created_at DESC");
$stmt->execute([$course_id]);
$materials = $stmt->fetchAll();

// Record view if it's a student
if (get_user_role() == 'student') {
    foreach ($materials as $material) {
        $stmt = $pdo->prepare("INSERT INTO views (material_id, user_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE viewed_at = CURRENT_TIMESTAMP");
        $stmt->execute([$material['id'], $user_id]);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Materials - LMS</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <h1>Course Materials</h1>
    <?php if (empty($materials)): ?>
        <p>No materials available for this course.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($materials as $material): ?>
                <li>
                    <h2><?php echo htmlspecialchars($material['title']); ?></h2>
                    <p><?php echo nl2br(htmlspecialchars($material['content'])); ?></p>
                    <?php if ($material['file_path']): ?>
                        <a href="<?php echo $material['file_path']; ?>" target="_blank">Download File</a>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    <script src="assets/js/main.js"></script>
</body>
</html>