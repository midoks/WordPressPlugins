<?php
/**
 *	@func 微信(根据关键字,来判断发送信息)
 *	Author: Midoks
 *  Author URI: http://midoks.cachecha.com/
 */
include_once(WEIXIN_ROOT.'weixin-core.class.php');
class weixin_cmd extends weixin_core{

	public $info_xml = '';//解析前的数据
	public $info = array();//解析的数据

	public $db = null;//自动义数据库操作对象
	public $wp_db = null; //wordpress 方法定义数据对象
	public $plugins = null;//插件对象

	public $options = array();
	public $replay_type = '';

	public $is_safe_mode = false;//是否为安全模式

	//架构函数
	public function __construct(){
		
		$this->options = get_option('weixin_robot_options');
		parent::__construct();

		include_once(WEIXIN_ROOT_API.'weixin_robot_api_wordpress_dbs.php');
		$this->db = new weixin_robot_api_wordpress_dbs();//WP数据管理

		include_once(WEIXIN_ROOT_API.'weixin_robot_api_wordpress.php');
		$this->wp_db = new weixin_robot_api_wordpress($this);//wordpress数据库管理

		include_once(WEIXIN_ROOT.'wp-weixin-plugins.php');
		$this->plugins = new wp_weixin_plugins($this);//插件管理对象

		//编码编码和解码类
		include_once(WEIXIN_ROOT.'weixin_crypt/wxBizMsgCrypt.php');
	}

	//解析xml文件
	public function parse_xml($string){
		$xml = simplexml_load_string($string, 'SimpleXMLElement', LIBXML_NOCDATA);
		return (Array)$xml;
	}
	
	/**
	 *	@func 命令分析运行
	 *	@ret xml
	 */
	public function cmd(){
		//处理信息
		if(!isset($GLOBALS["HTTP_RAW_POST_DATA"])){
			$data = file_get_contents('php://input');
			if(!empty($data))
				$this->info_xml = $data;
			else
				if(!isset($_GET['debug']))
					exit('你的请求问题!!!');
		}else{
			$this->info_xml = $GLOBALS["HTTP_RAW_POST_DATA"];//POST数据
		}
		//解析后的数据
		$this->info = $this->parse_xml($this->info_xml);
		//file_put_contents(WEIXIN_ROOT.'info.txt', $this->info_xml);

		//安全检查和设置
		if($this->check_safe_mode()){
			$this->set_safe_data();
		}

		//设置debug模式
		$this->set_debug_mode();

		//插件接口调用//回复选择
		if($wp_plugins = $this->plugins->dealwith('all', $this->info)){
			$result =  $wp_plugins;
		}else{
			$result = $this->cmd_choose();
		}

		//安全模式下,返回数据转换
		if($this->is_safe_mode){
			//file_put_contents(WEIXIN_ROOT.'config.txt', json_encode($this->options));
			//$msg = str_replace(array("\r", "\n", "\r\n", ''), '',$msg);
			//file_put_contents(WEIXIN_ROOT.'result.txt', $result);
			$result_en = $this->encode_data($result);
			//file_put_contents(WEIXIN_ROOT.'result_en.txt', $result_en);
			$info = $this->parse_xml($result_en);
			//file_put_contents(WEIXIN_ROOT.'data.txt', json_encode($info));
			if(!empty($info['Encrypt'])){
				$result = $result_en;
			}
		}
		
		//开启数据库记录判断
		if($this->options['weixin_robot_record']){
			$this->weixin_robot_wp_db_insert();
		}
	
		return $result;
	}

