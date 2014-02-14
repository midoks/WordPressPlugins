<?php
/**
 *	@func 文件本地保存
 */
define('AM_SAVE_WP_UPLOAD', dirname(dirname(AM_ROOT)).'/uploads');
class wp_am_save{
	public $obj;
	public $conf;

	public function __construct($obj, $conf){
		$this->obj = $obj;
		$this->conf = $conf;
		$this->start($conf);
	}


	//安全创建文件夹
	public function mkdir_p($path, $mode='0777'){
		$path = trim($path, '/');
		$list = explode('/', $path);
		array_pop($list);
		$dir = implode('/', $list);
		if(file_exists($dir)){
			mkdir($path, $mode);
		}else{
			$this->mkdir_p($dir, $mode);
			mkdir($path, $mode);
		}
	}

	public function start($conf){
		$list = $conf['position_local'];
		$fn = $conf['fn'];

		foreach($list as $k=>$v){
			$dir = AM_SAVE_WP_UPLOAD.'/'.trim(dirname($v), '/');
			if(!file_exists($dir)){
				$this->mkdir_p($dir);
				//if(false == $b)	continue;
			}
			
			$file_exist = $dir.'/'.$fn[$k];
			if(!file_exists($file_exist)){
				$res = $this->obj->linkID->download($v);
				if($res){
					file_put_contents($file_exist, $res);
				}	
			}
		}

	}



}
?>
