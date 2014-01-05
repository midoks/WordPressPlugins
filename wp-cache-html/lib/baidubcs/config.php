<?php
//请求的主机
define('BCS_HOST', 'bcs.duapp.com');
//sdk superfile分片大小 ，单位 B（字节）
define ( 'BCS_SUPERFILE_SLICE_SIZE', 1024 * 1024);
//superfile 每个object分片后缀
define ( 'BCS_SUPERFILE_POSTFIX', '_bcs_superfile_');
//文件默认权限
define('OBJECT_DEFAULT_ACL' , 'private');
//防盗链设置
define('OBJECT_DEFAULT_REFFER', '*');
?>
