<?php
//微信数据库操作类
class weixin_robot_api_wordpress_dbs{

	//表名
	public $table_name =  'midoks_weixin_robot';//数据库表名(记录)
	public $table_name_menu = 'midoks_weixin_robot_menu';//自定义菜单(自定义)
	public $table_self_keyword = 'midoks_weixin_robot_replay';//自定义关键字回复(自定义)
	public $table_extends = 'midoks_weixin_robot_extends';//自定义扩展
	
	//数据库实例
	public $linkID;

	//架构函数
	public function __construct(){
		global $wpdb;
		$this->linkID = $wpdb;
	}

	//创建数据表
	public function create_table(){
		$sql = "create table if not exists `{$this->table_name}`(
			`id` bigint(20) not null auto_increment comment '自增ID',
			`from` varchar(64) not null comment '发送方账号',
			`to` varchar(32) not null comment '开发者微信号',
			`msgid` char(64) not null comment '消息ID',
			`msgtype` varchar(10) not null comment '消息类型',
			`createtime` varchar(13) not null comment '消息创建时间',
			`content` varchar(100) not null comment '文本消息内容', 
			`picurl` varchar(100) not null comment '图片消息内容',
			`location_x` double(10,6) not null comment '地理位置x消息内容',
			`location_y` double(10,6) not null comment '地理位置y消息内容',
			`scale` double(10,6) not null comment '地理位置y精度消息内容',
			`label` varchar(255) not null comment '地理位置y附带位置消息内容',
			`title` text not null comment 'link标题',
			`description` longtext not null comment 'link描述',
			`url` varchar(255) not null comment 'link地址',
			`event` varchar(255) not null comment '事件类型',
			`eventkey` varchar(255) not null comment '事件key值',
			`format` varchar(255) not null comment '语音格式',
			`recognition` varchar(255) not null comment '语音识别结果',
			`mediaid` varchar(255) not null comment '媒体文件ID',
			`thumbmediaid` varchar(255) not null comment '媒体缩略图ID',
			`response` varchar(255) NOT NULL comment '响应信息',
		   	`response_time` double(10,6) not null comment '响应时间',
			primary key(`id`)
			)engine=MyISAM default character set utf8 comment='微信机器人插件' collate utf8_general_ci";
		$this->linkID->query($sql);
	}

	//插件入数据
	public function insert($from, $to, $msgid, $msgtype, $createtime, $content, $picurl, $location_x, $location_y,
		$scale, $label, $title, $description, $url, $event,$eventkey,$format, $recognition, $mediaid,$thumbmediaid, $response, $response_time){
		$sql = "INSERT INTO `{$this->table_name}` (`id`, `from`, `to`, `msgid`, `msgtype`, `createtime`, `content`, `picurl`, `location_x`, `location_y`, `scale`, `label`, `title`, `description`, `url`, `event`, `eventkey`, `format`,`recognition`,`mediaid`, `thumbmediaid`, `response`, `response_time`) VALUES(null,'{$from}','{$to}','{$msgid}', '{$msgtype}','{$createtime}', '{$content}','{$picurl}','{$location_x}', '{$location_y}','{$scale}', '{$label}', '{$title}','{$description}', '{$url}', '{$event}','{$eventkey}','{$format}', '{$recognition}', '{$mediaid}','{$thumbmediaid}', '{$response}', '{$response_time}')";
		//echo $sql;
		return $this->linkID->query($sql);
	}

	//删除数据表
	public function delete(){
		$sql = 'DROP TABLE IF EXISTS '.$this->table_name;
		$this->linkID->query($sql);
	}

	//清空数据
	public function clear(){
		$sql = 'truncate '.$this->table_name;
		$this->linkID->query($sql);
	}

	//自定义回复
	public function create_table_relpy(){
		$sql = "create table if not exists `{$this->table_self_keyword}`(
			`id` bigint(20) not null auto_increment comment '自增ID',
			`keyword` varchar(255) not null comment '关键字',
			`relpy` text not null comment '回复信息',
			`status` char(64) not null comment '消息ID',
			`time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' comment '有效期',
			`type` varchar(100) not null default 'text' comment '回复类型',
			primary key(`id`),
			UNIQUE KEY `keyword` (`keyword`)
			)engine=MyISAM default character set utf8 comment='微信机器人关键字自定义回复' collate utf8_general_ci";
		return $this->linkID->query($sql);
	}



	//插入数据
	public function insert_relpy($keyword, $relpy, $status, $time, $type){
		$sql = "INSERT INTO `{$this->table_self_keyword}` (`id`, `keyword`, `relpy`, `status`, `time`, `type`) VALUES(null,'{$keyword}','{$relpy}','{$status}', '{$time}', '{$type}')";
		return $this->linkID->query($sql);
	}

	//删除数据表
	public function delete_relpy(){
		$sql = 'DROP TABLE IF EXISTS '.$this->table_self_keyword;
		return $this->linkID->query($sql);
	}

	//清空数据
	public function clear_relpy(){
		$sql = 'truncate '.$this->table_self_keyword;
		return $this->linkID->query($sql);
	}

	//删除ID
	public function delete_relpy_id($id){
		$sql = 'delete from `'.$this->table_self_keyword."` where `id`='{$id}'";
		return $this->linkID->query($sql);
	}

	//改变status状态
	public function change_relpy_status($id, $status){
		$sql = "UPDATE `{$this->table_self_keyword}` SET `status`='{$status}' WHERE `id`='{$id}'";
		return $this->linkID->query($sql);
	}

	public function change_reply($id, $keyword, $relpy, $type){
		$sql = "UPDATE `{$this->table_self_keyword}` SET `keyword`='{$keyword}',`relpy`='{$relpy}',`type`='{$type}' WHERE `id`='{$id}'";
		return $this->linkID->query($sql);
	}

	//创建菜单同步表
	public function create_table_menu(){
		$sql = "create table if not exists `{$this->table_name_menu}`(
			`id` int(10) not null auto_increment comment '自增ID',
			`menu_name` varchar(255) not null comment '菜单名',
			`menu_type` varchar(100) not null default 'click' comment '回复类型',
			`menu_key` text not null comment '键值',
			`menu_callback` varchar(255) not null comment '回复信息',
			`pid` int(10) not null comment '父级ID',
			primary key(`id`)
			)engine=MyISAM default character set utf8 comment='微信机器人自定义菜单设置' collate utf8_general_ci";
		return $this->linkID->query($sql);
	}

	public function update_menu($id, $name, $type, $value){
		$sql = "update {$this->table_name_menu} set menu_name='{$name}', menu_type='{$type}', menu_callback='{$value}' where id='{$id}'";
		return $this->linkID->query($sql);
	}

	//插入值
	public function insert_menu($menu_name, $menu_type, $menu_key, $menu_callback, $pid){
		$sql = "INSERT INTO `{$this->table_name_menu}` (`id`, `menu_name`, `menu_type`, `menu_key`, `menu_callback`, `pid`)"
			." VALUES(null,'{$menu_name}','{$menu_type}','{$menu_key}', '{$menu_callback}', '{$pid}')";
		return $this->linkID->query($sql);
	}

	public function delete_menu_id($id){
		$sql = 'delete from `'.$this->table_name_menu."` where `id`='{$id}'";
		$this->delete_menu_g_id($id);
		return $this->linkID->query($sql);
	}

	public function delete_menu_g_id($pid){
		$sql = 'delete from `'.$this->table_name_menu."` where `pid`='{$pid}'";
		return $this->linkID->query($sql);
	}

	//删除数据表
	public function delete_menu(){
		$sql = 'DROP TABLE IF EXISTS '.$this->table_name_menu;
		return $this->linkID->query($sql);
	}

	//清空数据
	public function clear_menu(){
		$sql = 'truncate '.$this->table_name_menu;
		return $this->linkID->query($sql);
	}

	public function select_menu_key($key){
		$sql = "select `id`,`menu_name`, `menu_type`, `menu_key`, `menu_callback`, `pid`"
			." from `{$this->table_name_menu}` where `menu_key`='{$key}' limit 1";
		$data  = $this->linkID->get_results($sql);
		if(empty($data)){
			return false;
		}else{
			return $data[0]->menu_name;
		}	
		return false;
	}

	public function create_extends(){
		$sql = "create table if not exists `{$this->table_extends}`(
			`id` int(10) not null auto_increment comment '自增ID',
			`ext_name` varchar(255) not null comment '扩展名',
			`ext_type` varchar(100) not null comment '扩展类型',
			`ext_int` int not null comment '是否启动',
			primary key(`id`),
			UNIQUE KEY `ext_name` (`ext_name`)
			)engine=MyISAM default character set utf8 comment='微信机器人扩展管理' collate utf8_general_ci";
		return $this->linkID->query($sql);
	}

	public function select_extends(){
		$sql = "select `id`,`ext_name`,`ext_type`,`ext_int` from `{$this->table_extends}`";
		$data  = $this->linkID->get_results($sql);
		if($data){
			$ret = array();
			foreach($data as $k=>$v){
				$a['ext_name'] = $v->ext_name;
				$b = explode('.',$v->ext_name);
				$a['ext_cn'] = $b[0];
				$a['ext_type'] = $v->ext_type;
				$ret[] = $a;
			}
			return $ret;
		}
		return false;
	}

	public function select_extends_name($name){
		$sql = "select `id`,`ext_name`,`ext_type`,`ext_int` from `{$this->table_extends}` where ext_name='{$name}'";
		$data = $this->linkID->query($sql);
		return $data;
	}

	public function select_extends_type($type){
		$sql = "select `id`,`ext_name`,`ext_type`,`ext_int` from `{$this->table_extends}` where ext_type='{$type}'";
		$data = $this->linkID->get_results($sql);
		if($data){
			$ret = array();
			foreach($data as $k=>$v){
				$a['ext_name'] = $v->ext_name;
				$a['ext_type'] = $v->ext_type;
				$ret[] = $a;
			}
			return $ret;
		}
		return false;
	}

	//插入值
	public function insert_extends($ext_name, $ext_type, $ext_int){
		$sql = "INSERT INTO `{$this->table_extends}` (`id`, `ext_name`, `ext_type`, `ext_int`)"
			." VALUES(null,'{$ext_name}','{$ext_type}','{$ext_int}')";
		return $this->linkID->query($sql);
	}

	//删除数据表
	public function delete_extends(){
		$sql = 'DROP TABLE IF EXISTS '.$this->table_extends;
		return $this->linkID->query($sql);
	}

	public function delete_extends_name($name){
		$sql = 'delete from `'.$this->table_extends."` where `ext_name`='{$name}'";
		return $this->linkID->query($sql);
	}

	public function clear_extends(){
		$sql = 'truncate '.$this->table_extends;
		return $this->linkID->query($sql);
	}

