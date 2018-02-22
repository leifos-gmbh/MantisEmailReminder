<?php

include("./leifos_mantis_config.php");
sendBugEmails($config);

/**
 * Collect open bugs for Mantis users
 *
 * @param array $config
 * @return array
 */
function collectBugs(array $config) {

    // Create and check connection
    $conn = mysqli_connect($config["servername"], $config["username"], $config["password"], $config["dbname"]);
    if(!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Collecting all users (which have bugs) and their bugs in one array
    $all_bugs = array();

    // Select all active users except admin and users without email address
    $sql_users = "SELECT * FROM mantis_user_table WHERE enabled='1' AND (email <> '' OR email <> NULL) AND email <> 'root@localhost'";
    $result_users = mysqli_query($conn, $sql_users);

    if (mysqli_num_rows($result_users) > 0) {
        while ($row_users = mysqli_fetch_assoc($result_users)) {
            $user_bug = array(
                "email" => $row_users["email"],
                "username" => $row_users["realname"]
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
                    $user_bug["bugs"][$bug_number]["projectname"] = $row_users_bug["projectname"];
                    $user_bug["bugs"][$bug_number]["bugname"] = $row_users_bug["bugname"];
                    $user_bug["bugs"][$bug_number]["bugid"] = $row_users_bug["bugid"];
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
    return $all_bugs;
}

/**
 * Creates a message per user which contains his open bugs and sends it as an email to him
 *
 * @param $config
 */
function sendBugEmails($config) {

    $all_bugs = collectBugs($config);

    try {

        $subject = "[LeifosBugTracker] Your current open bugs";
        $from = "From: Leifos <noreply@leifos.com>";

        foreach ($all_bugs as $users) {
            $to = $users["email"];
            $message = "Hello " . $users["username"] . ", \nYou have open bugs: \n\n";
            foreach ($users as $user_bugs) {
                foreach ($user_bugs as $key=>$bugs) {
                    $message = $message . $key. ". " . $bugs["projectname"] . ": " . $bugs["bugname"] . " --> " . $config["url"] . "/view.php?id=" . $bugs["bugid"] . "\n";
                }
            }
            mail($to, $subject, $message, $from);
            echo "Email sent to " . $users["email"] . "\n";
        }
        
    }catch(Exception $e){
        echo 'Message could not be sent. Mailer Error: ', $e->ErrorInfo;
    }

}

?>