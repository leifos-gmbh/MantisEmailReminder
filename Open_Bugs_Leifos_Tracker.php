<?php

include("./leifos_mantis_config.php");

function collectBugs($servername, $username, $password, $dbname, $url) {

    // Create and check connection
    $conn = mysqli_connect($servername, $username, $password, $dbname);
    if(!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    $all_bugs = array();

    // Select all active users except admin and users without email address
    $sql_users = "SELECT * FROM mantis_user_table WHERE enabled='1' AND email <> '' AND email <> 'root@localhost'";
    $result_users = mysqli_query($conn, $sql_users);

    if (mysqli_num_rows($result_users) > 0) {
        while ($row_users = mysqli_fetch_assoc($result_users)) {
            $user_bug = array(
                "email" => $row_users["email"],
                "message" => "Hello " . $row_users["realname"] . ", \nYou have open bugs: \n\n"
            );

            // Select bugs with status not equal to 'resolved' or 'closed' for current user
            $sql_users_bugs = "SELECT p.name AS projectname, b.summary AS bugname, b.id AS bugid, b.project_id, p.id 
                                FROM mantis_bug_table AS b, mantis_project_table AS p  
                                WHERE b.project_id=p.id AND NOT ( b.status='80' OR b.status='90' ) AND b.handler_id=" . $row_users["id"] .
                                " ORDER BY b.project_id";
            $result_users_bugs = mysqli_query($conn, $sql_users_bugs);

            $bug_number = 1;
            if (mysqli_num_rows($result_users_bugs) > 0) {
                while ($row_users_bug = mysqli_fetch_assoc($result_users_bugs)) {
                    $message = $bug_number . ". " . $row_users_bug["projectname"] . ": " . $row_users_bug["bugname"] .
                        " --> " . $url . $row_users_bug["bugid"] . "\n";
                    $user_bug["message"] = $user_bug["message"] . $message;
                    $bug_number++;
                }
                array_push($all_bugs, $user_bug);

            } else {
                echo "No bugs found for " . $row_users["realname"] . "\n";
            }
        }

    } else {
        echo "No users found \n";
    }

    mysqli_close($conn);

    //print_r($all_bugs);
    sendEmail($all_bugs);
}

function sendEmail($all_bugs) {

    try {

        $subject = "[LeifosBugTracker] Your current open bugs";
        $from = "From: Leifos <noreply@leifos.com>";

        foreach ($all_bugs as $user_bug) {
            $to = $user_bug["email"];
            $message = $user_bug["message"];
            mail($to, $subject, $message, $from);
            echo "Email sent to " . $user_bug["email"] . "\n";
        }
        
    }catch(Exception $e){
        echo 'Message could not be sent. Mailer Error: ', $e->ErrorInfo;
    }

}

collectBugs($servername, $username, $password, $dbname, $url);

?>