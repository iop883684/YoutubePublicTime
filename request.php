<?php
//get list video

require_once 'Global.php';

// header('Content-Type: application/json');

$inst1=UserFactory::Instance();
// $inst1->result();
$youtube = $inst1->youtube;
$client = $inst1->client;

function getListVideoID(Google_Service_YouTube $youtube) {
  // Call the search.list method to retrieve results matching the specified
    // query term.
    $searchResponse = $youtube->search->listSearch('id', array(
      'maxResults' => '50',
      'forMine' => true,
      'type' => 'video',
      'pageToken' => $_POST['token']
    ));

    // Add each result to the appropriate list, and then display the lists of
    // matching videos, channels, and playlists.

    $listVideosId = '';

    $pageToken = $searchResponse['nextPageToken'];

    foreach ($searchResponse['items'] as $searchResult) {

          if ($listVideosId == '') {
            
            $listVideosId = $searchResult['id']['videoId'];

          } else{

            $listVideosId .= ', ' .$searchResult['id']['videoId'];
          }
          
          // echo $searchResult['id']['videoId']." ";

    }


    if (count($searchResult) > 0) {
      
      getVideoDetail($youtube, $listVideosId, $pageToken);

    } else{

      sendErrorMessage('no data return');

    }

}


function getVideoDetail(Google_Service_YouTube $youtube, $videoId, $pageToken) {
  // Call the YouTube Data API's videos.list method to retrieve videos.
  $videos = $youtube->videos->listVideos("id, status, snippet", array(
      'id' => $videoId
  ));

  // If $videos is empty, the specified video was not found.
  if (empty($videos)) {

    echo '<h3>Can\'t find a video with video id: %s</h3>'. $videoId;

  } else {
    // Since the request specified a video ID, the response only

    // $array = json_decode(json_encode($videos), true);
    // var_dump($array);
    // echo json_encode($array);
    // print_r(json_decode($videos, true));

    // echo json_encode(object_to_array($videos));

    
    $listPriveVideo = "";
    $listVideosId = array();
    $listVideosTitle = array();
    $listVideoPrivate = array();

    foreach ($videos['items'] as $searchResult) {

      if ($searchResult['status']['privacyStatus'] == "private") {

          if ($listPriveVideo == '') {
            
            $listPriveVideo = $searchResult['id'];

          } else{

            $listPriveVideo .= ',' .$searchResult['id'];
          }
      }

      array_push($listVideosId, $searchResult['id']);
      array_push($listVideosTitle, $searchResult['snippet']['title']);
      array_push($listVideoPrivate, $searchResult['status']['privacyStatus']);
     

    }

     $res = array('status' => 1,
                  'nextPageToken' => $pageToken,
                  'privateVideo' => $listPriveVideo,
                  'listId' => $listVideosId,
                  'listTitle' => $listVideosTitle,
                  'private' => $listVideoPrivate
                  );

    echo json_encode($res);
    
  }
}

function setVideoTime(Google_Service_YouTube $youtube, $videoId, $publicDate) {
  // Call the YouTube Data API's videos.list method to retrieve videos.

     // Call the API's videos.list method to retrieve the video resource.
    $listResponse = $youtube->videos->listVideos("status", array('id'=>$videoId));


    // If $listResponse is empty, the specified video was not found.
    if (empty($listResponse)) {

      sendErrorMessage('Can\'t find a video');

    } else {
      // Since the request specified a video ID, the response only
      // contains one video resource.
      $video = $listResponse[0];
      $videoStatus = $video['status'];

      $videoStatus->privacyStatus = 'private'; #privacyStatus options are public, private, and unlisted
      $videoStatus->publishAt = $publicDate;//'2016-11-23T07:29:00.50Z';

      $video->setStatus($videoStatus);
      $updateResponse = $youtube->videos->update('status', $video);
    
      $message .= '<li> Update success for video id: '.$updateResponse['id'].' </li>';

      $res = array('status' => 1,
                  'message' => $message
                  );

      echo json_encode($res);

    }
}

function sendErrorMessage ($mes){

   $res = array('status' => 0,
                'message' => $mes,
                  );

    echo json_encode($res);

}

// Check to ensure that the access token was successfully acquired.
if ($client->getAccessToken()) {
  // This code executes if the user ays tenters an action in the form
  // and submits the form. Otherwise, the page displhe form above.
  if($client->isAccessTokenExpired()) {

      // echo ("Token expired ");
  }

  if (isset($_POST['token'])) {

    try {
        getListVideoID($youtube);

    } catch (Google_Service_Exception $e) {

      sendErrorMessage($e->getMessage());

    } catch (Google_Exception $e) {

      sendErrorMessage($e->getMessage());
    }

  } else if (isset($_POST['videoId']) && isset($_POST['time']))  {

    try {

        setVideoTime($youtube, $_POST['videoId'], $_POST['time']);

    } catch (Google_Service_Exception $e) {

      sendErrorMessage($e->getMessage());

    } catch (Google_Exception $e) {

      sendErrorMessage($e->getMessage());
    }

  }



}



?>