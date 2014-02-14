<?php
/*
 * Plugin Name: WP附件管理
 * Plugin URI: http://midoks.cachecha.com/
 * Description: WP附件地址管理(本地,百度bcs,阿里云)[针对个人博客]
 * Version: 3.0
 * Author: Midoks
 * Author URI: http://midoks.cachecha.com/
 */
define('AM_ROOT', str_replace('\\', '/', dirname(__FILE__)).'/');
define('AM_ROOT_NA', plugins_url('/', __FILE__));
define('AM_LIBS', AM_ROOT.'libs/');
date_default_timezone_set('PRC');//设置时区
//附件管理
class wp_am{
	
	//构造函数
	public function __construct(){
		//插件安装时调用
		register_activation_hook(__FILE__, array(&$this, 'am_install'));
		//插件卸载时,调用
		register_deactivation_hook(__FILE__, array(&$this, 'am_uninstall'));

		if(is_admin()){
			//最先进行
			add_action('init', array($this, 'init'), 4);
			add_action('admin_init' , array($this, 'menu_init'));
			//菜单初始化
			add_action('admin_menu', array(&$this, 'BuildMenu'));
			//后台设置
			add_filter('plugin_action_links', array($this, 'wp_am_action_links'), 10, 2);
		}	
	}

	//插件|安装时调用
	public function am_install(){
		//$option = get_option('wp-am-option');
		$option['height'] = '60';
		$option['width'] = '60';
		$option['pic_preview'] = false;//是否开启图片预览
		$option['position'] = 'local';//默认本地管理
		$option['ak'] = '';
		$option['sk'] = '';	
		$option['bucket_name'] = 'wordpress'; //bucket名字(文件)
		$option['file_prefix'] = ''; //文件前缀(默认)
		$option['file_type'] = '0'; //文件格式
		$option['file_referer'] = '*'; //文件格式
		add_option('wp_am_option', $option);
	}

	//插件|卸载时挑中
	public function am_uninstall(){
		delete_option('wp_am_option');
	}

	//接受信息
	public function init(){

		//配置修改
		if(isset($_POST['submit']) && isset($_POST['option_page']) && $_POST['option_page'] == 'wp_am_option' ){
			update_option('wp_am_option', $_POST['wp_am_option']);
		}			

		//ajax接受
		if(( isset($_GET['page']) && ($_GET['page'] == 'wp_am') && ($_GET['type'] == 'ajax')) || (!empty($_FILES))){
			//$upinfo = $_FILES;
			//file_put_contents(AM_ROOT.'d2.txt',json_encode($upinfo));
			include(AM_ROOT.'wp-am-box.php');
			$wab = new wp_am_box();
			exit;
		}	
	}

	//菜单初始化
	public function menu_init(){
		include(AM_ROOT.'wp-am-option.php');
		$this->wao = new wp_am_option();
		$this->wao->config_init();
	}

	//构建菜单
	public function BuildMenu(){
		//添加主目录
		add_menu_page('wp_am_options',
			'附件管理',
			'manage_options',
			'wp_am',
			array(&$this, 'show'),
			AM_ROOT_NA.'/img/am.png');
		//添加子目录
		add_submenu_page('wp_am',
			'wp_am',	
			'附件配置',
			'manage_options',
			'wp_am_config',
			array(&$this, 'config'));
		add_submenu_page('wp_am',
			'wp_am',	
			'附件在线管理',
			'manage_options',
			'wp_am_manage',
			array(&$this, 'manage'));

		//在文章页添加组件
		add_meta_box('wp_am_meta_box', __('附件管理(midoks)'), array($this, 'wp_am_add_meta_box'), 'post');
	}

	public function F($name){
		return file_get_contents(AM_ROOT.'html/'.$name.'.html');
	}
	public function show(){
		echo $this->F('show');
	}
	public function config(){
		$this->wao->config_show();
		//echo $this->F('config');
	}
	public function manage(){
		echo '<div id="advanced-sortables" class="meta-box-sortables ui-sortable"><div id="wp_am_meta_box" class="postbox ">
			<div class="handlediv" title="点击以切换"><br></div><h3 class="hndle"><span>附件管理(midoks)</span></h3>
			<div class="inside">';
		$this->wp_am_add_meta_box();
		echo '</div></div></div>';
	}

