<?php
// Adaptation.php
// Refer to Chapter 16, 22, 27 -Secure database access and session-based authentication
// Core connection values
define('DATA_BASE_NAME', 'SportsTeam');
define('DATA_BASE_HOST', 'localhost');
//Apache webserver values - from prior assignments 
define('USER_NAME',      'phpWebEngine');
define('USER_PASSWORD',  '!_phpWebEngine');


/*

Need to set up DDL first before utilizing VVVV

// Define fallback/default role for unauthenticated users
// Guest = default role
define('NO_ROLE', 'Guest');

// Mapping of roles to corresponding DB users 
// Match these roles in the DB 
$DBPasswords = [
  'Guest'    => 'Password0',  // SELECT-only access
  'Player'   => 'Password1',
  'Coach'    => 'Password2',
  'Manager'  => 'Password3'
];

// Check if session has a valid user and valid role
function authenticatedUser() {
  global $DBPasswords;
  return isset($_SESSION['UserName']) &&
         isset($_SESSION['UserRole']) &&
         isset($DBPasswords[$_SESSION['UserRole']]);
}

// Determine what user to connect to the DB as
$DBUser = authenticatedUser() ? $_SESSION['UserRole'] : NO_ROLE;
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
