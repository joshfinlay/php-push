<?php
	class pushNotification {
		public $platform;
		public $apnsHost = "gateway.push.apple.com";
		public $apnsPort = 2195;
		public $certificateFile;
		public $gcmUrl = "https://android.googleapis.com/gcm/send";
		public $gcmKey = "";
		
		
		function __construct($platform = "ios", $auth = "/default/path/to/pem/file") {
			$this->setPlatform($platform, $auth);
		}
		
		function setGcmKey($key) {
			$this->gcmKey = $key;
		}
		
		function setPlatform($platform, $auth) {
			$this->platform = $platform;
			if ($platform == "android") { $this->gcmKey = $auth; }
			else { $this->certificateFile = $auth; }
		}
		
		function sendMessage($regid, $payload) {
			if ($this->platform == "android") {
				$this->_gcmSendPush($regid, $payload);
			}
			else {
				$this->_apnsSendPush($regid, $payload);
			}
		}
		
		function _gcmSendPush($regids, $payload) {
			if ($this->gcmKey == "") {
				throw new Exception("GCM API Key not set. Use setGcmKey(key) first.");
			}
			else {
				$post = array(
					"registration_ids"  => $regids,
					"data"              => $payload
				);
				if (!isset($post["data"]["title"])) { $post["data"]["title"] = "Push Notification"; }
				if (!isset($post["data"]["message"])) { $post["data"]["message"] = $payload["aps"]["alert"]; }
				
				$headers = array( 
					"Authorization: key=" . $this->gcmKey,
					"Content-Type: application/json"
				);
				
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $this->gcmUrl);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
				curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post));
				$result = curl_exec($ch);
				if (curl_errno($ch)) { die("GCM Error: " . curl_error($ch)); }
				curl_close($ch);
				
				return json_decode($result, true);
			}
		}
		
		function _apnsSendPush($regid, $payload) {
			$cert = $this->certificateFile;
			if (!file_exists($cert)) {
				throw new Exception("Certificate not found: \"{$cert}\"");
			}
			else {
				$streamContext = stream_context_create();
				stream_context_set_option($streamContext, "ssl", "local_cert", $cert);
				$apns = stream_socket_client("ssl://{$this->apnsHost}:{$this->apnsPort}", $error, $errorString, 2, STREAM_CLIENT_CONNECT, $streamContext);
				if (!$apns) { die("Error #{$apns->errno}: {$apns->errostr}"); }
				$payload = json_encode($payload, JSON_FORCE_OBJECT);
				$apnsMessage = chr(0).chr(0).chr(32).pack('H*', str_replace(" ", "", $regid)).chr(0).chr(strlen($payload)).$payload;
				fwrite($apns, $apnsMessage);
				
				@socket_close($apns);
				@fclose($apns);
			}
		}
	}
?>
