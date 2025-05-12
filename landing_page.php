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
    body { 
      font-family: Arial, sans-serif; 
      background-color: #eef; 
      padding: 2rem;
      position: relative; /* Needed to position logout button */
    }
    .box { 
      background: white; 
      padding: 1rem 2rem; 
      border-radius: 6px; 
      max-width: 600px; 
      margin: auto; 
    }
    /* Styling for the logout button */
    .logout-button {
      position: absolute;
      top: 20px;
      right: 30px;
      background-color: #ff4c4c;
      color: white;
      padding: 10px 20px;
      border: none;
      border-radius: 4px;
      text-decoration: none;
      font-weight: bold;
    }
    .logout-button:hover {
      background-color: #ff0000;
    }
</style>
</head>
<body>

<!-- Logout Button -->
<a href="logout.php" class="logout-button">Log Out</a>

<h1 style="text-align: center;">Welcome to SFY Soccer Management Software</h1>

<?php
// this assigns user level credentials based on the output from database
if (!isset($_SESSION["role_name"]) || !isset($_SESSION["role_password"]))
{
    switch ($role)
    {
        case "Player":
            $_SESSION["role_name"] = Player_ROLE_NAME;
            $_SESSION["role_password"] = Player_ROLE_PASSWORD;
            break;
        case "Coach":
            $_SESSION["role_name"] = COACH_ROLE_NAME;
            $_SESSION["role_password"] = COACH_ROLE_PASSWORD;
            break;
        case "Manager":
            $_SESSION["role_name"] = MANGER_ROLE_NAME;
            $_SESSION["role_password"] = MANGER_ROLE_PASSWORD;
            break;
        default:
            $_SESSION["role_name"] = GUEST_ROLE_NAME;
            $_SESSION["role_password"] = GUEST_ROLE_PASSWORD;
            break;
    }
}
// Only call appropriate role-specific page, which can itself call no_level_content.php inside
if ($role === "Guest") {
    require_once("no_level_content.php"); 
}
elseif ($role === "Player") {
    require_once("player_level_content.php");
}
elseif ($role === "Coach") {
    require_once("coach_level_content.php");
}
elseif ($role === "Manager") {
    require_once("manager_level_content.php");
}
?>   

</body>
</html>
