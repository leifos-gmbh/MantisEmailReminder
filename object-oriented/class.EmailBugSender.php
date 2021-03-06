<?php

require_once("./leifos_mantis_config.php");
include_once("./class.BugCollector.php");

class EmailBugSender
{
    private $email_bugs;
    private $subject = "[LeifosBugTracker] Your current open bugs";
    private $from = "From: Leifos <noreply@leifos.com>";
    private $to;
    private $message;
    const URL_INDEX = "/view.php?id=";

    /**
     * Creates a message per user which contains his open bugs and sends it as an email to him
     *
     * @param $config
     */
    function sendBugEmails($config)
    {
        $bugCollection = new BugCollector($config);
        $this->email_bugs = $bugCollection->collectBugs();

        try {

            foreach ($this->email_bugs as $users) {
            	// only send mails to leifos addresses
            	if (!is_int(strpos($users["email"], "leifos")))
				{
					echo "No mail sent to " . $users["email"] . " (not a leifos address) \n";
					continue;
				}
                $this->to = $users["email"];
                $this->message = "Hello " . $users["username"] . ", \nYou have open bugs: \n\n";
                foreach ($users["bugs"] as $key=>$bugs) {
                    $this->message = $this->message . $key. ". " . $bugs["projectname"] . ": " . $bugs["bugname"] . " --> " . $config["url"] . EmailBugSender::URL_INDEX . $bugs["bugid"] . "\n";
                }
                mail($this->to, $this->subject, $this->message, $this->from);
                echo "Email sent to " . $users["email"] . "\n";
            }

        }catch(Exception $e){
            echo 'Message could not be sent. Mailer Error: ', $e->ErrorInfo;
        }

    }

}

?>