<?php
// member.php
// Temporary landing page for testing login/session and role verification
// Replace with the main page later 

require_once('Adaptation.php');
require_once('config.php');
session_start();

if (isset($_GET['guest']) && $_GET['guest'] === 'true') {
  $_SESSION['UserName'] = 'guest';
  $_SESSION['UserRole'] = 'Guest'; 
}

// Check if user is logged in
if (!isset($_SESSION['UserName']) || !isset($_SESSION['UserRole'])) {
    echo "You are not logged in. <a href='login_form.php'>Return to login</a>";
    exit;
}

// Access session values
$user = htmlspecialchars($_SESSION['UserName']);
$role = htmlspecialchars($_SESSION['UserRole']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Member Dashboard</title>
  <!-- basic styling --> 
  <style>
    body { font-family: Arial, sans-serif; background-color: #eef; padding: 2rem; }
    .box { background: white; padding: 1rem 2rem; border-radius: 6px; max-width: 600px; margin: auto; }
  </style>
</head>
<body>
  <div class="box">
    <h2>Welcome, <?php echo $user; ?>!</h2>
    <p>You are logged in as <strong><?php echo $role; ?></strong>.</p>

    <?php if ($role === 'Guest') : ?>
      <p>You are a guest. You may only view player statistics.</p>
    <?php elseif ($role === 'Player') : ?>
      <p>As a player, you can edit your personal information and view all player stats.</p>
    <?php elseif ($role === 'Coach') : ?>
      <p>Coach access. (Functionality to be determined.)</p>
    <?php elseif ($role === 'Manager') : ?>
      <p>Manager access. (Full privileges coming soon.)</p>
    <?php endif; ?>

    <!-- logout --> 
    <p><a href="logout.php">Log out</a></p>
  </div>
</body>
</html>
