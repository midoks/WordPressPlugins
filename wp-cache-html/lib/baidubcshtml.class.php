<?php
include(WP_CACHE_HTML_LIB.'config.php');
include(WP_CACHE_HTML_LIB.'baidubcs/baidu_sdk/bcs.class.php');

class baidubcshtml{
	private $bcs = null;
	private $path;//上传的路径

	public function __construct(){
		$this->bcs = new BaiduBCS(BCS_AK, BCS_SK, BCS_HOST);
		$this->ready();
	}

	private function ready(){
		$this->path = 'http://'.BCS_HOST.'/'.BCS_BUCKET.'/';
		$this->header = array(
			'expires' => 'Tue, 19 Jan 9999 03:14:07 GMT',//32位表达的最长时间
		);
	}

	public function write($fn, $content){
		$len = strlen($content);
		if(empty($len)){
			//echo '出现数据为空...!!!';
			return false;
		}
		$info = $this->bcs->create_object_by_content(BCS_BUCKET, '/'.$fn, $content, array(
			'acl'=>'private',
			'headers' => array(
				'expires'=>$this->header['expires'],
				'Content-Type'=>BCS_MimeTypes::get_mimetype('html'),
				'Content-Length' =>  strlen($content),
		)));
		if($info->isOk())
			return true;
		return false;
	}

	public function read($fn){
		$fn = '/'.$fn;
		$info = $this->bcs->get_object(BCS_BUCKET, $fn);
		//var_dump($info);
		if($info->isOk())
			return $info->body;
		return;
	}


	public function ftime($fn){
		$fn = '/'.$fn;
		$info = $this->bcs->get_object_info(BCS_BUCKET, $fn);
		if($info->isOk()){
			$time = $info->header;
			$time = $time['Last-Modified'];
			$time = strtotime($time);
			//var_dump($time);
			return $time;
		}
		return 0;
	}


	public function fexists($fn){
		$info = $this->bcs->is_object_exist(BCS_BUCKET, '/'.$fn);
		//var_dump($info);
		return $info;
	}

	public function getUrl($fn){
		$url = $this->path.$fn;
		return $url;
	}
}
?>
