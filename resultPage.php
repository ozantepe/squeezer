<?php
$downloadLinks = null;
/*
form area
*/

include 'downloaderClass.php';
$downloader = new downloader();
$downloader->prepareDownloading();
$downloader->download();
$downloader->zipData();
$zippedData = $downloader->getZippedData();

/*
action area
*/
?>