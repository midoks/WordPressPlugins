<?php
class MemcacheHtml{
	
	public $linkID = null;
	public $content = '';

	public function __construct(){
		$this->linkID = new Memcache();
		$this->linkID->connect('localhost', 11211);
	
	}

	public function write($fn, $content){
		$this->linkID->set($fn, $content, MEMCACHE_COMPRESSED, WP_CACHE_HTML_TIME);
	}

	public function read($fn){
		if(empty($this->content)){
			return false;
		}
		return $this->content;
	}

	public function ftime($fn){
		return time();
	}

	public function fexists($fn){
		$data = $this->linkID->get($fn);
	
		if(!empty($data)){
			$this->content = $data;
			return true;
		}
		return false;
	}

	public function getUrl($fn){
	
	}
}
?>
