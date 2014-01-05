<?php
class class_cache_html{

	private $api = null;//接口实例化类
	private $id = null;//唯一名称

	public $sign_set = false;

	public function __construct(){
		$func = WP_CACHE_HTML_METHOD.'html';
		include(WP_CACHE_HTML_LIB.$func.'.class.php');
		$this->api = new $func();
	}

	public function time_ing(){
		list($usec, $sec) = explode(' ', microtime());
    	return ((float)$usec + (float)$sec);
	}


	public function set(){
		//var_dump($this->sign_set);
		if($this->sign_set){//执行第二次
			//echo '执行第2次';
			ob_start();//打开输出缓存器
		}else{
			$this->set_wrap();
			//echo '执行第一次';
			//执行第一次
			$this->sign_set = true;
		}
	}

	public function set_wrap(){
		$url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];//蜘蛛访问的页面
		//var_dump($url);
		//$url = $_SERVER['REQUEST_URI'];//蜘蛛访问的页面
		$url = str_replace(array(':','/','.','%','?','!','@','#',
			'$','^','*','~','"','\'','{','}','\\',
			',','.','?','/',';','<','>','&','(',')'),'_',$url);
		//$md5_url = md5($url);
		$this->id = $url;

		$this->get($this->id);
	}

	public function start(){
		$content = ob_get_flush();

		if(strlen($content)==0){
			//echo '没有任何内容...';
			return false;
		}
		//////////////////////////////////
		if($this->is_saves($content)){
			$this->saves($content);
		}
	}

	//判断保存的条件
	public function is_saves($content){

		if(strpos($_SERVER['PHP_SELF'], 'wp-admin') !== false){return false;}//后台登陆
		if(strlen($content)<10240){return false;}//小于10kb不保存

		if(is_user_logged_in()){return false;}//echo '用户登录了,不保存页面';
	


		//基本
		if(is_single()||is_category()||is_home()){return true;}
		return true;
	}

	public function get($id){
		if($this->api->fexists($id)){
			//date_default_timezone_set('PRC');
			$ftime = $this->api->ftime($id);

			if($ftime){
				$time_diff = time() - $ftime;
				//var_dump($time_diff);
				if($time_diff < WP_CACHE_HTML_TIME){
					//$begin = $this->time_ing();
					global $time_ing_begin;
					$begin = $time_ing_begin;
					$len = $this->api->read($id);
					//$len = $this->read_to_frame($this->api->getUrl($this->id));
					$end = $this->time_ing();
					$info = '<!-- 本次读取缓存耗时:'.($end-$begin).'秒 -->';
					if(!empty($len)){
						//优化之后才显示
						$len = preg_replace("/<head>(.*)<\/head>/ims", "\\0 ".$info, $len);
						echo $len;exit;
					}
				}
			}
			//////
		}
	}

	public function saves($content){
		//if(!$this->api->fexists($this->id)){//保存的条件
			if(WP_CACHE_HTML_OPT_SAVED){
				$content = $this->opt($content);
			}
			$this->api->write($this->id, $content);
		//}
	}

	public function opt($c){
		//除去//注释
		//$c = preg_replace('/\/\/(.*)?/m', '', $c);
		//除去/**/注释
		//$c = preg_replace('/\/\*(.*)\*\//is', '', $c);
		//去注释<!-- -->
		$c =  preg_replace('/<!--(.*)?-->/', '', $c);
		//var_dump($c);
		//除去\r\n\t
		//$c = str_replace(array("\r","\n", "\t", "\r\n"), '' ,$c);
		//转化为html实体
		//$c = htmlentities($c);
		return $c;
	}

	public function read_to_frame($url){

$content = <<<EOT
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8">
<style type="text/css">
body{margin:0px;padding:0px;text-align:center;background:write;border:0;}
</style>
</head>
<script>
function SetWinHeight(obj) { 
	var win=obj;
	console.log(win);
	if(document.getElementById) { 
		if(win && !window.opera) { 
			if(win.contentDocument && win.contentDocument.body.offsetHeight) 
				win.height = win.contentDocument.body.offsetHeight; 
			else if(win.Document && win.Document.body.scrollHeight) 
			win.height = win.Document.body.scrollHeight + 30; 
		}
	}
} 
</script>
<body style="background-color=transparent">
<iframe name="cache_html"  width="100%" height="100%" onload="Javascript:SetWinHeight(this)" 
frameborder="0" border="0"  marginwidth="0" marginheight="0" src="{$url}"></iframe>
</body>
</html>
EOT;
		return $content;
	}
}
?>
