<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$role = get_user_role();
$course_id = $_GET['course_id'] ?? null;

if (!$course_id) {
    die("Course ID is required");
}

// Check if the user is enrolled in the course or is a lecturer
if ($role == 'student' && !is_enrolled($user_id, $course_id)) {
    die("You are not enrolled in this course");
}

// Fetch assignments for the course
$assignments = get_course_assignments($course_id);

// Handle assignment submission
if ($role == 'student' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $assignment_id = $_POST['assignment_id'];
    if (isset($_FILES['submission']) && $_FILES['submission']['error'] == 0) {
        $file_name = $_FILES['submission']['name'];
        $file_tmp = $_FILES['submission']['tmp_name'];
        $file_path = "uploads/assignments/" . time() . "_" . $file_name;
        
        if (move_uploaded_file($file_tmp, $file_path)) {
            $stmt = $pdo->prepare("INSERT INTO submissions (assignment_id, user_id, file_path) VALUES (?, ?, ?)");
            if ($stmt->execute([$assignment_id, $user_id, $file_path])) {
                $success = "Assignment submitted successfully";
            } else {
                $error = "Error submitting assignment";
            }
        } else {
            $error = "Error uploading file";
        }
    } else {
        $error = "No file uploaded";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Assignments - LMS</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <h1>Course Assignments</h1>
    <?php if (isset($success)): ?>
        <p style="color: green;"><?php echo $success; ?></p>
    <?php endif; ?>
    <?php if (isset($error)): ?>
        <p style="color: red;"><?php echo $error; ?></p>
    <?php endif; ?>
    <?php if (empty($assignments)): ?>
        <p>No assignments available for this course.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($assignments as $assignment): ?>
                <li>
                    <h2><?php echo htmlspecialchars($assignment['title']); ?></h2>
                    <p><?php echo nl2br(htmlspecialchars($assignment['description'])); ?></p>
                    <p>Due Date: <?php echo format_date($assignment['due_date']); ?></p>
                    <?php if ($role == 'student'): ?>
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="assignment_id" value="<?php echo $assignment['id']; ?>">
                            <label for="submission">Submit Assignment:</label>
                            <input type="file" id="submission" name="submission" required>
                            <input type="submit" value="Submit">
                        </form>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    <p><a href="courses.php?id=<?php echo $course_id; ?>">Back to Course</a></p>
    <script src="assets/js/main.js"></script>
</body>
</html>