<?php
require_once('config.php');
require_once('Adaptation.php');
session_start();

$preserve = $_SESSION['signup_preserve'] ?? [];
$errors = $_SESSION['signup_errors'] ?? [];

unset($_SESSION['signup_preserve']);
unset($_SESSION['signup_errors']);
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
      width: 400px;
    }
    h2 {
      text-align: center;
    }
    .form-group {
      margin-bottom: 1rem;
    }
    label {
      display: block;
      margin-bottom: 0.25rem;
    }
    input, select, button {
      width: 100%;
      padding: 0.5rem;
    }
    .error {
      color: red;
      font-size: 0.9em;
      margin-bottom: 0.5rem;
    }
  </style>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const roleSelect = document.getElementById('role');
      const idField = document.getElementById('idField');
      const idLabel = document.getElementById('idLabel');
      const idInput = document.getElementById('id');

      function toggleIdField() {
        const role = roleSelect.value;
        if (role === 'Player') {
          idField.style.display = 'block';
          idLabel.textContent = 'Enter Player ID (must exist in system):';
          idInput.required = true;
        } else if (role === 'Coach') {
          idField.style.display = 'block';
          idLabel.textContent = 'Enter Coach ID (must exist in system):';
          idInput.required = true;
        } else {
          idField.style.display = 'none';
          idLabel.textContent = '';
          idInput.required = false;
        }
      }

      roleSelect.addEventListener('change', toggleIdField);
      toggleIdField(); // Initial check on page load
    });
  </script>
</head>

<body>
  <div class="register-box">
    <h2>Create an Account</h2>

    <?php if (!empty($errors)): ?>
      <div class="error">
        <ul>
          <?php foreach ($errors as $e): ?>
            <li><?= htmlspecialchars($e) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form action="process_signup.php" method="POST">
      <div class="form-group">
        <label for="username">Username (Required, Unique):</label>
        <input type="text" id="username" name="username" required
               value="<?= htmlspecialchars($preserve['username'] ?? '') ?>">
      </div>

      <div class="form-group">
        <label for="name">First and Last Name:</label>
        <input type="text" id="name" name="name" required
               value="<?= htmlspecialchars($preserve['name'] ?? '') ?>">
      </div>

      <div class="form-group">
        <label for="email">Email (Required, Unique):</label>
        <input type="email" id="email" name="email" required
               value="<?= htmlspecialchars($preserve['email'] ?? '') ?>">
      </div>

      <div class="form-group">
        <label for="password">Password (Min 8 chars, letter + number):</label>
        <input type="password" id="password" name="password" required>
      </div>

      <div class="form-group">
        <label for="confirm_password">Confirm Password:</label>
        <input type="password" id="confirm_password" name="confirm_password" required>
      </div>

      <div class="form-group">
        <label for="role">Role:</label>
        <select name="role" id="role" required>
          <option value="" disabled <?= !isset($preserve['role']) ? 'selected' : '' ?>>Select role</option>
          <option value="Player" <?= ($preserve['role'] ?? '') === 'Player' ? 'selected' : '' ?>>Player</option>
          <option value="Coach"  <?= ($preserve['role'] ?? '') === 'Coach'  ? 'selected' : '' ?>>Coach</option>
          <option value="Manager"<?= ($preserve['role'] ?? '') === 'Manager'? 'selected' : '' ?>>Manager</option>
        </select>
      </div>

      <div class="form-group" id="idField">
        <label for="id" id="idLabel"></label>
        <input type="number" id="id" name="id"
               value="<?= htmlspecialchars($preserve['id'] ?? '') ?>">
      </div>

      <button type="submit">Register</button>
      <p style="text-align:center; margin-top:1rem;">
        <a href="login_form.php">Back to login</a>
      </p>
    </form>
  </div>
</body>
</html>
