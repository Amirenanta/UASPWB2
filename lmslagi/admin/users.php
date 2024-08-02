<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Ensure only administrators can access this page
check_role('administrator');

// Handle user management actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $username = $_POST['username'] ?? '';
                $email = $_POST['email'] ?? '';
                $password = $_POST['password'] ?? '';
                $role = $_POST['role'] ?? '';
                if (register_user($username, $email, $password, $role)) {
                    $success = "User added successfully";
                } else {
                    $error = "Error adding user. Username may already exist.";
                }
                break;
            case 'edit':
                $user_id = $_POST['user_id'] ?? '';
                $username = $_POST['username'] ?? '';
                $email = $_POST['email'] ?? '';
                $role = $_POST['role'] ?? '';
                if (update_user($user_id, $username, $email, $role)) {
                    $success = "User updated successfully";
                } else {
                    $error = "Error updating user. Username may already exist.";
                }
                break;
            case 'delete':
                $user_id = $_POST['user_id'] ?? '';
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                if ($stmt->execute([$user_id])) {
                    $success = "User deleted successfully";
                } else {
                    $error = "Error deleting user";
                }
                break;
        }
    }
}

// Fetch all users
$stmt = $pdo->query("SELECT * FROM users ORDER BY username");
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - LMS</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <h1>User Management</h1>
    <?php if (isset($success)): ?>
        <p style="color: green;"><?php echo htmlspecialchars($success); ?></p>
    <?php endif; ?>
    <?php if (isset($error)): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <h2>Add New User</h2>
    <form method="POST">
        <input type="hidden" name="action" value="add">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required><br>
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required><br>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required><br>
        <label for="role">Role:</label>
        <select id="role" name="role" required>
            <option value="student">Student</option>
            <option value="lecturer">Lecturer</option>
            <option value="administrator">Administrator</option>
        </select><br>
        <input type="submit" value="Add User">
    </form>

    <h2>User List</h2>
    <table>
        <tr>
            <th>Username</th>
            <th>Email</th>
            <th>Role</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?php echo htmlspecialchars($user['username']); ?></td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
                <td><?php echo htmlspecialchars($user['role']); ?></td>
                <td>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                        <input type="submit" value="Edit">
                    </form>
                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this user?');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
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