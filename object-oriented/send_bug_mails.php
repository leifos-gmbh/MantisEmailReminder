<?php

chdir(__DIR__);

include_once("./class.EmailBugSender.php");

$mail = new EmailBugSender();
$mail->sendBugEmails($config);

?>