	//初始化后台选项设置
	public function wp_am_action_links($links, $file) {
    	if(basename($file) != basename(plugin_basename(__FILE__))){return $links;}
    	$settings_link = '<a href="admin.php?page=wp_am_config">设置</a>';
    	array_unshift($links, $settings_link);
    	return $links;
	}

	//在文章页添加组件
	public function wp_am_add_meta_box(){
		echo $this->F('meta_box');

		//echo '<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>';
		echo "<script src=\"".AM_ROOT_NA."js/jquery.min.js\" type=\"text/javascript\"></script>";
		echo "<script src=\"".AM_ROOT_NA."js/uploadify/jquery.uploadify.min.js\" type=\"text/javascript\"></script>";
		//echo "<script src=\"".AM_ROOT_NA."js/uploadify/uploadify.js\" type=\"text/javascript\"></script>";
		echo '<link type="text/css" href="'.AM_ROOT_NA.'js/uploadify/uploadify.css" rel="stylesheet"/>';
		$root = AM_ROOT_NA;
		$timestamp = time();
		$md5 = md5(time());
		//上传设置
		$upload_confg =
<<<EOT
<script>
console.log('作者博客:midoks.cachecha.com,你的使用,是我最大的支持!!!');
jQuery(function($){
$(document).ready(function(){


$('#upload').uploadify({
	'formData'     : {
		'timestamp' : '{$timestamp}',
		'token'     : '{$md5}',
	},
	//'id'		: 	'midoks_wordpress',
	'swf'     	: 	'{$root}js/uploadify/uploadify.swf',
	'uploader'	: 	'admin.php?page=wp_am&type=ajax',
	'width'		:	'40',//宽度
	'height'	:	'24',
	'queueID'   : 	'fileQueue', //队列ID
	'multi'   	: 	true, //是否支持多文件上传
	'buttonText': 	'上传', //按钮上的文字

	onSWFReady	:	function(){
		//启动初始化
		$.fn.get_root_file();
		//console.log('onSWFReady');
	},
	onUploadError:	function(){
		$.fn.Toast('上传失败!!!', 3000);
	},
	onUploadSuccess:function(){
		//$.fn.Toast('test!!test', 1000);
		//查看当前的转台的值
		var data = $('#wp_am_meta_box_inline_current_status').data('current');//当前状态
		
		if(typeof data == 'undefined' ){
			$.fn.get_root_file();//获取根目录
		}else{
			if(typeof data.fn != 'undefined'){
				var fn = data.fn[0];//文件名			
				var position = data.position[0];//文件位置
				var position_local = data.position_local[0];//基本属性
				var uptime = data.uptime[0];//上传时间
				var filetype = data.filetype[0];//文件类似
				var wrx = data.wrx[0];//权限
				var reffer = data.reffer[0];//防盗链

				
				var tmp = position_local.split("/");
				tmp.pop();
				var tmps = tmp.join("/")+"/";
				var info = {
					position:position,
					filetype:filetype,
					wrx:wrx,
					reffer:reffer,
					uptime:uptime,
					fn:fn[i],
					position_local:tmps,
				};	
				//console.log(info);
				$.fn.get_root_file('get_child_file', info);
				//提示
				$.fn.Toast('上传成功!!!', 3000);
			}else{
				$.fn.get_root_file();//获取根目录
			}
		}
		
	},

	//debug:true,
});

$('#upload').addClass('button-primary');
var w = $('#wp_am_meta_box_show').width();
var h = $('#wp_am_meta_box_show').height();
$('#upload-queue').css('display', 'block').css('position','absolute').
css('left', parseInt($('#wp_am_meta_box_show').width())/2-50).css('z-index',1).
css('top', parseInt($('#wp_am_meta_box_show').height())/2-50).css('opacity', 0.5);
//$('#upload-button').addClass('button-primary');

});});
</script>
EOT;

		
		echo "<script src=\"".AM_ROOT_NA."js/jquery_plugin_menu.js\" type=\"text/javascript\"></script>";
		echo "<script src=\"".AM_ROOT_NA."js/wp_am_box.js\" type=\"text/javascript\"></script>";
		echo trim($upload_confg);
	}

}


//加载后,实例化(运行)
//function wp_am_init() {
$wp_am = new wp_am();
//}
//add_action('plugins_loaded', 'wp_am_init');
?>
