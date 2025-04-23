<?php 
// login_form.php
// Displays login form for registered users or allows guests to continue. 
// Sends login data to handle_login.php -> session verified -> user redirected to member.php
// login_form.php -> handle_login.php -> member.php (eventually the roster/team page)
// References: Ch. 16 (Session/Role control), Ch. 27 (Secure login flows)
  require_once('config.php');       // Loads the defined project paths
  require_once('Adaptation.php');   // Connects to DB using guest creds (or others if logged in)
  session_start();                  // Starts/resumes session for login tracking
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Sports Team Login</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f4f4f4;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }
    .login-box {
      background: white;
      padding: 2rem;
      border: 1px solid #ccc;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    .login-box h2 {
      text-align: center;
    }
    .form-group {
      margin-bottom: 1rem;
    }
    label {
      display: block;
      margin-bottom: 0.5rem;
    }
    input, select, button {
      width: 100%;
      padding: 0.5rem;
    }
    .button-group {
      display: flex;
      flex-direction: column;
      gap: 0.5rem;
    }
  </style>
</head>
<body>
  <div class="login-box">
    <h2>Login</h2>

    <!-- Login sent to handle_login.php via POST -->
    <form action="handle_login.php" method="POST">
      <div class="form-group">
        <label for="username">Username:</label>
        <input type="text" name="username" id="username" required maxlength="100">
      </div>

      <div class="form-group">
        <label for="password">Password:</label>
        <input type="password" name="password" id="password" required minlength="6" maxlength="16">
      </div>

      <!-- Role dropdown corresponds/works with the role table in DB (ID_Role) -->
      <div class="form-group">
        <label for="role">Role:</label>
        <select name="role" id="role" required>
          <option value="" disabled selected>Select role</option>
          <option value="Player">Player</option>
          <option value="Coach">Coach</option>
          <option value="Manager">Manager</option>
        </select>
      </div>

      <!-- Button optons-  Login, go to register form, or continue as Guest -->
      <div class="button-group">
        <button type="submit">Login</button>
        <button type="button" onclick="location.href='register_user.php'">Register New Account</button>
        <button type="button" onclick="location.href='member.php?guest=true'">Continue as Guest</button>
      </div>
    </form>
  </div>
</body>
</html>
