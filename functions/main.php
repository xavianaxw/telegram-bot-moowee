<?php

require_once('lib/simple_html_dom.php');

include('functions/get-cinemas.php');
include('functions/get-movies.php');

function get_html( $query ){
  log_error(GOOGLE_MOVIE_URL.$query);
  $curl = curl_init();
  curl_setopt($curl, CURLOPT_URL, GOOGLE_MOVIE_URL.$query );
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
  $str = curl_exec($curl);
  curl_close($curl);

  $html = str_get_html($str);
  return $html;
}

function log_error( $message ){
  error_log("[".date('Y-m-d-H:i:s')."] ".$message.PHP_EOL, 3, "error.log");
}

?>
