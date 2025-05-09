<?php
// homepage.php - Home page for the Soccer Management App
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Welcome to SoccerBase DB</title>
  <style>
    body {
      /* pulled a checkboard pattern */
      font-family: Arial, sans-serif;
      background: 
        repeating-linear-gradient(
          45deg,
          #4caf50,
          #4caf50 40px,
          #388e3c 40px,
          #388e3c 80px
        ),
        repeating-linear-gradient(
          -45deg,
          #4caf50,
          #4caf50 40px,
          #388e3c 40px,
          #388e3c 80px
        );
      background-blend-mode: multiply;
      color: white;
      text-align: center;
      padding: 5rem;
      min-height: 100vh;
    }

    .container {
    background-color: #d9f7db;         /* pastel green-#d9f7db */
    border: 2px solid white;             /* white border */
    border-radius: 12px;
    padding: 2rem 3rem;
    max-width: 600px;
    margin: auto;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
  }

  h1 {
    font-size: 2.5rem;
    color: #2e7d32;
    margin-bottom: 1rem;
  }

  p {
    font-size: 1.1rem;
    color: #333;
    margin: 0.5rem 0;
  }

    a.login-btn {
    display: inline-block;
    margin-top: 1.5rem;
    padding: 0.75rem 1.5rem;
    background-color: white;
    color: #2e7d32;
    font-weight: bold;
    border: 2px solid #2e7d32;
    border-radius: 8px;
    text-decoration: none;
    font-size: 1.1rem;
    transition: background-color 0.2s ease-in-out;
  }

  a.login-btn:hover {
    background-color: #c8e6c9;
  }
  </style>
</head>
<body>
  <div class="container">
    <h1>⚽ Welcome to SoccerDB ⚽</h1>
    <p>Hello! Log on to the dashboard for viewing games or match statistics!</p>
    <p>If you are a player or coach, login to manage your team and players.</p>
    <p>Continue on as a guest to view the schedule and match results. </p>
    <a class="login-btn" href="login_form.php">Log In</a>
  </div>
</body>

</html>
