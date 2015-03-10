<?php
/*
Plugin Name: WP微信机器人
Plugin URI: http://midoks.cachecha.com/
Description: Weixin connected to the WordPress, use the information you faster (微信连接Wordpress,使你的传播的信息更快)
Version: 5.2.25
Author: Midoks
Author URI: http://midoks.cachecha.com/
*/

//定义插件地址
define('WEIXIN_ROOT', str_replace('\\', '/', dirname(__FILE__)).'/');
//微信机器人插件URL地址
define('WEIXIN_ROOT_URL', plugins_url('', __FILE__));
//库地址
define('WEIXIN_ROOT_LIB', WEIXIN_ROOT.'lib/');
//第三方接口目录
define('WEIXIN_ROOT_API', WEIXIN_ROOT.'api/');
//定义网络地址
define('WEIXIN_ROOT_NA', plugins_url('image/', __FILE__));
define('WEIXIN_ROOT_VOICE', plugins_url('voice/', __FILE__));

//插件位置
define('WEIXIN_ROOT_POS' , __FILE__);

//定义微信 Token
define('WEIXIN_TOKEN', 'midoks');

//add_action('pre_get_posts', 'weixin_robot_start', 4);
add_action('init', 'weixin_robot_start', 1);
//微信机器人服务开始启用
function weixin_robot_start(){
	//微信消息处理类
	include_once(WEIXIN_ROOT_LIB.'weixin_robot.php');
	$weixin_robot = new weixin_robot();
	if(isset($_GET['midoks']) ){//sign	
		//验证或返回信息
		$weixin_robot->valid();
		exit;
	}
	if(!is_admin()){//前台调用
		$weixin_robot->font();
	}
}

if(isset($_GET['echostr'])){
	weixin_robot_start();
}

//后台管理页
if(is_admin()){
	include_once(WEIXIN_ROOT.'wp-weixin-robot-options.php');
	$options = new wp_weixin_robot_options();
}
?>
