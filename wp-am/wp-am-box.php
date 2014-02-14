<?php
/**
 *	@文章页组件控制
 */
class wp_am_box{

	public $methods = array(
		'get_root_file',
		'get_child_file',
		'get_parent_file',
		'delete_file',
	);
	public $linkID = null;

	public $option = null;//配置信息

	//构造函数
	public function __construct(){
		//初始化
		$this->init();
	
		//上传信息
		if(!empty($_FILES)  && 
			isset($_FILES['Filedata']) // 过滤其他的上传行为
		){
			//file_put_contents(AM_ROOT.'fi.txt',json_encode($_FILES));
			$this->upload();exit;
		}


		//接受传来的数据
		//有高度(heigth)和宽度(width)
		//是使用的方法
		//和传递的参数
		$post_info = empty($_POST)?'': $_POST;

		//SELECT * FROM `midoks_options` WHERE option_name="wp_am_option"
		$this->option = get_option('wp_am_option');
		$option = $this->option;
		$file_referer = $option['file_referer'];
		$file_referer = explode("\r\n", $file_referer);
		$this->option['file_referer'] = $file_referer;

		

		

		//返回的信息
		//1.文件名
		//2.文件创建时间
		//..文件类型
		//3.文件权限
		//4.文件是否公开访问
		//
		//5.防盗链设置
		//非空
		$method = isset($_POST['method']) ? $_POST['method']: '';
		if(isset($method) && !empty($method)){
			if(in_array($method, $this->methods)){
				$this->$method();
			}else{
				//测试使用
				$this->common();
			}
		}
		//$this->test();
	}

	//初始化
	private function init(){
		//实例化对象
		include(AM_LIBS.'class.manage.php');
		$this->linkID = new class_manage();
	}

	private function test(){
		file_put_contents(AM_ROOT.'post.txt', json_encode(array_merge($_POST,$_FILES)));
	}

	public function common(){
			$common = array('ListNum' =>4);
			$common = array_merge($common,$_POST);
			echo json_encode($common);
	}

	public function get_root_file(){
		$array = $this->linkID->get_root_file();
		$array['config'] = $this->option;
		echo json_encode($array);
	}

	public function get_child_file(){
		$args = $_POST['args'];
		$array = $this->linkID->get_child_file($args);
		$array['config'] = $this->option;

		echo json_encode($array);
		flush();
		if(isset($array['filetype']) && 'file'==$array['filetype'][0]
		    && isset($this->option['local_backup'])
			&& 'true' == $this->option['local_backup']	
			&& 'local' != $this->option['position']){
			include(AM_ROOT.'wp-am-save.php');
			new wp_am_save($this, $array);
		}
		
	}

	public function get_parent_file(){
		$args = $_POST['args'];
		$array = $this->linkID->get_parent_file($args);
		$array['config'] = $this->option;
		echo json_encode($array);
	}

	public function upload(){
		return $this->linkID->upload();
	}

	public function delete_file(){
		$args = $_POST['args'];
		$json = $this->linkID->delete_file($args);
		$json['config'] = $this->option;
		echo json_encode($json);
	}

}
?>
