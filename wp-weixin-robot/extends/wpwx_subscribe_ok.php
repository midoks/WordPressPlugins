<?php
class wpwx_subscribe_ok{

	public $obj;

	public function __construct($obj){
		$this->obj = $obj;

	}

	public function start($args){
		return $this->obj->toMsgText('(subscribe)订阅！！！');
	}


}

?>
