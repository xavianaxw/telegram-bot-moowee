<?php

/**
 *
 *   get_cinemas()
 *
 *   Parameters
 *   $location - keyword or coordinate of user's current location
 *
 **/
function get_cinemas( $location )
{
  log_error('get_cinema');
  $query = "near=".$location;

  $html = get_html($query);
  log_error('retrieved html');

  $results = array();

  foreach($html->find('#movie_results .theater') as $div) {
    log_error('found a theatre');
    $result = array();

    $theatre_id = $div->find('h2 a',0)->getAttribute('href');
    $theatre_info = $div->find('.info',0)->innertext;

    $result['theatre_id'] = substr( $theatre_id, (strrpos($theatre_id, 'tid=')+4) );
    $result['theatre_name'] = $div->find('h2 a',0)->innertext;

    $pos_info = strrpos( $theatre_info, '<a');
    if( $pos_info !== false )
      $result['theatre_info'] = substr( $theatre_info, 0, strrpos( $theatre_info, '<a'));
    else
      $result['theatre_info'] = $theatre_info;

    /*$result['movies'] = array();
    foreach($div->find('.movie') as $movie) {

      $movie_id = $movie->find('.name a',0)->getAttribute('href');
      $movie_id = substr( $movie_id, (strrpos($movie_id, 'mid=')+4) );

      $pos = strrpos($movie->find('.info',0)->innertext, ' - <a');
      $info = $movie->find('.info',0)->innertext;

      if( $pos !== false ){
        $trailer = urldecode($movie->find('.info a', 0)->getAttribute('href'));
        $trailer = substr($trailer, 7);
        $trailer = substr($trailer, 0, strpos($trailer, '&'));
      }
      else {
        $trailer = '';
      }

      // showtimes
      $times = array();
      // echo 'movie: '.$movie->find('.name a',0)->innertext.'<br>';
      // echo $movie->find('.times',0)->childNodes(0);
      foreach($movie->find('.times > span') as $time) {
        // echo 'time: '.$time->innertext.'<br>';
        // echo 'attr: '.$time->getAttribute('style').'<br>';

        if( trim($time->getAttribute('style')) != 'padding:0' )
        {
          $pos_time = false;
          $pos_time = strrpos($time->innertext, '-->');
          if( $pos_time !== false ){
            $showtime = substr($time->innertext, $pos_time+3);
            //echo $showtime.'<br>';
          }
          //echo $time;
          array_push( $times, $showtime );
        }
      }

      array_push($result['movies'], array(
        'id' => $movie_id,
        'name' => $movie->find('.name a',0)->innertext,
        'info' => ($pos !== false) ? substr( $info, 0, $pos ) : $info, // (does <a> exist) ? (yes) : (no)
        'time' => $times,
        'trailer' => $trailer
      ));
    }*/

    $results[] = $result;
  }

  return $results;
}

?>
