<?php
// no_level_content.php
// Public page showing general team and game info
// No special database roles assigned; uses phpWebEngine user from Adaptation.php (no idea still)

require_once('config.php');
require_once('Adaptation.php');
session_start(); 

// Connect to DB using phpWebEngine credentials 
$db = new mysqli(DATA_BASE_HOST, USER_NAME, USER_PASSWORD, DATA_BASE_NAME);
if ($db->connect_errno !== 0) {
    die("Database connection failed: " . $db->connect_error);
}

// Query for team names
$teamQuery = "SELECT Name FROM Team";
$teamResult = $db->query($teamQuery);

// Query for upcoming games
$gameQuery = "
    SELECT 
        t1.Name AS HomeTeam, 
        t2.Name AS AwayTeam, 
        Location, 
        Month, Day, Year 
    FROM Game
    JOIN Team t1 ON Game.HomeTeam = t1.ID
    JOIN Team t2 ON Game.AwayTeam = t2.ID
    ORDER BY Year, Month, Day
";
$gameResult = $db->query($gameQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Visitor Page</title>
  <style>
    body { font-family: Arial, sans-serif; background-color: #eef; padding: 2rem; }
    .box { background: white; padding: 1rem 2rem; border-radius: 6px; max-width: 800px; margin: auto; margin-bottom: 2rem; }
    h2 { text-align: center; }
    table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
    th, td { border: 1px solid #ccc; padding: 0.5rem; text-align: center; }
    th { background-color: #ddd; }
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
    <h2>Upcoming Games</h2>
    <?php if ($gameResult && $gameResult->num_rows > 0): ?>
      <table>
        <tr>
          <th>Date</th>
          <th>Home Team</th>
          <th>Away Team</th>
          <th>Location</th>
        </tr>
        <?php while ($game = $gameResult->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars(sprintf("%02d/%02d/%04d", $game['Month'], $game['Day'], $game['Year'])) ?></td>
            <td><?= htmlspecialchars($game['HomeTeam']) ?></td>
            <td><?= htmlspecialchars($game['AwayTeam']) ?></td>
            <td><?= htmlspecialchars($game['Location']) ?></td>
          </tr>
        <?php endwhile; ?>
      </table>
    <?php else: ?>
      <p>No games scheduled.</p>
    <?php endif; ?>
  </div>

</body>
</html>
