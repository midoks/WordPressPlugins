<?php
//@ WP微信机器人|锁实现操作
class weixin_robot_api_lock{

	public $table_lock_table = 'midoks_weixin_robot_lock';


	public function create_lock_table(){
		$sql = "create table if not exists `{$this->table_lock_table}`(
				`id` bigint(20) not null auto_increment comment '自增ID',
				`user_id` varchar(255) not null comment '用户ID',
				`lock_content` text comment '锁定的内容',
				`lock_ex` varchar(255) comment '锁定的插件',
				`expired_time` int(10) not null comment '过期时间',
				primary key(`id`)
			)engine=MyISAM default character set utf8 comment='锁机制实现' collate utf8_general_ci";
		global $wpdb;
		return $wpdb->query($sql);
	}

	public function drop_lock_table(){
		$sql = 'DROP TABLE IF EXISTS '.$this->table_lock_table;
		global $wpdb;
		return $wpdb->query($sql);
	}

	public function select_lock_table($user_id){
		$sql = "select `id`,`user_id`,`lock_content`, `lock_ex`, `expired_time` from `{$this->table_lock_table}` where user_id='{$user_id}'";
		global $wpdb;
		return $wpdb->get_results($sql);
	}

	public function insert_lock_table($user_id, $lock_content, $lock_ex, $expired_time){
		$sql = "INSERT INTO `{$this->table_lock_table}` (`id`, `user_id`, `lock_content`, `lock_ex`, `expired_time`)".
			" VALUES(null,'{$user_id}','{$lock_content}', '{$lock_ex}','{$expired_time}')";
		global $wpdb;
		return $wpdb->query($sql);
	}

	public function update_lock_table($user_id, $lock_content, $expired_time){
		$sql = "UPDATE `{$this->table_lock_table}` SET `lock_content`='{$lock_content}',".
			" `expired_time`='{$expired_time}' WHERE `user_id`='{$user_id}'";
		global $wpdb;
		return $wpdb->query($sql);
	}

	public function delete_lock_table($user_id){
		$sql = 'delete from `'.$this->table_lock_table."` where `user_id`='{$user_id}'";
		global $wpdb;
		return $wpdb->query($sql);
	}

	public function clear_lock_table(){
		$sql = 'truncate '.$this->table_lock_table;
		global $wpdb;
		$wpdb->query($sql);
	}
}
?>
