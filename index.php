<?php
	// Define variables and set to empty values
	$seedPage = "";
	$seedPageErr = "";
	$validation = true;

	if ($_SERVER["REQUEST_METHOD"] == "POST") {
	  if (empty($_POST["seedPage"])) {
	    $seedPageErr = "*Please enter a url to search";
	    $validation = false;
	  } else {
	    $seedPage = test_input($_POST["seedPage"]);
	    // Check if URL address syntax is valid 
	    if (!preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i",$seedPage)) {
	      $seedPageErr = "Invalid URL"; 
	      $validation = false;
	    }
	  } 
	}

	if(isset($_POST['submit']) && $validation) {
		// Start session if submitted
		session_start();

	    // Get input values to variables
		$data_types = array('document', 'image', 'video', 'audio', 'script');
		$selected_data_type = htmlspecialchars($_POST['data_type']);
		$depth = 1;
		$seedPage = htmlspecialchars($_POST['seedPage']);

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

	function test_input($data) {
	  $data = trim($data);
	  $data = stripslashes($data);
	  $data = htmlspecialchars($data);
	  return $data;
	}
?>
<html>
	<head>
		<meta charset="utf-8">
		<title>Squeezer</title>
		<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.2.7/semantic.min.css">
	</head>
	<body>
		<div class="ui container">
			<div class="ui horizontal menu">
				<div class="item">
					<a href="index.php">Home</a>
				</div>
				<div class="item">
					<a href="about.html">About</a>
				</div>
				<div class="item">
					<a href="contact.html">Contact Us</a>
				</div>
			</div>
			<h1 class="ui center aligned grey header">Welcome to SQUEEZER</h1>
			<form class="ui form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
				<div class="ui segments">
					<div class="ui yellow inverted segment">
						<div class="ui grey label" for="seedPage">
							Please enter the url to search
						</div>
						<div class="field" name="URL">
							<input type="text" name="seedPage" placeholder="Search..." value="<?php echo $seedPage;?>">
							<span class="error"><?php echo $seedPageErr;?></span>
						</div>
					</div>
					<div class="ui yellow inverted segment">
						<div class="ui grey label" for="data_type">
							Please select the data type
						</div>
						<div class="two wide field" name="data_type">
							<select class="ui simple dropdown" name="data_type">
								<option value="document">Document</option>
								<option value="image">Image</option>
								<option value="video">Video</option>
								<option value="audio">Audio</option>
								<option value="script">Script</option>
							</select>
						</div>
					</div>
					<div class="ui yellow inverted segment">
						<button type="submit" name="submit" class="ui grey button">Search</button>
					</div>
				</div>
			</form>
		</div>
	</body>
</html>