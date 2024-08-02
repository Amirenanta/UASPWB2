<?php
function assign_to_course($course_id, $user_id, $role) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("INSERT INTO course_assignments (course_id, user_id, role) VALUES (?, ?, ?)");
        return $stmt->execute([$course_id, $user_id, $role]);
    } catch (PDOException $e) {
        // Handle unique constraint violation (user already assigned to course)
        if ($e->getCode() == '23000') {
            return false;
        }
        throw $e;
    }
}

function remove_from_course($course_id, $user_id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM course_assignments WHERE course_id = ? AND user_id = ?");
    return $stmt->execute([$course_id, $user_id]);
}

function is_enrolled($user_id, $course_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM course_assignments WHERE user_id = ? AND course_id = ?");
    $stmt->execute([$user_id, $course_id]);
    return $stmt->fetch() !== false;
}

function get_user_courses($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT c.* 
        FROM courses c 
        JOIN course_assignments ca ON c.id = ca.course_id 
        WHERE ca.user_id = ?
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

function get_course_materials($course_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM materials WHERE course_id = ? ORDER BY created_at DESC");
    $stmt->execute([$course_id]);
    return $stmt->fetchAll();
}

function get_course_assignments($course_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM assignments WHERE course_id = ? ORDER BY due_date ASC");
    $stmt->execute([$course_id]);
    return $stmt->fetchAll();
}

function record_material_view($material_id, $user_id) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO views (material_id, user_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE viewed_at = CURRENT_TIMESTAMP");
    $stmt->execute([$material_id, $user_id]);
}

function get_material_views($material_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(DISTINCT user_id) as view_count FROM views WHERE material_id = ?");
    $stmt->execute([$material_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC)['view_count'];
}

function format_date($date) {
    return date("F j, Y, g:i a", strtotime($date));
}

function sanitize_input($input) {
    return htmlspecialchars(trim($input));
}
function get_username($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch();
    return $result ? $result['username'] : 'Unknown User';
}

function get_recent_activities($user_id, $role) {
    global $pdo;
    $activities = [];

    if ($role == 'student') {
        // Get recent assignment submissions
        $stmt = $pdo->prepare("
            SELECT a.title, s.submitted_at 
            FROM submissions s
            JOIN assignments a ON s.assignment_id = a.id
            WHERE s.user_id = ?
            ORDER BY s.submitted_at DESC
            LIMIT 5
        ");
        $stmt->execute([$user_id]);
        while ($row = $stmt->fetch()) {
            $activities[] = "Submitted assignment: " . $row['title'] . " on " . $row['submitted_at'];
        }

        // Get recent course enrollments
        $stmt = $pdo->prepare("
            SELECT c.name, ca.created_at
            FROM course_assignments ca
            JOIN courses c ON ca.course_id = c.id
            WHERE ca.user_id = ? AND ca.role = 'student'
            ORDER BY ca.created_at DESC
            LIMIT 5
        ");
        $stmt->execute([$user_id]);
        while ($row = $stmt->fetch()) {
            $activities[] = "Enrolled in course: " . $row['name'] . " on " . $row['created_at'];
        }
    } elseif ($role == 'lecturer') {
        // Get recent material uploads
        $stmt = $pdo->prepare("
            SELECT title, created_at
            FROM materials
            WHERE created_by = ?
            ORDER BY created_at DESC
            LIMIT 5
        ");
        $stmt->execute([$user_id]);
        while ($row = $stmt->fetch()) {
            $activities[] = "Uploaded material: " . $row['title'] . " on " . $row['created_at'];
        }

        // Get recent assignment creations
        $stmt = $pdo->prepare("
            SELECT title, created_at
            FROM assignments
            WHERE created_by = ?
            ORDER BY created_at DESC
            LIMIT 5
        ");
        $stmt->execute([$user_id]);
        while ($row = $stmt->fetch()) {
            $activities[] = "Created assignment: " . $row['title'] . " on " . $row['created_at'];
        }
    } elseif ($role == 'administrator') {
        // Get recent user registrations
        $stmt = $pdo->prepare("
            SELECT username, created_at
            FROM users
            ORDER BY created_at DESC
            LIMIT 5
        ");
        $stmt->execute();
        while ($row = $stmt->fetch()) {
            $activities[] = "New user registered: " . $row['username'] . " on " . $row['created_at'];
        }

        // Get recent course creations
        $stmt = $pdo->prepare("
            SELECT name, created_at
            FROM courses
            ORDER BY created_at DESC
            LIMIT 5
        ");
        $stmt->execute();
        while ($row = $stmt->fetch()) {
            $activities[] = "New course created: " . $row['name'] . " on " . $row['created_at'];
        }
    }

    return $activities;
}
?>