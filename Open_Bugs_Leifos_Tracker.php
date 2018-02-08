<?php

include("/var/www/html/mantisbt-2.10.0/config/config_inc.php");

function collectBugs() {

    // Create and check connection
    $servername = "localhost";
    $username = "root";
    $password = "123456";
    $dbname = "bugtracker";

    $conn = mysqli_connect($servername, $username, $password, $dbname);
    if(!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Select all active users
    $sql_users = "SELECT * FROM mantis_user_table WHERE enabled='1'";
    $result_users = mysqli_query($conn, $sql_users);

    if (mysqli_num_rows($result_users) > 0) {
        while ($row_users = mysqli_fetch_assoc($result_users)) {
            $message = "Hello " . $row_users["realname"] . ", \nYou have open bugs: \n\n";

            // Select bugs with status not equal to 'resolved' or 'closed' for current user
            $sql_users_bugs = "SELECT p.name AS projectname, b.summary AS bugname, b.id AS bugid, b.project_id, p.id 
                                FROM mantis_bug_table AS b, mantis_project_table AS p  
                                WHERE b.project_id=p.id AND NOT ( b.status='80' OR b.status='90' ) AND b.handler_id=" . $row_users["id"] .
                                " ORDER BY b.project_id";
            $result_users_bugs = mysqli_query($conn, $sql_users_bugs);

            $bug_number = 1;
            if (mysqli_num_rows($result_users_bugs) > 0) {
                while ($row_users_bug = mysqli_fetch_assoc($result_users_bugs)) {
                    $message = $message . $bug_number . ". " . $row_users_bug["projectname"] . ": " . $row_users_bug["bugname"] .
                                " --> http://localhost/mantisbt-2.10.0/view.php?id=" . $row_users_bug["bugid"] . "\n";
                    $bug_number++;
                }

                try {

                    $to = $row_users["email"];
                    $subject = "[LeifosBugTracker] Your current open bugs";
                    $from = "From: Leifos <noreply@leifos.com>";

                    mail($to, $subject, $message, $from);
                    echo "Email sent to " . $row_users ["realname"] . "<br>";

                }catch(Exception $e){
                    echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
                }
            } else {
                echo "No bugs found for " . $row_users["realname"] . "<br>";
            }

        }
    } else {
        echo "No users found <br>";
    }

    mysqli_close($conn);
}

echo collectBugs();

?>
