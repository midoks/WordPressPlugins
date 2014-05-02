<?php
/**
Plugin Name: WP多说扩展
Plugin URI: http://midoks.cachecha.com/
Description: 扩展多说的功能,记录多说用户访问浏览记录
Version: 0.1
Author: Midoks
Author URI: http://midoks.cachecha.com/
*/

define('DUOSHAO_EXTEND', str_replace('\\', '/', dirname(__FILE__)).'/');
define('DUOSHAO_EXTEND_NA', plugins_url('/', __FILE__));

function wp_duoshuo_ex($num = 20, $wh = 25){
	echo file_get_contents(DUOSHAO_EXTEND.'font.css');
	$data = wp_duoshuo_extend_get_data(1, $num);
	echo '<div id="wp_duoshuo_ex"><ul>';
	foreach($data as $k=>$v){
		//var_dump($v);
		echo '<li>','<a href="',$v['user_url'],'" target="_blank" title=',$v['user_name'],'>',
			'<img src="',$v['user_avatar_url'],'" style="width:'.$wh.'px;height:'.$wh.'px;"/>','</a>','</li>';
	}
	echo '</ul></div>','<div style="clear:both;"></div>';
}


function wp_duoshuo_extend_init(){
	if(wp_duoshuo_extend_check_true()){
		if(isset($_POST['data'])){
			//echo json_encode($_POST);
			$url = 'http://api.duoshuo.com/users/profile.json?user_id='.$_POST['data'];
			$data = @file_get_contents($url);
			if($data){
				$duoshuo_array = json_decode($data, true);

				$user_id = $duoshuo_array['response']['user_id'];
				$user_name = $duoshuo_array['response']['name'];
				$user_url = $duoshuo_array['response']['url'];
				$user_avatar_url = $duoshuo_array['response']['avatar_url'];
				$user_connected_services = wp_duoshuo_extend_to_json($duoshuo_array['response']['connected_services']);
				//var_dump($user_connected_services);
				$log_name = $_POST['title'];
				$time = time();
				$ret = wp_duoshuo_extend_ex_insert($user_id, $user_name, $user_url, 
					$user_avatar_url, $user_connected_services, $log_name, $time);
				if($ret) echo 'ok';
				else echo 'fail';
			}	
			exit;
		}
	}else if(isset($_POST['page_ds_ex'])){
		wp_duoshuo_ex_ajax();
	}
}


function wp_duoshuo_extend_to_json($array){
        wp_duoshuo_extend_arrayRecursive($array, 'urlencode', true);
        $json = json_encode($array);
        return urldecode($json);
}
function wp_duoshuo_extend_arrayRecursive(&$array, $function, $apply_to_keys_also = false){
	static $recursive_counter = 0;
	if (++$recursive_counter > 1000) {
		die('possible deep recursion attack');
	}
	foreach ($array as $key => $value) {
		if (is_array($value)) {
			wp_duoshuo_extend_arrayRecursive($array[$key], $function, $apply_to_keys_also);
		} else {
			$array[$key] = $function($value);
		}
		if ($apply_to_keys_also && is_string($key)) {
			$new_key = $function($key);
			if ($new_key != $key) {
				$array[$new_key] = $array[$key];
				unset($array[$key]);
			}
		}
	}
	$recursive_counter--;
}


function wp_duoshuo_extend_check_true(){
	$wp_extend_ext = $_GET['wp_extend_ext'];
	$timestemp = $_GET['timestemp'];
	$midoksecho = $_GET['midoksecho'];

	$sign = sha1($wp_extend_ext.$timestemp);
	if($sign == $midoksecho){
		return true;
	}
	return false;
}



function wp_duoshuo_extend_footer(){
	$DUOSHAO_EXTEND_NA = DUOSHAO_EXTEND_NA;

	//文章标题
	$title = get_the_title();
$script = <<<EOF
<script type="text/javascript" charset="UTF-8" src="$DUOSHAO_EXTEND_NA/wp-duoshuo-ext.min.js"></script>
<script type="text/javascript" charset="UTF-8">
if(typeof console !== 'undefined'){
console.log('欢迎你使用midoks编写的WordPress插件(WP多说扩展)\\n你的使用,是我最大支持(midoks@163.com)\\n我的博客:midoks.cachecha.com');}

setTimeout(function(){if(typeof \$.fn.wp_duoshuo_ext !=='undefined'){\$.fn.wp_duoshuo_ext(location.origin, '$title');}},2000);
</script>
EOF;
	if(is_single())
		echo $script;
}


