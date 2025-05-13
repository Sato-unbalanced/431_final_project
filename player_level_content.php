<?php
// player_level_content.php
// no_level_content.php + Player personalized dash: view teammates, stats, upcoming games, update own info.
require_once('config.php');
require_once('Adaptation.php');
session_start();

$role = $_SESSION['UserRole'] ?? null;

if ($role !== 'Player') {
    // Role is not Manager — show error
    echo "Access denied. Only Players are allowed.";
    exit; // Optional: stop further script execution
}

require_once('no_level_content.php');
//retrives credential that were assigened from the role that the user has at a database level
$database_username = $_SESSION['role_name'];
$database_password = $_SESSION['role_password'];

// Connect to DB
$db = new mysqli(DATA_BASE_HOST, $database_username, $database_password, DATA_BASE_NAME);
if ($db->connect_errno !== 0) {
    die("Database connection failed: " . $db->connect_error);
}
// --- Get Player's Info and Team ---
$currentUserName = $_SESSION['UserName'] ?? '';

// UserLogin.UserName -> Player.ID -> Player.TeamID -> Team.Name
// Join UserLogin to Player to Team to get current user's team and personal info
// if player TeamID = null, display nothing 
$stmt = $db->prepare("
    SELECT Player.ID, Player.FirstName, Player.LastName, Player.TeamID, Team.Name AS TeamName
    FROM UserLogin
    JOIN Player ON UserLogin.ID = Player.ID
    LEFT JOIN Team ON Player.TeamID = Team.ID
    WHERE UserLogin.UserName = ?
");

$stmt->bind_param('s', $currentUserName);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    echo "<p>Player profile not found. You do not have access to player content. Please contact support.</p>";
    exit;
}

$playerData = $result->fetch_assoc();
$playerID = $playerData['ID'];
$playerFirstName = $playerData['FirstName'];
$playerLastName = $playerData['LastName'];
$playerTeamID = $playerData['TeamID'];
$playerTeamName = $playerData['TeamName'];

// --- If form submitted, update personal info ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_info'])) {
  $newFirstName = trim($_POST['first_name'] ?? '');
  $newLastName  = trim($_POST['last_name'] ?? '');
  $newStreet    = trim($_POST['street'] ?? '');
  $newCity      = trim($_POST['city'] ?? '');
  $newState     = trim($_POST['state'] ?? '');
  $newCountry   = trim($_POST['country'] ?? '');
  $newZipcode   = trim($_POST['zipcode'] ?? '');

  // Validate the zipcode (example for US ZIP code)
  $zipcodePattern = '/^\d{5}(-\d{4})?$/'; // Matches 5-digit or 9-digit ZIP codes
  if (!preg_match($zipcodePattern, $newZipcode)) {
      $updateMessage = "<p style='color:red; text-align:center;'>Invalid ZIP code format.</p>";
  } else {
      // Player.ID
      // Updates current player's record in Player table using their ID
      $update = $db->prepare("
          UPDATE Player 
          SET FirstName = ?, LastName = ?, Street = ?, City = ?, State = ?, Country = ?, Zipcode = ? 
          WHERE ID = ?
      ");
      $update->bind_param('sssssssi', $newFirstName, $newLastName, $newStreet, $newCity, $newState, $newCountry, $newZipcode, $playerID);

      if ($update->execute()) {
          $updateMessage = "<p style='color:green; text-align:center;'>Information updated successfully!</p>";
      } else {
          $updateMessage = "<p style='color:red; text-align:center;'>Failed to update information.</p>";
      }
  }
}


// --- Get teammates ---
 
