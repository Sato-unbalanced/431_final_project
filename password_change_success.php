<?php
// password_change_success.php
// Final confirmation screen after successful password reset/ update (Ref-Ch. 27-Password Management)
require_once('config.php'); // Project path constants
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Password Updated</title>

  <!-- Automatically redirect user to login_form.php after 4 seconds 
   stylistic choice, can be changed if we want-->
  <!-- Ch. 27-Quick return to login flow -->
  <meta http-equiv="refresh" content="4;url=login_form.php">

  <style>
    body {
      font-family: Arial, sans-serif;
      text-align: center;
      padding: 5rem;
      background-color: #f2f2f2;
    }
    .container {
      background: white;
      padding: 2rem;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
      display: inline-block;
    }
    a {
      display: inline-block;
      margin-top: 1rem;
      text-decoration: none;
      color: #0066cc;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Your password has been successfully updated!</h2>

    <p>You may now <a href="login_form.php">log in</a> with your new password.</p>

    <p>You will be redirected shortly...</p>
  </div>
</body>
</html>
