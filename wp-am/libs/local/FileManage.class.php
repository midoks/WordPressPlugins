<?php
/**
 *	@func 本地文件管理
 */

define('WP_AM_LOCALMANAGE', dirname(dirname(AM_ROOT)).'/uploads/');
define('WP_AM_LOCALMANAGE_NA', dirname(dirname(AM_ROOT_NA)).'/uploads/');
class FileManage{

	private $option = null;

	public function __construct(){
		$this->option = get_option('wp_am_option');
	}

	/* 读取备份文件中,所有目录 */
	private function fileList($name){
		$name = rtrim($name,'/').'/';
		$fp = opendir($name);
		$arr = array();
		while($n = readdir($fp))
			if($n=='.'|| $n=='..'){
			}else if(is_dir($name.$n)){
				$arr[]= $this->fileList($name.$n);
			}else if(is_file($name.$n)){
				$arr[] = $name.$n;
			}
		closedir($fp);
		return $arr;
	}

	private function DirList($name){
		$name = rtrim($name,'/').'/';
		$fp = opendir($name);
		$arr = array();
		while($n = readdir($fp)){
			if($n=='.'|| $n=='..'){
			}else{
				$arr[] = $name.$n;
			}
		}
		closedir($fp);
		return $arr;
	}

	public function get_p($p){
		$a = explode('/', $p);
		$pop = array_pop($a);
		$ret = implode('/', $a);
		if('uploads'==$pop){
			$ret = $ret.'/'.$pop.'/';
			return $ret;
		}else{
			return $ret;
		}
	}

	//获取父级功能
	public function get_parent_file($args){
		$arg = $args[0];
		$this->get_p($arg['position_local']);
		$pdir = $this->get_p($this->get_p($arg['position_local']));
		//$pdir_na = $this->get_p($this->get_p($arg['position']));
		if($pdir=='\\'){return array('err'=>'1');}else{
			$pdir .= '/';
		}
		$arg['position_local'] = $pdir;
		return $arg;
	}

	public function get_list_info($l){
		$list =array();
		foreach($l as $k=>$v){
			$list['fn'][] = basename($l[$k]);//文件名
			$list['position_local'][] = $l[$k];//本地文件位置
			$list['position'][] = WP_AM_LOCALMANAGE_NA.basename($l[$k]);//网络文件位置
			//$list['position'][] =  $l[$k];//本地文件位置
			$list['uptime'][] = date('Y-m-d H:i:s',filemtime($l[$k]));//创建时间
			$list['filetype'][] = filetype($l[$k]);//文件类型
			$list['wrx'][] = substr(sprintf('%o',fileperms($l[$k])),-4);//文件权限
			$list['reffer'][] = 'all';//本地文件防盗链,无法修改
		}
		return $list;
	}

	public function get_list_info2($l){
		$list = array();
		foreach($l as $k=>$v){
			$fn = basename($l[$k]);
			$na = str_replace(WP_AM_LOCALMANAGE,
				WP_AM_LOCALMANAGE_NA,
				$l[$k]);
			
			$list['fn'][] = $fn;//文件名
			$list['position_local'][] = $l[$k];//本地文件位置
			$list['position'][] = $na;//网络文件位置
			$list['uptime'][] = date('Y-m-d H:i:s',filemtime($l[$k]));//创建时间
			$list['filetype'][] = filetype($l[$k]);//文件类型
			$list['wrx'][] = substr(sprintf('%o',fileperms($l[$k])),-4);//文件权限
			$list['reffer'][] = 'all';//本地文件防盗链,无法修改
		}
		return $list;
	}


	//获取根目录列表
	public function get_root_file(){
		$l = $this->DirList(WP_AM_LOCALMANAGE);
		$list = $this->get_list_info($l);
		return $list;	
	}

	//获取子目录和文件
	public function get_child_file($args){
		$list = array();
		if(file_exists($args[0]['position_local'])){
			$this->file_na = dirname($args[0]['position_local']).'/';
			//var_dump($this->file_na);
			$l = $this->DirList($args[0]['position_local']);
			$list = $this->get_list_info2($l);
		}
		///$list = array_merge($args, $list);
		return $list;
	}

	//删除文件
	public function delete_file($args){
		$arg = $args[0];
		if($arg['filetype'] == 'file'){//文件操作
			$bool = @unlink($arg['position_local']);
			if($bool){
				return array('rmfile' => 1, 'refresh'=>dirname($arg['position_local']));
			}
		}else{
			return array('rmdir' => 0);
		}
		return $arg;
	}


	//目标地址(不存在则创建)
	private function target_dir(){
		$dir = WP_AM_LOCALMANAGE;

		$year = date('Y');//年份

		$dir = $dir.$year.'/';
		if(!file_exists($dir)){
			mkdir($dir);
		}

		$month = date('m');//月份
		$dir = $dir.$month.'/';
		if(!file_exists($dir)){
			mkdir($dir);
		}

		return $dir;
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
			$target = $this->target_dir().$this->set_fn_name($file['name']).'.'.$file['type'];//目标地址
			if(move_uploaded_file($tmp,$target)){
				echo  '1';
			}else{
				echo '0';
			}
		}else{
			echo '0';
		}
		//file_put_contents(AM_ROOT.'s.txt',json_encode($_FILES));
		//file_put_contents(AM_ROOT.'s2.txt',$tmp.'|'.$target);
		return true;
	}

}
?>
