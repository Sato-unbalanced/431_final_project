<?php
// Adaptation.php
// Refer to Chapter 16, 22, 27 -Secure database access and session-based authentication
// Core connection values
define('DATA_BASE_NAME', 'SportsTeam');
define('DATA_BASE_HOST', 'localhost');
//Apache webserver values - from prior assignments 
define('USER_NAME',      'phpWebEngine');
define('USER_PASSWORD',  '!_phpWebEngine');



// THROW ALL OF THIS VV into another file in the future. 

// Guest = default role
// NOTE: HW3 I got marked down some points for "No database users created and privileges 
// for those users not enforced at the DB level"

//Purpose of this file now is to create those MySQL users, give priverleges
//Adapation.php connects to MYSQL as the current sessions role, $_SESSION['UserRole'] maps to MySQL username
//Enforces access control in MySQL not just php - chapter 16 + 27


/* 
define('NO_ROLE', 'Guest');

// Mapping of roles to corresponding DB users 
// Match these roles in the DB 
$DBPasswords = [
  'Guest'   => 'Password0',  // View-only access
  'Player'  => 'Password1',  // Can edit personal info
  'Coach'   => 'Password2',  // TBD privileges
  'Manager' => 'Password3'   // Full access
];

// Function to verify an active authenticated session
// Check if session has a valid user and valid role
function authenticatedUser() {
  global $DBPasswords;
  return isset($_SESSION['UserName']) &&
         isset($_SESSION['UserRole']) &&
         array_key_exists($_SESSION['UserRole'], $DBPasswords);
}

// Determine which DB credentials to use (based on session or default guest)
$DBUser     = authenticatedUser() ? $_SESSION['UserRole'] : NO_ROLE;
$DBPassword = $DBPasswords[$DBUser];

// Connect to the MySQL database using the assigned creds
$db = new mysqli(DATA_BASE_HOST, $DBUser, $DBPassword, DATA_BASE_NAME);

// Error handling on failed connection
if ($db->connect_errno !== 0) {
  echo "Failed to connect to the database: " . $db->connect_error . "<br/>";
  exit;
}
?>
*/
