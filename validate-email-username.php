<?php
require_once("Adaptation.php"); // Assumes connection to DB via mysqli $db
// Can also receive the GET requestions from url bad such as?email=test@example.com
// or ?username=testuser to check availability
header("Content-Type: application/json");

$response = ["available" => false];

if (isset($_GET["email"])) {
    $email = $db->real_escape_string($_GET["email"]);
    $query = "SELECT * FROM UserLogin WHERE Email = '$email'";
    $result = $db->query($query);
    $response["available"] = $result->num_rows === 0;
}
elseif (isset($_GET["username"])) {
    $username = $db->real_escape_string($_GET["username"]);
    $query = "SELECT * FROM UserLogin WHERE UserName = '$username'";
    $result = $db->query($query); 
    $response["available"] = $result->num_rows === 0;
}

echo json_encode($response);
