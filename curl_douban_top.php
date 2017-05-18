<?php
	for ($start = 0; $start < 250; $start += 25) {
	    $url = "http://movie.douban.com/top250?start=$start&filter=&type=";
	    $titles = parsePage($url);
	    if ($titles === false) {
	        echo $url, "\n";
	    } else {
	        array_walk($titles, 'printTitle');
	    }
	}
 
 
function parsePage($url) {
    $html = file_get_contents($url);
    if ($html === false) {
        return false;
    }
     
    if (preg_match_all('/<a.+?<span class="title">([^<]+)/s', $html, $matches) === false) {
        return false;
    }
     
    $titles = array();
    foreach($matches[1] as $item) {
        $titles[] =$item;
    }
    return $titles;
}
 
 
$count = 0;
function printTitle($title) {
    global $count;
    ++$count;
    print_r($count." ".$title."<br/>");
}