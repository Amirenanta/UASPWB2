<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Ensure only administrators can access this page
check_role('administrator');

// Handle course management actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $name = sanitize_input($_POST['name']);
                $description = sanitize_input($_POST['description']);
                $stmt = $pdo->prepare("INSERT INTO courses (name, description) VALUES (?, ?)");
                if ($stmt->execute([$name, $description])) {
                    $success = "Course added successfully";
                } else {
                    $error = "Error adding course";
                }
                break;
            case 'edit':
                $course_id = $_POST['course_id'];
                $name = sanitize_input($_POST['name']);
                $description = sanitize_input($_POST['description']);
                $stmt = $pdo->prepare("UPDATE courses SET name = ?, description = ? WHERE id = ?");
                if ($stmt->execute([$name, $description, $course_id])) {
                    $success = "Course updated successfully";
                } else {
                    $error = "Error updating course";
                }
                break;
            case 'delete':
                $course_id = $_POST['course_id'];
                $stmt = $pdo->prepare("DELETE FROM courses WHERE id = ?");
                if ($stmt->execute([$course_id])) {
                    $success = "Course deleted successfully";
                } else {
                    $error = "Error deleting course";
                }
                break;
        }
    }
}

// Fetch all courses
$stmt = $pdo->query("SELECT * FROM courses ORDER BY name");
$courses = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Management - LMS</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <h1>Course Management</h1>
    <?php if (isset($success)): ?>
        <p style="color: green;"><?php echo $success; ?></p>
    <?php endif; ?>
    <?php if (isset($error)): ?>
        <p style="color: red;"><?php echo $error; ?></p>
    <?php endif; ?>

    <h2>Add New Course</h2>
    <form method="POST">
        <input type="hidden" name="action" value="add">
        <label for="name">Course Name:</label>
        <input type="text" id="name" name="name" required><br>
        <label for="description">Description:</label>
        <textarea id="description" name="description" rows="4" cols="50"></textarea><br>
        <input type="submit" value="Add Course">
    </form>

    <h2>Course List</h2>
    <table>
        <tr>
            <th>Course Name</th>
            <th>Description</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($courses as $course): ?>
            <tr>
                <td><?php echo htmlspecialchars($course['name']); ?></td>
                <td><?php echo htmlspecialchars($course['description']); ?></td>
                <td>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                        <input type="submit" value="Edit">
                    </form>
                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this course?');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                        <input type="submit" value="Delete">
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
    <p><a href="../dashboard.php">Back to Dashboard</a></p>
    <script src="../assets/js/main.js"></script>
</body>
</html>