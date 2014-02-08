<?php
/*
 * Plugin Name: WP代码备份插件
 * Plugin URI: http://midoks.cachecha.com/
 * Description:百度网盘备份[自己设置是否开启]
 * Version: 1.0
 * Author: Midoks
 * Author URI: http://midoks.cachecha.com/
 */
define('BACKUP_ROOT', str_replace('\\', '/', dirname(__FILE__)).'/');
include(BACKUP_ROOT.'config.php');
include(BACKUP_ROOT.'functions.php');
define('BACKUP_LIBS', BACKUP_ROOT.'pcs/');
include(BACKUP_LIBS.'backup.php');

//插件安装时调用
register_activation_hook(__FILE__, 'backup_install');
//插件卸载时,调用
register_deactivation_hook(__FILE__, 'backup_uninstall');
if(stripos($_SERVER['SCRIPT_NAME'], 'wp-cron.php')===false){
}else{
	if('bakcode_go' == $_GET['type']){backup_baidu_header(BACKUP_API_KEY, BACKUP_REDIRECT_URL);}

	if('bakcode_netdisk_oauth' == $_GET['type']){
		$token = '';
		if(isset($_GET['code'])){
			//if(!$_GET['code']){exit('$_GET["code"]参数没有,无法执行');}
			$info = backup_get_baidu_token($_GET['code'], BACKUP_API_KEY, 
				BACKUP_SECRET_KEY, BACKUP_REDIRECT_URL);
			if(!isset($info['access_token'])){
				//跳转获取token
				//header('Location: '.BACKUP_REDIRECT_URL_PREFIX);
				exit("\n获取token失败!!!\n");
			}
			$token = $info['access_token'];
		}else if($info = backup_get_token_by_refresh_token()){
			$token = $info['access_token'];
		}


		//$token不为空
		if(!empty($token)){
			//exit("\n备份成功!!!\n");
			$bp = new backup($token);
			//exit("\ntest:{$token}\n");
			$bool = $bp->up(BACKUP_DIR);
			//$bool = $bp->up(BACKUP_ROOT);
			if(!$bool)
				header('Location: '.BACKUP_REDIRECT_URL_PREFIX);//跳转获取token
			else
				exit("\n备份成功!!!\n");
		}else{
			exit("\n没有获取token\n");
		}
	}
}



add_action('admin_init', 'bakcode_menu_init');
add_action('admin_menu', 'bakcode_menu');
///////////////////////////
//后台设置
function bakcode_menu_init(){
	register_setting('bakcode_options', 'bakcode_options',  'bakcode_options_validate');
}
function bakcode_menu(){
	add_options_page('bakcode','代码保存设置','manage_options','midoks_bakcode','midoks_bakcode');
}
function midoks_bakcode(){
	echo file_get_contents(BACKUP_ROOT.'bakIntro.txt');
}
//////////////////////////
?>
