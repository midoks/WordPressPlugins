<?php
/**
 *	@func 微信事件类
 *	Author: Midoks
 *  Author URI: http://midoks.cachecha.com/
 */
class weixin_robot_event{

	public $cmd;

	//架构函数
	public function __construct($obj){
		$this->cmd = $obj;
	}

	//订阅事件
	public function subscribeEvent(){
		//插件接口调用
		if($wp_plugins = $this->cmd->plugins->dealwith('subscribe', $this->cmd->info)){
			return $wp_plugins;
		}

		$s = $this->cmd->options['subscribe'];
		if(!empty($s)){
			return $this->cmd->toMsgText($s);
		}
		return;
	}

	//取消订阅时间
	public function unsubscribeEvent(){
		return $this->cmd->toMsgText('谢谢你的使用!!!');
	}

	//上报地址事件(服务号开启后,会每隔5分钟回复一次)
	public function LOCATIONEvent(){
		//基本数据
		$info['Latitude'] = $this->cmd->info['Latitude'];
		$info['Longitude'] = $this->cmd->info['Longitude'];
		$info['Precision'] = $this->cmd->info['Precision'];

		//插件接口调用
		if($wp_plugins = $this->cmd->plugins->dealwith('location', $info)){
			return $wp_plugins;
		}
	}

	//用户已关注时的事件推送
	public function scanEvent(){
		if($wp_plugins = $this->cmd->plugins->dealwith('scan', $this->cmd->$info)){
			return $wp_plugins;
		}
	}

	//模版消息返回信息(暂时不处理)
	public function TEMPLATESENDJOBFINISHEvent(){}

	//事件推送群发结果(暂时不处理)
	public function MASSSENDJOBFINISHEvent(){}
}
?>
