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
      background-color: #d9f7db;
      display: flex;
      flex-direction: column;
      align-items: center;
      padding-top: 5vh;
    }

    .login-box, .reset-box {
      background: white;
      padding: 2rem;
      border: 1px solid #ccc;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
      width: 400px;
      margin-bottom: 2rem;
    }

    h2 {
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

    .reset-box p {
      margin-bottom: 1rem;
      font-size: 0.95rem;
      color: #333;
      text-align: center;
    }

    .reset-box button {
      background-color: #4caf50;
      color: white;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }

    .reset-box button:hover {
      background-color: #43a047;
    }
  </style>
</head>
<body>

  <!-- Login Form Container -->
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

      <!-- Button options - Login, go to register form, or continue as Guest -->
      <div class="button-group">
        <button type="submit">Login</button>
        <button type="button" onclick="location.href='register_user.php'">Register New Account</button>
        <!-- change guest page from member.php to landing_page.php --> 
        <button type="button" onclick="location.href='landing_page.php?guest=true'">Continue as Guest</button>
      </div>
    </form>
  </div>

  <!-- Password Reset Section -->
  <div class="reset-box">
    <h2>Forgot your password?<br>Want to change it?</h2>
    <p><strong>Reset your password here:</strong></p>
    <button onclick="location.href='reset_password.php'">Reset Password</button>
  </div>

</body>
</html>
