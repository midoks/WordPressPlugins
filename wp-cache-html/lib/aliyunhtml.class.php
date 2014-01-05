<?php
include(WP_CACHE_HTML_LIB.'config.php');
include(WP_CACHE_HTML_LIB.'aliyun/aliyun_sdk/sdk.class.php');

class aliyunhtml{
	private $oss = null;
	private $path;//上传的路径
	private $time = null;

	public function __construct(){
		$this->oss = new ALIOSS();
		$this->ready();
	}

	private function ready(){
	}

	//XML解析
	private function xmlparser($xml){
		$xml = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
		return (Array)$xml;
	}


	public function write($fn, $content){
		$option = array(
			'content' => $content,
			'length' => strlen($content),
				ALIOSS::OSS_HEADERS => array(
				'Expires' => '9999-12-29 12:00:00',
			),
		);
		$info = $this->oss->upload_file_by_content(BUCKET_NAME, $fn, $option);
		if($info->isOk()){
			return true;
		}
		return false;
		
	}

	public function read($fn){
		try{
			$option = array();
			$info = $this->oss->get_object(BUCKET_NAME, $fn, $option);
			//var_dump($info);
			if($info->isOk()){
				return $info->body;
			}
		}catch(Exception $e){}
		return;
	}

	public function ftime($fn){
		if(!$this->time){
			return $this->time;
		}else{
			$info = $this->oss->get_object_meta(BUCKET_NAME, $fn);
			if($info->isOk()){
				$time = $info->header;
				$time = $time['last-modified'];
				$time = strtotime($time);
				return $time;
			}else{
				return 0;
			}
		}
		return 0;
	}

	public function fexists($fn){
		$info = $this->oss->is_object_exist(BUCKET_NAME, $fn);
		//var_dump($info);
		if($info->isOk()){
			$time = $info->header;
			$time = $time['last-modified'];
			$time = strtotime($time);
			$this->time = $time;
			return true;
		}
		return false;
	}


}
?>
