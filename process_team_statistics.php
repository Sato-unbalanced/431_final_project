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

$role = htmlspecialchars($_SESSION['UserRole'] ?? '');
$database_username = $_SESSION['role_name'] ?? '';
$database_password = $_SESSION['role_password'] ?? '';

@$db = new mysqli(DATA_BASE_HOST, $database_username, $database_password, DATA_BASE_NAME);

$select_type = $_GET["select_type"] ?? '';
$stats_type = $_GET["satistic_type"] ?? '';
require_once("landing_page.php");

$coaches = $_GET['stat_coach'] ?? null;
if (isset($coaches) && $select_type && $stats_type) {
    $limit = isset($_GET['num']) ? (int)$_GET['num'] : 1;    

    if ($limit === 1) {
        $query = "";

        if ($select_type === "played") {
            $query = "SELECT Team.ID, Team.name AS team_name, COUNT(Game.ID) AS number_of_games_played
                      FROM Team
                      LEFT JOIN Game ON (HomeTeam = Team.ID OR AwayTeam = Team.ID)
                      WHERE NOT (Game.HomeScore = 0 AND Game.AwayScore = 0)
                      GROUP BY Team.ID ORDER BY number_of_games_played";
        } elseif ($select_type === "won") {
            $query = "SELECT Team.ID, Team.name AS team_name, COUNT(Game.ID) AS number_of_games_won
                      FROM Team
                      LEFT JOIN Game ON (HomeTeam = Team.ID AND HomeScore > AwayScore)
                                    OR (AwayTeam = Team.ID AND AwayScore > HomeScore)
                      WHERE NOT (Game.HomeScore = 0 AND Game.AwayScore = 0)
                      GROUP BY Team.ID ORDER BY number_of_games_won"; 
        }

        $query .= ($stats_type === "most") ? " DESC" : " ASC";
        $query .= " LIMIT 1;";

        $stmt = $db->prepare($query);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($team_id, $team_name, $count);

        echo "<div class='box'><table><tr><th>Team Name</th><th>Count</th></tr>";
        if ($stmt->fetch()) {
            echo "<tr><td>$team_name</td><td>$count</td></tr>";
        }
        echo "</table>";

        $query = "SELECT FirstName, LastName FROM Coach WHERE TeamID = ?";
        $stmt_1 = $db->prepare($query);
        $stmt_1->bind_param('i', $team_id);
        $stmt_1->execute();
        $stmt_1->store_result();
        $stmt_1->bind_result($coach_first_name, $coach_last_name);

        echo "<table><tr><th>Coach Names</th></tr>";
        while ($stmt_1->fetch()) {
            echo "<tr><td>$coach_first_name, $coach_last_name</td></tr>";
        }
        echo "</table></div>";

        $db->close();
    } else {
        echo "<p>There should only be a number value of 1.</p>";
    }
} elseif ($select_type && $stats_type) {
    $query = "";

    if ($select_type === "played") {
        $query = "SELECT Team.name AS team_name, COUNT(Game.ID) AS number_of_games_played
                  FROM Team
                  LEFT JOIN Game ON (HomeTeam = Team.ID OR AwayTeam = Team.ID)
                  WHERE NOT (Game.HomeScore = 0 AND Game.AwayScore = 0)
                  GROUP BY Team.ID ORDER BY number_of_games_played";
    } elseif ($select_type === "won") {
        $query = "SELECT Team.name AS team_name, COUNT(Game.ID) AS number_of_games_won
                  FROM Team
                  LEFT JOIN Game ON (HomeTeam = Team.ID AND HomeScore > AwayScore)
                                OR (AwayTeam = Team.ID AND AwayScore > HomeScore)
                  WHERE NOT (Game.HomeScore = 0 AND Game.AwayScore = 0)
                  GROUP BY Team.ID ORDER BY number_of_games_won"; 
    }

    $limit = isset($_GET['num']) ? (int)$_GET['num'] : 1;
    $limit = max(1, $limit);

    $query .= ($stats_type === "most") ? " DESC" : " ASC";
    $query .= " LIMIT $limit;";

    $stmt = $db->prepare($query);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($team_name, $count);

    echo "<div class='box'><table><tr><th>Team Name</th><th>Count</th></tr>";
    while ($stmt->fetch()) {
        echo "<tr><td>$team_name</td><td>$count</td></tr>";
    }
    echo "</table></div>";

    $db->close();
} else {
    echo "<p>There was at least one field missing. Try again.</p>";
}
?>

<style>
  body { font-family: Arial, sans-serif; background-color: #eef; padding: 2rem; }
  .box { background: white; padding: 1rem 2rem; border-radius: 6px; max-width: 900px; margin: auto; margin-bottom: 2rem; }
  table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
  th, td { border: 1px solid #ccc; padding: 0.5rem; text-align: center; }
  th { background-color: #ddd; }
</style>
