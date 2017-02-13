<?php 
include 'linkInvestigatorClass.php';

class Crawler {
	private $dataFormats = array(
		"document" => array("pdf", "doc", "docx", "txt", "ppt", "pptx", "xls", "xlsx"),
		"image" => array("png", "jpg", "jpeg", "gif"),
		"video" => array("webm", "mp4", "mkv", "avi", "3gp"),
		"audio" => array("mp3"),
		"script" => array("css", "js", "html")
	);
	private $dataTags = array (
		"document" => array("a"=>"href", "link"=>"href"),
		"image" => array("a"=>"href", "link"=>"href", "img"=>"src", "source"=>"src"),
		"video" => array("a"=>"href", "link"=>"href", "video"=>"src", "source"=>"src", "track"=>"src"),
		"audio" => array("a"=>"href", "link"=>"href", "audio"=>"src", "source"=>"src", "track"=>"src"),
		"script" => array("a"=>"href", "link"=>"href", "script"=>"src")
	);
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
	private $linkInvestigator;
	
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
		// link investigator creation
		$this->linkInvestigator = new linkInvestigator();
		$this->linkInvestigator->setDataFormats($this->dataFormats[$this->dataType]);
		$this->linkInvestigator->setFlags(null);
	}

	public function crawl() {
		// Check if layerCounter exceeded the depth or not
		while (($this->depth > $this->layerCounter) && ($this->layerCounter < $this->layerEnd)) {
			// Get the link from queue
			$url = $this->allLinks[$this->linkIndex];
			$this->linkInvestigator->setParent($url);
			// @Suppress warnings
			@$this->dom->loadHTML(file_get_contents($url, false, $this->context));  
			// Getting tagged elements of DOM
			foreach (array_keys($this->dataTags[$this->dataType]) as $tag) {
				$linkArray = $this->dom->getElementsByTagName($tag);
				foreach ($linkArray as $link) {
					$result = $this->linkInvestigator->investigateIt($link->getAttribute($this->dataTags[$this->dataType][$tag]));
					$link = $this->linkInvestigator->getLink();
					if ($result == 0) {
						if (!in_array($link, $this->allLinks)) {
							$this->allLinks[] = $link;
						}
					} else if ($result == 1) {
						if (!in_array($link, $this->downloadList)) {
							$this->downloadList[] = $link;
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
		}
	}
	
	// Getting extracted links according to given data type
	public function printCrawledList() {
		echo '<pre>';
		print_r($this->allLinks);
	}	
	
	public function getDownloadList() {
		return $this->downloadList;
	}
	
	// Destructor of crawler class
	public function __destruct() {
		
	}
}
/*
// Test
$dataType = "document";
$depth = 2; // default 1
$seedPage = "https://www.ce.yildiz.edu.tr/personal/gokhan/file/10381/2014";

// Initialize test
$myCrawler = new Crawler($dataType, $depth, $seedPage);
$myCrawler->prepareCrawling();
$myCrawler->crawl();
$myCrawler->printCrawledList();
echo '<pre>';
print_r($myCrawler->getDownloadList());
*/
?>