<?php

$dataType = null;
$depth = null;
$ceedLink = null;


/*
form area
*/

include 'crawlerClass.php';
$crawler = new crawler($dataType, $depth, $ceedPage);

$crawler->prepareCrawling();
$crawler->crawl();
$downloadLinks = $crawler->getDownloadList();

/*
action area
*/

?>