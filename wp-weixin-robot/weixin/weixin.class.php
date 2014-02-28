<?php
/**
 * 微信SDK
 * @author:midoks
 * @mail: midoks@163.com
 * @blog:midoks.cachecha.com
 */

define('WEIXIN_SDK', str_replace('\\', '/', dirname(__FILE__)).'/');
include(WEIXIN_SDK.'libs/base.class.php');
include(WEIXIN_SDK.'libs/template.class.php');
defined('WEIXIN_DEBUG') or define('WEIXIN_DEBUG', true);//默认关闭

class weixin{

	public $template;
	public $base;
	public $app_id;
	public $app_sercet;


	public function __construct($AppId='', $AppSecret=''){

		$this->app_id = $AppId;
		$this->app_sercet = $AppSecret;

		$this->template = new Weixin_Template();
		$this->base = new Weixin_BaseCore();
	}

//no appkey and appsercet

	// response message (text)	
	public function toMsgText($fromUserName, $toUserName, $Msg){
		return $this->template->toMsgText($fromUserName, $toUserName, $Msg);
	}

	// response message (image)	
	public function toMsgImage($fromUserName, $toUserName, $MediaId){
		return $this->template->toMsgImage($fromUserName, $toUserName, $MediaId);
	}

	// response message (voice)	
	public function toMsgVoice($fromUserName, $toUserName, $MediaId){
		return $this->template->toMsgVoice($fromUserName, $toUserName, $MediaId);
	}

	// response message (music)	
	public function toMsgMusic($fromUserName, $toUserName, $Title, $Description, $MusicUrl, $HQMusicUrl, $ThumbMediaId){
		return $this->template->toMsgMusic($fromUserName, $toUserName, $Title, $Description, $MusicUrl, $HQMusicUrl, $ThumbMediaId);
	}

	// response message (video)	
	public function toMsgVideo($fromUserName, $toUserName, $MediaId, $Title, $Description){
		return $this->template->toMsgVideo($toUserName, $fromUserName, $MediaId, $Title, $Description);
	}

	// response message (news)	
	public function toMsgNews($fromUserName, $toUserName, Array $News){
			return $this->template->toMsgNews($fromUserName, $toUserName, $News);
	}
//END TO MSG


//have appkey and appsercet


	/**
	 *	get token
	 *  @ret json
	 */
	public function getToken(){
		$app_id = $this->app_id;
		$app_sercet = $this->app_sercet;
		return $this->base->getToken($app_id, $app_sercet);
	}

	public function pushMsgText($token, $open_id, $msg){
		return $this->base->pushMsgText($token, $open_id, $msg);
	}

	public function pushMsgImage($token, $open_id, $media_id){
		return $this->base->pushMsgImage($token, $open_id, $media_id);
	}

	public function pushMsgImageAdv($token, $open_id, $file){
		$ret = $this->upload($token, 'image', $file);
		if(!$ret){
			return '{errcode: "network imeout"}';
		}
		$data = json_decode($ret, true);
		if(isset($data['errcode'])){
			return $ret;
		}
		$ret = $this->pushMsgImage($token, $open_id, $data['media_id']);
		return $ret;
	}

	public function pushMsgVoice($token, $open_id, $media_id){
		return $this->base->pushMsgVoice($token, $open_id, $media_id);
	}

	public function pushMsgVoiceAdv($token, $open_id, $file){
		$ret = $this->upload($token, 'voice', $file);
		if(!$ret){
			return '{errcode: "network imeout"}';
		}
		$data = json_decode($ret, true);
		if(isset($data['errcode'])){
			return $ret;
		}
		$ret = $this->pushMsgVoiceAdv($token, $open_id, $data['media_id']);
		return $ret;
	}

	public function pushMsgVideo($token, $open_id, $media_id, $title, $desc){
		return $this->base->pushMsgVoice($token, $open_id, $media_id, $title, $desc);
	}

