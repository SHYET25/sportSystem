<?php
include '../connection/connection.php';

// Check if the selectedEmails array is set
if (isset($_POST['selectedEmails']) && is_array($_POST['selectedEmails'])) {
    $emails = $_POST['selectedEmails'];
    
    foreach ($emails as $email) {
        // Fetch the request data from request_signup_tbl based on the email
        $query = "SELECT * FROM request_signup_tbl WHERE req_email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $request = $result->fetch_assoc();

        if ($request) {
            // Common variables
            $status = 'active';
            $sampleImg = 'sample.png';

            // Determine the user type and insert into the appropriate table
            if ($request['req_user_type'] == 'ATHLETE') {
                $insertQuery = "INSERT INTO athlete_tbl (ath_num, ath_first, ath_last, ath_sport, ath_position, ath_email, ath_pass, ath_img, STATUS)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $insertStmt = $conn->prepare($insertQuery);
                $insertStmt->bind_param('sssssssss', 
                    $request['req_stu_id'], 
                    $request['req_fname'], 
                    $request['req_lname'], 
                    $request['req_sport'], 
                    $request['req_position'], 
                    $request['req_email'], 
                    $request['req_pass'], 
                    $sampleImg, 
                    $status);
                $insertStmt->execute();

                // Get the last inserted ID from athlete_tbl
                $athleteId = $conn->insert_id;

                // Insert into basketball_overall_percentage if the sport is BASKETBALL
                if ($request['req_sport'] == 'BASKETBALL') {
                    $insertBasketballQuery = "INSERT INTO basketball_overall_percentage (id, ath_num, ath_3pt, ath_2pt, ath_ft, ath_shooting, ath_passing, ath_ofreb, ath_offense, ath_block, ath_steal, ath_defreb, ath_defense, ath_total)
                                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $insertBasketballStmt = $conn->prepare($insertBasketballQuery);
                    $insertBasketballStmt->bind_param('isssssssssssss', $athleteId, $request['req_stu_id'], $ath_3pt, $ath_2pt, $ath_ft, $ath_shooting, $ath_passing, $ath_ofreb, $ath_offense, $ath_block, $ath_steal, $ath_defreb, $ath_defense, $ath_total);
                    
                    // Initialize other columns with appropriate values (e.g., 0 or default values)
                    $ath_3pt = $ath_2pt = $ath_ft = $ath_shooting = $ath_passing = $ath_ofreb = $ath_offense = $ath_block = $ath_steal = $ath_defreb = $ath_defense = $ath_total = '0';
                    
                    $insertBasketballStmt->execute();
                    $insertBasketballStmt->close();
                }

                $insertStmt->close();
            } elseif ($request['req_user_type'] == 'COACH') {
                $insertQuery = "INSERT INTO coach_tbl (coach_fname, coach_lname, coach_email, coach_pass, coach_sport, coach_position, coach_img, STATUS)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $insertStmt = $conn->prepare($insertQuery);
                $insertStmt->bind_param('ssssssss', 
                    $request['req_fname'], 
                    $request['req_lname'], 
                    $request['req_email'], 
                    $request['req_pass'], 
                    $request['req_sport'], 
                    $request['req_position'], 
                    $sampleImg, 
                    $status);
                $insertStmt->execute();
                $insertStmt->close();
            }

            // Delete the request from request_signup_tbl after successful insertion
            $deleteQuery = "DELETE FROM request_signup_tbl WHERE req_email = ?";
            $deleteStmt = $conn->prepare($deleteQuery);
            $deleteStmt->bind_param('s', $email);
            $deleteStmt->execute();
            $deleteStmt->close();
        }
        
        $stmt->close();
    }
}

$conn->close();
?>
