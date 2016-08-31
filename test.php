<?php

ini_set("allow_url_fopen", false);

error_reporting(E_ALL);
ini_set('display_errors', 1);

define('GOOGLE_MOVIE_URL', 'http://www.google.ie/movies?');

//include('functions.php');
include('functions/main.php');

/*$input = '/cinema@MooweeBot 41a45e05c61f00be';
$pos = strpos($input, ' ');

$cmd = substr( $input, 0, $pos );
$cmd = str_replace('@MooweeBot', '', $cmd);

$query = substr( $input, $pos+1);

echo $cmd.' '.$query.'<br>';*/

/*$results = get_cinemas('3.0789069,101.5873401');
echo '<pre>'; print_r( $results ); echo '</pre>';*/

/*$inline_keyboards = [];

$theatres = array();
$results = get_cinemas( urlencode('Subang Jaya') );

foreach( $results as $r ){
  array_push($inline_keyboards, [[
    'text' => $r['theatre_name'],
    'callback_data' => 'cinema:'.$r['theatre_id']
  ]]);
}

echo '<pre>'; print_r( $inline_keyboards ); echo '</pre>';*/

$update = json_decode('{"update_id":548445901,"callback_query":{"id":"666285102099291889","from":{"id":155131589,"first_name":"Xavian","last_name":"Ang","username":"xavianaxw"},"message":{"message_id":462,"from":{"id":251951084,"first_name":"Moowee Bot","username":"MooweeBot"},"chat":{"id":155131589,"first_name":"Xavian","last_name":"Ang","username":"xavianaxw","type":"private"},"date":1472637790,"text":"Select a cinema"},"data":"cinema:d76e1c1b4aefe8b7"}}', true);

$query = $update['callback_query']['data'];
$url = array();

array_push($url, "mid=1234567890");

// structure type:value (e.g. cinema:4fe465a8fad6dd65)
$filters = explode(' ', $query);
foreach( $filters as $f ){
  $info = explode(':', $f);

  switch( $info[0] ) // represents filter - cinema, movie, location
  {
    case 'cinema': array_push($url, 'tid='.$info[1]); break;
    // case 'movie': break;
    // case 'location': break;
  }
}

$url = implode('&', $url);
echo $url;

?>
