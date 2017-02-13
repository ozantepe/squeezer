<?php
	// Start session to retrieve values from server session
	session_start(); 
	$downloadList = $_SESSION['downloadList'];
?>
<html>
	<head>
		<meta charset="utf-8">
		<title>Squeezer</title>
		<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.2.7/semantic.min.css">
	</head>
	<body>
		<div class="ui vertical compact menu">
			<div class="item">
				<a href="#">Home</a>
			</div>
			<div class="item">
				<a href="#">About</a>
			</div>
			<div class="item">
				<a href="#">Contact</a>
			</div>
		</div>
		<div class="ui container">
			<h1 class="ui header">Thanks for using Squeezer...</h1>
			<form class="ui form" action="" method="post">
				<label>Here are the squeezed links:</label><br><br>
				<?php
					foreach ($downloadList as $link) {
						echo '<input type="checkbox" name="link[]" value="'.$link.'"> '.$link.'<br>';
					}
				?>
				<br>
  				<label>Select your squeezed items then hit the download button !</label><br><br>
				<button type="submit" name="submit" class="ui button">Download</button><br><br>
			</form>	
		</div>
	</body>
</html>

<?php
	// If links selected and the download button clicked,
	if(isset($_POST['submit']) && isset($_POST['link'])) {
		// Add selected links to download list
		$downloadList = $_POST['link'];

		// Create downloader and download the list
		include 'downloaderClass.php';
		$downloader = new Downloader($downloadList);
		$downloader->prepareDownloading();
		$downloader->multiDownload();
		$downloader->zipData();
		$downloader->downloadZippedData();
	}
?>