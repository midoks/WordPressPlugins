<?php
/**
 *	@func 接口 用户自动菜单定义
 */
class weixin_robot_event_user{
	
	public $obj = null;

	public $callback = array('$', '#', '@','today','n', 'h', 'r', '?');

	//预留进行二次开发
	public $self_callback = array();//你自定义方法

	public function __construct($obj){
		$this->obj = $obj;
	}

	public function go($key){
		$data = $this->obj->db->weixin_get_menu_data();
		if($data){
			foreach($data as $k=>$v){
				if($key == $v['menu_key']){
					return $this->choose($v['menu_callback'], $v['menu_name']);
				}
			}
		}
		return  $this->obj->helper('key:'.$key."\n".'用户自定菜单响应未定义?');
	}	

	public function choose($case, $name){
		//插件接口调用
		if($wp_plugins = $this->obj->plugins->dealwith('menu', $name)){
			return $wp_plugins;
		}

		include_once(WEIXIN_ROOT_LIB.'text/weixin_robot_textreplay.php');
		if(in_array($case, $this->callback) || in_array(substr($case, 0, 1), $this->callback)){//预定义
			$text = new weixin_robot_textreplay($this->obj, $case);
			return $text->replay();
		}else if(in_array($case, $this->self_callback)){//预留接口
			return $this->self_choose($case);
		}else{
			$text = new weixin_robot_textreplay($this->obj, $case);
			$data = $text->replay();
			if(empty($data) || !$data){
				return $this->obj->toMsgText($case);
			}
			return $data;
		}
	
	}

	public function self_choose($case){}

}
?>
