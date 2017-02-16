<?php

class Downloader {

	private $downloadList = array();
	private $dataList = array();
	private $dirPath;
	private $zippedDataPath;
	private $maxFileSize = 31457280;

	public function __construct($downloadList) {
		$this->downloadList = $downloadList;
	}

	public function prepareDownloading() {
		$date = new DateTime();
		$this->dirPath = "/home/docs/".md5(uniqid()).(string)$date->getTimestamp();
		mkdir($this->dirPath, 0777, true); // mode will change for security
	}

	public function multiDownload() {
		$totalSize = 0;
		foreach ($this->downloadList as $dLink) {
			$totalSize += $this->retrieve_remote_file_size($dLink);
		}
		if ($totalSize <= $this->maxFileSize) {
			foreach ($this->downloadList as $dLink) {
				$this->download($dLink, false);
			}
		} else {
			throw new Exception("file size exceeding error!");
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
			$this->deleteDir($this->dirPath);
			return;
		}
	}

	public static function deleteDir($dirPath) {
    if (! is_dir($dirPath)) {
        throw new InvalidArgumentException("$dirPath must be a directory");
    }
    if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
        $dirPath .= '/';
    }
    $files = glob($dirPath . '*', GLOB_MARK);
    foreach ($files as $file) {
        if (is_dir($file)) {
            self::deleteDir($file);
        } else {
            unlink($file);
        }
    }
    rmdir($dirPath);
	}

	private function retrieve_remote_file_size($url){
     $ch = curl_init($url);

     curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
     curl_setopt($ch, CURLOPT_HEADER, TRUE);
     curl_setopt($ch, CURLOPT_NOBODY, TRUE);
		 curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

     $data = curl_exec($ch);
     $size = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);

     curl_close($ch);
     return $size;
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

$time_start = microtime(true);

foreach ($links as $link) {
$fp = fopen($link, 'r');
$data = stream_get_meta_data($fp);
fclose($fp);

echo $data['wrapper_data'][7].'<br>';
}

$d = new Downloader($links);
$d->prepareDownloading();
$d->multiDownload();
$d->zipData();
//$d->downloadZippedData();

$time_end = microtime(true);
echo ($time_end - $time_start)/60;
*/
?>
