<?php
// handle_login.php
// Handles login logic: receives POST from login_form.php and starts session if valid

require_once('Adaptation.php');  // DB config and connection logic
require_once('config.php');      // Load the project paths
session_start();                 // $_SESSION

// Sanitize and normalize input
$username = strtolower(trim($_POST['username'] ?? ''));
$password = $_POST['password'] ?? '';
$role     = $_POST['role'] ?? '';

// Basic validation checks -- might need to make them more specific over time 
if (empty($username) || empty($password) || empty($role)) {
    echo "All fields are required. <a href='login_form.php'>Try again</a>.";
    exit;
}

// SQL query to find user by username and role
$query = "
    SELECT 
        Roles.roleName, UserLogin.Password 
    FROM 
        UserLogin 
    JOIN 
        Roles ON UserLogin.Role = Roles.ID_Role 
    WHERE 
        UserLogin.UserName = ? 
        AND Roles.roleName = ?
";

// Prepare and bind parameters securely
if (($stmt = $db->prepare($query)) === false) {
    echo "Error: Failed to prepare query: " . $db->error;
    exit;
}

if ($stmt->bind_param("ss", $username, $role) === false) {
    echo "Error: Failed to bind parameters: " . $db->error;
    exit;
}

// Execute and fetch result
if (!($stmt->execute() && $stmt->store_result() && $stmt->num_rows === 1)) {
    echo "Login failed. Invalid username/role combination. <a href='login_form.php'>Try again</a>.";
    exit;
}

$stmt->bind_result($roleName, $passwordHash);
$stmt->fetch();

// Verify password against stored hash
if (!password_verify($password, $passwordHash)) {
    echo "Incorrect password. <a href='login_form.php'>Try again</a>.";
    exit;
}

// Login successful: store session variables
$_SESSION['UserName'] = $username;
$_SESSION['UserRole'] = $roleName;

// Redirect to member landing page (eventually swap this with actual home page)
header("Location: member.php");
exit;
?>
