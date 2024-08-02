<?php
require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Ensure only administrators can access this page
check_role('administrator');

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'assign':
                $course_id = $_POST['course_id'] ?? '';
                $user_id = $_POST['user_id'] ?? '';
                $role = $_POST['role'] ?? '';
                if (assign_to_course($course_id, $user_id, $role)) {
                    $success = "User assigned to course successfully";
                } else {
                    $error = "Error assigning user to course";
                }
                break;
            case 'remove':
                $course_id = $_POST['course_id'] ?? '';
                $user_id = $_POST['user_id'] ?? '';
                if (remove_from_course($course_id, $user_id)) {
                    $success = "User removed from course successfully";
                } else {
                    $error = "Error removing user from course";
                }
                break;
        }
    }
}

// Fetch all courses
$stmt = $pdo->query("SELECT * FROM courses ORDER BY name");
$courses = $stmt->fetchAll();

// Fetch all users
$stmt = $pdo->query("SELECT * FROM users ORDER BY username");
$users = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Courses - LMS</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <h1>Manage Courses</h1>
    
    <?php if (isset($success)): ?>
        <p style="color: green;"><?php echo htmlspecialchars($success); ?></p>
    <?php endif; ?>
    <?php if (isset($error)): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <h2>Assign User to Course</h2>
    <form method="POST">
        <input type="hidden" name="action" value="assign">
        <label for="course_id">Course:</label>
        <select id="course_id" name="course_id" required>
            <?php foreach ($courses as $course): ?>
                <option value="<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['name']); ?></option>
            <?php endforeach; ?>
        </select><br>
        <label for="user_id">User:</label>
        <select id="user_id" name="user_id" required>
            <?php foreach ($users as $user): ?>
                <option value="<?php echo $user['id']; ?>"><?php echo htmlspecialchars($user['username']); ?> (<?php echo htmlspecialchars($user['role']); ?>)</option>
            <?php endforeach; ?>
        </select><br>
        <label for="role">Role in Course:</label>
        <select id="role" name="role" required>
            <option value="student">Student</option>
            <option value="lecturer">Lecturer</option>
        </select><br>
        <input type="submit" value="Assign to Course">
    </form>

    <h2>Current Course Assignments</h2>
    <?php foreach ($courses as $course): ?>
        <h3><?php echo htmlspecialchars($course['name']); ?></h3>
        <?php
        $stmt = $pdo->prepare("SELECT ca.*, u.username, u.role AS user_role FROM course_assignments ca JOIN users u ON ca.user_id = u.id WHERE ca.course_id = ?");
        $stmt->execute([$course['id']]);
        $assignments = $stmt->fetchAll();
        ?>
        <?php if (empty($assignments)): ?>
            <p>No assignments for this course.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($assignments as $assignment): ?>
                    <li>
                        <?php echo htmlspecialchars($assignment['username']); ?> 
                        (<?php echo htmlspecialchars($assignment['user_role']); ?>) - 
                        Role in course: <?php echo htmlspecialchars($assignment['role']); ?>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="remove">
                            <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                            <input type="hidden" name="user_id" value="<?php echo $assignment['user_id']; ?>">
                            <input type="submit" value="Remove" onclick="return confirm('Are you sure you want to remove this user from the course?');">
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    <?php endforeach; ?>

    <p><a href="dashboard.php">Back to Dashboard</a></p>
    <script src="assets/js/main.js"></script>
</body>
</html>