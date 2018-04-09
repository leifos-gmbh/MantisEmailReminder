<?php
$config = array(
    // Your database connection
    "servername" => "your_servername",
    "username" => "your_db_username",
    "password" => "your_db_password",
    "dbname" => "your_db_name",

    // Your Mantis URL
    "url" => "https://yourMantisURL.com",

	// Your database table prefix (defined in /yourMantisDirectory/config/config_inc.php). Leave it blank if there is no custom prefix.
    "prefix" => "",

	// Your database table suffix (defined in /yourMantisDirectory/config/config_inc.php). Leave it blank if there is no custom suffix.
    "suffix" => "",

	// Support mail (gets unassigned bugs)
	"support_email" => "support@leifos.de"
);
?>