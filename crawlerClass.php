<?php 

class Crawler {
	
	private $dataType;   // Data type which is given by user
	private $depth;   // Depth of crawling
	private $seedPage;   // Targeted site url
	private $parsedSeedPage;   // Parsed url of seed page for path controls 
	private $linkIndex;   // Index for allLinks array
	private $layerEnd;   // Last location of current layer
	private $layerCounter;   // Counter for layer depth
	private $allLinks = array();   // Array for all crawled links
	private $downloadList = array();   // Array for download links which has desired data types
	private $dom;   // DOMDocument
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
		// Creating DOM object to get targeted site's infos as DOM
		$this->dom = new DOMDocument();
		// Initializing values to start crawling 
		$this->allLinks[] = $this->seedPage;
		$this->linkIndex = 0;
		$this->layerCounter = 0;
		$this->layerEnd = 1;
		// Parsing url of seed page for path controls at crawling
		$this->parsedSeedPage = parse_url($this->seedPage);
	}

	// Main function of crawling process
	public function crawl() {
		// Check if layerCounter exceeded the depth or not
		if($this->depth > $this->layerCounter) {
			// Get the link from queue
			$url = $this->allLinks[$this->linkIndex];
			// @Suppress warnings
			@$this->dom->loadHTML(file_get_contents($url, false, $this->context));  
			// Getting tagged elements of DOM
			$linkArray = $this->dom->getElementsByTagName("a");
			foreach ($linkArray as $link) {
				// Link correction
				$curr = $this->linkCorrection($link->getAttribute("href"), $url);
				if ($curr !== false) {
					// Parse current link for controls
					$parsedCurr = parse_url($curr);
					// Is current link sublink of the seed
					if (isset($parsedCurr["path"]) && strpos($parsedCurr["path"], $this->parsedSeedPage["path"]) !== false) {
						// If current link is sublink of the seed
						// Check if the current link has already been crawled
						// If not add it to all links
						if (!in_array($curr, $this->allLinks)) {
							$this->allLinks[] = $curr;
							// Check data type 
							if ($this->dataTypeControl($curr)) {
								$this->downloadList[] = $curr;
							}	
						}
					}
				}
			}
			// Increase link index for allLink array
			$this->linkIndex++;
			// Check if passed next layer 
			if ($this->linkIndex == $this->layerEnd) {
				$this->layerCounter++;
				$this->layerEnd = count($this->allLinks);
			}
			// Recursion starts
			$this->crawl(); 
		}
	}
	
	// Function which fixes and returns link according to rule link
	private function linkCorrection($link, $ruleLink) {
		// Parse url for following controls
		$parsedURL = parse_url($ruleLink);
		// Fixing strings as desired, using php's substr and the parsedURL
		$length = strlen($link);   // Just for first control
		if ((substr($link, $length-8) == "?lang=tr") || (substr($link, $length-8) == "?lang=en")) {
			return false;
		}else if (substr($link, 0, 1) == "/") {
			$link = $parsedURL["scheme"]."://".$parsedURL["host"].$link;
		}else if (substr($link, 0, 1) == "#") {
			$link = $parsedURL["scheme"]."://".$parsedURL["host"].$parsedURL["path"].$link;
		}else if (substr($link, 0, 7) == "mailto:") {
			return false;
		}else if (substr($link, 0, 11) == "javascript:") {
			return false;
		}else if (substr($link, 0, 5) != "https" && substr($link, 0, 4) != "http") {
			$link = $parsedURL["scheme"]."://".$parsedURL["host"].$parsedURL["path"].$link;
		}
		return $link;
	}
	
	// Function which check for data type
	private function dataTypeControl ($link) {
		$pieces = explode('.', $link);
		$ext = end($pieces);
		if ($this->dataType == $ext) {
			return true;
		}
		return false;
	}
	
	// Getting extracted links according to given data type
	public function getDownloadList() {
		echo '<pre>';
		print_r($this->downloadList);
	}
	
	// Getting extracted links according to given data type
	public function getCrawledList() {
		echo '<pre>';
		print_r($this->allLinks);
	}
	
	// Destructor of crawler class
	public function __destruct() {
		
	}
}

// Test
$dataType = "pdf";
$depth = 2;
$seedPage = "https://www.ce.yildiz.edu.tr/personal/pkoord/file";

// Initialize test
$myCrawler = new Crawler($dataType, $depth, $seedPage);
$myCrawler->prepareCrawling();
$myCrawler->crawl();
$myCrawler->getDownloadList();
$myCrawler->getCrawledList();
?>