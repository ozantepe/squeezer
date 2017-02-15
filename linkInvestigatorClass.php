<?php

class LinkInvestigator {

	private $parentLink;
	private $parsedParent;
	private $theLink;
	private $dataFormats;
	// an array includes boolean values under these keys
	// same_host
	// different_siblings
	// path_obligation
	private $linkSelectionFlags = array();
	private $pageExts = array("html", "jsf", "jsp", "aspx", "asp");

	public function __construct() {

	}

	public function setParent($parentLink) {
		$this->parentLink = $parentLink;
		$this->parsedParent = parse_url($parentLink);
	}

	public function setDataFormats($dataFormats) {
		$this->dataFormats = $dataFormats;
	}

	public function setFlags($flags) {
		if ($flags !== null) {
			$this->linkSelectionFlags = $flags;
		} else {
			$this->linkSelectionFlags['same_host'] = false;
			$this->linkSelectionFlags['no_siblings'] = false;
			$this->linkSelectionFlags['path_obligation'] = false;
		}
	}

	public function investigateIt ($link) {
		$parsedLink = $this->linkCorrection($link);
		if (!$parsedLink) return -1;
		$this->theLink = $this->unparse_url($parsedLink);
		return $this->linkSelection($parsedLink);
	}

	public function getLink () {
		return $this->theLink;
	}

	private function linkSelection ($parsedLink) {

		if ($this->linkSelectionFlags['same_host'] &&
			$parsedLink['host'] !== $this->parsedParent['host']) { // if hosts not same, junk it
			return -1;
		}

		// check if there link is siblings of rule links
		// junk it
		if ($this->linkSelectionFlags['no_siblings'] &&
			isset($this->parsedParent['path']) && isset($parsedLink['path'])) {
			$ruleDirs = explode('/',$this->parsedParent['path']);
			$linkDirs = explode('/',$parsedLink['path']);
			$rds = count($ruleDirs);
			$lds = count($linkDirs);
			if (($lds>=$rds)&&($rds>=2)) {
				for ($i=0; $i<$lds; $i++) {
					if (($ruleDirs[$rds-2] == $linkDirs[$i])&&($i<$lds-1)&&
						($ruleDirs[$rds-1] !== $linkDirs[$i+1])) {
							return -1;
					}
				}
			}
		}

		// if rule has path link gotta have path too.
		if ($this->linkSelectionFlags['path_obligation'] &&
			isset($this->parsedParent['path']) && !isset($parsedLink['path'])) {
			return -1;
		}

		if (isset($parsedLink['path']) && preg_match("/\.[a-z0-9]+$/i", $parsedLink['path']) ) {
			foreach ($this->dataFormats as $format) {
				if ( preg_match('/\.'.$format.'$/i', $parsedLink['path']) ) {
					return 1;
				}
			}
			foreach ($this->pageExts as $format) {
				if ( preg_match('/\.'.$format.'$/i', $parsedLink['path']) ) {
					return 0;
				}
			}
			return -1;
		}

		return 0;
	}

	private function linkCorrection($link) {
		// Parse url for following controls
		$parsedLink = parse_url($link);

		if (!isset($parsedLink["scheme"])) { // if no scheme in squeezed link add rule's
			$parsedLink["scheme"] = $this->parsedParent["scheme"];
		} else if ($parsedLink["scheme"] == "mailto") { // destroys mailto links
			return false;
		}

		if (!isset($parsedLink["host"])) { // if no host in squeezed link add rule's
			$parsedLink["host"] = $this->parsedParent["host"];
		}

		if (isset($parsedLink['path'])) { // if there is fake path delete it
			if ($parsedLink['path'] == '/' || $parsedLink['path'] == '..') {
				$parsedLink['path'] = '';
			} else if (isset($parsedLink['path'][0]) && ($parsedLink['path'][0] !== '/')) {
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

		return $parsedLink;
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

}

?>
