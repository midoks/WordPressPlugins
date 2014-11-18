<?php
//@ WP微信机器人|锁机制实现
//author: midoks
//email: midoks@163.com
class wp_weixin_memcache_lock{

	public $linkID = null;
	public $obj = null;

	public $save_time = null;
	public $expired_time = null;


	public $prefix = 'mwpwx_';

	public $conf = array(
		'local' => 'localhost',
		'port' => 11211,
	);

	public function __construct(){
		$this->save_time = 30*60;
		$this->expired_time = time() + $this->save_time;
		$this->init_connect();
	}

	public function set_obj($obj){
		$this->obj = $obj;
	}

	private function _pfx($name){
		return $this->prefix.$name;
	}

	public function init_connect(){
		if(!class_exists('Memcache')){
			exit('你没有安装Memcache扩展!!!,那就不要使用此模式!!!');
		}
		$this->linkID = new Memcache();
		$this->linkID->connect($this->conf['local'], $this->conf['port']);
	}

	public function check_lock(){
		$info = $this->obj->info;
		$user_id = $info['FromUserName'];
		$data = $this->linkID->get($this->_pfx($user_id));	
		if($data){
			return $data;
		}
		return false;
	}

	public function lock_content($file, $content=''){
		if(empty($content)){$content = 'midoks';}
		$info = $this->obj->info;
		$user_id = $info['FromUserName'];
		$data = $this->linkID->get($this->_pfx($user_id), MEMCACHE_COMPRESSED);
		if(!$data){
			$insert_content[] = $content;
			$data['lock_content'] = $insert_content;
			$data['lock_ex'] = $file;
			$data['expired_time'] = $this->expired_time;
			$b = $this->linkID->set($this->_pfx($user_id), $data,
				MEMCACHE_COMPRESSED, $this->save_time);
			if($b) return $b;
			else return false;
		}else{
			return true;
		}
	}


	public function add_lock_content($content){
		if(is_null($content)) return false;
		$info = $this->obj->info;
		$user_id = $info['FromUserName'];
		$data = $this->linkID->get($this->_pfx($user_id));
		if(empty($data)){
			return false;
		}else{
			array_push($data['lock_content'], $content);
			$data['lock_content'] = $data['lock_content'];
			$data['expired_time'] = $this->expired_time;

			$b = $this->linkID->replace($this->_pfx($user_id), $data, 
				MEMCACHE_COMPRESSED, $this->save_time);
			if($b) return $b;
			else return false;
		}	
	}

	public function delete_lock(){
		$info = $this->obj->info;
		$user_id = $info['FromUserName'];
		return $this->linkID->delete($this->_pfx($user_id));
	}

	public function exit_lock(){
		return $this->delete_lock();
	}


	private function cache_get_lock_info(){
		if($this->cache_get_lock_info){
			return $this->cache_get_lock_info;
		}else{
			$info = $this->obj->info;
			$user_id = $info['FromUserName'];
			$data = $this->linkID->get($this->_pfx($user_id));
			$ndata = $data['lock_content'];
			$this->cache_get_lock_info = $ndata;
			return $ndata;
		}
	}

	//获取当前的位置
	public function get_lock_pos($pos = ''){
		$data = $this->cache_get_lock_info();
		if(is_int($pos)){
			$pdata = $data[$pos];
			if($pdata) return $pdata;
			else return false;
		}
		return count($data);
	}

	public function get_lock_current_data(){
		return $this->get_lock_pos($this->get_lock_pos()-1);
	}

	public function get_lock_data(){
		$data = $this->cache_get_lock_info();
		if($data){return $data;}else{return false;}
	}
}
?>
