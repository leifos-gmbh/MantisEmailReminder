<?php

class BugCollector
{
    // Collecting all users (which have bugs) and their bugs in one array
    private $all_bugs = array();

    /**
     * Creates and checks connection
     *
     * @param array $config
     * @return mysqli
     */
    function connectDB(array $config)
    {
        $conn = new mysqli($config["servername"], $config["username"], $config["password"], $config["dbname"]);
        if(!$conn) {
            die("Connection failed: " . $conn->connect_error);
        }
        else {
            return $conn;
        }
    }

    /**
     * Closes the created connection
     *
     * @param $conn
     */
    function closeDB($conn)
    {
        $conn->close();
    }

    /**
     * Collects open bugs for Mantis users
     *
     * @param $conn
     * @return array
     */
    function collectBugs($conn)
    {
        // Select all active users except admin and users without email address
        $sql_users = "SELECT * FROM mantis_user_table WHERE enabled='1' AND (email <> '' OR email <> NULL) AND email <> 'root@localhost'";
        $result_users = $conn->query($sql_users);

        if ($result_users->num_rows > 0) {
            while ($row_users = $result_users->fetch_assoc()) {
                $user_bug = array(
                    "email" => $row_users["email"],
                    "username" => $row_users["realname"],
                    "bugs" => array()
                );

                // Select bugs with status not equal to 'resolved' or 'closed' for current user
                $sql_users_bugs = "SELECT p.name AS projectname, b.summary AS bugname, b.id AS bugid, b.project_id, p.id 
                                FROM mantis_bug_table AS b, mantis_project_table AS p  
                                WHERE b.project_id=p.id AND NOT ( b.status='80' OR b.status='90' ) AND b.handler_id=" . $row_users["id"] .
                    " ORDER BY b.project_id";
                $result_users_bugs = $conn->query($sql_users_bugs);

                $bug_number = 1;
                if ($result_users_bugs->num_rows > 0) {
                    while ($row_users_bug = $result_users_bugs->fetch_assoc()) {
                        $user_bug["bugs"][$bug_number]["projectname"] = $row_users_bug["projectname"];
                        $user_bug["bugs"][$bug_number]["bugname"] = $row_users_bug["bugname"];
                        $user_bug["bugs"][$bug_number]["bugid"] = $row_users_bug["bugid"];
                        $bug_number++;
                    }
                    array_push($this->all_bugs, $user_bug);

                } else {
                    echo "No bugs found for " . $row_users["realname"] . "\n";
                }
            }

        } else {
            echo "No users found \n";
        }

        return $this->all_bugs;
    }

}

?>