<?php
// register_user.php
// Registration form – Secure input collection. Reference Chapter 27

require_once('config.php');
require_once('Adaptation.php');
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register New Account</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f4f4f4;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }
    .register-box {
      background: white;
      padding: 2rem;
      border: 1px solid #ccc;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    .register-box h2 {
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
    .error {
      color: red;
      font-size: 0.9em;
    }
  </style>
</head>
<body>
  <div class="register-box">
    <h2>Create an Account</h2>
    <form id="registerForm" action="process_signup.php" method="POST">
      <div class="form-group">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>
        <div id="usernameError" class="error"></div>
      </div>

      <div class="form-group">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>
        <div id="emailError" class="error"></div>
      </div>

      <div class="form-group">
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
      </div>

      <div class="form-group">
        <label for="password_confirmation">Confirm Password:</label>
        <input type="password" id="password_confirmation" name="password_confirmation" required>
      </div>

      <div class="form-group">
        <label for="role">Role:</label>
        <select name="role" id="role" required>
          <option value="" disabled selected>Select role</option>
          <option value="Player">Player</option>
          <option value="Coach">Coach</option>
          <option value="Manager">Manager</option>
        </select>
      </div>

      <button type="submit">Register</button>
    </form>
  </div>

  <script>
    // Inline JS for validating email and username
    document.getElementById("username").addEventListener("blur", function () {
      const username = this.value;
      fetch(`validate_email_username.php?field=username&value=${encodeURIComponent(username)}`)
        .then(response => response.json())
        .then(data => {
          const errorBox = document.getElementById("usernameError");
          errorBox.textContent = data.available ? "" : "Username is already taken.";
        });
    });

    document.getElementById("email").addEventListener("blur", function () {
      const email = this.value;
      fetch(`validate_email_username.php?field=email&value=${encodeURIComponent(email)}`)
        .then(response => response.json())
        .then(data => {
          const errorBox = document.getElementById("emailError");
          errorBox.textContent = data.available ? "" : "Email is already registered.";
        });
    });
  </script>
</body>
</html>

<!-- Can also validate the fields using the stuff below VV if we decide no js 
<body>
  <div class="register-box">
    <h2>Register New Account</h2>
    <form action="process_signup.php" method="POST">
      <div class="form-group">
        <label for="username">Username (Required, Unique):</label>
        <input type="text" name="username" id="username" required maxlength="100">
      </div>

      <div class="form-group">
        <label for="email">Email (Required, Unique):</label>
        <input type="email" name="email" id="email" required maxlength="255">
      </div>

      <div class="form-group">
        <label for="role">Role:</label>
        <select name="role" id="role" required>
          <option value="" disabled selected>Select role</option>
          <option value="Player">Player</option>
          <option value="Coach">Coach</option>
          <option value="Manager">Manager</option>
        </select>
      </div>

      <div class="form-group">
        <label for="password">Password (Min 8 chars, include a letter and number):</label>
        <input type="password" name="password" id="password" required minlength="8">
      </div>

      <div class="form-group">
        <label for="confirm_password">Confirm Password:</label>
        <input type="password" name="confirm_password" id="confirm_password" required minlength="8">
      </div>

      <button type="submit">Create Account</button>
    </form>
    <p style="margin-top:1rem;"><a href="login_form.php">Back to login</a></p>
  </div>
</body>
  --> 

