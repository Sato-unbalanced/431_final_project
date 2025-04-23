<?php
// reset_password.php - Handles password reset request
//***** IMPORTANT: Needs to be linked to from member page? need to find where to send the user to this page to reset from one of the other pages
// resetting password flow: reset_password.php -> change_password.php -> process_password_change.php -> password_change_success.php
// validate email -> checks DB if email is there -> Generates a secure temporary password and emails it to user (Ch. 27) 
// hashes temp pass and updates DB with temp pass for user -> sets isTempPassword = 1 which lets the 
// system know to send them to change_password.php/able to change passowrd in process_password_change.php
// I have 'sendmail' configured to be able to send emails through mail()
require_once('config.php');         // Load project paths/configuration
require_once('Adaptation.php');     // DB connection constants
session_start();                    // Required for $_SESSION messaging

// Enable debugging messages (remove later)*******
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// If the form submitted...
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? '');

    //Validate email input format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        //Connect to DB 
        $db = new mysqli(DATA_BASE_HOST, USER_NAME, USER_PASSWORD, DATA_BASE_NAME);
        if ($db->connect_errno !== 0) {
            die("Database connection failed: " . $db->connect_error);
        }

        // Check if email exists in UserLogin - db
        $stmt = $db->prepare("SELECT ID, UserName FROM UserLogin WHERE Email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        // If no account matches
        if ($result->num_rows !== 1) {
            $error = "No account found with that email.";
        } else {
            $row = $result->fetch_assoc();
            $userId = $row['ID'];
            $username = $row['UserName'];

            // else - Generate secure 8-character temp password (Ch. 27)
            // bin2hex() and password_hash() it
            $newPassword = bin2hex(random_bytes(4));
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT); // Store hashed

            // Update DB with new password and flag as temporary
            $update = $db->prepare("UPDATE UserLogin SET Password = ?, IsTempPassword = 1 WHERE ID = ?");
            $update->bind_param("si", $hashedPassword, $userId);
            $update->execute();

            if ($update->affected_rows === 1) {
                // Prepare and send email to user
                // given your mail() config - might need to update the 'From:' somehow to make it official
                $subject = "Your SportsTeam Temporary Password";
                $message = "Hello $username,\n\nYour password has been reset.\n\nTemporary password: $newPassword\n\nPlease log in and change it immediately.";
                $headers = "From: no-reply@sportsteam.local\r\n";
                $headers .= "Reply-To: no-reply@sportsteam.local\r\n";

                if (mail($email, $subject, $message, $headers)) {
                    // Store notice in session and redirect to change password form
                    $_SESSION['ResetNotice'] = "A temporary password has been sent to your email.";
                    $_SESSION['UserNameReset'] = $username;  // Optional for later? display in form 
                    header("Location: change_password.php");
                    exit;
                } else {
                    $error = "Failed to send email. Please contact support.";
                }
            } else {
                $error = "Failed to update password in database.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reset Password</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      padding: 2rem;
      background-color: #f4f4f4;
    }
    .container {
      background: white;
      max-width: 400px;
      margin: 2rem auto;
      padding: 2rem;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    input[type="email"], button {
      width: 100%;
      padding: 0.75rem;
      margin-bottom: 1rem;
    }
    .message {
      font-weight: bold;
      margin-bottom: 1rem;
      color: green;
    }
    .error {
      font-weight: bold;
      color: red;
      margin-bottom: 1rem;
    }
  </style>
</head>
<body>
<div class="container">
    <h2>Reset Your Password</h2>

    <?php if (!empty($error)): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
      <label for="email">Enter your registered email:</label>
      <input type="email" name="email" id="email" required>
      <button type="submit">Send Temporary Password</button>
    </form>
</div>
</body>
</html>