<?php

/**
 * This sample sets and retrieves localized metadata for a video by:
 *
 * 1. Updating language of the default metadata and setting localized metadata
 *   for a video via "videos.update" method.
 * 2. Getting the localized metadata for a video in a selected language using the
 *   "videos.list" method and setting the "hl" parameter.
 * 3. Listing the localized metadata for a video using the "videos.list" method and
 *   including "localizations" in the "part" parameter.
 *
 * @author Ibrahim Ulukaya
 */

$videoId = $_GET['videoId'];
$action = $_GET['action'];
$dateValue = $_GET['date'];
$timeValue = $_GET['time'];
$pageToken = $_GET['pageToken'];

$htmlBody = 
<<<END


<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<script src="https://jonthornton.github.io/jquery-timepicker/jquery.timepicker.js"></script>
<link rel="stylesheet" type="text/css" href="https://jonthornton.github.io/jquery-timepicker/jquery.timepicker.css" />

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.5.0/js/bootstrap-datepicker.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.5.0/css/bootstrap-datepicker.standalone.css" />


<script src="http://jonthornton.github.io/Datepair.js/dist/datepair.js"></script>
<script src="http://jonthornton.github.io/Datepair.js/dist/jquery.datepair.js"></script>

<br>
<button type="button" onclick="clearParams()">Reset</button>
<br>
-------------------------------------------------
<br>
<br>
<form id="form2" name="form1" method="GET">
<input type="submit" name="action"  value="Get list video" >
</form>


<input type="submit" class="button" name="load more" value="insert" />

<form method="GET" id="testformid">
  <div>

  </div>
  <br>
  <div>

    List video id: (private video only)
    <br>
    <textarea form ="testformid" name="videoId" id="taid" cols="35" wrap="soft">$videoId</textarea>

    <br><br>
    Select public time:
   
    <p id="datepairExample">
    <input type="text" name="date" class="date start" />
    <input type="text" name="time" class="time start" /> 
    </p>
    
  </div> 
  <input type="submit" value="Scheduler Pulbic">
</form>
-------------------------------------------------
<br>
<br>

<script>
    // initialize input widgets first
    $('#datepairExample .time').timepicker({
        'disableTextInput':false,
        'showDuration': true,
        'timeFormat': 'G:i'
    });
  

    $('#datepairExample .date').datepicker({
        'disableTextInput':false,
        'format': 'yyyy-mm-dd',
        'autoclose': true
    });

    $('#datepairExample .time').timepicker('setTime', new Date());  
    $('#datepairExample .date').datepicker('setDate', new Date());

    // initialize datepair
    $('#datepairExample').datepair();

    function clearParams () {
        window.history.pushState({}, "Hide", "youtube.php");
    }

    function updateVideoList (lists){

      $("#taid").val( lists );
    }

    $(document).ready(function(){
        $('.button').click(function(){
            var clickBtnValue = $(this).val();
            var ajaxurl = 'request.php',
            data =  {'action': clickBtnValue};
            $.post(ajaxurl, data, function (response) {
                // Response div goes here.
                alert(response);
            });
        });

    });


</script>

END;

// Call set_include_path() as needed to point to your client library.

set_include_path(get_include_path() . PATH_SEPARATOR . 'google-api-php-client/src');

require_once 'Google/autoload.php';

  require_once 'Google/Client.php';
  require_once 'Google/Service/YouTube.php';
  session_start();


/*
 * You can acquire an OAuth 2.0 client ID and client secret from the
 * Google Developers Console <https://console.developers.google.com/>
 * For more information about using OAuth 2.0 to access Google APIs, please see:
 * <https://developers.google.com/youtube/v3/guides/authentication>
 * Please ensure that you have enabled the YouTube Data API for your project.
 */
$OAUTH2_CLIENT_ID = '1059495052682-pspshs7kbn3u09os8jqd0kvodh29mpcd.apps.googleusercontent.com';
$OAUTH2_CLIENT_SECRET = 'hOn-jPZN58DNwKEb0xyxo3vg';


$client = new Google_Client();
$client->setClientId($OAUTH2_CLIENT_ID);
$client->setClientSecret($OAUTH2_CLIENT_SECRET);
// $client->setIncludeGrantedScopes(true);
$client->setAccessType("offline");
$client->setApprovalPrompt ("force");


/*
 * This OAuth 2.0 access scope allows for full read/write access to the
 * authenticated user's account.
 */
$client->setScopes('https://www.googleapis.com/auth/youtube', 
  'https://www.googleapis.com/auth/youtube.force-ssl',
  'https://www.googleapis.com/auth/youtubepartner');
