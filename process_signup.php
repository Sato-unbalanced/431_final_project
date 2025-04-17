<?php
// process_signup.php
// Validate input, hash password, insert user
// Reference Ch. 27- secure registration

require_once('Adaptation.php');
session_start();

// --- Basic field validation ---
if (empty($_POST['username']) || empty($_POST['email']) || empty($_POST['password']) || empty($_POST['confirm_password']) || empty($_POST['role'])) {
    die("All fields are required.");
}

$username = strtolower(trim($_POST['username']));
$email = trim($_POST['email']);
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];
$role = $_POST['role'];

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Invalid email address.");
}

// Validate password rules
if (strlen($password) < 8 || !preg_match('/[a-z]/i', $password) || !preg_match('/\d/', $password)) {
    die("Password must be at least 8 characters long and include at least one letter and one number.");
}

if ($password !== $confirm_password) {
    die("Passwords do not match.");
}

// Hash password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Check uniqueness 
$queryCheck = $db->prepare("SELECT * FROM UserLogin WHERE UserName = ? OR Email = ?");
$queryCheck->bind_param('ss', $username, $email);
$queryCheck->execute();
$result = $queryCheck->get_result();

if ($result->num_rows > 0) {
    die("Username or email already exists.");
}

// Look up role ID
$queryRole = $db->prepare("SELECT ID_Role FROM Roles WHERE roleName = ?");
$queryRole->bind_param('s', $role);
$queryRole->execute();
$roleResult = $queryRole->get_result();

if ($roleResult->num_rows !== 1) {
    die("Selected role is invalid.");
}
$roleRow = $roleResult->fetch_assoc();
$roleID = $roleRow['ID_Role'];

// Insert new user
$queryInsert = $db->prepare("INSERT INTO UserLogin (Name_First, Name_Last, Email, UserName, Password, Role) VALUES ('First', 'Last', ?, ?, ?, ?)");
$queryInsert->bind_param('sssi', $email, $username, $hashedPassword, $roleID);

if ($queryInsert->execute()) {
    header("Location: signup_success.php");
    exit;
} else {
    die("Error inserting user: " . $db->error);
}
?>
