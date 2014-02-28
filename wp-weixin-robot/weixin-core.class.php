<?php
/**
 *	微信机器人核心驱动
 */
class weixin_core{

	public $obj = null;

	public function __construct(){
		//实例化消息模板类
      	include_once(WEIXIN_ROOT.'weixin/weixin.class.php');
		$this->obj = new weixin($this->options['ai'], $this->options['as']);
	}

	/**
	 * @func 返回文本信信息
	 * @param $Msg 信息
	 * @param $mode 是否开启截取模式(0:不开启,1:开启.默认开启)
	 * @ret string xml
	 * exp:
	 * echo $this->toMsgText($contentStr);//文本地址
	 */
	public function toMsgText($Msg, $mode=1){
		$this->replay_type = '文本回复';
		if($mode){
			$c = strlen($Msg);
			if($c > 2048){
				$Msg = $this->byte_substr($Msg);
			}
		}
		return $this->obj->toMsgText($this->info['FromUserName'], $this->info['ToUserName'], $Msg);
	}

	/**
	 *	@func 在手机客服端显示的一种(仅在客服端,有效果)
	 *	@param array $alink
	 *	@param string $suffix (一般为"\r\n", 默认为空)
	 *	@info 由(http://weibo.com/clothand)提供, 当然我做了优化
	 *	@exp:
	 *	$alink[0]['link'] = 'midoks.cachecha.com';
	 *	$alink[0]['title'] = '你好';
	 */
	public function toMsgTextAlink($alink, $suffix = ''){
		$link_info = '';
		foreach($alink as $k=>$v){
			$_n = "<a href='{$v['link']}'>{$v['title']}</a>".$suffix;
			$ret_n = $link_info.$_n; 
			$_c = strlen($ret_n);
			if($_c > 2048){
				return $this->toMsgText($link_info);
			}else{
				$link_info .= $_n;
			}
		}
		return $this->toMsgText($link_info);
	}

	
	/**
	 * @func 返回图片信息(测试未成功)
 	 * @param $MediaId 图片信息
	 * @ret string xml
	 * exp:
	 * echo $this->toMsgPic($MediaId);//图
	 */
	public function toMsgPic($MediaId){
		$this->replay_type = '图片回复';
  		return $this->obj->toMsgPic($this->info['FromUserName'], $this->info['ToUserName'], $MediaId);
	}


	/**
	 * @func 返回voice xml
	 * @param MediaId
	 * @ret string xml
	 * exp:
	 //echo $this->toMsgVoice($MediaId);
	 */
	public function toMsgVoice($MediaId){
		$this->replay_type = '声音回复';
		return $this->obj->toMsgVoice($this->info['FromUserName'], $this->info['ToUserName'], $MediaId);
	}

	/**
	 * @func 返回music xml
	 * @param $title //标题
	 * @param $desc //描述
	 * @param $MusicUrl //地址
	 * @param $HQMusicUrl //高清播放(会首先选择)
	 * @ret string xml
	 * exp:
	 //echo $this->toMsgVoice('声音','当男人好难！', $MusicUrl, $MusicUrl);//voice
	 */
	public function toMsgMusic($title, $desc, $MusicUrl, $HQMusicUrl, $ThumbMediaId=''){
		$this->replay_type = '音乐回复';
		return $this->obj->toMsgMusic($this->info['FromUserName'], $this->info['ToUserName'], $title, $desc, $MusicUrl, $HQMusicUrl, $ThumbMediaId);
	}

	/**
	 * @func 返回video xml
	 * @param 通过上传多媒体文件,得到id
	 * @param 缩图的媒体ID,通过上传多媒体文件,得到的id
	 * @ret string xml
	 */
	public function toMsgVideo($media_id, $thumb_media_id){
		$this->replay_type = '视频回复';
		return $this->obj->toMsgVideo($this->info['FromUserName'], $this->info['ToUserName'], $media_id, $thumb_media_id);
	}

