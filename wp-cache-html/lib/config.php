<?php
$option = get_option('wp_cache_html_options');
define('BUCKET_NAME', $option['bucket_name']);
if('local'==$option['method']){

}elseif('aliyun'==$option['method']){
	//Bucket命名规范：
	//只能包括小写字母，数字和短横线（-）
	//必须以小写字母和数字开头
	//长度必须在3-63之间
	//ACCESS_ID
	define('OSS_ACCESS_ID', $option['ak']);
	//ACCESS_KEY
	define('OSS_ACCESS_KEY', $option['sk']);
	include(WP_CACHE_HTML_LIB.'aliyun/config.php');
}elseif('baidubcs' == $option['method']){
	//AK公钥
	define('BCS_AK', $option['ak']);
	//SK私钥
	define('BCS_SK', $option['sk']);
	//BUCKET名字
	define ('BCS_BUCKET', $option['bucket_name']);
	///////////////////////////////////
	include(WP_CACHE_HTML_LIB.'baidubcs/config.php');
}else if('qiniu'== $option['method']){
	//ACCESS_ID
	define('QINIU_AK', $option['ak']);
	//ACCESS_KEY
	define('QINIU_SK', $option['sk']);
	include(WP_CACHE_HTML_LIB.'qiniu/config.php');
}elseif('SaeStorage' == $option['method']){
	include(WP_CACHE_HTML_LIB.'SaeStorage/config.php');
}
?>
