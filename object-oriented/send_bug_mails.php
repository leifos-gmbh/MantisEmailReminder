<?php

chdir('.');

include_once("./class.EmailBugSender.php");

$mail = new EmailBugSender();
$mail->sendBugEmails($config);

?>