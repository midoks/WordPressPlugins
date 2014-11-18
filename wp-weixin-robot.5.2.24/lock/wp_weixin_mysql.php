<?php
//@ WP微信机器人|锁机制实现
//author: midoks
//email: midoks@163.com
class wp_weixin_mysql_lock{

	public $linkID = null;
	public $obj = null;

	public $expired_time = null;
	public $user_id = null;



	public function __construct(){
		include_once(WEIXIN_ROOT_API.'weixin_robot_api_lock.php');
		$this->linkID = new weixin_robot_api_lock();
		$this->expired_time = time() + 30*60;
	}
	public function set_obj($obj){
		$this->obj = $obj;
	}


	//检查是否锁定
	public function check_lock(){
		$info = $this->obj->info;
		$user_id = $info['FromUserName'];
		$data = $this->linkID->select_lock_table($user_id);
		if(empty($data)){
			return false;
		}else{
			 $info = (array)$data[0];	
			if($info['expired_time']<=time()){
				$this->delete_lock();
				return false;
			}
			return $info;		
		}
	}

	/**
	 * @func 锁定内容
	 * @param $content 锁定内容
	 * @ret bool 锁定是否ok
	 */
	public function lock_content($file, $content=''){
		if(empty($content)){$content = 'midoks';}
		$info = $this->obj->info;
		$user_id = $info['FromUserName'];
		$data = $this->linkID->select_lock_table($this->user_id);
		$insert_content = array();
		if(empty($data)){
			$insert_content[] = $content;
			return $this->linkID->insert_lock_table($user_id, $this->to_json($insert_content),
				$file, $this->expired_time);
		}else{
			return true;
		}
	}

	//添加锁定内容
	public function add_lock_content($content){
		if(is_null($content)) return false;
		$info = $this->obj->info;
		$user_id = $info['FromUserName'];
		$data = $this->linkID->select_lock_table($user_id);
		if(empty($data)){
			return false;
		}else{
			$d = json_decode($data[0]->lock_content);
			$p = array_push($d, $content);
			$data = $this->linkID->update_lock_table($user_id, $this->to_json($d), $this->expired_time);
			if($data) return $data;
			else return false;
		}
	}

	public function delete_lock(){
		$info = $this->obj->info;
		$user_id = $info['FromUserName'];
		return $this->linkID->delete_lock_table($user_id);
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
			$data = $this->linkID->select_lock_table($user_id);
			$data = json_decode($data[0]->lock_content);
			$this->cache_get_lock_info = $data;
			return $data;
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

	public function to_json($array){
        $this->arrayRecursive($array, 'urlencode', true);
        $json = json_encode($array);
        return urldecode($json);
	}

    public function arrayRecursive(&$array, $function, $apply_to_keys_also = false){
        static $recursive_counter = 0;
        if (++$recursive_counter > 1000) {
            die('possible deep recursion attack');
        }
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $this->arrayRecursive($array[$key], $function, $apply_to_keys_also);
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
}
?>
