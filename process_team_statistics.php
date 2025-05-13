<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

require_once('config.php');
require_once('Adaptation.php');

if (isset($_GET['guest']) && $_GET['guest'] === 'true') {
  $_SESSION['UserName'] = 'guest';
  $_SESSION['UserRole'] = 'Guest'; 
}

// Check if user is logged in
// if (!isset($_SESSION['UserName']) || !isset($_SESSION['UserRole'])) {
//     echo "You are not logged in. <a href='login_form.php'>Return to login</a>";
//     exit;
// }

// Access session values
$role = htmlspecialchars($_SESSION['UserRole']);

$database_username = $_SESSION['role_name'];
$database_password = $_SESSION['role_password'];

@$db = new mysqli(DATA_BASE_HOST, $database_username, $database_password, DATA_BASE_NAME);

$select_type = $_GET["select_type"];
$stats_type = $_GET["satistic_type"];
require_once("landing_page.php");

$coaches = isset($_GET['stat_coach']) ? $_GET['stat_coach'] : null;
if(isset($coaches) && isset($select_type) && isset($stats_type))
{
    $limit = isset($_GET['num']) ? $_GET['num'] : 1;    
    //value provides should always be an integer since the input type is number

    if( (int)$limit == 1)
    {
        $query="";
        
        if($select_type == "played")
        {
            $query = "SELECT Team.ID, Team.name AS team_name, COUNT(Game.ID) AS number_of_games_played FROM Team LEFT JOIN Game ON (HomeTeam = Team.ID OR AwayTeam = Team.ID) GROUP BY Team.ID ORDER BY number_of_games_played";
        }
        elseif($select_type == "won")
        {
            $query = "SELECT Team.ID, Team.name AS team_name, COUNT(Game.ID) AS number_of_games_won FROM Team LEFT JOIN Game ON (HomeTeam = Team.ID AND HomeScore > AwayScore) OR (AwayTeam = Team.ID AND AwayScore > HomeScore ) GROUP BY Team.ID ORDER BY number_of_games_won"; 
        }

        
        $query .= ($stats_type == "most")? " DESC": " ASC";
        // there is no way to bind_param for limit so this was the only way to get it to work
        $query .= " LIMIT 1;";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($team_id, $team_name, $count);

        $stmt->fetch();
        echo "<table> <tr><th>Team Name</th><th>Count</th></tr>";
        echo "<tr>";
        echo "<td style=' border: 1px solid #ccc; padding: 0.5rem; text-align: center; '>".$team_name."</td>";
        echo "<td style='border: 1px solid #ccc; padding: 0.5rem; text-align: center; '>".$count."</td>";
        echo "</tr>";
        echo "</table>";

        $query = "SELECT FirstName, LastName FROM Coach WHERE TeamID = ?";
        $stmt_1 = $db->prepare($query);
        $stmt_1->bind_param('i', (int)$team_id );
        $stmt_1->execute();
        $stmt_1->store_result();
        $stmt_1->bind_result($coach_first_name, $coach_last_name);
        echo "<table> <tr><th>Coach Names</th></tr>";
        while($stmt_1->fetch())
        {
            echo "<tr>";
            echo "<td style='border: 1px solid #ccc; padding: 0.5rem; text-align: center; '>".$coach_first_name.", ".$coach_last_name."</td>";
            echo "</tr>";
        }
        echo "</table>";
        $db->close();
    }
    else
    {
        echo "<p>There should only be a number value of 1.</p>";
    }
}    
elseif (isset($select_type) && isset($stats_type))
{
    $query="";
    
    if($select_type == "played")
    {
        $query = "SELECT Team.name AS team_name, COUNT(Game.ID) AS number_of_games_played FROM Team LEFT JOIN Game ON (HomeTeam = Team.ID OR AwayTeam = Team.ID) GROUP BY Team.ID ORDER BY number_of_games_played";
    }
    elseif($select_type == "won")
    {
        $query = "SELECT Team.name AS team_name, COUNT(Game.ID) AS number_of_games_won FROM Team LEFT JOIN Game ON (HomeTeam = Team.ID AND HomeScore > AwayScore) OR (AwayTeam = Team.ID AND AwayScore > HomeScore ) GROUP BY Team.ID ORDER BY number_of_games_won"; 
    }

    $limit = isset($_GET['num']) ? $_GET['num'] : 1;
    $limit = ((int)$limit < 1)? '1': $limit;
    //value provides should always be an integer since the input type is number

    $query .= ($stats_type == "most")? " DESC": " ASC";
    // there is no way to binf_param for limit so this was the only way to get it to work
    $query .= " LIMIT ".$limit.";";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($team_name, $count);

    echo "<table> <tr><th>Team Name</th><th>Count</th></tr>";
    while($stmt->fetch())
    {
        echo "<tr>";
        echo "<td style=' border: 1px solid #ccc; padding: 0.5rem; text-align: center; '>".$team_name."</td>";
        echo "<td style='border: 1px solid #ccc; padding: 0.5rem; text-align: center; '>".$count."</td>";
        echo "</tr>";

    }
    echo "</table>";
    $db->close();
}
else
{
    echo "<p>There was at least one field misssing try again.</p>";
}

?>
