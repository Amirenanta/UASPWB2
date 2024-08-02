<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

// Redirect to dashboard if logged in, otherwise to login page
if (is_logged_in()) {
    header('Location: dashboard.php');
} else {
    header('Location: login.php');
}
exit;
?>