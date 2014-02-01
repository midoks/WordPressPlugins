<?php
/*
 * Plugin Name: 搜索引擎蜘蛛记录
 * Plugin URI: http://midoks.cachecha.com/
 * Description: 记录搜索引擎蜘蛛
 * Version: 2.0
 * Author: Midoks
 * Author URI: http://midoks.cachecha.com/
 */
date_default_timezone_set('PRC');
class wp_spider{

	public $tname = 'midoks_spider';//表名
	
	public function __construct(){
		//插件安装时调用
		register_activation_hook(__FILE__, array(&$this, 'install'));
		//插件卸载时,调用
		register_deactivation_hook(__FILE__, array(&$this, 'uninstall'));
		//add_action('pre_get_posts', array(&$this, 'insert'), 4);
		//$this->insert();
		add_action('init', array(&$this, 'insert'));
		add_filter('plugin_action_links', array($this, 'spider_action_links'), 10, 2);
		//echo '1231231as2d1f23as1d3f';
	}

	//插件启用操作
	public function install(){
		global $wpdb;
		$sql = "create table if not exists `{$this->tname}`(
					`id` int(10) not null auto_increment comment 'ID',
					`name` varchar(50) not null comment '蜘蛛名字',
					`time` varchar(13) not null comment '时间',
					`ip` varchar(15) not null comment 'IP地址',
					`url` varchar(255) not null comment '收录地址',
					primary key(`id`)
				)engine=MyISAM default character set utf8 comment='管理员表' collate utf8_general_ci;";
		//echo $sql;
		$wpdb->query($sql);
	}

	//插件卸载操作
	public function uninstall(){
		global $wpdb;
		$sql = "drop table `{$this->tname}`";
		$wpdb->query($sql);
	}

	//清空数据库
	public function clear(){
		global $wpdb;
		$sql = "truncate table `{$this->tname}`";
		$wpdb->query($sql);
	}

	public function insert(){
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		$spider_list = $this->spiderList();
		$url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];//蜘蛛访问的页面

		//前台记录
		if(!is_admin()){
			foreach($spider_list as $k=>$v){
				if((preg_match('/'.$k.'/i', $user_agent))){
					$this->insertValue( empty($v) ? 'test' : $v, $url);
					break;
				}
			}

			//新增加对微信公众记录添加
			/*if(isset($_GET['signature'])){
              	$postStr = $GLOBALS['HTTP_RAW_POST_DATA'];//post数据
				//提交后的数据
				if(!empty($postStr)){
					$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
                  	$this->insertValue('微信公众', //$url.'|'.
                                       json_encode($postObj));
                }else{
                	$this->insertValue('微信公众', $url);
                }
			}*/
			
		}
		return true;
	}

	public function insertValue($spiderName, $url){
		$time = time();
		$ip = $this->get_client_ip();
		return $this->insertTableValue($spiderName, $time, $ip, $url);
	}

	/**
	 *	@func 向表插入值
	 */
	public function insertTableValue($spiderName, $time, $ip, $url){
		global $wpdb;
		$sql = "insert into `{$this->tname}`(id, name, time, ip, url) values(NULL, '{$spiderName}', '{$time}', '{$ip}', '{$url}')";
		return $wpdb->query($sql);
	}

	/**
	 *	@func 蜘蛛种类
	 */
	public function spiderList(){
		$spider_list = array (
			'googlebot' => '谷歌',
			'mediapartners-google' => 'Google Adsense',
			'baiduspider' => '百度',
			'slurp' => '雅虎',
			'Sogou' => '搜狗',
			'sosospider' => '腾讯SOSO',
			'ia_archiver' => 'Alexa',
			'iaarchiver' => 'Alexa',
			'yodaobot' => 'Yodao',
			'sohu-search' => '搜狐',
			'msnbot' => 'MSN',
			'360Spider'=>'360',
			'DNSPod'=>'DNSPod',
		);
		return $spider_list;
	}

	//初始化后台选项设置
	public function spider_action_links($links, $file) {
    	if ( basename($file) != basename(plugin_basename(__FILE__))){
			return $links;
		}
    	$settings_link = '<a href="admin.php?page=basic_setting">设置</a>';
    	array_unshift($links, $settings_link);
    	return $links;
	}

	//获取客服端真是IP
	public function get_client_ip(){
		static $ip = null;
		if($ip != null) return $ip;
		if( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ){
			$arr = explode( ',' ,$_SERVER['HTTP_X_FORWARDED_FOR'] );
			$pos = array_search( 'unknown' , $arr );
			if( false !=$pos ) unset($arr[$pos]);
			$ip = trim( $arr[0] );
		}elseif( isset( $_SERVER['HTTP_CLIENT_IP'] ) ){
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		}elseif( isset($_SERVER['REMOTE_ADDR'] ) ){
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		//检查IP地址的合法性
		$ip = (false!==ip2long($ip)) ? $ip : '0,0,0,0';
		return $ip;
	}
}

global $spider;
$spider = new wp_spider();

define('SPIDER_ROOT', str_replace('\\', '/', dirname(__FILE__)).'/');
//后再后台控制代码
include_once(SPIDER_ROOT.'wp-spider-option.php');
$spider_option = new wp_spider_option();
?>
