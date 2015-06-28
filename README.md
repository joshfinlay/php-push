# php-push
PHP Push Notification Class

# See Reference below for full usage information

# Usage (iOS)
```php
  require_once("push.php");
  $push = new pushNotification("ios", "/path/to/pem/cert");
  $payload = array(
					"aps" => array("badge" => "auto", "alert" => "Hello!", "sound" => "beep.caf"),
					"extra_data" => "goes_here",
					"id" => time()
				);
	$push->sendMessage($REGID, $payload);
```

# Usage (Android)
```php
  require_once("push.php");
  $push = new pushNotification("android", "gcm_key_here");
  $payload = array(
					"aps" => array("badge" => "auto", "alert" => "Hello!", "sound" => "beep.caf"),
					"title" => "Title goes here", // if unset, defaults to "Push Notification" which may be undesirable
					"message" => "Hello!", //may be omitted, if so it uses "alert" value from above.
					"extra_data" => "goes_here",
					"id" => time()
				);
	$push->sendMessage($REGID, $payload);
```

# Reference
## pushNotification Class
A new instance of the pushNotification class is called with two sets of information:
* For Android: `new pushNotification("android", "android GCM key");`
* For iOS: `new pushNotification("ios", "/path/to/apns/pem/certificate");`

## sendMessage function
`sendMessage()` can be used to send a message to a device registration ID via the provider as defined when you created the class.

`sendMessage()` is more powerful than `simpleSend()` in that you can add additional data to the payload.

The payload varies between iOS and Android. iOS does not used a title attibute, however you can generally use the same payload for each provider and data should be safely ignored, and/or passed to your app as additional data in the payload.

The payload goes like this:
```php
$payload = array(
  "aps" => array("badge" => "auto", "alert" => "MESSAGE GOES HERE", "sound" => "beep.caf"),
  "title" => "Title goes here", // if unset, defaults to "Push Notification" which may be undesirable
	"message" => "Hello!", //may be omitted, if so it uses "alert" value from above.
	"extra_data" => "goes_here", // you can add more array keys/values to the $payload array to send to your app
	"id" => time() // this is additional data also, but we use it in our own implementation as a unique id when combined with the regid for this particular push notification.
);
```

Then you call sendMessage() from within your instance of the pushNotification class
```php
$push->sendMessage($REGID, $payload);
```

## simpleSend function
The `simpleSend()` function is the quickest and easiest way to send a push notification. It generates the payload for you, without any additional data.

```php
$push->simpleSend($REGID, "Message contents", "Message title (if applicable)");
```

## Return values
As unprofessional as this may be. I'm fairly sure `sendMessage()` returns a value(s) from the provider response, but at the moment I can't remember what it is. So you might need to tinker here.
