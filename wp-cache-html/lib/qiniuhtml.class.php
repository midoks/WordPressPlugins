<?php
include(WP_CACHE_HTML_LIB.'config.php');
include(WP_CACHE_HTML_LIB.'qiniu/sdk/rs.php');
include(WP_CACHE_HTML_LIB.'qiniu/sdk/rsf.php');
include(WP_CACHE_HTML_LIB.'qiniu/sdk/io.php');
class qiniuhtml{

	private $client = null;

	public $time = null;

	public function __construct(){
		Qiniu_SetKeys(QINIU_AK, QINIU_SK);
		$this->ready();
	}

	public function ready(){
	}

	//上传字符串
	public function write($fn, $content){
		$put = new Qiniu_RS_PutPolicy(BUCKET_NAME);
		$token = $put->Token(null);
		Qiniu_Put($token, $fn, $content, null);
	}

	public function read($fn){
		$baseUrl = Qiniu_RS_MakeBaseUrl(BUCKET_NAME.'.qiniudn.com', $fn);
		$getPolicy = new Qiniu_RS_GetPolicy();
		$privateUrl = $getPolicy->MakeRequest($baseUrl, null);
		$content = '';
		try{
			$content = file_get_contents($privateUrl);
		}catch(Exception $e){}
		return $content;
	}

	public function fexists($fn){
		$client = new Qiniu_MacHttpClient(null);
		list($ret, $err) = Qiniu_RS_Stat($client, BUCKET_NAME, $fn);
		if($err !== null){
			return false;
		}else{
			$pre_time = substr(sprintf('%F',$ret['putTime']), 0, 10);
			$this->time = $pre_time;
			return true;
		}
		return false;
	}

	public function ftime($fn){
		if($this->time != null){
			return $this->time;
		}
	}

}
?>
