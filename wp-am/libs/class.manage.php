<?php
/**
 * @func 管理
 */
class class_manage{


	public $linkID ;

	//@func 构造函数
	public function __construct(){
		//选择在管理哪里
		$option = get_option('wp_am_option');
		//echo json_encode($option);exit;
		switch($option['position']){
			case 'local':include(AM_LIBS.'local/FileManage.class.php');break;
			case 'baidu':include(AM_LIBS.'baidubcs/FileManage.class.php');break;
			case 'aliyun':include(AM_LIBS.'aliyun/FileManage.class.php');break;
			case 'qiniu':include(AM_LIBS.'qiniu/FileManage.class.php');break;
			default:include(AM_LIBS.'local/FileManage.class.php');break;
		}
		$this->linkID = new FileManage();
	}

	//调用类的接口
	public function __call($func, $args){
		if(!empty($args)){
			return $this->linkID->$func($args);
		}else{
			return $this->linkID->$func();
		}
	}

}
?>
