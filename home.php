<?php


require_once 'Global.php';


$inst1 = UserFactory::Instance();
$client = $inst1->client;
$yt = $inst1->youtube;
$htmlBody = file_get_contents('content.html');


// Check to ensure that the access token was successfully acquired.
if ($client->getAccessToken()) {
  // This code executes if the user ays tenters an action in the form
  // and submits the form. Otherwise, the page displhe form above.
  if($client->isAccessTokenExpired()) {

      echo "Token expired ";
  }

   if (isset($_GET['videoId']) && $_GET['videoId']) {

    //get list video Id
    $fullDate = $dateValue."T".$timeValue.":00.50Z";

    try {

      $pieces = explode(",", $videoId);

      foreach ($pieces as $key => $value) {
        # code...
        // echo $value;
        setVideoTime($yt, $value, $fullDate , $htmlBody);
      }
      


    } catch (Google_Service_Exception $e) {

      $htmlBody .= sprintf('<p>A service error occurred: <code>%s</code></p>',
          htmlspecialchars($e->getMessage()));

    } catch (Google_Exception $e) {

      $htmlBody .= sprintf('<p>An client error occurred: <code>%s</code></p>',
          htmlspecialchars($e->getMessage()));
    }

  } 
  
  $_SESSION['token'] = $client->getAccessToken();

} else {
  // If the user hasn't authorized the app, initiate the OAuth flow
  $state = mt_rand();
  $client->setState($state);
  $_SESSION['state'] = $state;

  $authUrl = $client->createAuthUrl();
  $htmlBody = <<<END
  <h3>Authorization Required</h3>
  <p>You need to <a href="$authUrl">authorize access</a> before proceeding.<p>
END;
}


/**
 * Returns a list of metadata for a video.
 *
 */



// set public time

function setVideoTime(Google_Service_YouTube $youtube, $videoId, $publicDate, &$htmlBody) {
  // Call the YouTube Data API's videos.list method to retrieve videos.

     // Call the API's videos.list method to retrieve the video resource.
    $listResponse = $youtube->videos->listVideos("status", array('id'=>$videoId));


# array( 'id' => $VIDEO_ID, 'status' => array('privacyStatus' => 'public')));

    // If $listResponse is empty, the specified video was not found.
    if (empty($listResponse)) {

      $htmlBody .= sprintf('<h3>Can\'t find a video with video id: %s</h3>', $videoId);

    } else {
      // Since the request specified a video ID, the response only
      // contains one video resource.
      $video = $listResponse[0];
      $videoStatus = $video['status'];

      $videoStatus->privacyStatus = 'private'; #privacyStatus options are public, private, and unlisted
      $videoStatus->publishAt = $publicDate;//'2016-11-23T07:29:00.50Z';

      $video->setStatus($videoStatus);
      $updateResponse = $youtube->videos->update('status', $video);
    
      $htmlBody .= sprintf('<li> Update success for id: %s </li>', $updateResponse['id']);
      $htmlBody .= '</ul>';
    }
}


?>

<!doctype html>
<html>
<head>
<title>Set and retrieve localized metadata for a video</title>
</head>
<body>
  <?=$htmlBody?>
</body>