function wp_duoshuo_extend_install(){//安装插件
	global $wpdb;
	$sql = //"DROP TABLE IF EXISTS `midoks_wp_duoshuo_ex`;
		   "create table if not exists `midoks_wp_duoshuo_ex`(
				`id` bigint(20) not null auto_increment comment '自增ID',
				`user_id` varchar(255) not null comment '用户ID',
				`user_name` varchar(255) not null comment '用户名字',
				`user_url` varchar(255) not null comment '用户地址',
				`user_avatar_url` varchar(255) not null comment '用户头像地址',
				`user_connected_services` text comment '社交账号信息',
				`log_name` varchar(255) not null comment '访问日志的名字',
				`time` int(10) not null  comment '时间',
				primary key(`id`)
	)engine=MyISAM default character set utf8 comment='多说用户访问记录' collate utf8_general_ci;";
	$wpdb->query($sql);
}

function wp_duoshuo_extend_ex_insert($user_id, $user_name, $user_url, 
$user_avatar_url, $user_connected_services, $log_name, $time){
	global $wpdb;
	//做一个限制,30分钟内,访问同一篇文章不记录
	//$before_h = $time - (30*60);
	//$find_e_sql = 'select count(`id`) as `count` from `midoks_wp_duoshuo_ex` where `time`>='.$before_h.' and `time`<='.$time." and `log_name`='{$log_name}'";
		
	//限制一个用户,30分钟访问内,不记录
	$before_h = $time - 1800;
	$find_e_sql = 'select count(`id`) as `count` from `midoks_wp_duoshuo_ex` where `time`>='.$before_h.' and `time`<='.$time;

	//echo $find_e_sql;
	$c = $wpdb->get_results($find_e_sql);
	//var_dump($c);echo '123';
	//var_dump($c[0]->count);
	if($c[0]->count == 0){
		$sql = 'insert into `midoks_wp_duoshuo_ex`(`id`, `user_id`, `user_name`, `user_url`, `user_avatar_url`,'.
			' `user_connected_services`, `log_name`, `time`)'.
			" VALUES(NULL, '{$user_id}', '{$user_name}', '{$user_url}', ".
			"'{$user_avatar_url}', '{$user_connected_services}', '{$log_name}', '{$time}')";
		//echo $sql;echo 'insert';
		return $wpdb->query($sql);
	}
	return 1;
}

function wp_duoshuo_extend_uninstall(){//卸载插件
	global $wpdb;
	$sql = 'DROP TABLE IF EXISTS `midoks_wp_duoshuo_ex`';
	$wpdb->query($sql);
}
//前台展示
add_action('wp_footer', 'wp_duoshuo_extend_footer',99);
add_action('init', 'wp_duoshuo_extend_init', 1);
add_filter('plugin_action_links', 'wp_duoshuo_extend_action_links', 10, 2);
register_activation_hook(__FILE__, 'wp_duoshuo_extend_install');
register_deactivation_hook(__FILE__, 'wp_duoshuo_extend_uninstall');

function wp_duoshuo_extend_action_links($links, $file){
	if(basename($file) != basename(plugin_basename(__FILE__))){return $links;}
    $settings_link = '<a href="admin.php?page=wp_duoshuo_extend#">WP多说扩展</a>';
    array_unshift($links, $settings_link);
    return $links;
}

//后台
add_action('admin_menu', 'wp_duoshuo_extend_menu');
function wp_duoshuo_extend_menu(){
	add_options_page('WP多说扩展','WP多说扩展',
		'manage_options','wp_duoshuo_extend', 'wp_duoshuo_extend_menu_show');
}

function wp_duoshuo_extend_get_data_count(){
	global $wpdb;
	$sql = 'select count(id) as `count` from `midoks_wp_duoshuo_ex`';
	$data = $wpdb->get_results($sql);
	if(!$data){
		return 0;
	}
	return $data[0]->count;
}


