<?php

	ini_set('error_reporting', E_ALL);
	ini_set('display_errors', true);
	
	require_once('webwatch.class.php');
	
	$watcher = new webwatch();
	
	$watcher->setTwilioAccountSid('ACd335ad70a6f756a2dd93972b6cb008zZz');
	$watcher->setTwilioAuthToken('86d2aa6b10341ebb49fcd7bac98390zZz');
	
	$web_clients = array(
		'Google' => array(
			'email' => 'aarondcaryl+google@gmail.com',
			'from_phone' => '716-508-0112',
			'to_phone' => '585-245-4640',
			'notification' => 'call',
			'sites' => array(
		    	'http://www.google.com/',
		    	'http://www.google.com/404'
		    )
		),
		'Yahoo' => array(
			'email' => 'aarondcaryl+yahoo1@gmail.com,aarondcaryl+yahoo2@gmail.com',
			'from_phone' => '716-508-0112',
			'to_phone' => '585-245-4640,716-508-0112',
			'notification' => 'sms',
			'sites' => array(
		    	'http://www.yahoo.com/',
		    	'http://www.yahoo.com/404'
		    )
		),
		'Apple' => array(
			'email' => 'aarondcaryl+apple@gmail.com',
			'from_phone' => '716-508-0112',
			'to_phone' => '585-245-4640',
			'notification' => 'call',
			'sites' => array(
		    	'http://www.apple.com/',
		    	'http://www.apple.com/404'
		    )
		)
	);
	
	foreach ($web_clients as $k=>$v) {
		echo '<h3>'. $k .'</h3>';
	    $watcher->setNotifyEmail($v['email']);
	    $watcher->setNotifyFromPhone($v['from_phone']);
	    $watcher->setNotifyToPhone($v['to_phone']);
	    $watcher->setNotifyType($v['notification']);
	    foreach ($v['sites'] as $url) {
		    echo '<p>';
		    $watcher->watch($url);
		    echo '</p>';
	    }
	}

?>