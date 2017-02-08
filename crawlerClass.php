<?php 

class Crawler {
	private $dataType;   // Data type which is given by user
	private $depth;   // Depth of crawling
	private $ceedPage;   // Targeted site url
	private $parsedCeedPage;
	private $downloadList = array();   // Array for download links which has desired data types
	private $options;   // User-Agent options
	private $context;   // Context for request info
	private $dom;
	private $allLinks = array();
	private $linkIndex;
	private $nextLayerLoc;
	private $layerCounter;
	
	
	
	public function __construct($dataType, $depth, $ceedPage) {
		$this->dataType = $dataType;
		$this->depth = $depth;
		$this->ceedPage = $ceedPage;
	}
	
	public function prepareCrawling() {
		// Creating user-agent settings
		$this->options = array('http'=>array('method'=>"GET", 'headers'=>"User-Agent: squeezerBot\n"));
		$this->context = stream_context_create($this->options);
		
		array_push($this->allLinks, $this->ceedPage);
		$this->layerCounter = 0;
		$this->linkIndex = 0;
		$this->nextLayerLoc = 1;
		
		// Create DOM object to get targeted site's infos as DOM
		$this->dom = new DOMDocument();
		$this->parsedCeedPage = parse_url($this->ceedPage);
	}

	public function crawl() {
		if($this->depth > $this->layerCounter) {
			
			$url = $this->allLinks[$this->linkIndex];
			
			// @Suppress warnings
			@$this->dom->loadHTML(file_get_contents($url, false, $this->context));  
			
			$linkArray = $this->dom->getElementsByTagName("a");
			
			foreach ($linkArray as $link) {
				
				$curr = $this->linkCorrection($link->getAttribute("href"), $url);
				$parsedURL = parse_url($curr);
				
				// Is retrieved link sublink of the ceed
				if (isset($parsedURL["path"]) && strpos($parsedURL["path"],$this->parsedCeedPage["path"]) !== false) {
					// Check if the current link has already been crawled
					if (!in_array($curr, $this->allLinks)) {
						array_push($this->allLinks, $curr);
						if ($this->dataTypeControl($curr)){ // checks data type 
							array_push($this->downloadList,$curr);
						}
					}
				}
			}

			$this->linkIndex++;
			// checks if passed next layer 
			if ($this->linkIndex == $this->nextLayerLoc) {
				$this->layerCounter++;
				$this->nextLayerLoc = count($this->allLinks);
			}
			$this->crawl(); // recursion starts
		}
	}
	
	private function linkCorrection ($link, $ruleLink) {
		$parsedURL = parse_url($ruleLink); // url parsed for controls
		// Fixing strings as desired, using php's substr and parse_url functions
		if (substr($link, 0, 1) == "/") {
			$link = $parsedURL["scheme"]."://".$parsedURL["host"].$link;
		}else if (substr($link, 0, 1) == "#") {
			$link = $parsedURL["scheme"]."://".$parsedURL["host"].$parsedURL["path"].$link;
		}else if (substr($link, 0, 5) != "https" && substr($link, 0, 4) != "http") {
			$link = $parsedURL["scheme"]."://".$parsedURL["host"].$parsedURL["path"].$link;
		}
		return $link;
	}
	
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
		return $this->downloadList;
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
$dataType = "pdf";
$depth = 4;
$ceedPage = "https://www.ce.yildiz.edu.tr/personal/pkoord";

// Initialize test
$myCrawler = new Crawler($dataType, $depth, $ceedPage);
$myCrawler->prepareCrawling();
$myCrawler->crawl();
?>