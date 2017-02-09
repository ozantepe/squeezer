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
	
	public function __construct($dataType, $depth, $seedPage) {
		$this->dataType = $dataType;
		$this->depth = $depth;
		$this->seedPage = $seedPage;
	}
	
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

	public function crawl() {
		// Check if layerCounter exceeded the depth or not
		if (($this->depth > $this->layerCounter) && ($this->layerCounter < $this->layerEnd)) {
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
					if (!in_array($curr, $this->allLinks) && $this->linkSelection($parsedCurr, $this->parsedSeedPage)) {	
						$this->allLinks[] = $curr;
						// Check data type 
						if ($this->dataTypeControl($curr)) {
							$this->downloadList[] = $curr;
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
		
	
	private function linkSelection ($parsedLink, $parsedRuleLink) {
		
		if ($parsedLink['host'] !== $parsedRuleLink['host']) { // if hosts not same, junk it
			return false;
		}
		
		// check if there link is same layered directory as rule link
		// junk it
		if (isset($parsedRuleLink['path']) && isset($parsedLink['path'])) {
			$ruleDirs = explode('/',$parsedRuleLink['path']);
			$linkDirs = explode('/',$parsedLink['path']);
			$rds = count($ruleDirs);
			$lds = count($linkDirs);
			if (($lds>=$rds)&&($rds>=2)) {
				for ($i=0; $i<$lds; $i++) {
					if (($ruleDirs[$rds-2] == $linkDirs[$i])&&($i<$lds-1)&&
						($ruleDirs[$rds-1] !== $linkDirs[$i+1])) {
							return false;	
					}
				}
			}
		} 
		
		// if rule has path link gotta have path too.
		if (isset($parsedRuleLink['path']) && !isset($parsedLink['path'])) {
			return false;
		}
		
		return true;
	}
	
	private function linkCorrection($link, $ruleLink) {
		// Parse url for following controls
		$parsedRuleLink = parse_url($ruleLink);
		$parsedLink = parse_url($link);
	
		if (!isset($parsedLink["scheme"])) { // if no scheme in squeezed link add rule's
			$parsedLink["scheme"] = $parsedRuleLink["scheme"];
		} else if ($parsedLink["scheme"] == "mailto") { // destroys mailto links 
			return false;
		}
		
		if (!isset($parsedLink["host"])) { // if no host in squeezed link add rule's
			$parsedLink["host"] = $parsedRuleLink["host"];
		}
		
		if (isset($parsedLink['path'])) { // if there is fake path delete it
			if ($parsedLink['path'] == '/') {
				$parsedLink['path'] = '';
			}
		}
		
		if (isset($parsedLink["fragment"])) { // destroys fragments
			$parsedLink["fragment"] = null;
		}
		
		if (isset($parsedLink["query"])) {
			if (strpos($parsedLink["query"], "lang") !== false) { // destroys language querys
				return false;
			}
		}
		
		return $this->unparse_url($parsedLink);
	}
	
	private function unparse_url($parsed_url) { 
	  $scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : ''; 
	  $host     = isset($parsed_url['host']) ? $parsed_url['host'] : ''; 
	  $port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : ''; 
	  $user     = isset($parsed_url['user']) ? $parsed_url['user'] : ''; 
	  $pass     = isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : ''; 
	  $pass     = ($user || $pass) ? "$pass@" : ''; 
	  $path     = isset($parsed_url['path']) ? $parsed_url['path'] : ''; 
	  $query    = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : ''; 
	  $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : ''; 
	  return "$scheme$user$pass$host$port$path$query$fragment"; 
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
	public function printCrawledList() {
		echo '<pre>';
		print_r($this->allLinks);
	}	
	
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
$depth = 3;
$seedPage = "http://www.ce.yildiz.edu.tr/personal/sirma";

// Initialize test
$myCrawler = new Crawler($dataType, $depth, $seedPage);
$myCrawler->prepareCrawling();
$myCrawler->crawl();
$myCrawler->printCrawledList();
echo '<pre>';
print_r($myCrawler->getDownloadList());
?>