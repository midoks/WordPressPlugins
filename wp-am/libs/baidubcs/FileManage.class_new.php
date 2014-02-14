<?php
/**
 *	@func 百度云文件管理
 */

//载入SDK
include(AM_LIBS.'baidubcs/baidu_sdk/bcs.class.php');

class FileManage{

	private $bcs = null;
	private $path;//上传的路径
	private $option = null;
	private $object_referer = array();
	private $file_referer = array();


	//架构函数|初始化
	public function __construct(){
		$this->init_config();
		$this->bcs = new BaiduBCS(BCS_AK, BCS_SK, BCS_HOST);
		$this->ready();
	}

	public function init_config(){
		$this->option = get_option('wp_am_option');
		$option = $this->option;
		$file_referer = $option['file_referer'];
		$file_referer = explode("\r\n", $file_referer);
		$this->option['file_referer'] = $file_referer;

		//请求的主机
		define('BCS_HOST', 'bcs.duapp.com');
		//AK公钥
		define('BCS_AK', $option['ak']);
		//SK私钥
		define('BCS_SK', $option['sk']);
		//superfile 每个object分片后缀
		define ( 'BCS_SUPERFILE_POSTFIX', '_bcs_superfile_');
		//sdk superfile分片大小 ，单位 B（字节）
		define ( 'BCS_SUPERFILE_SLICE_SIZE', 1024 * 1024);

		//BUCKET名字
		define ('BCS_BUCKET', $option['bucket_name']);
		//文件前缀信息
		define('FILE_PREFIX', $option['file_prefix']);//文件
		//文件默认权限
		define('OBJECT_DEFAULT_ACL' , 'public-read');

		//file_put_contents(AM_ROOT.'object_reffer.txt', json_encode($file_referer));

	
		define('OBJECT_DEFAULT_REFFER', '*');
	}

	//获取根目录列表
	private function get_bucket_list(){
		$list = array();
		$info = @$this->bcs->list_bucket();
		$arr = json_decode($info->body, true);
		foreach($arr as $k=>$v){
			$list[] = $v['bucket_name'];
		}
		return $list;
	}

	//创建Bucket
	private function create_bucket($bucket){
		$response = @$this->bcs->create_bucket($bucket, OBJECT_DEFAULT_ACL);
		$this->set_bucket_acl();
		if($response->isOK()){
			return true;
		}else{
			return false;
		}
	}

	//检测路径是否存在,不存在就创建
	//否则$this->path = false;
	private function ready(){
		if(!in_array(BCS_BUCKET, $this->get_bucket_list())){
			if($this->create_bucket(BCS_BUCKET)){
				//$this->bcs->create_object(BCS_BUCKET,'/w/');
				$this->path = 'http://'.BCS_HOST.'/'.BCS_BUCKET;
			}else{
				$this->path = false;
			}
		}

		$this->path = 'http://'.BCS_HOST.'/'.BCS_BUCKET;
		$this->header = array(
			//'expires' =>gmdate('D, d M Y H:i:s',time()+(OBJECT_DEFAULT_TIME*365*24*60*60)).' GMT',
			'expires' => 'Tue, 19 Jan 9999 03:14:07 GMT',//32位表达的最长时间
		);
		//$this->set_bucket_acl();
	}

	private function set_bucket_acl(){
		$referer = $this->option['file_referer'];
		if(empty($referer)){
			$referer = array('*');
		}

		$acl = array (
			'statements' => array(
				'0' => array (
					'user' 		=> array('*'),
					'resource' 	=> array (BCS_BUCKET.'/'),
					'action' 	=> array (BaiduBCS::BCS_SDK_ACL_ACTION_GET_OBJECT),
					'effect' 	=> BaiduBCS::BCS_SDK_ACL_EFFECT_ALLOW,
					'referer'	=> $referer,
		)));
		$response = $this->bcs->set_bucket_acl(BCS_BUCKET, $acl);
		//file_put_contents(AM_ROOT.'tt_bucket_acl.txt', json_encode($response));
	}

	//获取列表实例1
	private function get_root_file_list_exp(){
		$list = array();
		$info = @$this->bcs->list_bucket();
		$arr = json_decode($info->body, true);
		foreach($arr as $k=>$v){
			$list[] = $v['bucket_name'];
		}	
		return $list;
	}

