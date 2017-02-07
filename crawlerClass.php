<?php 

class Crawler {
	
	private $dataType;        			// Data type which is given by user
	private $depth;   		  			// Depth of crawling
	private $seedPage; 		  			// Targeted site url
	private $linksCrawled = array(); 	// Array for already crawled links
	private $linksToCrawl = array();    // Array for links which is gonna be crawled
	private $downloadList = array();    // Array for download links which has desired data types
	private $info = array();			// Array for infos of links
	private $options;					// User-Agent options
	
	// Constructor of crawler class
	public function __construct($dataType, $depth, $seedPage) {
		$this->dataType = $dataType;
		$this->depth = $depth;
		$this->seedPage = $seedPage;
	}
	
	// Creating user-agent settings
	public function prepareCrawling() {
		$this->options = array('http'=>array('method'=>"GET", 'headers'=>"User-Agent: squeezerBot\n"));
		$context = stream_context_create($this->options);
	}
		
	// Main function of crawling process
	public function crawl() {
		
		$url = $this->seedPage;
		
		// Getting targeted site as Document Object Model
		$dom = new DOMDocument();
		//@Suppress warnings
		@$dom->loadHTML(file_get_contents($url, false, $context));  
		
		// Getting tagged elements of DOM
		$linkArray = $dom->getElementsByTagName("a");
		foreach ($linkArray as $link) {
			
			// Getting tagged elements' attributes, a.k.a. links
			$curr = $link->getAttribute("href");
			
			// Fixing strings as desired, using php's substr and parse_url functions
			if (substr($curr, 0, 1) == "/") {
				$curr = parse_url($url)["scheme"]."://".parse_url($url)["host"].$curr;
			}else if (substr($curr, 0, 1) == "#") {
				$curr = parse_url($url)["scheme"]."://".parse_url($url)["host"].parse_url($url)["path"].$curr;
			}else if (substr($curr, 0, 5) != "https" && substr($curr, 0, 4) != "http") {
				$curr = parse_url($url)["scheme"]."://".parse_url($url)["host"].parse_url($url)["path"].$curr;
			}
			
			// Get link if it's data type equals to given data type
			$length = strlen($curr);
			if (substr($curr, $length-4) == $this->dataType) {
				$this->downloadList[] = $curr;
			}
			
			// Check if the current link has already been crawled
			if (!in_array($curr, $this->linksCrawled)) {
				
				// Store current link to linksCrawled
				$this->linksCrawled[] = $curr;
				// Store current link to linksToCrawled as well
				$this->linksToCrawl[] = $curr;
				
				// Get details of the current link
				//getDetails($curr);  
			}
		}

	
		// Crawling linksToCrawl
		while (!empty($this->linksToCrawl)) {
			$link = array_shift($this->linksToCrawl);
			// Crawling deeper links amount of depth
			for ($j = 0; $j < $this->depth; $j++) {
				$this->crawlDeep($link);
			}			
		}
	}
	
	// Function which crawls once
	public function crawlDeep($url) {
		
		// Getting targeted site as Document Object Model
		$dom = new DOMDocument();
		@$dom->loadHTML(file_get_contents($url, false, $context));   //suppress warnings
		
		// Getting tagged elements of DOM
		$linkArray = $dom->getElementsByTagName("a");
		foreach ($linkArray as $link) {
			
			// Getting tagged elements' attributes, a.k.a. links
			$curr = $link->getAttribute("href");
			
			// Fixing strings as desired, using php's substr and parse_url functions
			if (substr($curr, 0, 1) == "/") {
				$curr = parse_url($url)["scheme"]."://".parse_url($url)["host"].$curr;
			}else if (substr($curr, 0, 1) == "#") {
				$curr = parse_url($url)["scheme"]."://".parse_url($url)["host"].parse_url($url)["path"].$curr;
			}else if (substr($curr, 0, 5) != "https" && substr($curr, 0, 4) != "http") {
				$curr = parse_url($url)["scheme"]."://".parse_url($url)["host"].parse_url($url)["path"].$curr;
			}
			
			// Get link if it's data type equals to given data type
			$length = strlen($curr);
			if (substr($curr, $length-4) == $this->dataType) {
				$this->downloadList[] = $curr;
			}
			
			// Check if the current link has already been crawled
			if (!in_array($curr, $this->linksCrawled)) {
				
				// Store current link to linksCrawled
				$this->linksCrawled[] = $curr;
				
				// Get details of the current link
				//getDetails($curr);  
			}
		}
	}
	
	// Getting extracted links according to given data type
	public function getDownloadList() {
		echo '<pre>';
		print_r($this->downloadList);
	}
	
	// Destructor of crawler class
	public function __destruct() {
		
	}
}



// Test
$dataType = ".pdf";
$depth = 2;
$seedPage = "https://www.ce.yildiz.edu.tr/personal/pkoord/file";


$myCrawler = new Crawler($dataType, $depth, $seedPage);
$myCrawler->prepareCrawling();
$myCrawler->crawl();
$myCrawler->getDownloadList();

?>