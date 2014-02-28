<?php
class wpwx_menu_hello{

	private $obj = null;

	//构造函数 | init
	public function __construct($obj){
		$this->obj = $obj;
	}

	/** 
	 *	@param $kw 菜单名
	 */
	public function start($kw){
		if('你好' == $kw){
			return $this->obj->toMsgText('菜单插件启动!!,嘿嘿!');
		}
		return false;
	}
	
}


?>
