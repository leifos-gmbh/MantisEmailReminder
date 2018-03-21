<?php

class BugCollector
{
    private $config;
    private $conn;
    // Collecting all users (which have bugs) and their bugs in one array
    private $all_bugs = array();

    /**
     * class.BugCollector constructor.
     * @param array $config
     */
    function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Creates and checks connection
     */
    function connectDB()
    {
        $this->conn = new mysqli($this->config["servername"], $this->config["username"], $this->config["password"], $this->config["dbname"]);
        if(!$this->conn) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    /**
     * Closes the created connection
     */
    function closeDB()
    {
        $this->conn->close();
    }

    /**
     * Collects open bugs for Mantis users
     * @return array
     */
    function collectBugs()
    {
        $this->connectDB();
        // Select all active users except admin and users without email address
        $sql_users = "SELECT * FROM mantis_user_table WHERE enabled='1' AND (email <> '' OR email <> NULL) AND email <> 'root@localhost'";
        $result_users = $this->conn->query($sql_users);

        if ($result_users->num_rows > 0) {
            while ($row_users = $result_users->fetch_assoc()) {
            	$this->addUserBugs($row_users);
            }
        } else {
            echo "No users found \n";
        }

        // add support user for bugs not being adressed
		$this->addUserBugs(array(
			"email" => $this->config["support_email"],
			"username" => "Support Team",
			"id" => "0",
		));

        $this->closeDB();
        return $this->all_bugs;
    }

    /**
     * Add bugs of user to list
     *
	 * @param $row_users
	 */
    protected function addUserBugs($row_users)
    {
		$user_bug = array(
			"email" => $row_users["email"],
			"username" => $row_users["realname"],
			"bugs" => array()
		);

		$id_str = ($row_users["id"] != null)
			? " b.handler_id=" . $row_users["id"]." "
			: " b.handler_id IS NULL ";

		// Select bugs with status not equal to 'resolved' or 'closed' for current user
		$sql_users_bugs = "SELECT p.name AS projectname, b.summary AS bugname, b.id AS bugid, b.project_id, p.id 
                                FROM mantis_bug_table AS b, mantis_project_table AS p  
                                WHERE b.project_id=p.id AND NOT ( b.status='80' OR b.status='90' ) AND ".$id_str.
			" ORDER BY b.project_id";
		$result_users_bugs = $this->conn->query($sql_users_bugs);

		$bug_number = 1;
		if ($result_users_bugs->num_rows > 0) {
			while ($row_users_bug = $result_users_bugs->fetch_assoc()) {
				$user_bug["bugs"][$bug_number]["projectname"] = $row_users_bug["projectname"];
				$user_bug["bugs"][$bug_number]["bugname"] = $row_users_bug["bugname"];
				$user_bug["bugs"][$bug_number]["bugid"] = $row_users_bug["bugid"];
				$bug_number++;
			}
			array_push($this->all_bugs, $user_bug);
		}
    }


}

?>