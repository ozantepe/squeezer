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
			<h1 class="ui header">Welcome to Squeezer</h1>
			<form class="ui form" action="" method="post">
				<label for="seed_page">Please enter the url to search</label>
				<div class="ten wide field">
					<input type="text" name="seed_page" placeholder="Search...">
				</div>
				<label for="data_type">Please select the extension</label>
				<div class="two wide field">
					<select class="ui compact selection dropdown" name="data_type">
						<option value="document">Document</option>
						<option value="image">Image</option>
						<option value="video">Video</option>
						<option value="audio">Audio</option>
						<option value="script">Script</option>
					</select>
					<br><br>
					<button type="submit" name="submit" class="ui button">Search</button>	
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
		$_SESSION['downloadList'] = $downloadList;
		header('Location: result.php');				
	}
?>