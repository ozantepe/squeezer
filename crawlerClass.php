<?php 

class Crawler {
	
	private $dataType;   // Data type which is given by user
	private $depth;   // Depth of crawling
	private $seedPage;   // Targeted site url
	private $done = array();   // Array for already crawled links
	private $queue = array();   // Array for links which is gonna be crawled
	private $downloadList = array();   // Array for download links which has desired data types
	private $info = array();   // Array for infos of links
	private $options;   // User-Agent options
	private $context;   // Context for request info
	
	// Constructor of crawler class
	public function __construct($dataType, $depth, $seedPage) {
		$this->dataType = $dataType;
		$this->depth = $depth;
		$this->seedPage = $seedPage;
	}
	
	// Prepare crawler for crawling
	public function prepareCrawling() {
		// Creating user-agent settings
		$this->options = array('http'=>array('method'=>"GET", 'headers'=>"User-Agent: squeezerBot\n"));
		$this->context = stream_context_create($this->options);
		// Creating queue
		$this->queue[0] = $this->seedPage;
	}

	// Main function of crawling process
	public function crawl() {
		// Crawl while queue is not empty and check for depth as well
		while(($this->queue) && $this->depth > 0) {
			// Take first element from queue
			$url = array_shift($this->queue);
			// Create DOM object to get targeted site's infos as DOM
			$dom = new DOMDocument();
			// @Suppress warnings
			@$dom->loadHTML(file_get_contents($url, false, $this->context));  
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
				// Check link if it's data type equals to given data type
				// and if it's not in download list, add it to download list
				if ((substr($curr, strlen($curr)-4) == $this->dataType) && (!in_array($curr, $this->downloadList))) {
					$this->downloadList[] = $curr;
				}
				// Check if the current link has already been crawled
				if (!in_array($curr, $this->done) && !in_array($curr, $this->queue)) {
					// Store current link to done
					$this->done[] = $curr;
					// Store current link to queue as well
					$this->queue[] = $curr;
					// Get details of the current link
					//getDetails($curr);  
				}
			}
			// Decrease depth
			$this->depth--;
		}
	}
	
	// Getting extracted links according to given data type
	public function getDownloadList() {
		echo '<pre>';
		print_r($this->downloadList);
	}
	
	// Getting extracted links according to given data type
	public function getCrawledList() {
		echo '<pre>';
		print_r($this->done);
	}
	
	// Funciton for retrieving details of links
	public function getDetails($url) {
		// Getting targeted site as Document Object Model
		$dom = new DOMDocument();
		// @Suppress warnings
		@$dom->loadHTML(file_get_contents($url, false, $this->context));
		// Title info
		$title = $dom->getElementsByTagName("title");
		// Check if can't grab title
		if ($title->length > 0) {
			$node = $title->item(0);
			$title = $node->nodeValue;
		}else {
			$title = "";
		}	
		// Description info
		$description = "";
		// Keywords info
		$keywords = "";
		// Getting metas from DOM
		$metas = $dom->getElementsByTagName("meta");
		for ($i = 0; $i < $metas->length; $i++) {
			$meta = $metas->item($i);
			if ($meta->getAttribute("name") == strtolower("description")) {
				$description = $meta->getAttribute("content");
			}
			if ($meta->getAttribute("name") == strtolower("keywords")) {
				$keywords = $meta->getAttribute("content");
			}
		}
		$info['Title'] = $title;
		$info['Description'] = $description;
		$info['Keywords'] = $keywords;
		$info['URL'] = $url;
	}
	
	// Destructor of crawler class
	public function __destruct() {
		
	}
}

// Test
$dataType = ".pdf";
$depth = 16;
$seedPage = "https://www.ce.yildiz.edu.tr/personal/pkoord/file";

// Initialize test
$myCrawler = new Crawler($dataType, $depth, $seedPage);
$myCrawler->prepareCrawling();
$myCrawler->crawl();
$myCrawler->getDownloadList();
$myCrawler->getCrawledList();
?>