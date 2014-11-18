<?php
//锁机制实现类
class wp_weixin_lock{

	public $conf = array(	
		'link' => 'mysql', //memcache, mysql(默认),

	);
	private $linkID = null;

	public function __construct(){	
		switch($this->conf['link']){
			case 'memcache':
				include_once(WEIXIN_ROOT.'lock/wp_weixin_memcache.php');
				$this->linkID = new wp_weixin_memcache_lock();break;
			case 'mysql':
			default:
				include_once(WEIXIN_ROOT.'lock/wp_weixin_mysql.php');
				$this->linkID = new wp_weixin_mysql_lock();
				break;
		}
	}

	public function __call($method, $args){
		return call_user_func_array(array($this->linkID, $method), $args);
	}


}
?>
