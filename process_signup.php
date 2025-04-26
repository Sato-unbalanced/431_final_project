<?php
// process_signup.php
// Purpose: Validate input, hash password, insert new user securely.
// Reference- Ch.27 - Secure Registration and Password Handling

session_start();
require_once('Adaptation.php');  // DB constants
require_once('config.php');      // Project-level paths and config

// Error and input preservation arrays
// Purpose- collects validation errors- dispalys register_user.php
// Temporarily save user input so if reload- fields entered by user remain- Ch. 27
$errors = [];
$preserve = [];

// Full error display.Development/debugging (REMOVE later) *****
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Connect to MySQL using shared web credentials (phpWebEngine user in Adaptation.php)
// phpWebEngine is the bridge to the db - separate from roles/users/etc
$db = new mysqli(DATA_BASE_HOST, USER_NAME, USER_PASSWORD, DATA_BASE_NAME);
if ($db->connect_errno !== 0) {
    die("Database connection failed: " . $db->connect_error);
}

// DEBUG - print submitted form fields remove later ***
echo "<pre>"; print_r($_POST); echo "</pre>";

// Sanitize and normalize input
$username = strtolower(trim($_POST['username'] ?? ''));
$name = trim($_POST['name'] ?? '');  
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';
$role = $_POST['role'] ?? '';

// Preserve input for redisplay if errors occur as mentioned above ^^
$preserve['username'] = $username;
$preserve['name'] = $name;
$preserve['email'] = $email;
$preserve['role'] = $role;

// --- Input Validation -------------------------------------------------------

// Required field check
if (empty($username) || empty($email) || empty($password) || empty($confirm_password) || empty($role)) {
    $errors[] = "All fields are required.";
}

if (empty($name)) {
    $errors[] = "Name is required.";
}

// Email format validation (Ch. 27– User input filtering)
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email format.";
}

// Validate password complexity enforcement (Ch. 27-Security best practices)
if (strlen($password) < 8 || !preg_match('/[a-z]/i', $password) || !preg_match('/\d/', $password)) {
    $errors[] = "Password must be at least 8 characters and include both letters and numbers.";
}

// Password confirmation match
if ($password !== $confirm_password) {
    $errors[] = "Passwords do not match.";
}

// Ensure username and email are unique - db
$stmt = $db->prepare("SELECT * FROM UserLogin WHERE UserName = ? OR Email = ?");
$stmt->bind_param('ss', $username, $email);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $errors[] = "Username or email already exists.";
}

// Validate selected role exists in Roles table - db
$stmt = $db->prepare("SELECT ID_Role FROM Roles WHERE roleName = ?");
$stmt->bind_param('s', $role);
$stmt->execute();
$roleResult = $stmt->get_result();
if ($roleResult->num_rows !== 1) {
    $errors[] = "Invalid role selected.";
}

// --- On errors -> redirect back to form with session-stored data ---
if (!empty($errors)) {
    $_SESSION['signup_errors'] = $errors;
    $_SESSION['signup_preserve'] = $preserve;
    header("Location: register_user.php");
    exit;
}

// --- All checks passed: insert new user securely ---------------------------------------------------------------
// Hash password before storing (Ch. 27– password_hash usage)
$roleID = $roleResult->fetch_assoc()['ID_Role'];
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Prepared statements to prevent SQL injection
$insert = $db->prepare("INSERT INTO UserLogin (Name, Email, UserName, Password, Role) VALUES (?, ?, ?, ?, ?)");
$insert->bind_param('ssssi', $name, $email, $username, $hashedPassword, $roleID);

// On success -> clear preserved data and redirect -> signup_success.php page
if ($insert->execute()) {
    unset($_SESSION['signup_preserve']);
    header("Location: signup_success.php");
    exit;
} else {
    // Fail on db error
    die("Database error: " . $db->error);
}
?>
