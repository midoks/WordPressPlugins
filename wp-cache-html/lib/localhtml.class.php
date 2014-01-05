<?php
define('WP_CACHE_HTML_LOCAL', WP_CACHE_HTML_ROOT.'local/');
class localhtml{

	public $path = null;

	public function __construct(){
		$this->path = plugins_url('wp-cache-html/',dirname(dirname(__FILE__)));
		$this->path = $this->path.'local/';
	}

	public function write($fn, $content){
		return file_put_contents(WP_CACHE_HTML_LOCAL.$fn.'.html', $content);
	}

	public function read($fn){
		return file_get_contents(WP_CACHE_HTML_LOCAL.$fn.'.html');
	}

	public function ftime($fn){
		$fn = WP_CACHE_HTML_LOCAL.$fn.'.html';
		return filemtime($fn);;
	}

	public function fexists($fn){
		if(file_exists(WP_CACHE_HTML_LOCAL.$fn.'.html')){
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
