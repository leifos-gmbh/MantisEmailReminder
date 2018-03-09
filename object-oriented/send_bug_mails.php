<?php

include_once("EmailBugSender.php");

$mail = new EmailBugSender();
$mail->sendBugEmails($config);

?>