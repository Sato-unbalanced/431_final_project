<?php
require_once('config.php');
require_once('Adaptation.php');
session_start();

// Connect to DB
$db = new mysqli(DATA_BASE_HOST, USER_NAME, USER_PASSWORD, DATA_BASE_NAME);
if ($db->connect_errno !== 0) {
    die("Database connection failed: " . $db->connect_error);
}

// Include no_level_content.php
require_once('no_level_content.php');

// --- Handle Enter Game Results ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enter_result'])) {
    $gameID = intval($_POST['result_game_id']);
    $homeScore = intval($_POST['home_score']);
    $awayScore = intval($_POST['away_score']);

    $updateResult = $db->prepare("UPDATE Game SET HomeScore = ?, AwayScore = ? WHERE ID = ?");
    $updateResult->bind_param('iii', $homeScore, $awayScore, $gameID);
    $updateResult->execute();
}

// --- Handle Add Game ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_game'])) {
    $location = trim($_POST['location']);
    $month = intval($_POST['month']);
    $day = intval($_POST['day']);
    $year = intval($_POST['year']);
    $homeTeam = intval($_POST['home_team']);
    $awayTeam = intval($_POST['away_team']);

    $addGame = $db->prepare("INSERT INTO Game (Location, Month, Day, Year, HomeTeam, AwayTeam, HomeScore, AwayScore) VALUES (?, ?, ?, ?, ?, ?, 0, 0)");
    $addGame->bind_param('siiiii', $location, $month, $day, $year, $homeTeam, $awayTeam);
    $addGame->execute();
}

// --- Handle Delete Game ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_game'])) {
    $gameID = intval($_POST['game_id']);
    $deleteGame = $db->prepare("DELETE FROM Game WHERE ID = ?");
    $deleteGame->bind_param('i', $gameID);
    $deleteGame->execute();
}

// --- Handle Add Coach ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_coach'])) {
    $coachID = intval($_POST['coach_id']);
    $firstName = trim($_POST['first_name']);
    $lastName = trim($_POST['last_name']);
    $teamID = intval($_POST['team_id']);

    $addCoach = $db->prepare("INSERT INTO Coach (ID, TeamID, FirstName, LastName) VALUES (?, ?, ?, ?)");
    $addCoach->bind_param('iiss', $coachID, $teamID, $firstName, $lastName);
    $addCoach->execute();
}

// --- Handle Delete Coach ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_coach'])) {
    $coachID = intval($_POST['coach_id']);
    $deleteCoach = $db->prepare("DELETE FROM Coach WHERE ID = ?");
    $deleteCoach->bind_param('i', $coachID);
    $deleteCoach->execute();
}

// --- Handle Update Coach ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_coach'])) {
    $coachID = intval($_POST['update_coach_id']);
    $firstName = trim($_POST['update_first_name']);
    $lastName = trim($_POST['update_last_name']);
    $teamID = intval($_POST['update_team_id']);

    $updateCoach = $db->prepare("UPDATE Coach SET TeamID = ?, FirstName = ?, LastName = ? WHERE ID = ?");
    $updateCoach->bind_param('issi', $teamID, $firstName, $lastName, $coachID);
    $updateCoach->execute();
}

// --- Handle Add Team ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_team'])) {
    $teamName = trim($_POST['team_name']);
    $addTeam = $db->prepare("INSERT INTO Team (Name) VALUES (?)");
    $addTeam->bind_param('s', $teamName);
    $addTeam->execute();
}

// --- Handle Delete Team ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_team'])) {
    $teamID = intval($_POST['team_id']);
    $deleteTeam = $db->prepare("DELETE FROM Team WHERE ID = ?");
    $deleteTeam->bind_param('i', $teamID);
    $deleteTeam->execute();
}

// --- Handle Update Team ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_team'])) {
    $teamID = intval($_POST['update_team_id']);
    $teamName = trim($_POST['update_team_name']);
    $updateTeam = $db->prepare("UPDATE Team SET Name = ? WHERE ID = ?");
    $updateTeam->bind_param('si', $teamName, $teamID);
    $updateTeam->execute();
}

