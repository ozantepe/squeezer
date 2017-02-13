<?php

class Downloader {
	
	private $downloadList = array();
	private $dataList = array();
	private $dirPath;
	private $zippedDataPath;
	
	public function __construct($downloadList) {
		$this->downloadList = $downloadList;
	}
	
	public function prepareDownloading() {
		$date = new DateTime();
		$this->dirPath = "docs/".(string)$date->getTimestamp();
		mkdir($this->dirPath, 0777, true); // mode will change for security
	}
	
	public function multiDownload() {
		foreach ($this->downloadList as $dLink) {
			$this->download($dLink, false);
		}
	}
	
	public function download($link) {
		$file = file_get_contents($link); // file has token to main memory
		if (!$file) return;	// problem controller
		$parsedLink = parse_url($link);
		
		// extension founder and path creator
		$fileName = str_replace("/", "-", $parsedLink["path"]);
		$filePath = $this->dirPath.'/'.$fileName;
		
		// file creation
		file_put_contents($filePath, $file);
		$this->dataList[$fileName] = $filePath;
		
		// can be added immediate download for single document without saving data
		
	}
	
	public function zipData() {
		// zip file creation
		$zip = new ZipArchive();
		$this->zippedDataPath = $this->dirPath."/squeezer.zip";
		if ($zip->open($this->zippedDataPath, ZIPARCHIVE::CREATE) !== true) {
			echo 'problem<br>';
			return;
		}
		
		// adding files
		foreach(array_keys($this->dataList) as $key) {
			$zip->addFile($this->dataList[$key], $key);
		}
		$zip->close();
	}
	
	public function downloadZippedData () {
		if (file_exists($this->zippedDataPath)) {
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename='.basename($this->zippedDataPath));
			header('Content-Transfer-Encoding: binary');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
			header('Content-Length: ' . filesize($this->zippedDataPath));
			ob_clean();
			flush();
			readfile($this->zippedDataPath);
			exit();
		}
	}
	
	function __destruct() {
		
	}
	
}
/*
// test area
$links = array(
"https://www.ce.yildiz.edu.tr/user/photo/thumb/131/hhb.png",
"https://www.ce.yildiz.edu.tr/user/photo/thumb/24/NA.jpg",
"https://www.ce.yildiz.edu.tr/user/photo/thumb/30/agy.png",
"https://www.ce.yildiz.edu.tr/user/photo/thumb/61/fk.png",
"https://www.ce.yildiz.edu.tr/user/photo/thumb/29/mek.jpg"
);

$d = new Downloader($links);
$d->prepareDownloading();
$d->multiDownload();
$d->zipData();
$d->downloadZippedData();
*/
?>

