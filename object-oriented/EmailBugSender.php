<?php

require("./leifos_mantis_config.php");
include("./BugCollector.php");

class EmailBugSender extends BugCollector
{
    private $conn;
    private $email_bugs;
    private $subject = "[LeifosBugTracker] Your current open bugs";
    private $from = "From: Leifos <noreply@leifos.com>";
    private $to;
    private $message;
    private const urlIndex = "/view.php?id=";

    /**
     * Creates a message per user which contains his open bugs and sends it as an email to him
     *
     * @param $config
     */
    function sendBugEmails($config)
    {
        $bugCollection = new BugCollector();
        $this->conn = $bugCollection->connectDB($config);
        $this->email_bugs = $bugCollection->collectBugs($this->conn);
        $bugCollection->closeDB($this->conn);

        try {

            foreach ($this->email_bugs as $users) {
                $this->to = $users["email"];
                $this->message = "Hello " . $users["username"] . ", \nYou have open bugs: \n\n";
                foreach ($users["bugs"] as $key=>$bugs) {
                    $this->message = $this->message . $key. ". " . $bugs["projectname"] . ": " . $bugs["bugname"] . " --> " . $config["url"] . EmailBugSender::urlIndex . $bugs["bugid"] . "\n";
                }
                mail($this->to, $this->subject, $this->message, $this->from);
                echo "Email sent to " . $users["email"] . "\n";
            }

        }catch(Exception $e){
            echo 'Message could not be sent. Mailer Error: ', $e->ErrorInfo;
        }

    }

}

$mail = new EmailBugSender();
$mail->sendBugEmails($config);

?>