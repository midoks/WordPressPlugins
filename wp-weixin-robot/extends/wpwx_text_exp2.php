<?php
/**
 * 图文实例
 */
class wpwx_text_exp2{

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
		if('2' == $kw){
			$textPic = array(
					array(
						'title'=> '标题',
						'desc'=> '描述',
						'pic'=> "http://www.baidu.com./1.jpg",//图片地址
						'link'=>$pic,//图片链接地址
					),//第一个图片为大图
					array(
						'title'=> '标题',
						'desc'=> '描述',
						'pic'=> "http://www.baidu.com./1.jpg",//图片地址
						'link'=> '',//图片链接地址
					),//此自以后皆为小图
					array(
						'title'=> '标题',
						'desc' => '描述',
						'pic'=> "http://www.baidu.com./1.jpg",//图片地址
						'link' => '',//图片链接地址
					)
			);
			return $this->obj->toMsgTextPic($textPic);
		}
		return false;
	}

	
}


?>
