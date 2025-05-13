<?php
// coach_level_content.php
// Coach dashboard: shows no_level_content.php + manage team, players, statistics
require_once('config.php');
require_once('Adaptation.php');
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
// Include public visitor content to display up top
require_once('no_level_content.php');

//retrives credential that were assigened from the role that the user has at a database level
$database_username = $_SESSION['role_name'];
$database_password = $_SESSION['role_password'];

// Connect to DB
$db = new mysqli(DATA_BASE_HOST, $database_username, $database_password, DATA_BASE_NAME);
if ($db->connect_errno !== 0) {
    die("Database connection failed: " . $db->connect_error);
}

// --- Get Coach Info and Team ---
$currentUserName = $_SESSION['UserName'] ?? '';

$stmt = $db->prepare("
    SELECT Coach.ID, Coach.FirstName, Coach.LastName, Coach.TeamID, Team.Name AS TeamName
    FROM UserLogin
    JOIN Coach ON UserLogin.ID = Coach.ID
    JOIN Team ON Coach.TeamID = Team.ID
    WHERE UserLogin.UserName = ?
");
$stmt->bind_param('s', $currentUserName);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    echo "<p>Coach profile not found. Please contact support.</p>";
    exit;
}

$coachData = $result->fetch_assoc();
$coachID = $coachData['ID'];
$coachFirstName = $coachData['FirstName'];
$coachLastName = $coachData['LastName'];
$coachTeamID = $coachData['TeamID'];
$coachTeamName = $coachData['TeamName'];

// --- Handle Form Submissions ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_stats'])) {
        $playerID = intval($_POST['player_id']);
        $goals = intval($_POST['goals']);
        $assists = intval($_POST['assists']);
        $passes = intval($_POST['passes']);

        $check = $db->prepare("SELECT ID FROM Statistics WHERE PlayerID = ?");
        $check->bind_param('i', $playerID);
        $check->execute();
        $checkResult = $check->get_result();

        if ($checkResult->num_rows > 0) {
            $update = $db->prepare("UPDATE Statistics SET Goals = ?, Assists = ?, Passes = ? WHERE PlayerID = ?");
            $update->bind_param('iiii', $goals, $assists, $passes, $playerID);
        } else {
            $update = $db->prepare("INSERT INTO Statistics (PlayerID, Goals, Assists, Passes) VALUES (?, ?, ?, ?)");
            $update->bind_param('iiii', $playerID, $goals, $assists, $passes);
        }
        $update->execute();

        
    }
    // add player form --------------------------------------------
    if (isset($_POST['add_player'])) {
        $newID        = trim($_POST['new_id']);
        $newFirstName = trim($_POST['new_first_name']);
        $newLastName  = trim($_POST['new_last_name']);
        $newStreet    = trim($_POST['new_street']);
        $newCity      = trim($_POST['new_city']);
        $newState     = trim($_POST['new_state']);
        $newCountry   = trim($_POST['new_country']);
        $newZipcode   = trim($_POST['new_zipcode']);

        $insert = $db->prepare("
            INSERT INTO Player (TeamID, ID, FirstName, LastName, Street, City, State, Country, Zipcode)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $insert->bind_param('issssssss', $coachTeamID, $newID, $newFirstName, $newLastName, $newStreet, $newCity, $newState, $newCountry, $newZipcode);
        $insert->execute();
    }
    // remove player button ---------------------------------------
    if (isset($_POST['remove_player'])) {
      $removePlayerID = intval($_POST['remove_player_id']);
  
      $remove = $db->prepare("UPDATE Player SET TeamID = NULL WHERE ID = ? AND TeamID = ?");
      $remove->bind_param('ii', $removePlayerID, $coachTeamID);
      $remove->execute();
    }

    // add free agent button -------------------------
    if (isset($_POST['add_free_agent'])) {
      $freeAgentID = intval($_POST['free_agent_id']);

      $assign = $db->prepare("UPDATE Player SET TeamID = ? WHERE ID = ? AND TeamID IS NULL");
      $assign->bind_param('ii', $coachTeamID, $freeAgentID);
      $assign->execute();
    }

  
}

