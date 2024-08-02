<?php
require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$role = get_user_role();
$course_id = $_GET['id'] ?? null;

if ($course_id) {
    // Fetch specific course details
    $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
    $stmt->execute([$course_id]);
    $course = $stmt->fetch();
    if (!$course) {
        die("Course not found");
    }
    
    // Check if user is enrolled or is a lecturer
    if (!is_enrolled($user_id, $course_id) && $role != 'administrator') {
        die("You are not enrolled in this course");
    }
    
    // Fetch materials for the course
    $stmt = $pdo->prepare("SELECT * FROM materials WHERE course_id = ? ORDER BY created_at DESC");
    $stmt->execute([$course_id]);
    $materials = $stmt->fetchAll();
    
    // Fetch assignments for the course
    $stmt = $pdo->prepare("SELECT * FROM assignments WHERE course_id = ? ORDER BY due_date ASC");
    $stmt->execute([$course_id]);
    $assignments = $stmt->fetchAll();
} else {
    // Fetch all courses for the user
    $courses = get_user_courses($user_id);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courses - LMS</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php if ($course_id): ?>
        <h1><?php echo htmlspecialchars($course['name']); ?></h1>
        <p><?php echo htmlspecialchars($course['description']); ?></p>

        <h2>Materials</h2>
        <?php if (empty($materials)): ?>
            <p>No materials available for this course.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($materials as $material): ?>
                    <li>
                        <a href="view_materials.php?id=<?php echo $material['id']; ?>">
                            <?php echo htmlspecialchars($material['title']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <h2>Assignments</h2>
        <?php if (empty($assignments)): ?>
            <p>No assignments available for this course.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($assignments as $assignment): ?>
                    <li>
                        <a href="assignments.php?id=<?php echo $assignment['id']; ?>">
                            <?php echo htmlspecialchars($assignment['title']); ?>
                        </a>
                        (Due: <?php echo $assignment['due_date']; ?>)
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

    <?php else: ?>
        <h1>Your Courses</h1>
        <?php if (empty($courses)): ?>
            <p>You are not enrolled in any courses.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($courses as $course): ?>
                    <li>
                        <a href="courses.php?id=<?php echo $course['id']; ?>">
                            <?php echo htmlspecialchars($course['name']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    <?php endif; ?>

    <p><a href="dashboard.php">Back to Dashboard</a></p>
    <script src="assets/js/main.js"></script>
</body>
</html>
