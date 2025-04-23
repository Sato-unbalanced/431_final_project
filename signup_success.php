<?php
// signup_success.php
// Confirmation screen for after successful registration (Ch. 27)
// Links back to login_form.php
require_once('config.php');   // Loads site-wide config and paths 
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Signup Success</title>
  <style>
    body {
      font-family: Arial;
      text-align: center;
      padding: 5rem;
    }
  </style>
</head>
<body>
  <h2>Account created successfully!!!</h2>
  <p>You may now <a href="login_form.php">log in</a>.</p>
</body>
</html>
