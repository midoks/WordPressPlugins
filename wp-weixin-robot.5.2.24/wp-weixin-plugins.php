<?php
/**
 *	WP微信机器人插件控制类
 */
class wp_weixin_plugins{

	public $obj = null;
	public $db = null;
	public $info = array();
	public $option = array();
	public $lock = null;

	//构造函数
	public function __construct($obj){
		define('WEIXIN_PLUGINS', WEIXIN_ROOT.'extends/');
		$this->obj = $obj;

		$this->info = isset($this->obj->info) ? $this->obj->info : null;
		$this->option = $this->obj->options;
		$this->db = $this->obj->db;


		//锁机制
		include_once(WEIXIN_ROOT.'wp-weixin-lock.php');
		$this->lock = new wp_weixin_lock();
		$this->lock->set_obj($obj);
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
			//已经关注,并再次扫描事件
			case 'scan'		:	$res = $this->p_scan($args);break;
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
	private function p_scan($args){
		if($data = $this->plugins_start('scan', $args)){
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
					include_once($abspath);
					$tt = explode('.', $v['ext_name']);
					$cn = $tt[0];
					$obj = new $cn($this);
					if(method_exists($obj, 'start')){
						$data = $obj->start($args);
						if( $data )	return $data;
					}
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
						$d = WEIXIN_PLUGINS.$f;
						$data = $this->get_plugins_info($d);
						if(!$data){
							continue;
						}
						$a['info'][] = $data;
						$a['abspath'][] = $d;
						$a['path'][] = $f;
						$q = explode('_', $f);
						$a['type'][] = $q[1];
						$b = explode('.', $f);
						$a['classname'][] = $b[0];
					}
				}
			}
		}
		return $a;
	}

	/*	解释说明
	 *	extend_name:扩展名称
	 *  plugin_url:开发扩展的地址
	 *	author: 作者
	 *	version:版本信息
	 *	email:邮件地址
 	 *	description: 描述信息
 	 */
	private function get_plugins_info($file){
		$content = file_get_contents($file);
		preg_match('/\/\*(.*?)\*\//is', $content, $info);

		if(!isset($info[1])){
			return false;
		}

		$e = trim(trim($info[1]), '*');
		$list = explode("\n", $e);
		$nString = array();

		foreach($list as $k=>$v){
			$tmp = trim(str_replace(array('*', ' '), '', $v));
			
			//分割":"、 " "
			$tmp_E = explode(' ', $tmp, 2);
			if(count($tmp_E)<2){
				$tmp_E = explode(':', $tmp, 2);
			}
			
			if(!empty($tmp_E[0])){
				$nString[strtolower($tmp_E[0])] = trim($tmp_E[1]);
			}
		}

		//扩展名称(必选)
		if(!isset($nString['extend_name'])){
			return false;
		}

		//扩展地址(必选)
		if(!isset($nString['extend_url'])){
			return false;
		}

		//作者昵称(必选)
		if(!isset($nString['author'])){
			return false;
		}

		//扩展版本信息(必选)
		if(!isset($nString['version'])){
			return false;
		}

		//扩展联系邮件地址(必选)
		if(!isset($nString['email'])){
			return false;
		}

		//扩展描述信息
		if(!isset($nString['description'])){
			return false;
		}
		return $nString;
	}

	private function get_file_suffix($file){
		$l = explode('.', $file);
		$c = count($l);
		return $l[$c-1];
	}


	//插件安装
	public function install($fn){
		$abspath = WEIXIN_PLUGINS.$fn;
		if($this->_c($abspath)){
			$tt = explode('.', $fn);
			$cn = $tt[0];
			if(!class_exists($cn)){
				$this->obj->notices('此文件下,类名有错!!!');exit;
			}
			$obj = new $cn($this);
			if(method_exists($obj, 'install')){
				return $obj->install();
			}
		}
		return false;
	}

	//插件卸载
	public function uninstall($fn){
		$abspath = WEIXIN_PLUGINS.$fn;
		if($this->_c($abspath)){
			$tt = explode('.', $fn);
			$cn = $tt[0];
			$obj = new $cn($this);
			if(method_exists($obj, 'uninstall')){
				return $obj->uninstall();
			}
			return false;
		}
	}


	private function _c($f){
		$db = $this->obj->db;
		if(!file_exists($f)){
			$fn = basename($f);
			$db->delete_extends_name($fn);
			return false;
		}else{
			include_once($f);
			return true;
		}
	}


	//后台控制
	public function admin($fn){
		$abspath = WEIXIN_PLUGINS.$fn.'.php';
		if($this->_c($abspath)){
			$obj = new $fn($this);
			if(method_exists($obj, 'admin')){
				if(method_exists($obj, 'admin')){
					$obj->admin();
				}
			}
		}
	}

	public function font($fn){
		$abspath = WEIXIN_PLUGINS.$fn.'.php';
		if($this->_c($abspath)){
			$obj = new $fn($this);
			if(method_exists($obj, 'font')){
				if(method_exists($obj, 'font')){
					$obj->font();
				}
			}
		}
	}


	//在WP微信机器人后台控制(添加子菜单)
	public function admin_menu($obj, $name, $pageName){
		add_submenu_page('weixin_robot',
			'weixin_robot',	
			$name,
			'manage_options',
			$pageName,
			array($obj, $pageName));
	}



	//锁定机制
	/**
	 * 创建锁
	 * @param 锁定内容
	 */
	/*public function create_lock($content){}

	//删除锁
	public function delete_lock(){}

	public function exit_lock(){return $this->delete_lock();}

	//获取锁定位置
	public function get_lock_position(){}

	//获取锁定的内容
	public function get_lock_content(){}

	//获取锁定的所有内容
	public function get_lock_all_content(){}
	 */

	//数据是否锁定
	public function data_lock($mixed){
		if($res = $this->check_lock()){
			//var_dump($res);
			$abspath = WEIXIN_PLUGINS.$res['lock_ex'];
			if(!file_exists($abspath)){$this->delete_lock();}
			include_once($abspath);
			$exp = explode('.', $res['lock_ex']);
			$obj = new $exp[0]($this);
			if(method_exists($obj, 'lock')){
				$info = $obj->lock($mixed);
				if(empty($info)){
					return $this->toMsgText('调试程序中...!');
				}else{
					return $info;
				}
			}	
		}else{
			return false;
		}
	}



	public function __call($method, $args){
		$lock_func = array(
			'check_lock',
			'lock_content',
			'add_lock_content',
			'delete_lock',
			'exit_lock',
			'get_lock_pos',
			'get_lock_current_data',
			'get_lock_data');
		if(in_array($method, $lock_func)){
			if(!empty($args))
				return call_user_func_array(array($this->lock, $method), $args);
			else
				return call_user_func(array($this->lock, $method));
		}

		if(!empty($args)){
			return call_user_func_array(array($this->obj, $method), $args);
		}else{
			return call_user_func(array($this->obj, $method));
		}
	}

	/**
	 *	@func 获取接受的所有信息
	 *	@ret array 返回所有的信息
	 */
	public function getAcceptInfo(){
		return $this->obj->info;
	}

	/**
	 * 	@func 获取本插件的所有配置信息
	 *	@ret array 返回数组
	 */
	public function getConfigInfo(){
		return $this->obj->options;
	}

	//用户OpenID
	public function getUserOpenID(){
		return $this->obj->info['FromUserName'];
	}

	//获取开发AppID
	public function getAppID(){
		return $this->obj->obj->app_id;
	}

	//获取开发AppSelect
	public function getAppSelect(){
		return $this->obj->obj->app_sercet;
	}
}
?>