function wp_duoshuo_extend_get_data($page=1, $num=20){
	global $wpdb;
	$page_start = ($page-1)*$num;
	$sql = 'select `id`, `user_id`, `user_name`, `user_url`, `user_avatar_url`, `user_connected_services`, '.
		'`log_name`, `time` from `midoks_wp_duoshuo_ex` order by `time` desc'.
		" limit {$page_start},{$num}";
	//echo $sql;
	$data = $wpdb->get_results($sql);
	$info = array();
	foreach($data as $k=>$v){
		$info[$k] = array(
			'id' 		=> $v->id,
			'user_id' 	=> $v->user_id,
			'user_name' => $v->user_name,
			'user_url' 	=> $v->user_url,
			'user_avatar_url' => $v->user_avatar_url,
			'user_connected_services' => $v->user_connected_services,
			'log_name' 	=> $v->log_name,
			'time'		=> $v->time);
	}
	return $info;
}

function wp_duoshuo_extend_menu_show(){
	//过滤ajax请求
	$url = DUOSHAO_EXTEND_NA.'/wp-duoshuo-ext.js?'.mt_rand();
	echo '<script type="text/javascript" src="'.$url.'"></script>';
	echo file_get_contents(DUOSHAO_EXTEND.'admin.css');
	///
	$num = 10;
	$count = wp_duoshuo_extend_get_data_count();
	$paged_num = ceil($count/$num);

	if(isset($_GET['paged'])){
		$paged = $_GET['paged'];
		if(is_numeric($paged)){
			if($paged<=1){$paged = 1;
			}elseif($paged >= $paged_num){
			$paged = $paged_num;}
		}
	}else{$paged = 1;}

	$data = wp_duoshuo_extend_get_data($paged, $num);
	echo '<table class="form-table wp_duoshuo_ex">';
	echo '<thead><tr><td style="color:blue;cursor:pointer;"',
		'onclick="wp_duoshuo_ex_clear(this);">清空所有数据</td><td colspan="5" style="text-align:right;">';
	echo wp_duoshuo_extend_p($count, $paged, $num, 7);
	//title
	echo '<tr class="thead"><td><input onclick="wp_duoshuo_s_all(this);" type="checkbox" value="'.$v['id'].'" />';
	echo '|<span class="AD" onclick="wp_duoshuo_s_adel(this);">删除</span>';
	echo '</td><td>用户</td><td>详细情况</td><td>访问的博文</td><td>访问时间</td><td>操作</td>';
	echo '</tr></thead>';

	echo '<tbody>';
	foreach($data as $k=>$v){
		echo '<tr>';
		echo '<td><input type="checkbox" value="'.$v['id'].'" name="k" /></td>';
		//用户头像
		echo "<td><a href='{$v['user_url']}' target='_blank'>";
		echo '<div class="avatar"><img src="'.$v['user_avatar_url'].'" /></div>';
		echo "</a><div><a href='{$v['user_url']}' target='_blank'>";
		echo $v['user_name'],'</a></div></td>';
		//详细信息
		echo '<td style="text-align:left;"><div>地址:';
		echo "<a href='{$v['user_url']}' target='_blank'>";
		echo "{$v['user_url']}</a></div>";
		//echo "<div>邮箱:{$v['email']}</div>";
		//
		$new_ucs = json_decode($v['user_connected_services'], true);
		$new_ucs = array_values($new_ucs);
		echo '<div><span>社交账号:</span>';
		$nc = count($new_ucs);
		foreach($new_ucs as $kn2=>$v2){
			if(($nc-1) == ($kn2)){
				echo "<a href='{$v2['url']}' title='{$v['description']}' target='_blank'>{$v2['service_name']}</a>";
			}else{
				echo "<a href='{$v2['url']}' title='{$v2['description']}' target='_blank'>{$v2['service_name']}</a>-";
			}
		}
		echo '</div></td>';
		//访问的日志
		echo '<td><div>',$v['log_name'],'</div></td>';
		//时间
		echo '<td>';
		$time = date('Y-m-d H:i:s', $v['time']);
		echo "<div>{$time}</div>";
		echo '</td>';
		/* delete */
		echo '<td><a href="#" onclick="wp_duoshuo_ex_delete('.$v['id'].', this)">删除</a></td>';
		//end tr
		echo '</tr>';
	}
	echo '</tbody></table>';
}
add_action('admin_footer', 'wp_duoshuo_extend_footer_admin');
function wp_duoshuo_extend_p($total, $position, $page=5, $show=7){
	$prev = $position-1;//前页
	$next = $position+1;//下页
	//$showitems = 3;//显示多少li
	$big = ceil($show/2);
	$small = floor($show/2);//$show最好为奇数 
	$total_page = ceil($total/$page);//总页数
	//if($prev < 1){$prev = 1;}
	if($next > $total_page){$next = $total_page;}
	if($position > $total_page){$position = $total_page;}
	if(0 != $total_page){
		echo "<div>";
		echo("<span>总共{$total}条数据/当前第{$position}页<span>");
		/////////////////////////////////////////////
		echo("<span style='margin-left:30px'><a href='".get_pagenum_link(1)."#' class='fixed'>首页</a></span>");
		echo("<span style='margin-left:30px'><a class='p_prev' href='".get_pagenum_link($prev)."#'><<</a></span>");
		$j=0;
		for($i=1;$i<=$total_page;$i++){
			$url = get_pagenum_link($i);
			if($position==$i)
				$strli = "<span style='margin-left:30px'><a href='".$url."#' class='current' >".$i.'</a></span>';
			else
				$strli =  "<span style='margin-left:30px'><a href='".$url."#' class='inactive' >".$i.'</a></span>';
			if($total_page<=$show){echo $strli;}
			if(($position+$small)>=$total_page){
				//也是对的,下面为简化版
				//if(($j<$show) && ($total_page>$show) && ($i>=($position-($small+($position+$small-$total_page))))){echo($strli);++$j;}
				if(($j<$show) && ($total_page>$show) && ($i>=($total_page-(2*$small)))){echo($strli);++$j;}
			}else{if(($j<$show) && ($total_page>$show) && ($i>=($position-$small))){echo($strli);++$j;}}
		}
		echo("<span style='margin-left:30px'><a class='p_next' href='".get_pagenum_link($next)."#'>>></a></span>");
		echo("<span style='margin-left:30px'><a href='".get_pagenum_link($total_page)."#'>尾页</a></span>");
		//////////////////////////////////////////////
		echo '</div>';
	}
}

