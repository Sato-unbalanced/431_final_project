<?php
session_start();
session_unset();
session_destroy();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Logged Out</title>
    <!-- basic styling for now, aesthetics later -->
  <style>
    body { font-family: Arial; background-color: #f9f9f9; text-align: center; padding-top: 100px; }
    .message-box { display: inline-block; background: white; padding: 2rem; border: 1px solid #ccc; border-radius: 10px; }
    a { color: blue; text-decoration: underline; }
  </style>
</head>
<body>
  <div class="message-box">
    <h2>You’ve been logged out.</h2>
    <p>Thank you for using the Soccer portal!</p>
    <p>See you next time. </p>
    <p><a href="login_form.php">Log back in</a></p>
  </div>
</body>
</html>
