<?php
/**
 * @func 微信机器人,主要控制类
 * Author: Midoks
 * Author URI: http://midoks.cachecha.com/
 */

class weixin_robot{

	public $cmd;//命令

	public function __construct(){
		//以上为测试和实例
		include_once(WEIXIN_ROOT_LIB.'weixin_cmd.php');
		//实例化
		$this->cmd = new weixin_cmd();
	}

	//@func 机器人功能验证和返回信息
	public function valid(){
		if(isset($_GET['debug'])){
			header('Content-type: text/html;charset=utf-8');
			//$this->kw = strtolower(trim($_GET['kw']));
			//$this->checkSignature();
			$this->responseMsg();
		}else{
			if($this->checkSignature()){
				$echoStr = (isset($_GET['echostr']))?$_GET['echostr']:'';
				if(!empty($echoStr)){
					echo $echoStr;
				}else{
					$this->responseMsg();
				}
			}
		}
        exit;
	}

	//验证消息真实性
	private function checkSignature(){
        $signature = $_GET['signature'];
        $timestamp = $_GET['timestamp'];
        $nonce = $_GET['nonce'];	
	
		$token = WEIXIN_TOKEN;
		$tmpArr = array($token, $timestamp, $nonce);
		sort($tmpArr, SORT_STRING);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );
      
		if($tmpStr == $signature){
			return true;
		}else{
			return false;
		}
	}

	//返回的信息
	public function responseMsg(){
		echo $this->cmd->cmd();
	}

	public function font(){
		$this->cmd->font();
	}

}
?>