 	/**
	 * @func 返回图文
	 * @param array $info
	 * @param array $array 
	 * @ret string xml
	 * exp
	 * $textPic = array(
			array(
				'title'=> '标题',
				'desc'=> '描述',
				'pic'=> $this->bigPic(),//图片地址
				'link'=>$pic,//图片链接地址
			),//第一个图片为大图
			array(
				'title'=> '标题',
				'desc'=> '描述',
				'pic'=> $this->smallPic(),//图片地址
				'link'=> '',//图片链接地址
			),//此自以后皆为小图
			array(
				'title'=> '标题',
				'desc' => '描述',
				'pic'  => $this->smallPic(),//图片地址
				'link' => '',//图片链接地址
			),
			array(
				'title'=> '标题',
				'desc' => '描述',
				'pic'  => $this->smallPic(),//图片地址
				'link' => '',//图片链接地址
			),
			array(
				'title'=> '标题',
				'desc' => '描述',
				'pic'  => $this->smallPic(),//图片地址
				'link' => '',//图片链接地址
			),
			array(
				'title'=> '标题',
				'desc' => '描述',
				'pic'  => $this->smallPic(),//图片地址
				'link' => '',//图片链接地址
			),
		);
	//echo $this->toMsgTextPic($textPic);//图文
	*/
	public function toMsgTextPic($picTextInfo){
		$this->replay_type = '图文回复';
		$fromUserName = $this->info['FromUserName'];
        $toUserName = $this->info['ToUserName'];
  		return $this->obj->toMsgNews($fromUserName, $toUserName, $picTextInfo);
	}

	/**
	 * 在客服端列表的展示形式
	 *
	 *  $list[0]['title'] = '0';
	 *	$list[0]['desc'] =  '0';
	 *	$list[0]['link'] = "http://midok.cachecha.com/";
	 */
	public function toMsgTextPicList($list){
		$info = array();
		foreach($list as $k=>$v){
			$a['title'] = $v['title'];
			$a['desc'] =  $v['desc'];
			$a['link'] = $v['link'];
			$info[] = $a;
		}
		return $this->toMsgTextPic($info);//图文
	}


//
	public function getReToken(){
		$data = $this->obj->getToken();
		$data = json_decode($data, true);
		$data['expires_in'] = time() + $data['expires_in'];
		$this->options['weixin_robot_token'] = json_encode($data);
		update_option('weixin_robot_options', $this->options);
		return $data['access_token']; 
	}
	
	public function getToken(){
		if(empty($this->options['ai']) || empty($this->options['as'])){
			//$this->notice('请填写服务号完整信息!!!');
			exit('请填写服务号完整信息!!!');
		}

		if(!empty($this->options['weixin_robot_token'])){
			$data = $this->options['weixin_robot_token'];
			$data = json_decode($data, true);
			if($data['expires_in'] <= time()){
				$_data =  $this->obj->getToken();
				$data = json_decode($_data, true);
				if(isset($data['errcode'])){//判断错误
					exit($_data);
				}
				$data['expires_in'] = time() + $data['expires_in'];
				$this->options['weixin_robot_token'] = json_encode($data);
				update_option('weixin_robot_options', $this->options);
			}
		}else{
			$_data = $this->obj->getToken();
			$data = json_decode($_data, true);
			if(isset($data['errcode'])){//判断错误
				exit($_data);
			}
			$data['expires_in'] = time() + $data['expires_in'];
			$this->options['weixin_robot_token'] = json_encode($data);
			update_option('weixin_robot_options', $this->options);
		}
		return $data['access_token'];
	}

	public function menuDel(){
		$token = $this->getToken();
		$data = $this->obj->menuDel($token);
		return $data;
	}

	public function menuSet($json){
		$token = $this->getToken();
		return $this->obj->menuSet($token, $json);
	}

	public function menuGet(){
		$token = $this->getToken();
		$data = $this->obj->menuGet($token);
		$data = json_decode($data, true);
		if(isset($data['errcode'])){
			return false;
		}
		return $data;
	}

	//主动推送消息(24小时联系的人)

	public function pushMsgText($open_id, $msg){
		$token = $this->getToken();
		return $this->obj->pushMsgText($token, $open_id, $msg);
	}

	public function pushMsgImage($open_id, $media_id){
		$token = $this->getToken();
		return $this->obj->pushMsgImage($token, $open_id, $media_id);
	}

	public function pushMsgImageAdv($open_id, $file){
		if(filesize($file) > 131072){
			return '{errcode: "file size too big"}';
		}
		$token = $this->getToken();
		return $this->obj->pushMsgImageAdv($token, $open_id, $file);
	}

	public function pushMsgVoice($open_id,$media_id){
		$token = $this->getToken();
		return $this->obj->pushMsgVoice($token, $open_id, $media_id);
	}