	private function get_reffer($info){
		$infoq = $info['statements'];
		//file_put_contents(AM_ROOT.'info.txt', json_encode($info));
		foreach($infoq as $k=>$v){
			if(isset($v['referer'])){
				$referer = $v['referer'];
				if($referer != $this->object_referer){
					$this->set_bucket_acl();
					$path = $info['statements'][0]['resource'][0];//动态的改变权限
					$this->set_default_reffer(str_replace(BCS_BUCKET,'',$path));//动态的改变权限
				}
				//file_put_contents(AM_ROOT.'reffer.txt', json_encode($referer));
				return implode('|',$referer);
			}
				return "没有获取权限({)";
		}
	}

	//防盗链(单个文件)
	private function set_default_reffer($object){
		if(empty($object) || is_null($object)){
			return false;
		}

		$referer = $this->option['file_referer'];

		if(empty($referer)){
			$referer = array('*');
		}

		$acl = array (
			'statements' => array (
				'0' => array (
					'user' 		=> array('*'),
					'resource' 	=> array (BCS_BUCKET.$object),
					'action' 	=> array (BaiduBCS::BCS_SDK_ACL_ACTION_GET_OBJECT),
					'effect' 	=> BaiduBCS::BCS_SDK_ACL_EFFECT_ALLOW,
					'referer'	=> $referer,
		)));
		//$info = $this->bcs->set_object_acl(BCS_BUCKET, $object, $acl);
		return false;
		if($info->isOK()){
			return true;
		}else{
			return false;
		}
	}


	//处理数据
	private function cl_root_and_child($info){
		$info = json_decode($info->body, true);
		if(isset($info['Error'])){return false;}
		$info = $info['object_list'];
		
		//信息处理
		$list = array();
		foreach($info as $k=>$v){
			$list['fn'][] = trim(str_replace($info[$k]['parent_dir'], '', $info[$k]['object']), '/');
			$list['position_local'][] = $info[$k]['object'];
			$list['position'][] = 'http://'.BCS_HOST.'/'.BCS_BUCKET.$info[$k]['object'];
			$list['uptime'][] = date('Y-m-d H:i:s',$info[$k]['mdatetime']);//创建时间

			$getInfo = $this->bcs->get_object_acl(BCS_BUCKET, $info[$k]['object']);
			$getInfo = json_decode($getInfo->body, true);
			if(isset($getInfo['Error'])){
				$list['filetype'][] = 'dir';//文件类型
				$list['wrx'][] = '0666';//文件权限
				$list['reffer'][] = 'all';//本地文件防盗链,无法修改
			}else{
				$list['filetype'][] = 'file';//文件类型
				$list['wrx'][] = '0666';//文件权限
				$list['reffer'][] = $this->get_reffer($getInfo);//根据相应的情况
			}
		
		}	
		return $list;
	}

	private function get_root_file_list_exp2(){
		//信息获取
		//$dir = '/';//.FILE_PREFIX.'/';
		if(FILE_PREFIX!=''){
			$dir = '/'.FILE_PREFIX.'/';
		}else{
			$dir = '/';
		}
		//file_put_contents(AM_ROOT.'info.txt', $dir);
		$info = $this->bcs->list_object_by_dir(BCS_BUCKET, $dir, 2);
		//信息处理
		$list = $this->cl_root_and_child($info);
		return $list;
	}

	//获取根目录列表
	public function get_root_file(){
		$info = $this->get_root_file_list_exp2();
		return $info;
	}


	private function get_child_file_list_exp($args){
		//信息获取
		$position_local = $args['position_local'];
		$info = $this->bcs->list_object_by_dir(BCS_BUCKET, $position_local, 2);
		//信息处理
		$list = $this->cl_root_and_child($info);
		return $list;
	}


	//获取子目录和文件
	public function get_child_file($args){
		$data = $this->get_child_file_list_exp($args[0]);
		return $data;
	}

	//获取父级功能
	public function get_parent_file($args){
		$arg = $args[0];
		$pdir = dirname(dirname($arg['position_local']));
		if($pdir=='\\'){return array('err'=>'1');}else{	
			$pdir .= '/';
		}
		$arg['position_local'] = $pdir;
		return $arg;
	}

