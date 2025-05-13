<?php
// no_level_content.php
// Public visitor page showing general team and game info
// No special database roles assigned; uses phpWebEngine user from Adaptation.php

require_once('config.php');
require_once('Adaptation.php');
if (session_status() === PHP_SESSION_NONE) {
  session_start();
} // Still needed if user sessions in general (guest, etc.)

//retrives credential that were assigened from the role that the user has at a database level
$database_username = $_SESSION['role_name'];
$database_password = $_SESSION['role_password'];

// Connect to DB
$db = new mysqli(DATA_BASE_HOST, $database_username, $database_password, DATA_BASE_NAME);
if ($db->connect_errno !== 0) {
    die("Database connection failed: " . $db->connect_error);
}
// Query for team names
$teamQuery = "SELECT Name FROM Team";
$teamResult = $db->query($teamQuery);

// Query for schedule of games including scores
$gameQuery = "
    SELECT 
        t1.Name AS HomeTeam, 
        t2.Name AS AwayTeam, 
        Location, 
        Month, Day, Year, 
        HomeScore, AwayScore
    FROM Game
    JOIN Team t1 ON Game.HomeTeam = t1.ID
    JOIN Team t2 ON Game.AwayTeam = t2.ID
    ORDER BY Year, Month, Day
";
$gameResult = $db->query($gameQuery);

// Query for Top Team (most wins, excluding TBA games)
$topTeamQuery = "
    SELECT Team.Name, COUNT(*) AS Wins
    FROM (
        SELECT HomeTeam AS TeamID
        FROM Game
        WHERE HomeScore > AwayScore AND HomeScore != 0 AND AwayScore != 0
        UNION ALL
        SELECT AwayTeam AS TeamID
        FROM Game
        WHERE AwayScore > HomeScore AND HomeScore != 0 AND AwayScore != 0
    ) AS WinningTeams
    JOIN Team ON WinningTeams.TeamID = Team.ID
    GROUP BY Team.Name
    ORDER BY Wins DESC
    LIMIT 1
";
$topTeamResult = $db->query($topTeamQuery);
$topTeam = $topTeamResult ? $topTeamResult->fetch_assoc() : null;

// Query for Coaches list
$coachQuery = "
    SELECT Coach.FirstName, Coach.LastName, Team.Name AS TeamName
    FROM Coach
    JOIN Team ON Coach.TeamID = Team.ID
";
$coachResult = $db->query($coachQuery);

// Query for most common match location
$locationQuery = "
    SELECT Location, COUNT(*) AS GamesPlayed
    FROM Game
    GROUP BY Location
    ORDER BY GamesPlayed DESC
    LIMIT 1
";
$locationResult = $db->query($locationQuery);
$topLocation = $locationResult ? $locationResult->fetch_assoc() : null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Visitor Page</title>
  <style>
    body { font-family: Arial, sans-serif; background-color: #eef; padding: 2rem; }
    .box { background: white; padding: 1rem 2rem; border-radius: 6px; max-width: 1000px; margin: auto; margin-bottom: 2rem; }
    h2 { text-align: center; }
    table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
    th, td { border: 1px solid #ccc; padding: 0.5rem; text-align: center; }
    th { background-color: #ddd; }
    input {padding: 8px 16px; border-radius: 5px;}
    select  
    {
      font-family: Arial, sans-serif;
      font-size: 16px;
      padding: 8px;
      border-radius: 5px;
      background-color: #f8f9fa;
      color: #333;
    }
  </style>
</head>
<body>

<div class="box">
  <h2>Teams</h2>
  <?php if ($teamResult && $teamResult->num_rows > 0): ?>
    <table>
      <tr><th>Team Name</th></tr>
      <?php while ($team = $teamResult->fetch_assoc()): ?>
        <tr><td><?= htmlspecialchars($team['Name']) ?></td></tr>
      <?php endwhile; ?>
    </table>
  <?php else: ?>
    <p>No teams found.</p>
  <?php endif; ?>
</div>

<div class="box">
  <h2>Schedule of Games</h2> 
  <?php if ($gameResult && $gameResult->num_rows > 0): ?>
    <table>
      <tr>
        <th>Date</th>
        <th>Home Team</th>
        <th>Home Score</th>
        <th>Away Team</th>
        <th>Away Score</th>
        <th>Location</th>
      </tr>
      <?php while ($game = $gameResult->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars(sprintf("%02d/%02d/%04d", $game['Month'], $game['Day'], $game['Year'])) ?></td>
          <td><?= htmlspecialchars($game['HomeTeam']) ?></td>
          <td><?= ($game['HomeScore'] == 0 && $game['AwayScore'] == 0) ? "TBA" : htmlspecialchars($game['HomeScore']) ?></td>
          <td><?= htmlspecialchars($game['AwayTeam']) ?></td>
          <td><?= ($game['HomeScore'] == 0 && $game['AwayScore'] == 0) ? "TBA" : htmlspecialchars($game['AwayScore']) ?></td>
          <td><?= htmlspecialchars($game['Location']) ?></td>
        </tr>
      <?php endwhile; ?>
    </table>
  <?php else: ?>
    <p>No games scheduled.</p>
  <?php endif; ?>
</div>

<!-- Game Statistics -->
<div class="box">
  <h2>Game Statistics</h2>

  <h3>Top Team:</h3>
  <?php if ($topTeam): ?>
    <p><strong><?= htmlspecialchars($topTeam['Name']) ?></strong> with <?= htmlspecialchars($topTeam['Wins']) ?> win(s)</p>
  <?php else: ?>
    <p>No games completed yet to determine top team.</p>
  <?php endif; ?>

  <h3>Coaches and Their Teams:</h3>
  <?php if ($coachResult && $coachResult->num_rows > 0): ?>
    <ul>
      <?php while ($coach = $coachResult->fetch_assoc()): ?>
        <li><?= htmlspecialchars($coach['FirstName']) . " " . htmlspecialchars($coach['LastName']) ?> - <?= htmlspecialchars($coach['TeamName']) ?></li>
      <?php endwhile; ?>
    </ul>
  <?php else: ?>
    <p>No coaches found.</p>
  <?php endif; ?>

  <h3>Most Common Match Location:</h3>
  <?php if ($topLocation): ?>
    <p><strong><?= htmlspecialchars($topLocation['Location']) ?></strong> with <?= htmlspecialchars($topLocation['GamesPlayed']) ?> games hosted.</p>
  <?php else: ?>
    <p>No match locations available.</p>
  <?php endif; ?>

</div>


<div class="box">
      <form action="process_team_statistics.php" method="GET">
        <table>
          <caption>Overall Team Statistics</caption>
          <tr>
            <td> 
              <label>
                Number of Games:
                <select id="select_type" name="select_type">
              <option value="" selected disabled hidden>Select Measure Type</option>
              <option value="played">Played</option>
              <option value="won">Won</option>
              </select>
              </label>
              
            </td>
            <td> 
              <select id="satistic_type" name="satistic_type">
              <option value="" selected disabled hidden>Select Statistic Measure</option>
              <option value="most">Most</option>
              <option value="least">Least</option>
              </select>
            </td>
            </tr>
            <tr> 
              <td>
                  <label for="num">
                    Number of results: 
                    <input type="number" id= "num" name="num" value="1">
                  </label>
              </td>    
            </tr>
            
            
            <tr>
               <td colspan="2" style="text-align: center;"><input type="submit" value="Submit" /></td>
            </tr>
        </table>
      </form>
    </div>
</body>
</html>