	public function pushMsgVoiceAdv($open_id, $file){
		if(filesize($file) > 262144){
			return '{errcode: "file size too big"}';
		}
		$token = $this->getToken();
		return $this->obj->pushMsgVoiceAdv($token, $open_id, $file);
	}

	public function pushMsgVideo($open_id, $media_id, $title, $desc){
		$token = $this->getToken();
		return $this->obj->pushMsgVoice($token, $open_id, $media_id);
	}

	public function pushMsgVideoAdv($open_id, $file, $title, $desc){
		if(filesize($file) > 1048576){
			return '{errcode: "file size too big"}';
		}
		$token = $this->getToken();
		return $this->obj->pushMsgVoiceAdv($token, $file,$open_id, $file);
	}

	public function pushMsgMusic($open_id, $file, $title, $desc, $musicurl, $hqmusicurl){
		$token = $this->getToken();
		return $this->obj->pushMsgMusic($token, $open_id, $thumb_media_id, $title, $desc, $musicurl, $hqmusicurl);
	}

	public function pushMsgMusicAdv($open_id, $file, $title, $desc, $musicurl, $hqmusicurl){
		if(filesize($file) > 65536){
			return '{errcode: "file size too big"}';
		}
		$token = $this->getToken();
		return $this->obj->pushMsgMusicAdv($token, $open_id, $file, $title, $desc, $musicurl, $hqmusicurl);
	}



	/**
	 * @exp: $info should be:
	 *		$info[]["title"] = "Happy Day";
     *      $info[]["description"]="Is Really A Happy Day";
     *      $info[]["url"] = "URL";
     *      $info[]["picurl"] = "PIC_URL";
	 */
	public function pushMsgNew($open_id, $info){
		$token = $this->getToken();
		return $this->obj->pushMsgNew($token, $open_id, $info);
	}
//END PUSH
	
	public function download($media_id){
		$token = $this->getToken();
		return $this->obj->download($token, $media_id);
	}

	public function upload($type, $file){
		$token = $this->getToken();
		return $this->obj->upload($token, $type, $file);
	}

	/*public function uploadUrl($type, $url){
		$token = $this->getToken();
		$content = file_get_contents($url, false, stream_context_create(array('http'=> array('timeout'=> 10))));
		//var_dump($content);
		$fn = basename($url);
		$fn = "author.jpg";
		$mime = mime_content_type($fn);
		$mime = "image/jpeg";
		return $this->obj->uploadUrl($token, $type, $fn, $mime, $content);
	}*/

//user info about
	public function getUserInfo($open_id){
		$token = $this->getToken();
		return $this->obj->getUserInfo($token, $open_id);
	}

	public function getUserList($next_openid){
		$token = $this->getToken();
		return $this->obj->getUserList($token, $next_openid);
	}

	public function setUserGroup($json){
		$token = $this->getToken();
		return $this->obj->setUserGroup($token, $json);
	}

	public function getUserGroup(){
		$token = $this->getToken();
		return $this->obj->getUserGroup($token);
	}

	public function getUserGroupPosition($json){
		$token = $this->getToken();
		return $this->obj->getUserGroupPosition($token, $json);
	}

	public function modUserGroup($json){
		$token = $this->getToken();
		return $this->obj->modUserGroup($token, $json);
	}

	public function movUserGroup($json){
		$token = $this->getToken();
		return $this->obj->movUserGroup($token, $json);
	}


///base func lib
	/**
	 * 	返回定长字节(仅utf-8)
	 *	@param string $str 截取字符传
	 *	@param int $len 字节长度(默认:2048字节)
	 *	@ret string	
	 */
	public function byte_substr($str, $len = 2048){
		$ret = '';
		$c = strlen($str);
		for($i=0; $i<$c; $i++){
			if(ord(substr($str, $i, 1)) > 0xa0){
				$temp_wd = substr($str, $i, 3);
				$i += 2;

				$temp_len = strlen($ret);
				if(($temp_len+3)>$len){
					return $ret;
				}else if(($temp_len+3) == $len){
					return $ret.$temp_wd;
				}else{
					$ret .= $temp_wd;
				}
			}else{
				$temp_wd = substr($str, $i, 1);
				$temp_len = strlen($ret);

				if(($temp_len+1)>$len){
					return $ret;
				}else if(($temp_len+1) == $len){
					return $ret.$temp_wd;
				}else{
					$ret .= $temp_wd;
				}
			}
		}
		return $ret;
	}

	public function to_json($arr){
		return $this->obj->to_json($arr);
	}

}
?>
