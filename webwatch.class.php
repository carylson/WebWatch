<?php

	require_once('twilio.class.php');

	/**
	 * A monitoring and notification class for immediately communicating website
	 * outages to a webmaster, or webmaster(s).
	 * 
	 * Basically, the class simulates a web request to a specified URL and either
	 * SMSs, calls, or e-mails any number of website administrators if an error
	 * occurs (according to http header status code returned).
	 * 
	 * To make the most of this application, create a CRON job that automatically 
	 * runs the script every so often!
	 * 
	 * It's critical to run this application on a separate server from the
	 * website(s) you're testing!
	 * 
     * @author Aaron D. Caryl - http://www.aaroncaryl.com/
	 */
	class webwatch {
	
		private $notify_email;
		private $notify_from_phone;
		private $notify_to_phone;
		private $notify_type;
		private $twilio_account_sid;
		private $twilio_auth_token;
	
		/**
		 * Instantiate the class.
		 *
		 * Sets some default values needed throughout processing, and also calls the
		 * handleCall() method, necessary for Twilio-powered call notifications.
		 */
		public function __construct() {
			// Set default/example config values.
			$this->notify_email = 'somebody@somewhere.com,somebody_else@somewhere.com';
			$this->notify_from_phone = '555-555-5555';
			$this->notify_to_phone = '555-555-5555,555-555-5556';
			$this->notify_type = 'sms';
			$this->twilio_account_sid = 'ACd335ad70a6f756a2dd9397..........';
			$this->twilio_auth_token = '86d2aa6b10341ebb49fcd7..........';
			// Handle phone call warning message if necessary. 
			$this->handleCall();
			return $this;
		}
		
		/**
		 * Checks a URL for failure.
		 *
		 * Uses cURL library to check a particular web page's headers.  If the header
		 * code returned represents an error of sorts, return the required data to
		 * start the notification process.
		 * 
		 * $url: A singular URL string
		 */
		private function checkUrl($url) {
	        $return = array();
	        $curl = curl_init();
	        curl_setopt ($curl, CURLOPT_URL, $url);
	        // Return a string instead of echoing to screen
	        curl_setopt ($curl, CURLOPT_RETURNTRANSFER, true);
	        // Follow redirects (recursive)
	        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
	        // Only get headers, not content (time-saver)
	        curl_setopt($curl, CURLOPT_NOBODY, true);
	        // Override default 30-second timeout
	        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
	        // Prevent cacheing
	        curl_setopt($curl, CURLOPT_DNS_CACHE_TIMEOUT, -1);
	        $result = curl_exec($curl);
	        $errno = curl_errno($curl);
	        if ($errno != 0) {
	            $return['message'] = curl_error($curl);
	            $return['success'] = false;
	        } else {
	            $http = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	            $return['code'] = $http;
	            // Header status code for errors is anything outside of 200s.
	            if ($http >= 200 && $http < 300) {
	                $return['message'] = $url .' is up!';
	                $return['success'] = true;
	            } else {
	                $return['message'] = $url .' is down due to a '. $http .' error!';
	                $return['success'] = false;
	            }
	        }
	        curl_close($curl);
	        return $return;
		}
		
		/**
		 * Retreive the notify_email variable.
		 */
		public function getNotifyEmail() {
			return $this->notify_email;
		}
		
		/**
		 * Retreive the notify_from_phone variable.
		 */
		public function getNotifyFromPhone() {
			return $this->notify_from_phone;
		}
		
		/**
		 * Retreive the notify_to_phone variable.
		 */
		public function getNotifyToPhone() {
			return $this->notify_to_phone;
		}
		
		/**
		 * Retreive the notify_type variable.
		 */
		public function getNotifyType() {
			return $this->notify_type;
		}
		
		/**
		 * Retreive the twilio_account_sid variable.
		 */
		public function getTwilioAccountSid() {
			return $this->twilio_account_sid;
		}
		
		/**
		 * Retreive the twilio_auth_token variable.
		 */
		public function getTwilioAuthToken() {
			return $this->twilio_auth_token;
		}
		
		/**
		 * Outputs a "Twiml" (Twilio XML) file to deliver a Twilio-powered call
		 * notification.
		 * 
		 * Simply accepts a message via $_GET['message'], and displays it formatted
		 * appropriately for Twilio's call services.
		 * 
		 * More on Twiml can be found here: http://www.twilio.com/docs/api/twiml/
		 */
		private function handleCall() {
			// Only proceed to output call Twiml if $_GET['call'] is set!
			if (isset($_GET['call'])) {
				$error_message = !empty($_GET['message']) ? urldecode(stripslashes($_GET['message'])) : 'An unknown error occurred.' ;
				header('content-type: text/xml');
				echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";
				echo '
				<Response>
				    <Say>Automated website downtime warning!</Say>
				    <Say>'. $error_message .'</Say>
				    <Say>Goodbye</Say>
				    <Hangup/>
				</Response>
				';
				exit;
			}
		}

		/**
		 * Set the notify_email variable.
		 * 
		 * $notify_email: A comma-separated string of email addresses.
		 */
		public function setNotifyEmail($notify_email=false) {
			if (!is_string($notify_email)) {
				return false;
			}
			$this->notify_email = $notify_email;
			return true;
		}

		/**
		 * Set the notify_from_phone variable.
		 * 
		 * $notify_from_phone: A singular phone number string.
		 */
		public function setNotifyFromPhone($notify_from_phone=false) {
			if (!is_string($notify_from_phone)) {
				return false;
			}
			$this->notify_from_phone = $notify_from_phone;
			return true;
		}

		/**
		 * Set the notify_to_phone variable.
		 * 
		 * $notify_to_phone: A comma-separated string of phone numbers.
		 */
		public function setNotifyToPhone($notify_to_phone=false) {
			if (!is_string($notify_to_phone)) {
				return false;
			}
			$this->notify_to_phone = explode(',', $notify_to_phone);
			return true;
		}
		
		/**
		 * Set the notify_type variable.
		 * 
		 * $notify_type: A singular "notification type" string.  Can be either "sms"
		 * or "call".
		 */
		public function setNotifyType($notify_type=false) {
			if (!is_string($notify_type) || !in_array($notify_type, array('sms', 'call'))) {
				return false;
			}
			$this->notify_type = $notify_type;
			return true;
		}

		/**
		 * Set the twilio_account_sid variable.
		 * 
		 * $twilio_account_sid: A singular account sid string, obtained from Twilio
		 * API.
		 */
		public function setTwilioAccountSid($twilio_account_sid=false) {
			if (!is_string($twilio_account_sid)) {
				return false;
			}
			$this->twilio_account_sid = $twilio_account_sid;
			return true;
		}

		/**
		 * Set the twilio_auth_token variable.
		 * 
		 * $twilio_auth_token: A singular auth token string, obtained from Twilio API.
		 */
		public function setTwilioAuthToken($twilio_auth_token=false) {
			if (!is_string($twilio_auth_token)) {
				return false;
			}
			$this->twilio_auth_token = $twilio_auth_token;
			return true;
		}
		
		/**
		 * Check a URL!
		 *
		 * Invokes checkUrl() on a URL to retreive its header data, and
		 * then proceeds to send a notification if the status code returned
		 * indicates an error occurred.
		 * 
		 * This method also includes an email-notification fallback 
		 * mechanism, in case the selected Twilio notification type fails.
		 * 
		 * $url: A singular URL string
		 */
		public function watch($url=false) {
			if (!is_string($url)) {
				echo 'Invalid URL "'. $url .'".';
				return false;
			}
	        $result = $this->checkURL($url);
	        if ($result['success'] != true) {
	        	$ApiVersion = '2010-04-01';
	        	$AccountSid = $this->twilio_account_sid;
	        	$AuthToken = $this->twilio_auth_token;
	        	$client = new TwilioRestClient($AccountSid, $AuthToken);
	        	switch($this->notify_type) {
	        		case 'call':
	        			// Send a phone call notification!
			            foreach ($this->notify_to_phone as $to_phone) {
				        	$response = $client->request('/' . $ApiVersion . '/Accounts/' . $AccountSid . '/Calls', 
				        		'POST', array(
				        		'To' => $to_phone,
				        		'From' => $this->notify_from_phone,
				        		'Url' => 'http://'. $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'] .'?call&message='. urlencode($result['message']),
				        		'IfMachine' => 'Continue'
				        	));
			            }
	        		break;
	        		default:
	        			// Send a SMS notification!
			            foreach ($this->notify_to_phone as $to_phone) {
				        	$response = $client->request('/' . $ApiVersion . '/Accounts/' . $AccountSid . '/SMS/Messages', 
				        		'POST', array(
				        		'To' => $to_phone,
				        		'From' => $this->notify_from_phone,
				        		'Body' => $result['message']
				        	));
			        	}
	        		break;
	        	}
	            // Send an e-mail notification if the site is down and the Twilio magic fails!
	        	if ($response->IsError) {
					$subject = 'Website Error: '. $result['message'];
					$message = $subject .'<br/>Twilio Error: '. $response->ErrorMessage;
					$headers  = 'MIME-Version: 1.0' . "\n";
					$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\n";
					$headers .= 'From: WebWatch <noreply@'. $_SERVER['SERVER_NAME'] .'>' . "\n";
					mail($this->notify_email, $subject, $message, $headers);
	        	}
	        }
	        echo $result['message'];
	        return true;
		}

	}

?>