//////////////////////////////

	//获取所有数据
	public function weixin_get_count(){
		$sql = 'select count(id) as count from `'.$this->table_name.'`';
		$data = $this->linkID->get_results($sql);
		return $data[0]->count;
	}

	//获取个类型的总数据
	public function weixin_get_msgtype_count($text = 'text'){
		$sql = 'select count(`id`) as count from `'.$this->table_name."` where `msgtype`='{$text}'";
		$data = $this->linkID->get_results($sql);
		return $data[0]->count;
	}

	//微信数据获取
	//@param uint $page_no 第几页数据
	//@param uint $num 每页显示的数据
	public function weixin_get_data($page_no = 1, $num = 20){
		if($page_no < 1){
			$page_no = 1;
		}
		$start = ($page_no-1)*$num;
		$sql = "select `id`,`from`,`to`,`msgtype`,`createtime`,`content`,`picurl`,`location_x`,`location_y`, `scale`, `label`, `title`,"
			."`description`,`url`,`event`, `eventkey`,`format`,`recognition`,`mediaid`,`thumbmediaid`,`response`, `response_time`"
			." from `{$this->table_name}` order by `id` desc limit {$start},{$num}";
		$data  = @$this->linkID->get_results($sql);
		$newData = array();
		foreach($data as $k=>$v){
			$arr = array();
			$arr['id'] = $v->id;
			$arr['from'] = $v->from;
			$arr['to'] = $v->to;
			$arr['msgtype'] = $v->msgtype;

			//暂时显示文本消息
			switch($v->msgtype){
				case 'text':$arr['content'] = $v->content;break;
				default:$arr['content'] = $v->content;
			}

			//菜单点击事件
			if('CLICK' == $v->event){
				$data = $this->select_menu_key($v->eventkey);
				if($data){
					$arr['content'] = '菜单:'.$data;
				}else{
					$arr['content'] = '菜单:已经不存在';
				}
			}else if('subscribe' == $v->event){//订阅事件
				$arr['content'] = '订阅事件';
			}else if('unsubscribe' == $v->event){//取消订阅事件
				$arr['content'] = '取消订阅事件';
			}else if('LOCATION' == $v->event){
				$arr['content'] = '地理位置上报告事件';
			}else if('location' == $v->msgtype){
				$arr['content'] = '地理位置上报告事件';
			}else if('voice' == $v->msgtype){
				$arr['content'] = '语音事件';
			}

			$arr['createtime'] = date('Y-m-d H:i:s', $v->createtime);
			$arr['response'] = $v->response;
			$arr['response_time'] = $v->response_time;
			$newData[] = $arr;
		}
		return $newData;
	}

	//返回消息(用户推送的信息)
	public function weixin_get_data_chat($openid, $time){
		//现在到3秒前的数据
		$s_time = $time-3;
		$sql = "select `id`,`from`,`to`,`msgtype`,`createtime`,`content`,`picurl`,`location_x`,`location_y`, `scale`, `label`, `title`,"
			."`description`,`url`,`event`, `eventkey`,`format`,`recognition`,`mediaid`,`thumbmediaid`,`response`, `response_time`"
			." from `{$this->table_name}` where `from`='{$openid}' and (`createtime` >= '{$s_time}' and `createtime` <= '{$time}')  order by `id` desc";
		//echo $sql;
		$data  = @$this->linkID->get_results($sql);
		$newData = array();
		foreach($data as $k=>$v){
			$arr = array();
			$arr['id'] = $v->id;
			$arr['from'] = $v->from;
			$arr['to'] = $v->to;
			$arr['msgtype'] = $v->msgtype;

			//暂时显示文本消息
			switch($v->msgtype){
				case 'text':$arr['content'] = $v->content;break;
				default:$arr['content'] = $v->content;
			}

			//菜单点击事件
			if('CLICK' == $v->event){
				$data = $this->select_menu_key($v->eventkey);
				if($data){
					$arr['content'] = '菜单:'.$data;
				}else{
					$arr['content'] = '菜单:已经不存在';
				}
			}else if('subscribe' == $v->event){//订阅事件
				$arr['content'] = '订阅事件';
			}else if('unsubscribe' == $v->event){//取消订阅事件
				$arr['content'] = '取消订阅事件';
			}else if('LOCATION' == $v->event){
				$arr['content'] = '地理位置上报告事件';
			}else if('location' == $v->msgtype){
				$arr['content'] = '地理位置上报告事件';
			}else if('voice' == $v->msgtype){
				$arr['content'] = '语音事件';
			}

			$arr['createtime'] = date('Y-m-d H:i:s', $v->createtime);
			$arr['response'] = $v->response;
			$arr['response_time'] = $v->response_time;

			//test
			$arr['n_time'] = intval($v->createtime);
			$arr['r_time'] = $time;
			$newData[] = $arr;
		}
		return $newData;
	}

	//查询回复语句
	public function weixin_get_relpy_data($kw=''){
		if(!empty($kw)){
			$sql = "select `id`,`keyword`,`relpy`,`status`,`time`,`type`"
				." from `{$this->table_self_keyword}` where `keyword` like '%{$kw}%' order by `id` desc";
		}else{
			$sql = "select `id`,`keyword`,`relpy`,`status`,`time`,`type`"
				." from `{$this->table_self_keyword}` order by `id` desc";
		}
		$data  = $this->linkID->get_results($sql);
		if(empty($data)){
			return false;
		}else{
			$arrs = array();
			foreach($data as $k=>$v){
				$arr['id'] = $v->id;
				$arr['keyword'] = $v->keyword;
				$arr['relpy'] = $v->relpy;
				$arr['status'] = $v->status;
				$arr['time'] = $v->time;
				$arr['type'] = $v->type;
				$arrs[] = $arr;
			}
			return $arrs;
		}		
	}

	//查询回复语句
	public function weixin_get_menu_data(){
		$sql = "select `id`,`menu_name`,`menu_type`,`menu_key`, `menu_callback`, `pid`"
			." from `{$this->table_name_menu}` order by `id` desc";
		$data  = $this->linkID->get_results($sql);
		if(empty($data)){
			return false;
		}else{
			$arrs = array();
			foreach($data as $k=>$v){
				$arr['id'] = $v->id;
				$arr['menu_name'] = $v->menu_name;
				$arr['menu_type'] = $v->menu_type;
				$arr['menu_key'] = $v->menu_key;
				$arr['menu_callback'] = $v->menu_callback;
				$arr['pid'] = $v->pid;
				$arrs[] = $arr;
			}
			return $arrs;
		}		
	}

	//获取一级菜单列表
	public function weixin_get_menu_p_data(){
		$sql = "select `id`,`menu_name`, `menu_type`, `menu_key`, `menu_callback`, `pid`"
			." from `{$this->table_name_menu}` where `pid`='0'";
		$data  = $this->linkID->get_results($sql);
		if(empty($data)){
			return false;
		}else{
			$arrs = array();
			foreach($data as $k=>$v){
				$arr['id'] = $v->id;
				$arr['menu_name'] = $v->menu_name;
				$arr['menu_type'] = $v->menu_type;
				$arr['menu_key'] = $v->menu_key;
				$arr['menu_callback'] = $v->menu_callback;
				$arr['pid'] = $v->pid;
				$arrs[] = $arr;
			}
			return $arrs;
		}		
	}

	//获取一级菜单下的列表
	public function weixin_get_menu_p_data_id($id){
		$sql = "select `id`,`menu_name`, `menu_type`, `menu_key`, `menu_callback`, `pid`"
			." from `{$this->table_name_menu}` where `pid`='{$id}'";
		$data  = $this->linkID->get_results($sql);
		if(empty($data)){
			return false;
		}else{
			$arrs = array();
			foreach($data as $k=>$v){
				$arr['id'] = $v->id;
				$arr['menu_name'] = $v->menu_name;
				$arr['menu_type'] = $v->menu_type;
				$arr['menu_key'] = $v->menu_key;
				$arr['menu_callback'] = $v->menu_callback;
				$arr['pid'] = $v->pid;
				$arrs[] = $arr;
			}
			return $arrs;
		}		
	}

	//获取一级菜单总数
	public function weixin_get_menu_p_count(){
		$sql = 'select count(id) as count from `'.$this->table_name_menu."` where `pid`='0'";
		$data = $this->linkID->get_results($sql);
		return $data[0]->count;
	}

	//获取二级菜单总数
	public function weixin_get_menu_c_count($id){
		$sql = 'select count(id) as count from `'.$this->table_name_menu."` where `pid`='{$id}'";
		$data = $this->linkID->get_results($sql);
		return $data[0]->count;
	}
}
?>
