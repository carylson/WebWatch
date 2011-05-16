<?php

	ini_set('error_reporting', E_ALL);
	ini_set('display_errors', true);
	
	require_once('webwatch.class.php');
	
	$watcher = new webwatch();
	
	$watcher->setNotifyEmail('aaronc@noein.com');
	$watcher->setNotifyFromPhone('716-508-0112');
	$watcher->setNotifyToPhone('585-245-4640');
	$watcher->setNotifyType('call');
	$watcher->setTwilioAccountSid('ACd335ad70a6f756a2dd93972b6cb00893');
	$watcher->setTwilioAuthToken('86d2aa6b10341ebb49fcd7bac983909a');
	
	$watcher->watch('http://www.google.com/');
	echo '<br/>';
	$watcher->watch('http://www.google.com/404');
    
?>