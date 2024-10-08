<?php
include '../connection/connection.php';
$status = "done";
// Prepare the SQL query to fetch match data
$query = "SELECT `bball_match_id`, `match_name`, `team_1`, `team_2`, `team_1_score`, `team_2_score`, `match_win`, `match_lose`, `match_date_time` FROM `basketball_game_result` WHERE `STATUS` = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $status);
// Prepare the statement

// Execute the query
$stmt->execute();

// Get the result
$result = $stmt->get_result();

// Fetch all matches
$matches = array();
while ($row = $result->fetch_assoc()) {
    $matches[] = $row;
}

// Output the results as JSON
echo json_encode($matches);

// Close the statement and connection
$stmt->close();
$conn->close();
?>
