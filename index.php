<?php

/**
 *
 *    Telegram Bot: Moowee
 *    Description
 *    Making things a wee bit easier when looking for movie showtimes. Moo~!
 *
 *    Author: Xavian Ang <xavianaxw@gmail.com>
 *
 *    Contributors:
 *    - Tracy Wee
 *
 **/

ini_set("allow_url_fopen", false);

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

define('BOT_TOKEN', '251951084:AAGSO5K_mbJo4Niw64xTd-ruEjMWpJOVdWE');
define('API_URL', 'https://api.telegram.org/bot'.BOT_TOKEN);
define('BR', '&#13;&#10;');
define('GOOGLE_MOVIE_URL', 'https://www.google.com/movies?');

include('functions/main.php');

// Input (Without Webhook)
// $update = file_get_contents(API_URL.'/getupdates');
// $update = json_decode($update, true);
// $update = (isset($update['result'][0])) ? $update['result'][0] : NULL;

// Input (With Webhook)
$update = file_get_contents('php://input');
$update = json_decode($update, true);

// echo '<pre>'; print_r( $update ); echo '</pre>'; //die();

if( !$update ){
  exit;
}

if( isset($update['message']) ){
  $msg_id = $update['message']['message_id'];
  $chat_id = $update['message']['chat']['id'];
  $type = $update['message']['chat']['type']; // private or group
  $name = ($type == 'private') ? $update['message']['chat']['first_name'] : $update['message']['from']['first_name'];

  if( isset($update['message']['text']) )
  {
    // replace @MooweeBot should it be used
    $msg = stripslashes($update['message']['text']);

    // find position of first break to separate cmd from query
    $pos = strpos($msg, ' ');

    // get command
    if( $pos !== false ){
      $cmd = substr( $msg, 0, $pos );
      $query = substr( $msg, $pos+1);
    }
    else {
      $cmd = $msg;
    }

    $cmd = str_replace('@MooweeBot', '', $cmd);

    // file_get_contents(API_URL."/sendMessage?chat_id=155131589&text=cmd:".urlencode($cmd).'/'.urlencode(json_encode($update)));

    switch( $cmd ){
      case "/start":
        $reply = "Moo thinks /help might help";
        file_get_contents(API_URL."/sendMessage?chat_id=".$chat_id."&text=".urlencode($reply)."&parse_mode=HTML");
        break;
      case "/help":
        $reply = "Moo need help?";
        file_get_contents(API_URL."/sendMessage?chat_id=".$chat_id."&text=".urlencode($reply)."&parse_mode=HTML");
        break;
      case "/hello":
        $reply = "Hello " . ( ($type == 'private') ? $name : $name." of <b>".$update['message']['chat']['title']."</b>" );
        file_get_contents(API_URL."/sendMessage?chat_id=".$chat_id."&text=".urlencode($reply)."&parse_mode=HTML");
        break;
      case "/cinema":
        $reply = "Searching movie info by cinema: <b>".$query."</b>";
        file_get_contents(API_URL."/sendMessage?chat_id=".$chat_id."&text=".urlencode($reply)."&parse_mode=HTML");
        break;
      case "/creator":
        $reply = $name." stop asking who the creator is! Moooooo!";
        file_get_contents(API_URL."/sendMessage?chat_id=".$chat_id."&text=".urlencode($reply));
        break;
      case "/nearby":
        if( $type == 'private' ){
          if( isset($query) ){
            $results = get_cinemas( urlencode($query) );

            if( isset($results) && count($results) )
            {
              $inline_keyboards = array();
              foreach( $results as $r ){
                array_push($inline_keyboards, array(
                  array(
                    'text' => $r['theatre_name'],
                    'callback_data' => "near:".urlencode($query)."-tid:".$r['theatre_id']
                  )
                ));
              }

              $reply_markup = array(
                'inline_keyboard' => $inline_keyboards
              );

              file_get_contents(API_URL."/sendMessage?chat_id=".$chat_id."&text=".urlencode("Select a cinema to display showtimes")."&parse_mode=HTML&reply_markup=".urlencode(json_encode($reply_markup)));
            }
            else
            {
              file_get_contents(API_URL."/sendMessage?chat_id=".$chat_id. "&text=".urlencode("Oh Moo! There are no cinemas near you"). "&parse_mode=HTML");
            }
          }
          else {
            $reply_markup = array(
              'keyboard' => array(
                array(
                  array(
                    'text' => 'Send us your location',
                    'request_location' => true
                  )
                )
              ),
              'one_time_keyboard' => true
            );
            file_get_contents(API_URL."/sendMessage?chat_id=".$chat_id. "&text=".urlencode('Moo wants to know where you are'). "&parse_mode=HTML&reply_markup=".urlencode(json_encode($reply_markup)));
          }
        }
        else {
            $reply_markup = array(
              'force_reply' => true,
              'selective' => true
            );
            file_get_contents(API_URL."/sendMessage?chat_id=".$chat_id. "&text=".urlencode('Moo wants to know where you are'). "&reply_markup=".urlencode(json_encode($reply_markup))."&reply_to_message_id=".$msg_id);

            //file_get_contents(API_URL."/sendMessage?chat_id=".$chat_id."&text=".urlencode('Oh Moo! /nearby command does not work in group chats'));
        }
        break;
    }
  }
  // when location is sent when request_location button is clicked
  // currently only supports private chat
  else if( isset($update['message']['location']) )
  {
    $chat_id = $update['message']['chat']['id'];
    $location = $update['message']['location']['latitude'].','.$update['message']['location']['longitude'];

    // Hide request_location
    file_get_contents(API_URL."/sendMessage?chat_id=".$chat_id. "&text=".urlencode("Finding cinemas near your location"). "&parse_mode=HTML&reply_markup=".urlencode(json_encode(array('hide_keyboard' => true))));

    $results = get_cinemas($location);

    if( isset($results) && count($results) ){
      $inline_keyboards = array();
      foreach( $results as $r ){
        array_push($inline_keyboards, array(
          array(
            'text' => $r['theatre_name'],
            'callback_data' => "coords:".$location."-tid:".$r['theatre_id']
          )
        ));
      }

      $reply_markup = array(
        'inline_keyboard' => $inline_keyboards
      );

      file_get_contents(API_URL."/sendMessage?chat_id=".$chat_id. "&text=".urlencode("Select a cinema to display showtimes"). "&parse_mode=HTML&reply_markup=".urlencode(json_encode($reply_markup)));
    }
    else
    {
      file_get_contents(API_URL."/sendMessage?chat_id=".$chat_id. "&text=".urlencode("Oh Moo! There are no cinemas near you"). "&parse_mode=HTML");
    }
  }
}
else if( isset($update['inline_query']) ) {

  $query_id     = $update['inline_query']['id'];
  $query_text   = $update['inline_query']['query'];
  $chat_id      = $update['inline_query']['from']['id'];

  $inlineQueryResults = array(
    array(
      'type' => 'article',
      'id' => '0',
      'title' => 'Article One',
      'description' => 'This is an example for article one',
      'message_text' => 'test',
      /*'input_message_content' => array(
        'message_text' => 'This is an <b>example</b> for article one',
        'parse_mode' => 'HTML'
      ),*/
      'reply_markup' => array(
        'inline_keyboard' => array(
          array(
            array(
              'text' => 'Test One',
              'url' => 'https://www.youtube.com/watch?v=mJU-JkgJyFM'
            ),
            array(
              'text' => 'Test Two',
              'callback_data' => 'second'
            )
          )
        )
      )
    ),
    array(
      'type' => 'article',
      'id' => '1',
      'title' => 'Article Two',
      'description' => 'This is an example for article two',
      'input_message_content' => array(
        'message_text' => 'This is an example for article two'
      )
    )
  );

  $updateReq = urlencode(json_encode($update));
  $msg = json_encode($inlineQueryResults);
  echo API_URL."/answerInlineQuery?inline_query_id=".$query_id."&results=".$msg;
  file_get_contents(API_URL."/sendMessage?chat_id=".$chat_id."&text=".$updateReq);
  file_get_contents(API_URL."/answerInlineQuery?inline_query_id=".$query_id."&results=".$msg);
  /*apiRequestJson("answerInlineQuery", array(
    'inline_query_id' => $query_id,
    'results' => $inlineQueryResults
  ));*/

  /*$query_id = $update['inline_query']['id'];
  $query_text = $update['inline_query']['query'];

  $inlineQueryResults = array(
    array(
      'type' => 'article',
      'id' => '0',
      'title' => 'Article One',
      'message_text' => 'This is an example for article one'
    ),
    array(
      'type' => 'article',
      'id' => '0',
      'title' => 'Article Two',
      'message_text' => 'This is an example for article two'
    )
  );

  echo '<pre>'; print_r($inlineQueryResults); echo '</pre>'; die();
  file_get_contents(API_URL."/answerInlineQuery?inline_query_id=".$query_id."&results=".urlencode(json_encode($inlineQueryResults)));*/
}
else if( isset($update['callback_query']) ){
  // file_get_contents(API_URL."/sendMessage?chat_id=155131589&text=".urlencode(json_encode($update)));

  $callback_id = $update['callback_query']['id'];
  $chat_id = $update['callback_query']['message']['chat']['id'];
  $query = $update['callback_query']['data'];
  $url = array();

  $filters = explode('-', $query);
  foreach( $filters as $f ){
    $data = explode(':', $f);

    if( $data[0] == 'near' )
      array_push($url, $data[0].'='.urlencode($data[1]) );
    else if( $data[0] == 'coords' )
      array_push($url, 'near='.$data[1] );
    else
      array_push($url, $data[0].'='.$data[1] );
  }
  $url = implode('&', $url);

  $results = get_movies( $url );

  $output = "<b>Showing results for ".$results[0]['theatre_name'].'</b>&#13;&#10;'.$results[0]['theatre_info'];
  $output .= "&#13;&#10;&#13;&#10;";

  if( isset($results[0]['movies']) ){
    foreach( $results[0]['movies'] as $m ){
      $output .= '<b>'.$m['name'].'</b>&#13;&#10;';
      $output .= $m['info'].'&#13;&#10;';
      foreach( $m['time'] as $t ){
        $output .= $t.' ';
      }
      $output .= '&#13;&#10;&#13;&#10;';
    }
  }
  else {
    $output .= "No results found";
  }

  file_get_contents(API_URL."/answerCallbackQuery?callback_query_id=".$callback_id);

  file_get_contents(API_URL."/sendMessage?chat_id=".$chat_id."&text=".urlencode($output).'&parse_mode=HTML');
}
else {
  file_get_contents(API_URL."/sendMessage?chat_id=155131589&text=".urlencode(json_encode($update)));
  // die('nothing also');
}

?>
