<?php
// handle_login.php
// Handles login logic: receives POST from login_form.php and starts session if valid
// References- secure authentication practices (Ch. 16 & 27)

require_once('Adaptation.php');  // DB config and connection logic
require_once('config.php');      // Load the project paths
session_start();                 // Start session for storing login state (Ref-Ch. 16)

// M Connect to the DB manually (since $db is not set elsewhere- resolve with assigning roles at db level?)
$db = new mysqli(DATA_BASE_HOST, USER_NAME, USER_PASSWORD, DATA_BASE_NAME);
if ($db->connect_errno !== 0) {
    die("Database connection failed: " . $db->connect_error);
}

// Sanitize user input to normalize- avoid injection to be specific
$username = strtolower(trim($_POST['username'] ?? ''));
$password = $_POST['password'] ?? '';
$role     = $_POST['role'] ?? '';

// Basic validation checks -- might need to make them more specific over time if wanted
if (empty($username) || empty($password) || empty($role)) {
    echo "All fields are required. <a href='login_form.php'>Try again</a>.";
    exit;
}

// Query to validate username and match with selected role
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

// Prepare and bind parameters securely - Prepared statement prevents SQL injection (Ref-Ch. 27)
if (($stmt = $db->prepare($query)) === false) {
    echo "Error: Failed to prepare query: " . $db->error;
    exit;
}

if ($stmt->bind_param("ss", $username, $role) === false) {
    echo "Error: Failed to bind parameters: " . $db->error;
    exit;
}

// Execute query and validate a single matching user - fetch the result
if (!($stmt->execute() && $stmt->store_result() && $stmt->num_rows === 1)) {
    echo "Login failed. Invalid username/role combination. <a href='login_form.php'>Try again</a>.";
    exit;
}

$stmt->bind_result($roleName, $passwordHash);
$stmt->fetch();

// Password verification using hash (Ref-Ch. 27-hashed passwords)
if (!password_verify($password, $passwordHash)) {
    echo "Incorrect password. <a href='login_form.php'>Try again</a>.";
    exit;
}

// Check if user is using a temporary password from: reset password flow
// Check ddl for IsTemp from UserLogin table for configuration
$stmt2 = $db->prepare("SELECT IsTempPassword FROM UserLogin WHERE UserName = ?");
$stmt2->bind_param("s", $username);
$stmt2->execute();
$tempResult = $stmt2->get_result();
$tempRow = $tempResult->fetch_assoc();

if ($tempRow && $tempRow['IsTempPassword']) {
    // Temporarily flag session for password change enforcement (Ch. 27 alt flow)
    // Optional flag if needed later 
    $_SESSION['ForcePasswordChange'] = true;
    header("Location: change_password.php");
    exit;
}

// Login successful. Save credentials in session for role-based access (Ref-Ch. 16)
$_SESSION['UserName'] = $username;
$_SESSION['UserRole'] = $roleName;

// Redirect to member landing page (eventually swap this with actual home page)
header("Location: landing_page.php"); //previously member.php
exit;
?>