	//删除文件 | 不可删除非空目录
	public function delete_file($args){
		$arg = $args[0];
		$pdir = dirname($arg['position_local']);
		if($arg['filetype']=='file'){
			$info = $this->bcs->delete_object(BCS_BUCKET, $arg['position_local']);
			if($info->status==200){
				return array('rmfile' => 1, 'refresh'=>($pdir == '\\' ? '/' : $pdir.'/'));
			}else{return array('rmdir' => 0);}
		}else{}
		return $arg;
	}

	//强制删除目录
	public function delete_file_fuck(){
	
	}


	//取得文件后缀名
	public function get_file_suffix($fn){
		$arr = explode('.', $fn);
		$type = array_pop($arr);
		$name = array_pop($arr);
		return array('name'=>$name, 'type'=>$type);
	}

	//设置文件名
	private function set_fn_name($fn){
		$name = '';
		$opt = $this->option;
		$method = $opt['file_type'];
		switch($method){
			case '0': $name = date('Y_m_d_H_i_s', time());break;
			case '1': $name = date('Y_m_d_H_i_s', time()).'_'.time();break;
			case '2': $name = $fn;break;
			default:  $name = date('Y_m_d_H_i_s', time());break;
		}
		return $name;
	}

	//上传
	public function upload(){
		if(!empty($_FILES)){
			$tmp = $_FILES['Filedata']['tmp_name'];//临时文件地址
			$fn = $_FILES['Filedata']['name'];
			$file = $this->get_file_suffix($fn);
			$tmpContent = file_get_contents($tmp);
			
			$year = date('Y');//年份
			$month = date('m');//月份
			if(FILE_PREFIX!=''){
				$file_path = FILE_PREFIX.'/'.$year.'/'.$month.'/';
			}else{
				$file_path = $year.'/'.$month.'/';
			}
			$file_path = '/'.$file_path.$this->set_fn_name($file['name']).'.'.$file['type'];
			$info = $this->bcs->create_object_by_content(BCS_BUCKET, $file_path, $tmpContent, array(
				'acl'=>'public-read',
				'headers' => array(
					'expires'=>$this->header['expires'],
					'Content-Type'=>BCS_MimeTypes::get_mimetype($file['type']),
				)));
			
			//设置反盗链
			//$this->set_default_reffer($file_path);
			$this->set_bucket_acl();
			//$response = $this->bcs->get_bucket_acl(BCS_BUCKET);
			///file_put_contents(AM_ROOT.'tt_bucket_acl.txt', json_encode($response));
			if($info->isOK()){echo  '1';}else{echo 'Invalid file type.';}
		}else{echo '0';}
		return true;
	}

	//本目录上传
	public function local_upload(){
	}	

	//清除所有的文件
	private function _clear(){
		$this->_clear_dir();
	}

	//所有目录
	private function _for_dir($path='/'){
		$list_dir = array();
		$response = $this->bcs->list_object_by_dir(BCS_BUCKET, $path, 1);
		$arr = json_decode($response->body, true);
		$dir = $arr['object_list'];
		if(!$dir) return false;//目录中没有内容
		foreach($dir as $k=>$v){
			if($v['is_dir']){
				$list_dir[] = $v['object'];
				$dir_x = $this->_for_dir($v['object']);
				if($dir_x)
					$list_dir = array_merge($list_dir, $dir_x);
			}
		}
		//返回所有目录的数组
		return $list_dir;
	}

	//所有目录
	private function _clear_dir(){
		$dir_list = $this->_for_dir();
		echo '<pre>';
		//var_dump($dir_list);
		//var_dump($this->bcs->list_object(BCS_BUCKET));
		//exit;
		echo '<pre>';
		$response = $this->bcs->list_object_by_dir(BCS_BUCKET, '/', 2);
		$arr = json_decode($response->body, true);
		var_dump($arr);
		$dir_list = $arr['object_list'];
		//exit;
		if($dir_list){
			foreach($dir_list as $k=>$v){
				var_dump($v['object']);
				$info = $this->bcs->delete_object(BCS_BUCKET, $v['object']);
				var_dump($info);
			}
		}
		//var_dump($dir_list);
		exit;
	}

	private function _clear_object(){
		$bool = $this->bucket_exists(BCS_BUCKET);
		if($bool){
			$response = $this->bcs->list_object(BCS_BUCKET);
			$arr = json_decode($response->body, true);
			foreach($arr['object_list'] as $k=>$v){
				$info = $this->bcs->delete_object(BCS_BUCKET, $v['object']);
			}
		}
		return $bool;
	}
//end
}
?>
