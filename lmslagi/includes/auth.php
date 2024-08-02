<?php
session_start();

function login($username, $password) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        return true;
    }
    return false;
}

function logout() {
    session_unset();
    session_destroy();
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function get_user_role() {
    return $_SESSION['role'] ?? null;
}

function check_role($required_role) {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
   
    $user_role = get_user_role();
    if ($user_role !== $required_role && $user_role !== 'administrator') {
        header('Location: unauthorized.php');
        exit;
    }
}

function register_user($username, $email, $password, $role) {
    global $pdo;
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
   
    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$username, $email, $hashed_password, $role]);
    } catch (PDOException $e) {
        if ($e->getCode() == '23000') {
            // Integrity constraint violation, likely duplicate username
            return false;
        }
        throw $e;
    }
}

function get_user_by_id($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT id, username, email, role FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

function update_user($user_id, $username, $email, $role) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, role = ? WHERE id = ?");
        return $stmt->execute([$username, $email, $role, $user_id]);
    } catch (PDOException $e) {
        if ($e->getCode() == '23000') {
            // Integrity constraint violation, likely duplicate username
            return false;
        }
        throw $e;
    }
}

function change_password($user_id, $new_password) {
    global $pdo;
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    return $stmt->execute([$hashed_password, $user_id]);
}
?>