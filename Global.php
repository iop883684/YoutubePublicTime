<?php

final class UserFactory
{
    /**
     * Call this method to get singleton
     *
     * @return UserFactory
     */

    public $youtube;
    public $client;

    public static function Instance()
    {
        static $inst = null;
        if ($inst === null) {
            $inst = new UserFactory();
            $inst->createGoogleClient();
        }
        return $inst;
    }

    /**
     * Private ctor so nobody else can instance it
     *
     */
    private function __construct()
    {

    }


   	private function createGoogleClient(){

   		set_include_path(get_include_path() . PATH_SEPARATOR . 'google-api-php-client/src');

		require_once 'Google/autoload.php';

    require_once 'Google/Client.php';
    require_once 'Google/Service/YouTube.php';
    session_start();

		$OAUTH2_CLIENT_ID = '1059495052682-pspshs7kbn3u09os8jqd0kvodh29mpcd.apps.googleusercontent.com';
		$OAUTH2_CLIENT_SECRET = 'hOn-jPZN58DNwKEb0xyxo3vg';


		$this->client = new Google_Client();
		$this->client->setClientId($OAUTH2_CLIENT_ID);
		$this->client->setClientSecret($OAUTH2_CLIENT_SECRET);
		// $client->setIncludeGrantedScopes(true);
		$this->client->setAccessType("offline");
		$this->client->setApprovalPrompt ("force");

		$this->client->setScopes('https://www.googleapis.com/auth/youtube', 
		  'https://www.googleapis.com/auth/youtube.force-ssl',
		  'https://www.googleapis.com/auth/youtubepartner');
		$redirect = filter_var('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'],
		    FILTER_SANITIZE_URL);
		$this->client->setRedirectUri($redirect);

		// Define an object that will be used to make all API requests.
		$this->youtube = new Google_Service_YouTube($this->client);

        if (isset($_GET['code'])) {
          if (strval($_SESSION['state']) !== strval($_GET['state'])) {
            die('The session state did not match.');
          }

          $this->client->authenticate($_GET['code']);
          $_SESSION['token'] = $this->client->getAccessToken();
          header('Location: ' . $redirect);

        }

        if (isset($_SESSION['token'])) {
          $this->client->setAccessToken($_SESSION['token']);
        }
   	}

   	public function test(){

   		echo "hello";
   	}
}

?>