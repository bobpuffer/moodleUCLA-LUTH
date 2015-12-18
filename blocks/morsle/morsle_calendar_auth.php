<?php
require_once($CFG->dirroot.'/config.php');
require_once($CFG->dirroot.'/google/google-api-php-client/src/Google/Client.php');
require_once($CFG->dirroot.'/google/google-api-php-client/src/Google/Service/Calendar.php');
require_once($CFG->dirroot.'/google/google-api-php-client/autoload.php');
require_once($CFG->dirroot.'/google/google-api-php-client/examples/templates/base.php');
global $CFG;
class block_morsle_auth{
  public function __construct($course_account){
    global $CFG;
    $this->private_key = file_get_contents($CFG->dirroot . '/repository/morsle/key.p12');
    if (!$this->private_key) {
     throw new Exception("Key could not be loaded from file $CFG->dirroot.'/repository/morsle/key.p12'");
    }
$this->client_id = '1016393342084-dqql4goj9s0l402sbf4dtnoq2tsk0hp8.apps.googleusercontent.com';
$this->service_account = '1016393342084-dqql4goj9s0l402sbf4dtnoq2tsk0hp8@developer.gserviceaccount.com';
if (!strlen($this->service_account) || !strlen($this->private_key)) {
         echo missingServiceAccountDetailsWarning();
      exit;
    }
$this->scopes = array('https://www.googleapis.com/auth/calendar');
$this->credentials = new Google_Auth_AssertionCredentials(
    $this->service_account,
    $this->scopes,
    $this->private_key,
    'notasecret',
    'http://oauth.net/grant_type/jwt/1.0/bearer',
    $course_account
);
$this->client = new Google_Client();
$this->client->setApplicationName("Service_account");
$this->client->setAssertionCredentials($this->credentials);
if ($this->client->getAuth()->isAccessTokenExpired()) {
  try{
   $this->client->getAuth()->refreshTokenWithAssertion($this->credentials);
  } catch (Exception $e){
        var_dump($e);
  }
}
$_SESSION['service_token'] = $this->client->getAccessToken();
//return json_decode($this->client->getAccessToken());
  $this->morsle_calendar = new Google_Service_Calendar($this->client);

}
}
?>