	public function pushMsgVideoAdv($token, $open_id, $file, $title, $desc){
		$ret = $this->upload($token, 'voice', $file);
		if(!$ret){
			return '{errcode: "network imeout"}';
		}
		$data = json_decode($ret, true);
		if(isset($data['errcode'])){
			return $ret;
		}
		$ret = $this->pushMsgVoice($token, $open_id, $data['media_id'], $title, $desc);
		return $ret;
	}

	public function pushMsgMusic($token, $open_id, $thumb_media_id, $title, $desc, $musicurl, $hqmusicurl){
		return $this->base->pushMsgMusic($token, $open_id, $thumb_media_id, $title, $desc, $musicurl, $hqmusicurl);
	}

	public function pushMsgMusicAdv($token, $open_id, $file, $title, $desc, $musicurl, $hqmusicurl){
		$ret = $this->upload($token, 'voice', $file);
		if(!$ret){
			return '{errcode: "network imeout"}';
		}
		$data = json_decode($ret, true);
		if(isset($data['errcode'])){
			return $ret;
		}
		$ret = $this->pushMsgVoice($token, $open_id, $data['media_id'], $title, $desc, $musicurl, $hqmusicurl);
		return $ret;
	}

	/**
	 * @exp: $info should be:
	 *		$info[]["title"] = "Happy Day";
     *      $info[]["description"]="Is Really A Happy Day";
     *      $info[]["url"] = "URL";
     *      $info[]["picurl"] = "PIC_URL";
	 */
	public function pushMsgNews($token, $open_id, $info){
		return $this->base->pushMsgNews($token, $open_id, $info);
	}
///END PUSH



//END PUSH ADV

	//menu setting
	public function menuGet($token){
		return $this->base->menuGet($token);
	}

	public function menuSet($token, $json){
		return $this->base->menuSet($token, $json);
	}

	public function menuDel($token){
		return $this->base->menuDel($token);
	}

//uplaod and download

	public function download($token, $media_id){
		return $this->base->download($token, $media_id);
	}

	public function upload($token, $type, $file){
		if(in_array($type, array('image', 'voice', 'video', 'thumb'))){
			return $this->base->upload($token, $type, $file);
		}else{
			return $this->notice('upload: invalid media type', __LINE__);
		}	
	}

	public function uploadUrl($token, $type, $fn, $mime, $content){
		if(in_array($type, array('image', 'voice', 'video', 'thumb'))){
			return $this->base->uploadUrl($token, $type, $fn, $mime, $content);
		}else{
			return $this->notice('upload: invalid media type', __LINE__);
		}
	}

//user info 
	public function getUserInfo($token, $open_id){
		return $this->base->getUserInfo($token, $open_id);
	}

	public function getUserList($token, $next_openid){
		return $this->base->getUserList($token, $next_openid);
	}

	public function setUserGroup($token, $json){
		return $this->base->setUserGroup($token, $json);
	}

	public function getUserGroup($token){
		return $this->base->getUserGroup($token);
	}

	public function getUserGroupPosition($token, $json){
		return $this->base->getUserGroupPosition($token, $json);
	}

	public function modUserGroup($token, $json){
		return $this->base->modUserGroup($token, $json);
	}

	public function movUserGroup($token, $json){
		return $this->base->movUserGroup($token, $json);
	}


	public function to_json($arr){
		return $this->base->to_json($arr);
	}

	public function notice($msg, $line){
		if(WEIXIN_DEBUG){
			$this->logs($msg, $line);
		}else{
			trigger_error($msg);
		}
		return $msg;
	}

	public function logs($msg, $line){
		$fn = WEIXIN_SDK.'logs/'.date('Y-m-d').'.log';
		$handler = fopen($fn, 'a');
		fwrite($handler, 'error: in '.__FILE__.' at '.$line.' line '.$msg."\t".date('Y-m-d H:i:s')."\r\n");
		fclose($handler);
	}
}
?>