// Player.TeamID
// Find all players who share the same TeamID as logged-in player
$teammateQuery = $db->prepare("
    SELECT FirstName, LastName, Street, City, State, Country, Zipcode
    FROM Player
    WHERE TeamID = ?
");
$teammateQuery->bind_param('i', $playerTeamID);
$teammateQuery->execute();
$teammateResult = $teammateQuery->get_result();

// --- Get team statistics ---

// Player.ID -> Statistics.PlayerID
// Join Player and Statistics to show goals, assists, passes for each teammate
$statsQuery = $db->prepare("
    SELECT Player.FirstName, Player.LastName, Statistics.Goals, Statistics.Assists, Statistics.Passes
    FROM Player
    LEFT JOIN Statistics ON Player.ID = Statistics.PlayerID
    WHERE Player.TeamID = ?
");
$statsQuery->bind_param('i', $playerTeamID);
$statsQuery->execute();
$statsResult = $statsQuery->get_result();

// --- Get upcoming games ---

// Game.HomeTeam or Game.AwayTeam = Player.TeamID
// Queries all games where the player's team is either home or away AND scores are TBA
// Added a conditional to only query games that are TBA- have not played yet for "Upcoming games"
$gameQuery = $db->prepare("
SELECT 
    t1.Name AS HomeTeam, 
    t2.Name AS AwayTeam, 
    Location, 
    Month, Day, Year,
    Game.HomeScore,
    Game.AwayScore
FROM Game
JOIN Team t1 ON Game.HomeTeam = t1.ID
JOIN Team t2 ON Game.AwayTeam = t2.ID
WHERE (Game.HomeTeam = ? OR Game.AwayTeam = ?)
  AND (Game.HomeScore = 0 AND Game.AwayScore = 0)
ORDER BY Year, Month, Day
");
$gameQuery->bind_param('ii', $playerTeamID, $playerTeamID);
$gameQuery->execute();
$gameResult = $gameQuery->get_result();

?>

<!-- CHAT Styling change if you want later --> 
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Player Dashboard</title>
  <style>
    body { font-family: Arial, sans-serif; background-color: #eef; padding: 2rem; }
    .box { background: white; padding: 1rem 2rem; border-radius: 6px; max-width: 900px; margin: auto; margin-bottom: 2rem; }
    h2 { text-align: center; }
    table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
    th, td { border: 1px solid #ccc; padding: 0.5rem; text-align: center; }
    th { background-color: #ddd; }
    form { margin-top: 2rem; }
    input[type="text"], input[type="submit"] {
      width: 100%;
      padding: 0.5rem;
      margin-bottom: 0.5rem;
    }
  </style>
</head>
<body>

<div class="box">
  <h2>Welcome, <?= htmlspecialchars($playerFirstName) . " " . htmlspecialchars($playerLastName) ?></h2>
</div>

<div class="box">
<?php if ($playerTeamName): ?>
  <h2>My Team, <?= htmlspecialchars($playerTeamName) ?></h2>
<?php else: ?>
  <h2 style="color: #b00; text-align:center;">You are not assigned to a team</h2>
<?php endif; ?>


  <?php if (!empty($updateMessage)) echo $updateMessage; ?>

  <?php if ($teammateResult && $teammateResult->num_rows > 0): ?>
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
      <?php while ($mate = $teammateResult->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($mate['FirstName']) ?></td>
        <td><?= htmlspecialchars($mate['LastName']) ?></td>
        <td><?= htmlspecialchars($mate['Street']) ?></td>
        <td><?= htmlspecialchars($mate['City']) ?></td>
        <td><?= htmlspecialchars($mate['State']) ?></td>
        <td><?= htmlspecialchars($mate['Country']) ?></td>
        <td><?= htmlspecialchars($mate['Zipcode']) ?></td>
      </tr>
      <?php endwhile; ?>
    </table>
  <?php else: ?>
    <p>No teammates found.</p>
  <?php endif; ?>

  <!-- Update form -->
  <div style="margin-top: 2rem; background-color: #f9f9f9; padding: 1rem; border-radius: 8px;">
    <h3 style="text-align: center;">Update Your Info</h3>
    <form method="POST" style="max-width: 500px; margin: auto;">
      <input type="text" name="first_name" placeholder="First Name" required>
      <input type="text" name="last_name" placeholder="Last Name" required>
      <input type="text" name="street" placeholder="Street" required>
      <input type="text" name="city" placeholder="City" required>
      <input type="text" name="state" placeholder="State" required>
      <input type="text" name="country" placeholder="Country" required>
      <input type="text" name="zipcode" placeholder="Zipcode" required>
      <input type="submit" name="update_info" value="Update Address and Info">
    </form>
  </div>
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
