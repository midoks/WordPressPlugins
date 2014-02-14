<?php
/**
 *	@func 本地文件管理
 */

include AM_LIBS.'qiniu/config.php';
include(AM_LIBS.'qiniu/sdk/rs.php');
include(AM_LIBS.'qiniu/sdk/rsf.php');
include(AM_LIBS.'qiniu/sdk/io.php');
class FileManage{

	private $client = null;

	public function __construct(){
		Qiniu_SetKeys(QINIU_AK, QINIU_SK);
		$this->client = new Qiniu_MacHttpClient(null);
		$this->ready();
	}


	private function ready(){
		
		//$list = Qiniu_RSF_ListPrefix($this->client, BUCKET_NAME, '', '');
		//var_dump($list);
		
		//exit;

		//$list = Qiniu_RS_Stat($this->client, BUCKET_NAME, 'logo.png');
		//var_dump($list);
		//exit;

		//$objs[] = new Qiniu_RS_EntryPath(BUCKET_NAME,'logo.png');
		//$objs[] = new Qiniu_RS_EntryPath(BUCKET_NAME,'smalllogo.png');
		//var_dump($objs);

		//$list = Qiniu_RS_BatchStat($this->client, $objs);
		//var_dump($list);
		//exit;
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
		$list =array();
		foreach($l as $k=>$v){
			$list['fn'][] = basename($l[$k]);//文件名
			$list['position_local'][] = $l[$k];//本地文件位置
			$list['position'][] = $this->file_na.'/'.basename($l[$k]);//网络文件位置
			//$list['position'][] =  $l[$k];//本地文件位置
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
		
		if(file_exists($args[0]['position_local'])){
			$this->file_na = $args[0]['position'];
			$l = $this->DirList($args[0]['position_local']);
			$list = $this->get_list_info2($l);
		}

		$list = array_merge($args, $list);
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


	//上传
	public function upload(){
		if(!empty($_FILES)){
			$tmp = $_FILES['Filedata']['tmp_name'];//临时文件地址
			$fn = $_FILES['Filedata']['name'];//目标地址

			$put = new Qiniu_RS_PutPolicy(BUCKET_NAME.'/dd/');
			$content = file_get_contents($tmp);
			$token = $put->Token(null);
			//file_put_contents(AM_ROOT.'w.txt', $content);
			list($ret, $err) = Qiniu_Put($token, $fn, $content, null);
			if( $err !== null){
				file_put_contents(AM_ROOT.'t1.txt', json_encode($err));
				echo '0';
			}else{
				file_put_contents(AM_ROOT.'t2.txt', json_encode($ret));
				echo '1';
			}
		}else{
			echo '0';
		}
		return true;
	}

}
?>
