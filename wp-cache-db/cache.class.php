<?php
/**
 *	@func cache 缓存使用
 *	@func 此文件代替wp-includes/wp-db.php文件
 *	@file:参考文件wp-includes/wp-db.php
 *	@author midoks
 *	@link midoks.cachecha.com
 */
//版本信息
define('PLUGINS_CACHE_VERSION', '1.0');
include_once('common.php');
///以上的为一些定义
class DbCache extends wpdb{

	//数据缓存接口
	public $api_cache = null;

	//默认缓存查询true
	public $cache_bool = true;

	//架构函数|初始化数据
	public function __construct($dbuser, $dbpassword, $dbname, $dbhost){
		//错误显示
		if(WP_DEBUG)
			$this->show_errors();

		//初始化
		$this->init_charset();
		//连接数据库
		$this->dbuser = $dbuser;
		$this->dbpassword = $dbpassword;
		$this->dbname = $dbname;
		$this->dbhost = $dbhost;
		$this->db_connect();

		//缓存的参数
		//$this->cache_config = get_option('wpcachedb_options');

		if(@include_once(CACHE_PATH.'/apicache.php')){
			//接口函数
			$this->api_cache =& new ApiCache(get_wp_db_cache_options('wpcachedb_options'));
		
			//只缓存前端需要的文件
			if (((defined('WP_ADMIN' ) && WP_ADMIN) ||
				(defined('DOING_CRON' ) && DOING_CRON) || 
				(defined('DOING_AJAX' ) && DOING_AJAX) || 
				strpos($_SERVER['REQUEST_URI'], 'wp-admin') || 
				strpos($_SERVER['REQUEST_URI'], 'wp-login') || 
				strpos($_SERVER['REQUEST_URI'], 'wp-register') || 
				strpos($_SERVER['REQUEST_URI'], 'wp-signup'))){
				$this->cache_bool = false;
			}

		}else{
			$this->cache_bool = false;
		}	
	}

	//架构函数|销毁数据
	public function __destruct(){
		return true;
	}

	//查询
	public function query($query) {
		return $this->cache_query($query, true);
	}

	//缓存查询
	public function cache_query($query, $bool){
		//数据库是否准备查询
		if(!$this->ready){
			return false;
		}

		//过滤在插件加载之前的查询
		$query = apply_filters('query', $query);

		//初始化的值
		$return_val = 0;
		$this->flush();

		//记录方法调用
		$this->func_call = "\$db->query(\"$query\")";

		//跟踪最后一次查询
		$this->last_query = $query;
		
		//开始计时
		if(defined('SAVEQUERIES') && SAVEQUERIES ){
			$this->timer_start();
		}

		//DbCache start 
		$cache_select = 'Local';//默认缓存方式
		if($this->cache_bool){
			$query_uid = md5($query);
			//对各个不同的查询类型拆分
			if(strpos($query, '_options')){//选项相关
				$this->api_cache->set($cache_select, 'option');
			}elseif(strpos($query, '_links')){//友情连接相关
				$this->api_cache->set($cache_select, 'links');
			}elseif(strpos($query, '_terms')){//条件
				$this->api_cache->set($cache_select, 'terms');
			}elseif(strpos($query, '_users')){//用户相关
				$this->api_cache->set($cache_select, 'users');
			}elseif(strpos($query, '_post')){//文章相关
				$this->api_cache->set($cache_select, 'post');
			}elseif(strpos($query, 'JOIN')){//条件连接相关
				$this->api_cache->set($cache_select, 'joins');
			}else{//其他
				$this->api_cache->set($cache_select, 'other');
			}
		}
		//DbCache end

		//DbCache start 
		$cached = false;//默认读取的数据为false && 是否支持DB插件缓存
		if($this->cache_bool && $this->api_cache->cfg['enabled']){
			$cached = $this->api_cache->read($query_uid);
			//echo '<pre>';var_dump($cached);echo '</pre>';
		}

		//检查是否获取
		if(false != $cached){//保存的数据
			//if(is_string($cached)){
				//var_dump($cached);
			//}
			$decached = $cached;
			//var_dump($decached);
			$this->last_error = '';
			$this->last_query = $decached['last_query'];
			$this->last_result = $decached['last_result'];
			$this->col_info = $decached['col_info'];
			$this->num_rows = $decached['num_rows'];
			//返回的查询的行数
			$return_val = $this->num_rows;

			if (defined('SAVEQUERIES') && SAVEQUERIES ){
				$this->queries[] = array( $query, $this->timer_stop(), $this->get_caller());
			}
			//return $return_val;
		}else{
            //echo $query;
			//资源集
			$this->result = @mysql_query($query, $this->dbh);
			++$this->num_queries;//记录查询次数数据

			if (defined('SAVEQUERIES') && SAVEQUERIES){
				$this->queries[] = array( $query, $this->timer_stop(), $this->get_caller());
			}
		}
		//DbCache start

		//错误显示
		if ( $this->last_error = mysql_error($this->dbh) ) {
			$this->print_error();
			return false;
		}

		//记录值
		if ( preg_match( '/^\s*(insert|delete|update|replace|alter)\s/i', $query ) ) {
			$this->rows_affected = mysql_affected_rows( $this->dbh );
			if ( preg_match( '/^\s*(insert|replace)\s/i', $query ) ) {
				$this->insert_id = mysql_insert_id($this->dbh);//记录插入的ID
			}
			// 返回影响的行数
			$return_val = $this->rows_affected;
		} else {
			//读取表的列信息
			$i = 0;
			while($i<@mysql_num_fields($this->result)){
				$this->col_info[$i] = @mysql_fetch_field($this->result);
				$i++;
			}

			//读取每行信息
			$num_rows = 0;
			while ( $row = @mysql_fetch_object($this->result)){
				$this->last_result[$num_rows] = $row;
				$num_rows++;
			}

			@mysql_free_result($this->result);

			//记录返回行数
			//并返回选择的行数
			$this->num_rows = $num_rows;
			$return_val     = $num_rows;

			//DbCache start && 没有被缓存
			if($this->cache_bool && ($cached === false)){
				//echo '保存值<br>';
				$cache_string = array(
					'last_query'=> $this->last_query,
					'last_result'=> $this->last_result,
					'col_info' => $this->col_info,
					'num_rows'=> $this->num_rows,
				);
				$this->api_cache->write($query_uid, $cache_string);
			}
			//DbCache start
		}
		return $return_val;
	}
	
	//清空缓存数据
	public function wp_db_cache_clean(){
		$this->api_cache->flush();
	}

	//清空过期数据
	public function wp_db_cache_clean_expire(){
		$this->api_cache->flush_expire();
	}
}
?>