$redirect = filter_var('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'],
    FILTER_SANITIZE_URL);
$client->setRedirectUri($redirect);


// Define an object that will be used to make all API requests.
$youtube = new Google_Service_YouTube($client);

if (isset($_GET['code'])) {
  if (strval($_SESSION['state']) !== strval($_GET['state'])) {
    die('The session state did not match.');
  }

  $client->authenticate($_GET['code']);
  $_SESSION['token'] = $client->getAccessToken();
  header('Location: ' . $redirect);

}

if (isset($_SESSION['token'])) {
  $client->setAccessToken($_SESSION['token']);
}

// Check to ensure that the access token was successfully acquired.
if ($client->getAccessToken()) {
  // This code executes if the user ays tenters an action in the form
  // and submits the form. Otherwise, the page displhe form above.
  if($client->isAccessTokenExpired()) {

      echo "Token expired ";
  }

  if (isset($_GET['action']) && $_GET['action']) {

    try {

          getListVideoID($youtube, $htmlBody);


    } catch (Google_Service_Exception $e) {

      $htmlBody .= sprintf('<p>A service error occurred: <code>%s</code></p>',
          htmlspecialchars($e->getMessage()));

    } catch (Google_Exception $e) {

      $htmlBody .= sprintf('<p>An client error occurred: <code>%s</code></p>',
          htmlspecialchars($e->getMessage()));
    }

  } else if (isset($_GET['videoId']) && $_GET['videoId']) {

    //get list video Id
    $fullDate = $dateValue."T".$timeValue.":00.50Z";

    try {

      $pieces = explode(",", $videoId);

      foreach ($pieces as $key => $value) {
        # code...
        // echo $value;
        setVideoTime($youtube, $value, $fullDate , $htmlBody);
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
 * Returns localized metadata for a video in a selected language.
 * If the localized text is not available in the requested language,
 * this method will return text in the default language.
 *
 * @param Google_Service_YouTube $youtube YouTube service object.
 * @param string $videoId The videoId parameter instructs the API to return the
 * localized metadata for the video specified by the video id.
 * @param $htmlBody - html body.
 */



function getListVideoID(Google_Service_YouTube $youtube, &$htmlBody) {
  // Call the search.list method to retrieve results matching the specified
    // query term.
    $searchResponse = $youtube->search->listSearch('id', array(
      'maxResults' => '5',
      'forMine' => true,
      'type' => 'video',
      'pageToken' => $pageToken
    ));

    // Add each result to the appropriate list, and then display the lists of
    // matching videos, channels, and playlists.

    $listVideosId = '';

    $pageToken = $searchResponse['pageToken'];

    foreach ($searchResponse['items'] as $searchResult) {

          if ($listVideosId == '') {
            
            $listVideosId = $searchResult['id']['videoId'];

          } else{

            $listVideosId .= ', ' .$searchResult['id']['videoId'];
          }
          
          // $htmlBody .= sprintf('<li> %s</li>', $searchResult['id']['videoId']);


    }


    if (count($searchResult) > 0) {
      
      getVideoDetail($youtube, $listVideosId, $htmlBody);

    }
}

function getData(){

  echo "heello";
  $htmlBody .= sprintf("get data");
}

/**
 * Returns a list of metadata for a video.
 *
 */
function getVideoDetail(Google_Service_YouTube $youtube, $videoId, &$htmlBody) {
  // Call the YouTube Data API's videos.list method to retrieve videos.
  $videos = $youtube->videos->listVideos("id, status, snippet", array(
      'id' => $videoId
  ));

  // If $videos is empty, the specified video was not found.
  if (empty($videos)) {

    $htmlBody .= sprintf('<h3>Can\'t find a video with video id: %s</h3>', $videoId);

  } else {
    // Since the request specified a video ID, the response only
    // contains one video resource.

    $htmlBody .= "<h3>Video Status: </h3><ul>";

    $listPriveVideo = "";

    foreach ($videos['items'] as $searchResult) {

      $htmlBody .= sprintf('<li>%s -- %s -- %s </li>', $searchResult['id'], $searchResult['snippet']['title'], $searchResult['status']['privacyStatus']);


      if ($searchResult['status']['privacyStatus'] == "private") {

          if ($listPriveVideo == '') {
            
            $listPriveVideo = $searchResult['id'];

          } else{

            $listPriveVideo .= ',' .$searchResult['id'];
          }
      }

     

    }

    $htmlBody .= '</ul>';

    // $htmlBody =  str_replace("video_id", $listPriveVideo , $htmlBody);
    $htmlBody .= '<script type="text/javascript"> updateVideoList("'.$listPriveVideo.'");</script>';
  }
}


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
</html>