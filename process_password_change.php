<?php
// process_password_change.php
// Securely verifies temporary password and updates with a new one
// Part of the no-login reset flow- Ch. 27 && prof. requirements (alternative secure reset)

// Load DB constants and project paths
require_once('config.php');
require_once('Adaptation.php');
session_start();

// Establish DB connection (phpWebEngine user)
$db = new mysqli(DATA_BASE_HOST, USER_NAME, USER_PASSWORD, DATA_BASE_NAME);
if ($db->connect_errno !== 0) {
    die("Database connection failed: " . $db->connect_error);
}

// Get form inputs
$email           = trim($_POST['email'] ?? '');
$tempPassword    = $_POST['current_password'] ?? '';
$newPassword     = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_new_password'] ?? '';

$errors = [];

// --- Input validation (Ch. 27-User input integrity) --------------------------------
// Validate input
if (empty($email) || empty($tempPassword) || empty($newPassword) || empty($confirmPassword)) {
    $errors[] = "All fields are required.";
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email format.";
}
if ($newPassword !== $confirmPassword) {
    $errors[] = "New passwords do not match.";
}
if (strlen($newPassword) < 8 || !preg_match('/[a-z]/i', $newPassword) || !preg_match('/\d/', $newPassword)) {
    $errors[] = "New password must be at least 8 characters long and contain both letters and numbers.";
}

if (!empty($errors)) {
    foreach ($errors as $error) {
        echo "<p>$error</p>";
    }
    echo "<p><a href='change_password.php'>Go back</a></p>";
    exit;
}

// --- Step 1- Look up user and ensure IsTempPassword is flagged --- db (IsTemp...)
// Ensures that we resetting only accounts in the temp-reset state 
/* 
IsTempPassword from Db- boolean value tracks whether a use is currently in a reset state.
Used to verfiy that a reset is pending,and restrict unauthorized resets. Allows an update of passwords
in our DB
*/
$stmt = $db->prepare("SELECT Password FROM UserLogin WHERE Email = ? AND IsTempPassword = 1");
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    // Either email is wrong / no reset was requested
    echo "Invalid email or no password reset requested. <a href='change_password.php'>Try again</a>.";
    exit;
}

$row = $result->fetch_assoc();
$storedHash = $row['Password'];

// --- Step 2- Verify the temporary password emailed to the user ---
if (!password_verify($tempPassword, $storedHash)) {
    echo "Incorrect temporary password. <a href='change_password.php'>Try again</a>.";
    exit;
}

// --- Step 3- RE-Hash and update with new password. Clear IsTempPassword flag ---
// REset to normal user state 
// Double check DB tables 
$newHash = password_hash($newPassword, PASSWORD_DEFAULT);
$update = $db->prepare("UPDATE UserLogin SET Password = ?, IsTempPassword = 0 WHERE Email = ?");
$update->bind_param('ss', $newHash, $email);

if ($update->execute()) {
    // Password updated and IsTempPassword flag cleared — user now has normal credentials again
    header("Location: password_change_success.php");
    exit;
} else {
    echo "Failed to update password. Try again.";
    exit;
}
?>
