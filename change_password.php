<?php
// change_password.php
// Form where user enters temporary password and sets a new one- POSTS to -> process_password_change.php
// Part of the password reset flow (Ref-Ch. 27-Secure credential recovery) - requires no login to reset password

require_once('config.php');         // Path constants for project
require_once('Adaptation.php');     // DB constants
session_start();                    

// Display flash message if returning from reset_password.php.. implement?
// Clear after first display
$notice = $_SESSION['ResetNotice'] ?? '';
unset($_SESSION['ResetNotice']);  
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Change Password</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      padding: 2rem;
    }
    .form-box {
      max-width: 400px;
      margin: auto;
    }
    .notice {
      color: green;
      font-weight: bold;
      margin-bottom: 1rem;
    }
    input, button {
      width: 100%;
      padding: 0.5rem;
      margin-bottom: 1rem;
    }
  </style>
</head>
<body>
  <div class="form-box">
    <h2>Change Your Password</h2>

    <!-- Notice from session (after reset_password.php completes) -->
    <?php if ($notice): ?>
      <div class="notice"><?= htmlspecialchars($notice) ?></div>
    <?php endif; ?>

    <!-- Form submission goes to the password processor (process_password_change.php) -->
    <form action="process_password_change.php" method="POST">
      <label for="email">Your Registered Email:</label>
      <input type="email" id="email" name="email" required>

      <label for="current_password">Temporary Password:</label>
      <input type="password" id="current_password" name="current_password" required>

      <label for="new_password">New Password:</label>
      <input type="password" id="new_password" name="new_password" required>

      <label for="confirm_new_password">Confirm New Password:</label>
      <input type="password" id="confirm_new_password" name="confirm_new_password" required>

      <button type="submit">Update Password</button>
    </form>

    <p><a href="login_form.php">Return to Login</a></p>
  </div>
</body>
</html>
