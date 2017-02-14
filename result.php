<?php
	// Start session to retrieve values from server session
	session_start();
	if (isset($_POST["token_id"]) && isset($_SESSION[$_POST["token_id"]])) {
		$downloadList = $_SESSION[$_POST["token_id"]];
		unset($_SESSION[$_POST["token_id"]]);
	}
?>
<html>
	<head>
		<meta charset="utf-8">
		<title>Squeezer</title>
		<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.2.7/semantic.min.css">
	</head>
	<body>
		<div class="ui horizontal menu">
			<div class="item">
				<a href="index.php">Home</a>
			</div>
			<div class="item">
				<a href="about.html">About</a>
			</div>
			<div class="item">
				<a href="contact.html">Contact</a>
			</div>
		</div>
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
				exit();
			} else {
		?>
		<h1 class="ui center aligned header">Thanks for using SQUEEZER...</h1>
		<div class="ui container">

			<form class="ui form" action="" method="post">
				<div class = "ui segments">
					<div class="ui inverted grey segment">
						<div class ="grouped fields">
							<div class="ui top attached teal label">
								<label>Here are the squeezed links:</label>
							</div>
							<br>
							<br>
							<?php
								foreach ($downloadList as $link) {
									echo '
									<div class="field">
										<div class="ui checkbox">
										<input type="checkbox" name="link[]" value="'.$link.'">
										<label>'.$link.'</label>
										</div>
									</div>
									';
								}
							?>

						</div>
					</div>
					<div class = "ui grey inverted segment">
						<div class ="grouped fields">
							<div class=field>
								<div class="ui bottom attached black label">
									<label>Select your squeezed items then hit the download button !</label>
								</div>
							</div>
							<div class="field">
								<button type="submit" name="submit" class="ui yellow button">Download</button>
							</div><br>
						</div>
					</div>
				</div>
			</form>
		</div>

		<?php
			}
		?>
	</body>
</html>
