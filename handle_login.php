<?php
// handle_login.php
// Handles login logic: receives POST from login_form.php and starts session if valid
// References- secure authentication practices (Ch. 16 & 27)

require_once('Adaptation.php');  // DB config and connection logic
require_once('config.php');      // Load the project paths
session_start();                 // Start session for storing login state (Ref-Ch. 16)

// Connect to the DB
$db = new mysqli(DATA_BASE_HOST, USER_NAME, USER_PASSWORD, DATA_BASE_NAME);
if ($db->connect_errno !== 0) {
    die("Database connection failed: " . $db->connect_error);
}

// Sanitize user input
$username = strtolower(trim($_POST['username'] ?? ''));
$password = $_POST['password'] ?? '';

// Basic validation checks
if (empty($username) || empty($password)) {
    echo "Username and password are required. <a href='login_form.php'>Try again</a>.";
    exit;
}

// Query to validate username and fetch password hash and role name
$query = "
    SELECT 
        Roles.roleName, UserLogin.Password 
    FROM 
        UserLogin 
    JOIN 
        Roles ON UserLogin.Role = Roles.ID_Role 
    WHERE 
        UserLogin.UserName = ?
";

// Prepare and bind parameters securely
if (($stmt = $db->prepare($query)) === false) {
    echo "Error: Failed to prepare query: " . $db->error;
    exit;
}

if ($stmt->bind_param("s", $username) === false) {
    echo "Error: Failed to bind parameters: " . $db->error;
    exit;
}

// Execute query
if (!($stmt->execute() && $stmt->store_result())) {
    echo "Error during login attempt. Please try again later.";
    exit;
}

// Check if exactly one user was found
if ($stmt->num_rows !== 1) {
    echo "Login failed. Invalid username. <a href='login_form.php'>Try again</a>.";
    exit;
}

$stmt->bind_result($roleName, $passwordHash);
$stmt->fetch();

// Verify password
if (!password_verify($password, $passwordHash)) {
    echo "Incorrect password. <a href='login_form.php'>Try again</a>.";
    exit;
}

// Check if user must change temp password
$stmt2 = $db->prepare("SELECT IsTempPassword FROM UserLogin WHERE UserName = ?");
$stmt2->bind_param("s", $username);
$stmt2->execute();
$tempResult = $stmt2->get_result();
$tempRow = $tempResult->fetch_assoc();

if ($tempRow && $tempRow['IsTempPassword']) {
    $_SESSION['ForcePasswordChange'] = true;
    header("Location: change_password.php");
    exit;
}

// Successful login
$_SESSION['UserName'] = $username;
$_SESSION['UserRole'] = $roleName;

// Redirect to landing page
header("Location: landing_page.php");
exit;
?>
