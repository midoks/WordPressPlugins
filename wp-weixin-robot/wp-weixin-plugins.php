<?php
/**
 *	WP微信机器人插件控制类
 */
class wp_weixin_plugins{

	public $obj = null;
	public $db = null;
	public $info = array();
	public $option = array();

	//构造函数
	public function __construct($obj){
		define('WEIXIN_PLUGINS', WEIXIN_ROOT.'extends/');
		$this->obj = $obj;
		$this->info = isset($this->obj->info) ? $this->obj->info : null;
		$this->option = $this->obj->options;
		$this->db = $this->obj->db;
	}

	/**
	 * @func 处理分离的功能
	 * @param string $func 功能名
	 * @param string $args 其他参数
	 * @return bool
	 */
	public function dealwith($func, $args){
		$res = '';
		switch($func){
			//所有
			case 'all'		:	$res = $this->p_all($args);break;
			//订阅
			case 'subscribe':	$res = $this->p_subscribe('');break;
			//文本消息	
			case 'text'		:	$res = $this->p_text($args);break;
			//图片消息
			case 'image'	:	$res = $this->p_image($args);break;
			//语音消息
			case 'voice'	:	$res = $this->p_voice($args);break;
			//视频消息
			case 'video'	:	$res = $this->p_video($args);break;
			//地理位置
			case 'location'	:	$res = $this->p_location($args);break;
			//连接信息
			case 'link'		: 	$res = $this->p_link($args);break;
			//菜单插件
			case 'menu'		:	$res = $this->p_menu($args);break;
			//默认消息
			default			:	$res = $this->p_text('');break;
		}
		if(empty($res)){
			return false;
		}
		return $res;
	}

	private function p_all($args){
		if(empty($args)){return false;}
		if($data = $this->plugins_start('all', $args)){
			return $data;
		}
		return false;
	}

	//订阅
	private function p_subscribe($args){
		if($data = $this->plugins_start('subscribe', $args)){
			return $data;
		}
		return false;
	}

	/**
	 *	@func 文本关键回复
	 *	@param string 字符
	 *	@ret xml
	 */
	private function p_text($kw){
		if(empty($kw)){return false;}
		if($data = $this->plugins_start('text', $kw)){
			return $data;
		}
		return false;
	}

	/**
	 *	@func 图片
	 *	@param array 图片消息
	 *	@return mixed
	 */
	private function p_image($info){
		if(empty($info)){return false;}
		if($data = $this->plugins_start('image', $info)){
			return $data;
		}
		return false;
	}

	/**
	 *	@func 声音信息
	 *	@param array 图片消息
	 *	@return mixed
	 */
	private function p_voice($info){
		if(empty($info)){return false;}
		if($data = $this->plugins_start('voice', $info)){
			return $data;
		}
		return false;
	}

	/**
	 *	@func 视频信息
	 *	@param array 图片消息
	 *	@return mixed
	 */
	private function p_video($info){
		if(empty($info)){return false;}
		if($data = $this->plugins_start('video', $info)){
			return $data;
		}
		return false;
	}

	/**
	 *	@func 地理信息
	 *	@param array 图片消息
	 *	@return mixed
	 */
	private function p_location($info){
		if(empty($info)){return false;}
		if($data = $this->plugins_start('location', $info)){
			return $data;
		}
		return false;
	}

	/**
	 *	@func 分享链接信息
	 *	@param array 图片消息
	 *	@return mixed
	 */
	private function p_link(){
		if(empty($info)){return false;}
		if($data = $this->plugins_start('link', $info)){
			return $data;
		}
		return false;
	}

	/**
	 * @func 分离出菜单控制(本插件功能并不能做到100%,提供次接口,让你自己控制)
	 * @param menu_name 菜单名字
	 */
	private function p_menu($menu_name){
		if(empty($menu_name)){return false;}
		if($data = $this->plugins_start('menu', $menu_name)){
			return $data;
		}
		return false;
	}

	//插件启用
	//返回数组
	private function plugins_start($name, $args){
		$db = $this->obj->db;
		$flist = $db->select_extends_type($name);
		if(!$flist) return false;
		foreach($flist as $k=>$v){
			if($name == $v['ext_type']){
				$abspath = WEIXIN_PLUGINS.$v['ext_name'];
				if(!file_exists($abspath)){
					$db->delete_extends_name($v['ext_name']);
				}else{
					include($abspath);
					$tt = explode('.', $v['ext_name']);
					$cn = $tt[0];
					$obj = new $cn($this);
					$data = $obj->start($args);
					if( $data )	return $data;
				}	
			}
		}
		return false;
	}

	public function get_all_plugins(){
		$a = array();
		if($h = opendir(WEIXIN_PLUGINS)){
			while($f = readdir($h)){
				if($f =='.' || $f=='..'){
				}else if(is_file(WEIXIN_PLUGINS.$f)){
					if('php' == $this->get_file_suffix($f)){
						$a['abspath'][] = WEIXIN_PLUGINS.$f;
						$a['path'][] = $f;
						$q = explode('_', $f);
						$a['type'][] = $q[1];
					}
				}
			}
		}
		return $a;
	}

	public function get_file_suffix($file){
		$l = explode('.', $file);
		$c = count($l);
		return $l[$c-1];
	}


	//插件安装
	public function install($fn){
		$abspath = WEIXIN_PLUGINS.$fn;
		include($abspath);
		$tt = explode('.', $fn);
		$cn = $tt[0];
		if(!class_exists($cn)){
			$this->obj->notices('此文件下,类名有错!!!');exit;
		}
		$obj = new $cn($this);
		if(method_exists($obj, 'install')){
			$obj->install();
		}
	}

	//插件卸载
	public function uninstall($fn){
		$abspath = WEIXIN_PLUGINS.$fn;
		include($abspath);
		$tt = explode('.', $fn);
		$cn = $tt[0];
		$obj = new $cn($this);
		if(method_exists($obj, 'uninstall')){
			$obj->uninstall();
		}
	}


	public function __call($method, $args){
		return call_user_func_array(array($this->obj, $method), $args);
	}

	
}
?>