	public function weixin_robot_wp_db_insert(){
		$db = $this->db;
		$info = $this->info;
			
		$from = $info['FromUserName'];
		$to = $info['ToUserName'];
	   	$msgid = isset($info['MsgId']) ? $info['MsgId']: '';
		$msgtype = $info['MsgType'];
		$createtime = $info['CreateTime'];

		//文本内容
		$content = isset($info['Content']) ? $info['Content']: '';

		//图片资源
		$picurl = isset($info['PicUrl']) ? $info['PicUrl']: '';

		//地理位置上传
		$location_x = isset($info['Location_X']) ? $info['Location_X']: '0.00';
		$location_y = isset($info['Location_Y']) ? $info['Location_Y']: '0.00';
		$scale = isset($info['Scale']) ? $info['Scale']: '0.00';
		$label = isset($info['Label']) ? $info['Label']: '';

		//link分享
	   	$title= isset($info['Title']) ? $info['Title']: '';
	   	$description = isset($info['Description']) ? $info['Description']: '';
		$url = isset($info['Url']) ? $info['Url']: '';

		//事件
		$event = isset($info['Event']) ? $info['Event']: '';

		//事件中的特殊操作
		if('TEMPLATESENDJOBFINISH'==$info['Event']){
			$content = '模版发送返回消息'.$info['Status'];
		}else if('MASSSENDJOBFINISH' == $info['Event']){
			$content = '事件推送群发结果:'.$info['Status'].
			'成功发送粉丝:'.$info['SentCount'].',失败发送粉丝:'.$info['ErrorCount'];
		}

		$eventkey = isset($info['EventKey']) ? $info['EventKey']: '';

		//语音识别
		$format = isset($info['Format']) ? $info['Format']: '';
		$recognition = isset($info['Recognition']) ? $info['Recognition']: '';

		//资源ID
		$mediaid = isset($info['MediaId']) ? $info['MediaId']: '';
		$thumbmediaid = isset($info['ThumbMediaId']) ? $info['ThumbMediaId']: '';

		//回复
		$response = (!empty($this->replay_type)) ? $this->replay_type : '无回复';

		//反应时间,本来是还有数据库插入的耗时(此时可以忽略不计)
		$response_time = timer_stop(0);
		//echo $response_time;
		//echo 'ok!!!';
		$res =  $db->insert($from, $to, $msgid, $msgtype, $createtime, $content, 
			$picurl, $location_x, $location_y,$scale, $label, $title, $description, 
			$url, $event,$eventkey,$format, $recognition, $mediaid, $thumbmediaid, $response, $response_time);
		//var_dump($res);
		return $res;
	}


	/**
	 * @func 类型选择
	 */
	public function cmd_choose(){
		switch($this->info['MsgType']){
			//文本消息	
			case 'text':return $this->textReply();break;
			//图片消息
			case 'image':return $this->imageReply();break;
			//语音消息
			case 'voice':return $this->voiceReply();break;
			//视频消息
			case 'video':return $this->videoReply();break;
			//事件消息
			case 'event':return $this->eventReply();break;
			//地理位置
			case 'location':return $this->locationReply();break;
			//连接信息
			case 'link':return $this->linkReply;break;
			//默认消息
			default:return $this->textReply();break;
		}
	}



	//文本消息回复
	public function textReply(){
		$kw = $this->info['Content'];//关键字
		include(WEIXIN_ROOT_LIB.'text/weixin_robot_textreplay.php');
		$text = new weixin_robot_textreplay($this, $kw);
		return $text->replay();
	}

	//图片消息回复
	public function imageReply(){

		$info['PicUrl'] = $this->info['PicUrl'];
		$info['MediaId'] = $this->info['MediaId'];

		//插件接口调用
		if($wp_plugins = $this->plugins->dealwith('image', $info)){
			return $wp_plugins;
		}

		return $this->helper("谢谢你的图片提交");
	}

	//语音消息回复(腾讯普通开发者未开启),使用时,请注意
	public function voiceReply(){

		$info['MediaId'] = $this->info['MediaId'];
		$info['Format'] = $this->info['Format'];
		$info['Recognition'] = $this->info['Recognition'];
		
		//插件接口调用
		if($wp_plugins = $this->plugins->dealwith('voice', $info)){
			return $wp_plugins;
		}
	}

	//视频消息回复
	public function videoReply(){
		$info['MediaId'] = $this->info['MediaId'];
		$info['ThumbMediaId'] = $this->info['ThumbMediaId'];

		//插件接口调用
		if($wp_plugins = $this->plugins->dealwith('video', $info)){
			return $wp_plugins;
		}

		return $this->helper("谢谢你的提交的视频信息");
	}


	//事件消息回复
	public function eventReply(){
		$type = $this->info['Event'];
		if($type == 'CLICK'){//自定义菜单事件
			include(WEIXIN_ROOT_LIB.'custommenu/weixin_robot_event_user.php');
			$key = $this->info['EventKey'];
			if(!empty($key)){
				$weixin_robot_event_user = new weixin_robot_event_user($this);
				return $weixin_robot_event_user->go($key);
			}
		}else{
			//载入事件处理
			include(WEIXIN_ROOT_LIB.'weixin_robot_event.php');
			$weixin_robot_event = new weixin_robot_event($this);
			$type = $type.'Event';
			return $weixin_robot_event->$type();
		}
	}

	//地理位置回复
	public function locationReply(){
	
		$info['Location_X'] = $this->info['Location_X'];
		$info['Location_Y'] = $this->info['Location_Y'];
		$info['Scale'] = $this->info['Scale'];
		$info['Label'] = $this->info['Label'];

		//插件接口调用
		if($wp_plugins = $this->plugins->dealwith('location', $info)){
			return $wp_plugins;
		}
		//return $this->helper("谢谢你的提交的地址信息");
	}

