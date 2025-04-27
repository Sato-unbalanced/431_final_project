
<?php
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
<style>
    body { font-family: Arial, sans-serif; background-color: #eef; padding: 2rem; }
    .box { background: white; padding: 1rem 2rem; border-radius: 6px; max-width: 600px; margin: auto; }
  </style>
</head>
<body>
<h1 style="text-align: center;">Welcome to SFY Soccer Management Software</h1>
<?php
    require_once("no_level_content.php");
    if($role !== "Guest")
    {
        require_once("player_level_content.php");
        if($role !== "Player")
        {
            require_once("coach_level_content.php");
            if ($role !== "Coach")
            {
                require_once("manager_level_content.php");
            }
        }
    }
?>   
<p><a href="logout.php">Log out</a></p>
</body>
</html>
