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
// Fetch user details
$stmt = $pdo->prepare("SELECT username, email FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
// Fetch courses (for students and lecturers)
if ($role == 'student' || $role == 'lecturer') {
    $stmt = $pdo->prepare("SELECT c.* FROM courses c JOIN enrollments e ON c.id = e.course_id WHERE e.user_id = ? AND e.status = 'approved'");
    $stmt->execute([$user_id]);
    $courses = get_user_courses($user_id);

}

// Fetch all courses and users for admin
if ($role == 'administrator') {
    $stmt = $pdo->query("SELECT * FROM courses");
    $courses = $stmt->fetchAll();
    
    $stmt = $pdo->query("SELECT * FROM users");
    $users = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - LMS</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <h1>Welcome, <?php echo htmlspecialchars($username); ?></h1>
    
    <h2>Your Courses</h2>
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

    <h2>Recent Activities</h2>
    <?php if (empty($recent_activities)): ?>
        <p>No recent activities.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($recent_activities as $activity): ?>
                <li><?php echo htmlspecialchars($activity); ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <?php if ($role == 'student' || $role == 'lecturer'): ?>
        <h2>Your Courses</h2>
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

    <?php if ($role == 'lecturer'): ?>
        <p><a href="upload_material.php">Upload Material</a></p>
    <?php endif; ?>

    <?php if ($role == 'administrator'): ?>
        <h2>All Courses</h2>
        <ul>
            <?php foreach ($courses as $course): ?>
                <li><?php echo htmlspecialchars($course['name']); ?></li>
            <?php endforeach; ?>
        </ul>
        <p><a href="admin/courses.php">Manage Courses</a></p>
        <li><a href="manage_courses.php">Assign Courses</a></li>
        <h2>All Users</h2>
        <ul>
            <?php foreach ($users as $user): ?>
                <li><?php echo htmlspecialchars($user['username']); ?> (<?php echo $user['role']; ?>)</li>
            <?php endforeach; ?>
        </ul>
        <p><a href="admin/users.php">Manage Users</a></p>
    <?php endif; ?>

    <p><a href="logout.php">Logout</a></p>
    <script src="assets/js/main.js"></script>
</body>
</html>