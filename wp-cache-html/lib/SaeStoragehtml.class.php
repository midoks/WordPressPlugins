<?php

include(WP_CACHE_HTML_LIB.'config.php');

class SaeStoragehtml{

	public $linkID  = null;

	public function __construct(){

		$this->linkID = new SaeStorage();
		$this->ready();
	}

	public function ready(){
	}

	public function fixfn($fn){
		return $fn.'.html';
	}

	public function write($fn, $content){
		$ret = $this->linkID->write(BUCKET_NAME, $this->fixfn($fn), $content);
		return $ret;
	}

	public function read($fn){
		$fn = $this->fixfn($fn);
		$ret = $this->linkID->read(BUCKET_NAME, $fn);
		return $ret;
	}

	public function ftime($fn){
		$fn = $this->fixfn($fn);
		$ret = $this->linkID->getAttr(BUCKET_NAME ,$fn);
		if(isset($ret['datetime'])){
			return $ret['datetime'];
		}
		return false;
	}

	public function fexists($fn){
		$fn = $this->fixfn($fn);
		$ret = $this->linkID->fileExists(BUCKET_NAME, $fn);
		return $ret;
	}

	public function getUrl($fn){
		$fn = $this->fixfn($fn);
		return $this->linkID->getUrl(BUCKET_NAME, $fn);
	}



}
?>
