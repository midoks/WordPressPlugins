<?php
class wpwx_location_ret{

	public $obj;

	public function __construct($obj){
		$this->obj = $obj;
	}

	public function start($args){
		return $this->obj->toMsgText('测试成功!!!(location)');
	}

}
?>
