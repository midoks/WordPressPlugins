<?php
/**
 *	@func 文本消息处理类
 */
class weixin_robot_textreplay{

	public $obj = null;
	public $kw = '';//关键字

	public $list_cmd = array('$', '#', '@','today','n', 'h', 'r', '?');

	/**
	 *	@func 构造函数
	 *	@param object $obj 模板引用对象
	 *	@param string $kw 关键字
	 */
	public function __construct($obj, $kw){
		$this->obj = $obj;
		$this->kw = $this->convert($kw);
	}

	//响应信息
	public function replay(){
		return $this->wx_result();
	}
	
	//文本结果返回
	public function wx_result(){
		$kw = $this->kw;

		//是否锁定
		if($wp_lock = $this->obj->plugins->data_lock($kw)){
			return $wp_lock;
		}

		//插件接口调用
		if($wp_plugins = $this->obj->plugins->dealwith('text', $kw)){
			return $wp_plugins;
		}

		if($kw == '?'){
			return $this->obj->toMsgText($this->obj->options['weixin_robot_helper']);//显示帮助信息
		}

		//聊天模式
		$this->chat_mode($kw);

		
		//文章信息
		if($wp_cmd = $this->wordpress_cmd($kw)){//执行后,结束
			return $wp_cmd;
		}

		//用户自定义关键字回复
		if($user_cmd = $this->user_keyword_cmd($kw)){
			return $user_cmd;
		}

		return $this->obj->helper();//显示帮助信息
	}

	public function chat_mode($kw){
		$option = $this->obj->options;
		if('true' == $option['weixin_robot_chat_mode']){
			if(!empty($option['weixin_robot_reply_id'])){
				$this->chat_mode_on($kw);
			}
		}
	}

	public function chat_mode_on($kw){
		$option = $this->obj->options;
		$info = $this->obj->info;

		if(strpos($kw, ':')){//自己ID向其他ID回复
			$in = explode(':', $kw);
			$send = substr($kw, strpos($kw,':')+1);
			$send = mb_convert_encoding($send, 'UTF-8');
			if($option['weixin_robot_reply_id'] == $info['FromUserName']){
				$res = $this->obj->pushMsgText($in[0], $send);
				$data = json_decode($res, true);
				if('ok'==$data['errmsg']){
					$this->obj->pushMsgText($option['weixin_robot_reply_id'], 'send success!!!');
				}else{
					$this->obj->pushMsgText($option['weixin_robot_reply_id'], 'send fail!!!');
				}
			}
			//echo '1';
		}else{//无":" ;其他ID向自己ID回复
			if($option['weixin_robot_reply_id'] != $info['FromUserName']){
				$res = $this->obj->pushMsgText($option['weixin_robot_reply_id'], $info['FromUserName'].':'.$kw);
			}
			//echo '2';
		}
	}

	//多个关键字提取,(建议关键字不要重叠,自己思量,ID降序)
	//ret bool
	private function user_keyword_cmd_multi($kw, $keyword){
		$list1 = explode(',', $keyword);
		foreach($list1 as $v){
			if($kw==$v) return true;
		}
		if($kw==$keyword) return true;
		return false;
	}

	private function internal_cmd($reply){
		if(in_array($reply, $this->list_cmd) || in_array(strtolower(substr($reply, 0, 1)), $this->list_cmd)){
			return true;
		}else{return false;}	
	}

	//用户自定义关键字回复
	public function user_keyword_cmd($kw){
		$data = $this->obj->db->weixin_get_relpy_data($kw);
		if($data){
			$arr = array();
			foreach($data as $k=>$v){
				if('1' == $v['status']){
					if($this->user_keyword_cmd_multi($kw, $v['keyword']) && 'text' == $v['type']){
						if($this->internal_cmd($v['relpy'])){
							return $this->wordpress_cmd($v['relpy']);
						}else{
							return $this->obj->toMsgText($v['relpy']);
						}
					}else if($this->user_keyword_cmd_multi($kw, $v['keyword']) && 'id' == $v['type']){
						if($this->internal_cmd($v['relpy'])){
							return $this->wordpress_cmd($v['relpy']);//这是为兼容错误
						}else{
							if(count($idsc = explode(',', $v['relpy']))>1){
								$data = $this->obj->wp_db->Qids($idsc);
							}else{
								$data = $this->obj->wp_db->Qid(trim($v['relpy'],','));
							}
							return $data;
						}
					}else if( $this->user_keyword_cmd_multi($kw, $v['keyword']) && 'music' == $v['type']){//推荐使用插件实现..
						if(!empty($v['relpy'])){
							$mlist = explode('|', $v['relpy']);
							if('3'==count($mlist)){
								//title
								if(empty($mlist[0])){
									$title = $kw;
								}else{
									$title = $mlist[0];
								}
								//desc
								if(empty($mlist[1])){
									$desc = date('Y-m-d H:i:s');
								}else{
									$desc = $mlist[1];
								}
								//relpy
								if(empty($mlist[2])){
									return false;
								}else{
									$relpy = $mlist[2];
								}

								return $this->obj->toMsgMusic($title, $desc, $relpy, $relpy);
							}
							return false;
							
						}else{
							return false;
						}
					}
				}
			}
			if(empty($arr)){
				return false;
			}
			return $arr;
		}
		return false;
	}

	//wordpress文件信息(主要的)
	public function wordpress_cmd($kw){
		$wp =  $this->obj->wp_db;
		$prefix = substr($kw, 0, 1);
		$suffix = substr($kw, 1);
		$res = '';


		/* 此,强制转换
		$int = intval($suffix);
		if($int<1){
			$int = 5;
		}else if($int >10){
			$int = 10;
		}
		*/

		//大小写不区分
		$prefix = strtolower($prefix);
		switch($prefix){
			case 'n': $res = $wp->news($suffix);break;
			case 'h': $res = $wp->hot($suffix);break;
			case 'r': $res = $wp->rand($suffix);break;
			//case '#': $res = $wp->get_tags();break;
			case '#': $res = $wp->get_tag_list($suffix);break;
			case '@': $res = $wp->get_category_data($suffix);break;
			default: break;
		}

		if(!empty($res)){
			return $res;
		}

		switch($kw){
			case 'today'	: $res = $wp->today();break;
			case 'p?'		: $res = $wp->total();break;
			default			: $res = $wp->pageView($kw);break;
		}
		return $res;
	}

	/**
	* 字符串半角和全角间相互转换
	* @param string $str 待转换的字符串
	* @param int $type TODBC:转换为半角；TOSBC，转换为全角
	* @return string 返回转换后的字符串
	*/
	public function convert($str, $type = 'TOSBC'){
		$dbc = array('！','？','。','＠','＃','＄');
		$sbc = array('!', '?', '.', '@', '#', '$');
		if($type == 'TODBC'){
			return str_replace($sbc, $dbc, $str); //半角到全角
		}elseif($type == 'TOSBC'){
			return str_replace($dbc, $sbc, $str); //全角到半角
		}else{
			return false;
		}
	}
}
?>
