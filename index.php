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
		<h1 class="ui center aligned header">Welcome to SQEEZER</h1>
		<div class="ui container">
			<form class="ui form" action="index.php" method="post">
				<div class="ui segments">
					<div class="ui grey inverted segment">
						<div class="ui teal label">
							<label for="seed_page">Please enter the url to search</label>
						</div>
						<input type="text" name="seed_page" placeholder="Search...">
					</div>
					<div class="ui grey inverted segment">
						<div class="ui teal label">
							<label for="data_type">Please select the extension</label>
						</div>
						<select class="ui simple dropdown" name="data_type">
							<option value="document">Document</option>
							<option value="image">Image</option>
							<option value="video">Video</option>
							<option value="audio">Audio</option>
							<option value="script">Script</option>
						</select>
					</div>
					<div class="ui grey inverted segment">
						<button type="submit" name="submit" class="ui yellow button">Search</button>
					</div>
				</div>
			</form>
		</div>
	</body>
</html>

<?php
	if(isset($_POST['submit'])) {
		// Start session if submitted
		session_start();

	    // Get input values to variables
		$data_types = array('document', 'image', 'video', 'audio', 'script');
		$selected_data_type = htmlspecialchars($_POST['data_type']);
		$depth = 1;
		$seedPage = htmlspecialchars($_POST['seed_page']);

		// Create crawler
		include 'crawlerClass.php';
		$crawler = new Crawler($selected_data_type, $depth, $seedPage);
		$crawler->prepareCrawling();
		$crawler->crawl();
		$downloadList = $crawler->getDownloadList();

		// Transfer download list to result page
		$token = md5(uniqid());
		$_SESSION[$token] = $downloadList;
		echo '
		<form id= "token_form" action="result.php" method="post">
		<input type="hidden" name="token_id" value="'.$token.'">
		</form>
		<script>
			document.getElementById("token_form").submit();
		</script>
		';
	}
?>
