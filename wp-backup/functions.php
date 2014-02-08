<?php
//安装时调用
function backup_install(){
	$option = get_option('wp-backup-option');
  	$option['expires_in'] = '';
  	$option['refresh_token'] = '';
  	$option['access_token'] = '';
  	$option['session_secret'] = '';
  	$option['session_key'] = '';
  	$option['scope'] = '';
	add_option('wp-backup-option');
}

//卸载时调用
function backup_uninstall(){
	delete_option('wp-backup-option');
}

//更新数据库中保存的信息
function backup_update_token_to_option($info){
	//var_dump(get_option('wp-backup-option'));
	$option['expires_in'] = $info['expires_in'];
  	$option['refresh_token'] = $info['refresh_token'];
  	$option['access_token'] = $info['access_token'];
  	$option['session_secret'] = $info['session_secret'];
  	$option['session_key'] = $info['session_key'];
  	$option['scope'] = $info['scope'];
  	update_option('wp-backup-option', $option);
}

//跳转
function backup_baidu_header($client_id, $redirect_url){
	$header_url = "https://openapi.baidu.com/oauth/2.0/authorize?".
		"response_type=code&".
		"client_id={$client_id}&".
  		"redirect_uri={$redirect_url}&".
		"scope=netdisk&display=popup";
	header('Location: '.$header_url);
	exit;
}


function backup_get_token_by_refresh_token(){
  	//return false;
  	$option = get_option('wp-backup-option');
  	if(isset($option['error'])){
    	return false;
    }
	$info = backup_get_baidu_refresh_token($option['refresh_token'], BACKUP_API_KEY, BACKUP_SECRET_KEY, $option['scope']);
  	return $info;
}



//条件是否具备
function backup_is_backup(){
	if(isset($_GET['doing_wp_cron']) && $_GET['doing_wp_cron']==''){
		return true;
	}
	return false;
}



/* 获取oauth json数据 */
function backup_get_baidu_token($code, $client_id, $client_secret, $redirect_url){
	$url_str ="https://openapi.baidu.com/oauth/2.0/token?".
	"grant_type=authorization_code&".
    "code={$code}&".
    "client_id={$client_id}&".
    "client_secret={$client_secret}&".
	"redirect_uri={$redirect_url}";
	$info = json_decode(file_get_contents($url_str), true);
	backup_update_token_to_option($info);
  	return $info;
}

//十年期限的refresh_token使用
function backup_get_baidu_refresh_token($refresh_token, $client_id, $client_secret, $scope){
	$url = "https://openapi.baidu.com/oauth/2.0/token?".
    	"grant_type=refresh_token&".
		"refresh_token={$refresh_token}&".
		"client_id={$client_id}&".
		"client_secret={$client_secret}&".
		"scope={$scope}";
	$info = file_get_contents($url);
  	if(!$info){
    	return false;
    }
  	$info = json_decode($info, true);
  	if(isset($info['error'])) return false;
  	update_option('wp-backup-option', $info);
  	return $info;
}

/* 获取数据 */
function backup_get_url_data($url, $arg = array()){
	//初始化连接
	$go = curl_init();
	//设置URL地址
	curl_setopt($go, CURLOPT_URL , $url);
	curl_setopt($go, CURLOPT_HEADER , 0);
	//设置是否可以跳转
	curl_setopt($go, CURLOPT_FOLLOWLOCATION , 1);
	//设置跳转的次数
	curl_setopt($go, CURLOPT_MAXREDIRS , 10);
	//curl_setopt($go, CURLOPT_USERGENT , $_SERVER['HTTP_USER_AGENT']);
	//头文件
	curl_setopt($go, CURLOPT_HEADER , 0);
	//返回数据流
	curl_setopt($go, CURLOPT_RETURNTRANSFER , 1);
	//SSL需要
	curl_setopt($go, CURLOPT_SSL_VERIFYPEER , 1);
	//POST数据
	//curl_setopt($go, CURLOPT_POST ,1);
	//curl_setopt($go, CURLOPT_POSTFIELDS ,$arg);
	$data = curl_exec($go);
	curl_close($go);
	return $data;		
}


/* 读取备份文件中,所有目录 */
function backup_foreach_file($name){
	$name = rtrim($name,'/').'/';
	$fp = opendir($name);
	$arr = array();
	while($n = readdir($fp))
		if($n=='.'|| $n=='..'){
		}else if(is_dir($name.$n)){
			$arr[]= backup_foreach_file($name.$n);
		}else if(is_file($name.$n)){
			$arr[] = $name.$n;
		}
	closedir($fp);
	return $arr;
}

/**
 *	整理数组
 *	把多位数据改为一维数组
 *	@param array $arr 多维数组
 *	@param array $yw 一维数据
 *	@return array 一维数组
 */
function backup_array_sort($arr, &$yw=array()){
	foreach($arr as $k=>$v){
		if(is_array($v)){
			backup_array_sort($v,$yw);
		}else{
			$yw[] = $v;
		}
	}
	return $yw;
}

/* 判断是否为json格式 */
function backup_is_json($str){
	$str = substr($str, 0, 1);
	if('{' != $str){
		return false;
	}
	return true;
}
?>