// --- Fetch data ---
$players = $db->prepare("SELECT ID, FirstName, LastName, Street, City, State, Country, Zipcode FROM Player WHERE TeamID = ?");
$players->bind_param('i', $coachTeamID);
$players->execute();
$playerResult = $players->get_result();

$stats = $db->prepare("
    SELECT Player.ID, Player.FirstName, Player.LastName, Statistics.Goals, Statistics.Assists, Statistics.Passes
    FROM Player
    LEFT JOIN Statistics ON Player.ID = Statistics.PlayerID
    WHERE Player.TeamID = ?
");
$stats->bind_param('i', $coachTeamID);
$stats->execute();
$statsResult = $stats->get_result();

$games = $db->prepare("
    SELECT 
        t1.Name AS HomeTeam, 
        t2.Name AS AwayTeam, 
        Location, 
        Month, Day, Year,
        HomeScore, AwayScore
    FROM Game
    JOIN Team t1 ON Game.HomeTeam = t1.ID
    JOIN Team t2 ON Game.AwayTeam = t2.ID
    WHERE (Game.HomeTeam = ? OR Game.AwayTeam = ?) 
      AND Game.HomeScore = 0 AND Game.AwayScore = 0
    ORDER BY Year, Month, Day
");
$games->bind_param('ii', $coachTeamID, $coachTeamID);
$games->execute();
$gameResult = $games->get_result();

// --- Fetch free agents (TeamID = 1) ---
$freeAgentsStmt = $db->prepare("
    SELECT ID, FirstName, LastName 
    FROM Player 
    WHERE TeamID IS NULL 
    ORDER BY LastName ASC, FirstName ASC
");


$freeAgentsStmt->execute();
$freeAgentsResult = $freeAgentsStmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Coach Dashboard</title>
  <style>
    body { font-family: Arial, sans-serif; background-color: #eef; padding: 2rem; }
    .box { background: white; padding: 1rem 2rem; border-radius: 6px; max-width: 1000px; margin: auto; margin-bottom: 2rem; }
    h2 { text-align: center; }
    table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
    th, td { border: 1px solid #ccc; padding: 0.5rem; text-align: center; }
    th { background-color: #ddd; }
    form { margin-top: 2rem; }
    input[type="text"], input[type="submit"], select {
      width: 100%;
      padding: 0.5rem;
      margin-bottom: 0.5rem;
    }
  </style>
</head>
<body>

<div class="box">
  <h2>Welcome, Coach <?= htmlspecialchars($coachFirstName) . " " . htmlspecialchars($coachLastName) ?></h2>
</div>

<div class="box">
  <h2>My Team, <?= htmlspecialchars($coachTeamName) ?></h2>

  <?php if ($playerResult && $playerResult->num_rows > 0): ?>
    <table>
      <tr>
        <th>First Name</th>
        <th>Last Name</th>
        <th>Street</th>
        <th>City</th>
        <th>State</th>
        <th>Country</th>
        <th>Zipcode</th>
      </tr>
      <?php while ($player = $playerResult->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($player['FirstName']) ?></td>
        <td><?= htmlspecialchars($player['LastName']) ?></td>
        <td><?= htmlspecialchars($player['Street']) ?></td>
        <td><?= htmlspecialchars($player['City']) ?></td>
        <td><?= htmlspecialchars($player['State']) ?></td>
        <td><?= htmlspecialchars($player['Country']) ?></td>
        <td><?= htmlspecialchars($player['Zipcode']) ?></td>
      </tr>
      <?php endwhile; ?>
    </table>
  <?php else: ?>
    <p>No players found.</p>
  <?php endif; ?>

  <!-- Add Player Form -->
  <h3 style="text-align:center;">Add New Player</h3>
  <form method="POST">
    <input type="text" name="new_id" placeholder="ID" required>
    <input type="text" name="new_first_name" placeholder="First Name" required>
    <input type="text" name="new_last_name" placeholder="Last Name" required>
    <input type="text" name="new_street" placeholder="Street" required>
    <input type="text" name="new_city" placeholder="City" required>
    <input type="text" name="new_state" placeholder="State" required>
    <input type="text" name="new_country" placeholder="Country" required>
    <input type="text" name="new_zipcode" placeholder="Zipcode" required>
    <input type="submit" name="add_player" value="Add Player">
  </form>

  <!-- remove player form --> 
  <h3 style="text-align:center;">Remove Player From Team</h3>
  <form method="POST">
    <select name="remove_player_id" required>
      <option value="">Select a Player</option>
      <?php
      $playerResult->data_seek(0);
      while ($player = $playerResult->fetch_assoc()):
      ?>
        <option value="<?= $player['ID'] ?>">
          <?= htmlspecialchars($player['FirstName']) . " " . htmlspecialchars($player['LastName']) ?>
        </option>
      <?php endwhile; ?>
    </select>
    <input type="submit" name="remove_player" value="Remove Player">
  </form>

  <h3 style="text-align:center;">Add Free Agent to Team</h3>
  <?php if ($freeAgentsResult && $freeAgentsResult->num_rows > 0): ?>
    <form method="POST">
      <select name="free_agent_id" required>
        <option value="">Select a Free Agent</option>
        <?php while ($free = $freeAgentsResult->fetch_assoc()): ?>
          <option value="<?= $free['ID'] ?>">
            <?= htmlspecialchars($free['FirstName']) . " " . htmlspecialchars($free['LastName']) ?>
          </option>
        <?php endwhile; ?>
      </select>
      <input type="submit" name="add_free_agent" value="Add Free Agent">
    </form>
  <?php else: ?>
    <p style="text-align:center;">No free agents available to add.</p>
  <?php endif; ?>


</div>

<div class="box">
  <h2>Team Statistics</h2>
  <?php if ($statsResult && $statsResult->num_rows > 0): ?>
    <table>
      <tr>
        <th>First Name</th>
        <th>Last Name</th>
        <th>Goals</th>
        <th>Assists</th>
        <th>Passes</th>
      </tr>
      <?php while ($stat = $statsResult->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($stat['FirstName']) ?></td>
        <td><?= htmlspecialchars($stat['LastName']) ?></td>
        <td><?= htmlspecialchars($stat['Goals'] ?? '0') ?></td>
        <td><?= htmlspecialchars($stat['Assists'] ?? '0') ?></td>
        <td><?= htmlspecialchars($stat['Passes'] ?? '0') ?></td>
      </tr>
      <?php endwhile; ?>
    </table>
  <?php else: ?>
    <p>No statistics found.</p>
  <?php endif; ?>

  <!-- Edit Player Stats Form -->
  <h3 style="text-align:center;">Edit Player Statistics</h3>
  <form method="POST">
    <select name="player_id" required>
      <option value="">Select a Player</option>
      <?php
      $statsResult->data_seek(0);
      while ($stat = $statsResult->fetch_assoc()):
      ?>
        <option value="<?= $stat['ID'] ?>">
          <?= htmlspecialchars($stat['FirstName']) . " " . htmlspecialchars($stat['LastName']) ?>
        </option>
      <?php endwhile; ?>
    </select>
    <input type="text" name="goals" placeholder="Goals" required>
    <input type="text" name="assists" placeholder="Assists" required>
    <input type="text" name="passes" placeholder="Passes" required>
    <input type="submit" name="update_stats" value="Update Player Stats">
  </form>
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
    <p>No upcoming games found.</p>
  <?php endif; ?>
</div>

</body>
</html>