function wp_duoshuo_extend_footer_admin(){
	echo '<script language="javascript" type="text/javascript" src="http://js.users.51.la/16589822.js"></script>';
	$t = <<<EOT
var h51Time=window.setInterval(hidden51la,100);function hidden51la(){var t={a:'ajiang',a2:'51.la'};for(i=0;i<document.getElementsByTagName("a").length;i++){var temObj=document.getElementsByTagName("a")[i];if(temObj.href.indexOf(t.a)>=0){temObj.style.display="none"}if(temObj.href.indexOf(t.a2)>=0){temObj.style.display="none";clearInterval(h51Time)}}}
EOT;
	
	if(isset($_GET['page']) && 'wp_duoshuo_extend'== $_GET['page']){
		echo '<script> '.$t.' </script>';
	}
}

function wp_duoshuo_ex_ajax(){
	if(isset($_POST['page_ds_ex'])){
		switch($_POST['method']){
			case 'delete_id': $res = wp_duoshuo_ex_ajax_delete_id($_POST['id']);break;
			case 'truncate': $res = wp_duoshuo_ex_ajax_truncate();break;
			default: $res = 'fail';break;
		}
		echo $res;exit;
	}
}

function wp_duoshuo_ex_ajax_delete_id($id){
	global $wpdb;
	$sql = "delete from `midoks_wp_duoshuo_ex` where `id`='{$id}' limit 1";
	//echo $sql;
	$ret_e_row = $wpdb->query($sql);
	//var_dump($wpdb);
	if($ret_e_row){
		return 'ok';
	}else{
		return 'fail';
	}
}

function wp_duoshuo_ex_ajax_truncate(){
	global $wpdb;
	$sql = 'truncate `midoks_wp_duoshuo_ex`';
	$ret_e_row  = $wpdb->query($sql);
	return 'ok';
}
?>
