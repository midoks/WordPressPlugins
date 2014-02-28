<?php
class wpwx_text_wxwt{

	private $obj = null;

	//构造函数 | init
	public function __construct($obj){
		$this->obj = $obj;
	}

	//开始执行
	//**
	//	如果你是文本处理 $kw 是一个用户关键
	//	如果你是不使用文本 $kw 发送过来的所有信息 (数据) 数组
	///
	public function start($kw){
		if('字数测试' == $kw){
			$str = str_repeat('.', 2049);//经过我测试,微信字数现在2k(包括2k)
			return $this->obj->toMsgText($str);
		}
		return false;
	}

	//启动时,运行
	public function install(){
		
	}

	//卸载时运行
	public function uninstall(){
		
	}

	
}


?>
