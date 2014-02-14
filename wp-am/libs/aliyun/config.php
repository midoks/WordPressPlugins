<?php

$option = get_option('wp_am_option');

//Bucket命名规范：
//只能包括小写字母，数字和短横线（-）
//必须以小写字母和数字开头
//长度必须在3-63之间
define('BUCKET_NAME', $option['bucket_name']);
define('FILE_PREFIX', $option['file_prefix']);


//个人测试
//ACCESS_ID
define('OSS_ACCESS_ID', $option['ak']);

//ACCESS_KEY
define('OSS_ACCESS_KEY', $option['sk']);


//是否记录日志
define('ALI_LOG', FALSE);

//自定义日志路径，如果没有设置，则使用系统默认路径，在./logs/
//define('ALI_LOG_PATH','');

//是否显示LOG输出
define('ALI_DISPLAY_LOG', FALSE);

//语言版本设置
define('ALI_LANG', 'zh');

$file_referer = $option['file_referer'];
if(empty($file_referer)){
//防盗链设置
define('DEFAULT_REFFER', '*');
}else{
//防盗链设置
define('DEFAULT_REFFER', $file_referer);
}
?>