// --- Fetch all games with TBA scores (for Enter Results dropdown) ---
$tbaGames = $db->query("
    SELECT Game.ID, t1.Name AS HomeTeam, t2.Name AS AwayTeam, Location, Month, Day, Year
    FROM Game
    JOIN Team t1 ON Game.HomeTeam = t1.ID
    JOIN Team t2 ON Game.AwayTeam = t2.ID
    WHERE Game.HomeScore = 0 AND Game.AwayScore = 0
    ORDER BY Year, Month, Day
");

// --- Fetch all upcoming games ---
$upcomingGames = $db->query("
    SELECT Game.ID, t1.Name AS HomeTeam, t2.Name AS AwayTeam, Location, Month, Day, Year
    FROM Game
    JOIN Team t1 ON Game.HomeTeam = t1.ID
    JOIN Team t2 ON Game.AwayTeam = t2.ID
    WHERE Game.HomeScore = 0 AND Game.AwayScore = 0
    ORDER BY Year, Month, Day
");

// --- Fetch all teams ---
$teams = $db->query("SELECT ID, Name FROM Team");

// --- Fetch all coaches and standings ---
$coachStats = $db->query("
    SELECT Coach.ID, Coach.FirstName, Coach.LastName, Team.Name AS TeamName,
    SUM(CASE WHEN (Game.HomeTeam = Team.ID AND Game.HomeScore > Game.AwayScore) OR (Game.AwayTeam = Team.ID AND Game.AwayScore > Game.HomeScore) THEN 1 ELSE 0 END) AS Wins,
    SUM(CASE WHEN (Game.HomeTeam = Team.ID AND Game.HomeScore < Game.AwayScore) OR (Game.AwayTeam = Team.ID AND Game.AwayScore < Game.HomeScore) THEN 1 ELSE 0 END) AS Losses
    FROM Coach
    JOIN Team ON Coach.TeamID = Team.ID
    LEFT JOIN Game ON Game.HomeTeam = Team.ID OR Game.AwayTeam = Team.ID
    GROUP BY Coach.ID
");

// --- Fetch full team standings ---
$standings = $db->query("
    SELECT Team.Name,
    SUM(CASE WHEN (Game.HomeTeam = Team.ID AND Game.HomeScore > Game.AwayScore) OR (Game.AwayTeam = Team.ID AND Game.AwayScore > Game.HomeScore) THEN 1 ELSE 0 END) AS Wins,
    SUM(CASE WHEN (Game.HomeTeam = Team.ID AND Game.HomeScore < Game.AwayScore) OR (Game.AwayTeam = Team.ID AND Game.AwayScore < Game.HomeScore) THEN 1 ELSE 0 END) AS Losses
    FROM Team
    LEFT JOIN Game ON Game.HomeTeam = Team.ID OR Game.AwayTeam = Team.ID
    GROUP BY Team.ID
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manager Dashboard</title>
  <style>
    body { font-family: Arial, sans-serif; background-color: #eef; padding: 2rem; }
    .box { background: white; padding: 1rem 2rem; border-radius: 6px; max-width: 1000px; margin: auto; margin-bottom: 2rem; }
    h2 { text-align: center; }
    table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
    th, td { border: 1px solid #ccc; padding: 0.5rem; text-align: center; }
    th { background-color: #ddd; }
    form { margin-top: 1rem; }
    input[type="text"], input[type="number"], select, input[type="submit"] {
        width: 100%;
        padding: 0.5rem;
        margin-bottom: 0.5rem;
    }
  </style>
</head>
<body>

<!-- Enter Game Results -->
<div class="box">
  <h2>Enter Game Results</h2>
  <form method="POST">
    <select name="result_game_id" required>
      <option value="">Select Game</option>
      <?php while ($game = $tbaGames->fetch_assoc()): ?>
        <option value="<?= $game['ID'] ?>">
          <?= htmlspecialchars(sprintf("%02d/%02d/%04d", $game['Month'], $game['Day'], $game['Year'])) ?>: <?= htmlspecialchars($game['HomeTeam']) ?> vs <?= htmlspecialchars($game['AwayTeam']) ?>
        </option>
      <?php endwhile; ?>
    </select>
    <input type="number" name="home_score" placeholder="Home Score" required>
    <input type="number" name="away_score" placeholder="Away Score" required>
    <input type="submit" name="enter_result" value="Submit Result">
  </form>
</div>

<!-- Upcoming Games Section -->
<div class="box">
  <h2>Upcoming Games</h2>
  <?php if ($upcomingGames && $upcomingGames->num_rows > 0): ?>
  <table>
    <tr>
      <th>Date</th>
      <th>Home Team</th>
      <th>Away Team</th>
      <th>Location</th>
      <th>Delete</th>
    </tr>
    <?php while ($game = $upcomingGames->fetch_assoc()): ?>
    <tr>
      <td><?= htmlspecialchars(sprintf("%02d/%02d/%04d", $game['Month'], $game['Day'], $game['Year'])) ?></td>
      <td><?= htmlspecialchars($game['HomeTeam']) ?></td>
      <td><?= htmlspecialchars($game['AwayTeam']) ?></td>
      <td><?= htmlspecialchars($game['Location']) ?></td>
      <td>
        <form method="POST" style="margin:0;">
          <input type="hidden" name="game_id" value="<?= $game['ID'] ?>">
          <input type="submit" name="delete_game" value="Delete">
        </form>
      </td>
    </tr>
    <?php endwhile; ?>
  </table>
  <!-- Add New Game Form -->
<h3 style="text-align:center;">Add New Game</h3>
<form method="POST">
    <input type="text" name="location" placeholder="Location" required>
    <input type="number" name="month" placeholder="Month (1-12)" min="1" max="12" required>
    <input type="number" name="day" placeholder="Day (1-31)" min="1" max="31" required>
    <input type="number" name="year" placeholder="Year (e.g. 2025)" min="2024" required>
    <input type="number" name="home_team" placeholder="Home Team ID" required>
    <input type="number" name="away_team" placeholder="Away Team ID" required>
    <input type="submit" name="add_game" value="Add Game">
</form>
  <?php else: ?>
    <p>No upcoming games found.</p>
  <?php endif; ?>
</div>

<!-- Full Team Standings -->
<div class="box">
  <h2>Full Team Standings</h2>
  <table>
    <tr>
      <th>Team</th>
      <th>Wins</th>
      <th>Losses</th>
    </tr>
    <?php while ($team = $standings->fetch_assoc()): ?>
    <tr>
      <td><?= htmlspecialchars($team['Name']) ?></td>
      <td><?= htmlspecialchars($team['Wins']) ?></td>
      <td><?= htmlspecialchars($team['Losses']) ?></td>
    </tr>
    <?php endwhile; ?>
  </table>
</div>

<!-- Manage Teams Section -->
<div class="box">
  <h2>Manage Teams</h2>

  <h3>Add New Team</h3>
  <form method="POST">
    <input type="text" name="team_name" placeholder="Team Name" required>
    <input type="submit" name="add_team" value="Add Team">
  </form>

  <h3>Update Existing Team</h3>
  <form method="POST">
    <select name="update_team_id" required>
      <option value="">Select Team</option>
      <?php while ($team = $teams->fetch_assoc()): ?>
        <option value="<?= $team['ID'] ?>"><?= htmlspecialchars($team['Name']) ?></option>
      <?php endwhile; ?>
    </select>
    <input type="text" name="update_team_name" placeholder="New Team Name" required>
    <input type="submit" name="update_team" value="Update Team">
  </form>

  <h3>Delete Team</h3>
  <form method="POST">
    <select name="team_id" required>
      <option value="">Select Team</option>
      <?php
      $teams->data_seek(0);
      while ($team = $teams->fetch_assoc()):
      ?>
        <option value="<?= $team['ID'] ?>"><?= htmlspecialchars($team['Name']) ?></option>
      <?php endwhile; ?>
    </select>
    <input type="submit" name="delete_team" value="Delete Team">
  </form>
</div>

<!-- Coaches Section -->
<div class="box">
  <h2>Coaches</h2>
  <?php if ($coachStats && $coachStats->num_rows > 0): ?>
  <table>
    <tr>
      <th>First Name</th>
      <th>Last Name</th>
      <th>Team</th>
      <th>Wins</th>
      <th>Losses</th>
      <th>Delete</th>
    </tr>
    <?php while ($coach = $coachStats->fetch_assoc()): ?>
    <tr>
      <td><?= htmlspecialchars($coach['FirstName']) ?></td>
      <td><?= htmlspecialchars($coach['LastName']) ?></td>
      <td><?= htmlspecialchars($coach['TeamName']) ?></td>
      <td><?= htmlspecialchars($coach['Wins']) ?></td>
      <td><?= htmlspecialchars($coach['Losses']) ?></td>
      <td>
        <form method="POST" style="margin:0;">
          <input type="hidden" name="coach_id" value="<?= $coach['ID'] ?>">
          <input type="submit" name="delete_coach" value="Delete">
        </form>
      </td>
    </tr>
    <?php endwhile; ?>
  </table>
  <?php else: ?>
    <p>No coaches found.</p>
  <?php endif; ?>

  <h3>Add New Coach</h3>
  <form method="POST">
    <input type="number" name="coach_id" placeholder="Coach ID" required>
    <input type="text" name="first_name" placeholder="First Name" required>
    <input type="text" name="last_name" placeholder="Last Name" required>
    <input type="number" name="team_id" placeholder="Team ID" required>
    <input type="submit" name="add_coach" value="Add Coach">
  </form>

  <h3>Update Coach</h3>
  <form method="POST">
    <input type="number" name="update_coach_id" placeholder="Coach ID" required>
    <input type="text" name="update_first_name" placeholder="New First Name" required>
    <input type="text" name="update_last_name" placeholder="New Last Name" required>
    <input type="number" name="update_team_id" placeholder="New Team ID" required>
    <input type="submit" name="update_coach" value="Update Coach">
  </form>
</div>

</body>
</html>