	//分享链接信息
	public function linkReply(){

		$info['Title'] = $this->info['Title'];
		$info['Description'] = $this->info['Description'];
		$info['Url'] = $this->info['Url'];

		//插件接口调用
		if($wp_plugins = $this->plugins->dealwith('link', $info)){
			return $wp_plugins;
		}
		//return $this->helper("谢谢你的连接信息");
	}

	//返回帮助信息
	public function helper($string = ''){
		if($this->options['weixin_robot_helper_is'] != 'true'){
			$text = $this->options['weixin_robot_helper'];
			if(!empty($string)){
				return $this->toMsgText($string."\n".$text);//文本
			}else{
				return $this->toMsgText($text);//文本
			}
		}
	}

	public function font(){
		$data = $this->db->select_extends();
		if($data){
			foreach($data as $k=>$v){
				//对已经启用进行后台调用
				$this->plugins->font($v['ext_cn']);
			}
		}
	}

/******************************消息回复*************************************/
	
	//大图片地址
  	public function bigPic(){
  		return WEIXIN_ROOT_NA.'640_320/'.mt_rand(1,5).'.jpg';
    }
  
  	//小图片地址
  	public function smallPic(){
  		return WEIXIN_ROOT_NA.'80_80/'.mt_rand(1,10).'.jpg';
	}


	//设置debug模式
	public function set_debug_mode(){
		if(empty($this->info_xml)){
            //显示模拟信息
          	//测试地址:www.cachecha.com/?midoks&debug=1
			if(isset($_GET['debug'])){
				if('true' == $this->options['weixin_robot_debug']){
					$array['MsgType'] = 'text';//text,event,
					$array['FromUserName'] = 'userid';
					$array['ToUserName'] = 'openid';
					$array['CreateTime'] = time();
					$array['Content'] = (isset($_GET['kw']))?$_GET['kw']:'?';

					//事件名
					//$array['MsgType'] = 'event';//text,event,
					//$array['Event'] = 'LOCATION';
					//$array['EventKey'] = 'MENU_1386835496';
					//
					//$array['Location_X'] = 'Location_X';
					//$array['Location_Y'] = 'Location_Y';
					//$array['Scale'] = 'Scale';
					//$array['Label'] = 'Label';
					//
					
					//var_dump($this->options);

					
					$this->info = $array;
				}else{
					exit('哈哈,哈哈哈,哈哈,哼,你走吧!!!');
				}
			}
		}
	}


	/**
	 * 检查是否为安全模式
	 * ret bool
	 */ 
	public function check_safe_mode(){
		if(isset($this->info['Encrypt'])){
			$this->is_safe_mode = true;
			return true;
		}
		return false;
	}

	/**
	 *	@func 设置解码后的数据
	 */
	public function set_safe_data(){
		$text = $this->info_xml;
		$decode_data = $this->decode_data($text);
		$this->info = $this->parse_xml($decode_data);
	}

	/**
	 *	@func 数据安全 编码
	 */
	public function encode_data($text){
		$token = WEIXIN_TOKEN;
		$encodingAesKey = $this->options['EncodingAESKey'];
		$appId = $this->options['ai'];
		$pc = new WXBizMsgCrypt($token, $encodingAesKey, $appId);

		$timeStamp = time();
		$encryptMsg = '';
		$nonce = $_GET['nonce'];
		$retCode = $pc->encryptMsg($text, $timeStamp, $nonce, $encryptMsg);
		if($retCode == 0){
			return $encryptMsg;
		}
		return $text;
	}

	/**
	 *	@func 数据安全 解码
	 */
	public function decode_data($text){
		$token = WEIXIN_TOKEN;
		$encodingAesKey = $this->options['EncodingAESKey'];
		$appId = $this->options['ai'];
		$pc = new WXBizMsgCrypt($token, $encodingAesKey, $appId);

		$timeStamp = time();
		$nonce = $_GET['nonce'];

		$sha1 = new SHA1;
		$array = $sha1->getSHA1($token, $timeStamp, $nonce, $this->info['Encrypt']);
		$ret = $array[0];
		if ($ret != 0) {
			return $ret;
		}
		$msg_sign = $array[1];
		
		$retCode = $pc->decryptMsg($msg_sign, $timeStamp, $nonce, $text, $msg);
		if($retCode == 0){
			return $msg;
		}
		return $text;
	}
}
?>
