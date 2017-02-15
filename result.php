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
		<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
	</head>
	<body>
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
			}else {
		?>
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
			<h1 class="ui center aligned grey header">Thanks for using SQUEEZER...</h1>
			<form class="ui form" action="" method="post">
				<div class = "ui segments">
					<div class="ui yellow inverted segment">
						<div class="ui grey label">
							<label>Here are the squeezed links</label>
						</div>
					</div>
					<div class="ui yellow inverted segment">	
						<div class="ui container">
							<div class="ui grey inverted segment">
								<?php
									if (isset($downloadList)) {
										foreach ($downloadList as $link) {
											echo '
											<div class="field">
												<div class="ui checkbox">
													<input class="checkbox" type="checkbox" name="link[]" value="'.$link.'">
													<label>'.$link.'</label>
												</div>
											</div>
											';
										}
									}else {
										header("Location: index.php");
									}
								?>
								<br>
								<div class="field">
									<div class="ui checkbox">
										<input class="checkbox" type="checkbox" name="select_all" id="select_all">
										<label>Select All</label>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class = "ui yellow inverted segment">
						<div class ="grouped fields">
							<div class="field">
								<div class="ui bottom attached grey label">
									<label>Select your squeezed items then hit the download button !</label>
								</div>
							</div>
							<div class="field">
								<button type="submit" name="submit" class="ui grey button">Download</button>
							</div><br>
						</div>
					</div>
				</div>
			</form>
		</div>
		<?php
			}
		?>
		<script type="text/javascript">	
			// Select all checkboxes
			$("#select_all").change(function() {  // "select_all" change
				// Change all ".checkbox" checked status 
			    $(".checkbox").prop('checked', $(this).prop("checked"));
			});
			// ".checkbox" change 
			$('.checkbox').change(function() { 
			    // Uncheck "select_all", if one of the listed checkbox item is unchecked
			    if(false == $(this).prop("checked")){ // If this item is unchecked
			    	// Change "select_all" checked status to false
			        $("#select_all").prop('checked', false); 
			    }
			    // Check "select_all" if all checkbox items are checked
			    if ($('.checkbox:checked').length == $('.checkbox').length ){
			        $("#select_all").prop('checked', true);
			    }
			});
		</script>
		</body>
</html>