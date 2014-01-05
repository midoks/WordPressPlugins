<?php
class NFShtml{

	public $path = null;

	public function __construct(){
		$this->path = '/home/bae/data/';
	}

	public function write($fn, $content){
		return file_put_contents($this->path.$fn.'.html', $content);
	}

	public function read($fn){
		return file_get_contents($this->path.$fn.'.html');
	}

	public function ftime($fn){
		$fn = $this->path.$fn.'.html';
		return filemtime($fn);;
	}

	public function fexists($fn){
		if(file_exists($this->path.$fn.'.html')){
			return true;
		}
		return false;
	}

	public function getUrl($fn){
		$url = $this->path.$fn.'.html';
		return $url;
	}
}
?>
