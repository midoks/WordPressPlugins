<?php
/**
 *	@func 百度云文件管理
 */

//载入配置文件
include AM_LIBS.'aliyun/config.php';
include(AM_LIBS.'aliyun/aliyun_sdk/sdk.class.php');


class FileManage{


	private $oss = null;
	private $path;//上传的路径
	private $option;

	//架构函数|初始化
	public function __construct(){
		$this->oss = new ALIOSS();
		$this->ready();
		$this->option = get_option('wp_am_option');
	}

	private function ready(){
		//设置是否打开curl调试模式
		$this->oss->set_debug_mode(FALSE);

		if(!in_array(BUCKET_NAME, $this->list_bucket())){
			$this->create_bucket(BUCKET_NAME);
		}
		
	}


	//XML解析
	private function xmlparser($xml){
		$xml = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
		return (Array)$xml;
	}

	//获取bucket列表
	private function list_bucket(){
		//信息获取
		$response = $this->oss->list_bucket();
		$xml = $this->xmlparser($response->body);
		$xml = (Array)$xml['Buckets'];
		$xml = (Array)$xml['Bucket'];
		//信息处理
		$list = array();
		foreach($xml as $k=>$v){
			$list[] = (String)$v->Name;
		}
		return $list;
	}

	//创建bucket
	private function create_bucket($name){
		$acl = ALIOSS::OSS_ACL_TYPE_PUBLIC_READ;
		$this->oss->create_bucket($name,$acl);
	}


	//获取父级功能
	public function get_parent_file($args){
		$arg = $args[0];
		$pdir = dirname(dirname($arg['position_local']));
		if('.'==$pdir){$pdir='';}else{$pdir .= '/';}
		$arg['position_local'] = $pdir;
		return $arg;
	}

	//对要处理的数据简化
	private function cl_root_and_child_list_foreach(){}

	//处理
	private function cl_root_and_child_list($xmls, $pdir=''){
		$list = array();

		//目录
		$dirs = (Array)$xmls['CommonPrefixes'];
		if(isset($dirs[0])){
			foreach($dirs as $k=>$v){
				$fn = (String)$v->Prefix;
				if(''== trim($fn, '/')){continue;}
				if(''== trim(str_replace($pdir, '', $fn), '/')){continue;}//除去上级目录
				$list['fn'][] = trim($fn, '/');
				$list['position'][] = 'http://'.BUCKET_NAME.'.oss.aliyuncs.com/'.$fn;
				$list['position_local'][] = $fn;
				$list['filetype'][] = 'dir';
				$list['wrx'][] = '0666';
				$list['uptime'][]= '1234';
				$list['reffer'][] = 'all';
			}
		}else if(isset($dirs['Prefix'])){
			$fn = $dirs['Prefix'];
			$list['fn'][] = trim(str_replace($pdir,'',$fn),'/');
			$list['position'][] = 'http://'.BUCKET_NAME.'.oss.aliyuncs.com/'.$fn;
			$list['position_local'][] =  $fn;
			$list['filetype'][] = 'dir';
			$list['wrx'][] = '0666';
			$list['uptime'][]= '1234';
			$list['reffer'][] = 'all';
		}

		//对象
		$xml = (Array)$xmls['Contents'];
		if(isset($xml[0])){
			foreach($xml as $k=>$v){
				$fn = (String)$v->Key;
				$fnn = trim(str_replace($pdir, '', $fn),'/');
				if(''== trim($fn, '/')){continue;}
				if(''== $fnn){continue;}

				$list['fn'][] = $fnn;
				$list['position'][] = 'http://'.BUCKET_NAME.'.oss.aliyuncs.com/'.$fn;
				$list['position_local'][] = $fn;
				$list['uptime'][] = date('Y-m-d H:i:s', strtotime($v->LastModified));
				$list['filetype'][] = 'file';
				$list['reffer'][] = 'all';
				$list['wrx'][] = '0666';
			}
		}else if(isset($xml['Key'])){
			$fn = $xml['Key'];
			$list['fn'][] = trim(str_replace($pdir, '', $fn),'/');
			$list['position'][] = 'http://'.BUCKET_NAME.'.oss.aliyuncs.com/'.$fn;
			$list['position_local'][] = $fn;
			$list['uptime'][] = date('Y-m-d H:i:s', strtotime($xml['LastModified']));
			$list['filetype'][] = 'file';
			$list['reffer'][] = 'all';
			$list['wrx'][] = '0666';
		}
		//var_dump($list);
		return $list;
	}

	private function get_root_file_list_exp(){
		if('' == FILE_PREFIX){
			$rootDir = '';
		}else{
			$rootDir = FILE_PREFIX.'/';
		}

		$opt = array('prefix' => $rootDir );

		$response = $this->oss->list_object(BUCKET_NAME, $opt);
		$xml = $this->xmlparser($response->body);

		if(''==$opt['prefix']){
			$list = $this->cl_root_and_child_list($xml);
		}else{
			$list = $this->cl_root_and_child_list($xml, $opt['prefix']);
		}

		
		return $list;
	}

	//根目录
	public function get_root_file(){
		$list = $this->get_root_file_list_exp();
		return $list;
	}


	//获取子目录信息
	private function get_child_file_list_exp($dir){
		$opt = array('prefix' => $dir['position_local'],);
		//var_dump($opt);
		$response = $this->oss->list_object(BUCKET_NAME, $opt);
		$xml = $this->xmlparser($response->body);
		//var_dump($xml);
		$list = $this->cl_root_and_child_list($xml, $dir['position_local']);
		return $list;
	}

	//子目录
	public function get_child_file($args){
		$dir = $args[0];
		$list = $this->get_child_file_list_exp($dir);
		return $list;	
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
			$fn = $_FILES['Filedata']['name'];//文件名
			$file = $this->get_file_suffix($fn);
			$content = file_get_contents($tmp);

			$year = date('Y');//年份
			$month = date('m');//月份
			if(FILE_PREFIX!=''){
				$file_path = FILE_PREFIX.'/'.$year.'/'.$month.'/';
			}else{
				$file_path = $year.'/'.$month.'/';
			}
			$file_path = $file_path.$this->set_fn_name($file['name']).'.'.$file['type'];

			//选项
			$option = array(
				'content' => $content,
				'length' => strlen($content),
					ALIOSS::OSS_HEADERS => array(
					'Expires' => '9999-12-29 12:00:00',
				),
			);
			$info = $this->oss->upload_file_by_content(BUCKET_NAME, $file_path, $option);
			//file_put_contents(AM_ROOT.'info.txt', json_encode($info));
			if($info->isOk()){echo  '1';}else{echo '0';}
		}else{echo '0';}
		return true;
	}


	//删除文件
	public function delete_file($args){
		$arg = $args[0];
		if($arg['filetype']=='file'){
			$info = $this->oss->delete_object(BUCKET_NAME, $arg['position_local']);
			if($info->status==204){
				return array('rmfile' => 1, 
					'refresh'=>dirname($arg['position_local']) == '\\' ? '/' : dirname($arg['position_local']).'/',);
			}else{
				return array('rmdir' => 0);
			}
		}else{}
		return $arg;	
	}


}
?>
