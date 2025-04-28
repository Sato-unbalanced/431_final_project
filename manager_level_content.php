<?php
// manager_level_content.php
// Manager dashboard: manage games, teams, coaches, and full standings.

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

// --- Handle Game Results Update ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enter_result'])) {
    $gameID = intval($_POST['game_id']);
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

// --- Handle Add Team ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_team'])) {
    $teamName = trim($_POST['team_name']);

    $addTeam = $db->prepare("INSERT INTO Team (Name) VALUES (?)");
    $addTeam->bind_param('s', $teamName);
    $addTeam->execute();
}

// --- Handle Update Team ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_team'])) {
    $teamID = intval($_POST['update_team_id']);
    $newTeamName = trim($_POST['new_team_name']);

    $updateTeam = $db->prepare("UPDATE Team SET Name = ? WHERE ID = ?");
    $updateTeam->bind_param('si', $newTeamName, $teamID);
    $updateTeam->execute();
}

// --- Handle Delete Team ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_team'])) {
    $teamID = intval($_POST['delete_team_id']);

    $deleteTeam = $db->prepare("DELETE FROM Team WHERE ID = ?");
    $deleteTeam->bind_param('i', $teamID);
    $deleteTeam->execute();
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

// --- Fetch TBA Games (HomeScore = AwayScore = 0) for entering results ---
// Game.HomeTeam -> Team.ID
// Game.AwayTeam -> Team.ID
$tbaGames = $db->query("
    SELECT ID, Month, Day, Year, Location
    FROM Game
    WHERE HomeScore = 0 AND AwayScore = 0
    ORDER BY Year, Month, Day
");

// --- Fetch all Teams for dropdowns ---
$teams = $db->query("SELECT ID, Name FROM Team");

// --- Fetch all Coaches and Standings ---
// Coach.TeamID -> Team.ID
// Game.HomeTeam/Game.AwayTeam -> Team.ID
$coachStats = $db->query("
    SELECT Coach.ID, Coach.FirstName, Coach.LastName, Team.Name AS TeamName,
    SUM(CASE WHEN (Game.HomeTeam = Team.ID AND Game.HomeScore > Game.AwayScore) OR (Game.AwayTeam = Team.ID AND Game.AwayScore > Game.HomeScore) THEN 1 ELSE 0 END) AS Wins,
    SUM(CASE WHEN (Game.HomeTeam = Team.ID AND Game.HomeScore < Game.AwayScore) OR (Game.AwayTeam = Team.ID AND Game.AwayScore < Game.HomeScore) THEN 1 ELSE 0 END) AS Losses
    FROM Coach
    JOIN Team ON Coach.TeamID = Team.ID
    LEFT JOIN Game ON Game.HomeTeam = Team.ID OR Game.AwayTeam = Team.ID
    GROUP BY Coach.ID
");

// --- Fetch Full Standings (Wins and Losses per Team) ---
// Team.ID -> Game.HomeTeam/Game.AwayTeam
$fullStandings = $db->query("
    SELECT Team.Name AS TeamName,
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
        .box { background: white; padding: 1rem 2rem; border-radius: 6px; max-width: 1100px; margin: auto; margin-bottom: 2rem; }
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

<!-- Enter Game Results Section -->
<div class="box">
    <h2>Enter Game Results</h2>
    <form method="POST">
        <select name="game_id" required>
            <option value="">Select a TBA Game</option>
            <?php while ($game = $tbaGames->fetch_assoc()): ?>
                <option value="<?= $game['ID'] ?>">
                    <?= htmlspecialchars(sprintf("%02d/%02d/%04d", $game['Month'], $game['Day'], $game['Year'])) ?> - <?= htmlspecialchars($game['Location']) ?>
                </option>
            <?php endwhile; ?>
        </select>
        <input type="number" name="home_score" placeholder="Home Score" min="0" required>
        <input type="number" name="away_score" placeholder="Away Score" min="0" required>
        <input type="submit" name="enter_result" value="Submit Result">
    </form>
</div>

<!-- Upcoming Games Section -->
<div class="box">
    <h2>Upcoming Games</h2>

    <!-- Add Game Form -->
    <h3 style="text-align:center;">Add New Game</h3>
    <form method="POST">
        <input type="text" name="location" placeholder="Location" required>
        <input type="number" name="month" placeholder="Month (1-12)" min="1" max="12" required>
        <input type="number" name="day" placeholder="Day (1-31)" min="1" max="31" required>
        <input type="number" name="year" placeholder="Year" required>
        <input type="number" name="home_team" placeholder="Home Team ID" required>
        <input type="number" name="away_team" placeholder="Away Team ID" required>
        <input type="submit" name="add_game" value="Add Game">
    </form>
</div>

<!-- Full Team Standings -->
<div class="box">
    <h2>Full Team Standings</h2>
    <?php if ($fullStandings && $fullStandings->num_rows > 0): ?>
        <table>
            <tr>
                <th>Team</th>
                <th>Wins</th>
                <th>Losses</th>
            </tr>
            <?php while ($row = $fullStandings->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['TeamName']) ?></td>
                    <td><?= htmlspecialchars($row['Wins'] ?? 0) ?></td>
                    <td><?= htmlspecialchars($row['Losses'] ?? 0) ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>No teams found.</p>
    <?php endif; ?>
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
            <option value="">Select Team to Update</option>
            <?php $teams->data_seek(0); while ($team = $teams->fetch_assoc()): ?>
                <option value="<?= $team['ID'] ?>"><?= htmlspecialchars($team['Name']) ?></option>
            <?php endwhile; ?>
        </select>
        <input type="text" name="new_team_name" placeholder="New Team Name" required>
        <input type="submit" name="update_team" value="Update Team">
    </form>

    <h3>Delete Team</h3>
    <form method="POST">
        <select name="delete_team_id" required>
            <option value="">Select Team to Delete</option>
            <?php $teams->data_seek(0); while ($team = $teams->fetch_assoc()): ?>
                <option value="<?= $team['ID'] ?>"><?= htmlspecialchars($team['Name']) ?></option>
            <?php endwhile; ?>
        </select>
        <input type="submit" name="delete_team" value="Delete Team">
    </form>
</div>

<!-- Coaches Section -->
<div class="box">
    <h2>Coaches</h2>

    <h3>Add New Coach</h3>
    <form method="POST">
        <input type="number" name="coach_id" placeholder="Coach ID" required>
        <input type="text" name="first_name" placeholder="First Name" required>
        <input type="text" name="last_name" placeholder="Last Name" required>
        <input type="number" name="team_id" placeholder="Team ID" required>
        <input type="submit" name="add_coach" value="Add Coach">
    </form>

    <h3>Update Coach Info</h3>
    <form method="POST">
        <input type="number" name="update_coach_id" placeholder="Coach ID" required>
        <input type="text" name="update_first_name" placeholder="New First Name" required>
        <input type="text" name="update_last_name" placeholder="New Last Name" required>
        <input type="number" name="update_team_id" placeholder="New Team ID" required>
        <input type="submit" name="update_coach" value="Update Coach">
    </form>

    <h3>Delete Coach</h3>
    <form method="POST">
        <input type="number" name="coach_id" placeholder="Coach ID" required>
        <input type="submit" name="delete_coach" value="Delete Coach">
    </form>
</div>

</body>
</html>
