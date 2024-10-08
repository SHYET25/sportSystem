<?php
session_start();
include '../connection/connection.php';

// Check for database connection errors
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user is logged in
if (isset($_SESSION['ath_email'])) {
    $loggedInUserEmail = $_SESSION['ath_email'];

    // Fetch notifications and related match details
    $stmt2 = $conn->prepare("
        SELECT 
            g.match_name,
            c.match_date_time,
            c.match_team
        FROM 
            basketball_created_match_notification c
        JOIN 
            basketball_game_result g ON c.match_id = g.bball_match_id
        WHERE 
            c.match_participant = ?
    ");
    if (!$stmt2) {
        echo json_encode(['status' => 'error', 'message' => 'Prepare failed: ' . $conn->error]);
        exit();
    }
    $stmt2->bind_param("s", $loggedInUserEmail);
    $stmt2->execute();
    $result2 = $stmt2->get_result();

    if ($result2 === false) {
        echo json_encode(['status' => 'error', 'message' => 'Query failed: ' . $conn->error]);
        exit();
    }

    $notifications = [];
    while ($row = $result2->fetch_assoc()) {
        $notifications[] = [
            'match_name' => $row['match_name'],
            'match_date_time' => $row['match_date_time'],
            'match_team' => $row['match_team']
        ];
    }

    // Output the data as JSON
    echo json_encode([
        'status' => 'success',
        'notifications' => $notifications
    ]);

    $stmt2->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'No user logged in.']);
}

$conn->close();
exit();
?>
