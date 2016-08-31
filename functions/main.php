<?php

require_once('lib/simple_html_dom.php');

include('functions/get-cinemas.php');

function get_html( $query ){
  $curl = curl_init();
  curl_setopt($curl, CURLOPT_URL, GOOGLE_MOVIE_URL.$query );
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
  $str = curl_exec($curl);
  curl_close($curl);

  $html = str_get_html($str);
  return $html;
}

?>
