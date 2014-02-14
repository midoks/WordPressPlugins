<?php

define('T_QN', str_replace('\\', '/', dirname(__FILE__)).'/');
include(T_QN.'config.php');
include(T_QN.'sdk/rs.php');
include(T_QN.'sdk/rsf.php');
include(T_QN.'sdk/io.php');

Qiniu_SetKeys(QINIU_AK, QINIU_SK);

$put = new Qiniu_RS_PutPolicy(BUCKET_NAME);
//file_put_contents(AM_ROOT.'J.TXT', json_encode(array('d')));
$token = $put->Token(null);
file_put_contents(T_QN.'Q.TXT', json_encode($token));

list($ret, $err) = Qiniu_Put($token, 't.txt', '123', null);
if( $err !== null){
	file_put_contents(T_QN.'t1.txt', json_encode($err));
}else{
	file_put_contents(T_QN.'t2.txt', json_encode($ret));
}
?>
