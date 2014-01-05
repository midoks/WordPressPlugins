<?php
/*
Plugin Name: wp-cache-html
Plugin URI: http://midoks.cachecha.com/
Description: wp-cache-html 
Version: 2.0
Author: Midoks
Author URI: http://midoks.cachecha.com/
 */

///下面的内容,复制到index.php上部分
/******* WP_CACHE_HTML start ********/
//define('WP_CACHE_HTML_ROOT', str_replace('\\', '/', dirname(__FILE__)).'/wp-content/plugins/wp-cache-html/');//index.php
define('WP_CACHE_HTML_ROOT', str_replace('\\', '/', dirname(__FILE__)).'/');//plugin
define('WP_CACHE_HTML_LIB', WP_CACHE_HTML_ROOT.'lib/');

/**
 * @func 	保存的方法
 * local 	本地保存
 * baidubcs 百度保存
 * qiniu 	七牛保存
 * aliyun 	阿里云
 * SaeStorage 新浪云SaeStorage
 */
$option = get_option('wp_cache_html_options');
define('WP_CACHE_HTML_METHOD', $option['method']);
//缓存html时间 s
define('WP_CACHE_HTML_TIME', ($option['timeout'] * 60));
//是否优化保存
define('WP_CACHE_HTML_OPT_SAVED', true);
//POS
define('WP_CACHE_HTML_POS' , __FILE__);

//if(!empty($options)){
////////////////////
include(WP_CACHE_HTML_ROOT.'class_cache_html.php');
global $class_cache_html;
$class_cache_html = new class_cache_html();
global $time_ing_begin;
$time_ing_begin = $class_cache_html->time_ing();
$class_cache_html->set();
//}
/******* WP_CACHE_HTML end ********/


//var_dump(WP_CACHE_HTML_METHOD);

//////////////////////////////////////////
function wp_cache_html_start(){
	global $class_cache_html;
	if(!is_null($class_cache_html)){
		$class_cache_html->set();
	}
}
function wp_cache_html_end(){	
	global $class_cache_html;
	//var_dump($class_cache_html);
	if(!isset($class_cache_html)){exit;}
	$class_cache_html->start();
}
function wp_cache_html_time_show_start(){
	$time = date('Y-m-d H:i:s');
	echo "<!-- 缓存时间{$time} start -->\n";
}
function wp_cache_html_time_show_end(){
	$time = date('Y-m-d H:i:s');
	echo "<!-- 缓存时间{$time} end -->\n";
}


if(!is_admin()){
	add_action('init', 'wp_cache_html_start', 1);
	//add_action('wp_head', 'wp_cache_html_time_show_start', 1);
	//add_action('wp_footer', 'wp_cache_html_time_show_end', 100);
	add_action('shutdown', 'wp_cache_html_end', 0);
}else{
	include(WP_CACHE_HTML_ROOT.'wp-cache-html-options.php');
}
/////////////////////////
//}
?>
