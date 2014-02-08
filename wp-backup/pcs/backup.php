<?php
/**
 * @func 备份到百度个人云上
 * @author midoks
 * @mail midoks@163.com
 */
defined('PCS_BACKUP') or define('PCS_BACKUP', str_replace('\\', '/', dirname(__FILE__)).'/');
include(PCS_BACKUP.'config.php');
class backup{

	//应用根目录
	public $root_dir;

	//资源操作
	public $linkID = null;
	//public $access_token = '';
	
	public function __construct($access_token){
		if(!$access_token){exit('token_error');}
		include(PCS_BACKUP.'libs/BaiduPCS.class.php');
		$this->linkID = new BaiduPCS($access_token);

		$this->ready();
	}

	//准备数据
	public function ready(){
		date_default_timezone_set('PRC');
		$appName = BACKUP_BP_APP_NAME;
      	$this->root_dir_test = '/apps/'.$appName.'/';
		//应用根目录
      	$this->root_dir = '/apps/'.$appName.'/'.BACKUP_NAME_PREFIX.'/'.date('Y').'/'.date('m').'/'.date('d-H-i-s').'/';
      	//$this->root_dir = '/apps/'.$appName.'/'.date('Y').'/'.date('m').'/'.date('H-i-s').'/';
		//$this->root_dir = '/apps/'.$appName.'/'.date('Y').'/'.date('m').'/';
	}


	/**
	 *	@func 创建目录
	 */
	public function createDir($dir=''){
		if(!empty($dir)){
			$make_dir = $dir;
		}else{
			$make_dir = $this->root_dir;
		}
		$info = $this->linkID->makeDirectory($make_dir);
		$arr = json_decode($info, true);
		if($arr['path']){//路径信息存在
			return true;
		}else if($arr['error_code']==31061){//已经存在
			return true;
		}
      	return false;
        //exit("目录没有生成成功!");
	}


	/* 文件列表 */
	public function sortList($dir){
		$list = backup_foreach_file($dir);
		$list = backup_array_sort($list);
		return $list;
	}

	public function sortCatFlie($dir){
		$list = $this->sortList($dir);
		$sort_list = array();
		foreach($list as $k=>$v){
			$relative_dir = str_replace($dir, '', $v);
			$relative_dir = $this->root_dir.$relative_dir;
			$sort_list['ori'][] = $v;
			$sort_list['path'][] = $relative_dir;
			$sort_list['filename'][] = basename($relative_dir);
			$sort_list['size'][] = @filesize($v);
			$sort_list['dir'][] = dirname($relative_dir).'/';
		}
		return $sort_list;
	}

	/**
	 * @func 秒传
	 * @param $path 文件绝对路径 test/test.php
	 * @param $contentLen 内容长度
	 * @param $contentMD5 md5
	 * @param $sliceMD5   
	 * @param $contentCrc32 crc32
	 */
	public function cloudUp($AppPath, $OriPath){
		$contentLen = filesize($OriPath);
		$contentMD5 = md5_file($OriPath);
		$handle = fopen($OriPath, 'rb');
		//待秒传文件校验段的MD5
		$content = fread($handle, 256*1024);
		$sliceMD5= md5($content);
		$contentCrc32 = crc32($content);
		$info = $this->linkID->cloudMatch($AppPath, $contentLen,
			$contentMD5, $sliceMD5, $contentCrc32);
		fclose($handle);
      	$info = json_decode($info, true);
		if(isset($info['error_code'])){return false;}
		return true;
	}

	/**
	 *	@func 分片上传
	 *	@param $fn  	文件名
	 */
	public function burstUp($AppPath, $OriPath, $fn){
      	//var_dump($AppPath, $OriPath, $fn);exit;
		$fs = filesize($OriPath);//文件大小
		$handle = fopen($OriPath, 'rb');
		//分片上传文件成功后返回的md5值数组集合
		$filesBlock = array();
		//设置分片上传文件块大小为20K
		$blockSize = 20480;
		$isCreateSuperFile = true;
		while (!feof($handle)) {
			$temp = $this->linkID->upload(fread($handle, $blockSize), $AppPath,
				$fn, null, $isCreateSuperFile);
			$temp = json_decode($temp, true);
			array_push($filesBlock, $temp);
		}
		fclose($handle);
		if (count($filesBlock) > 1) {
			$params = array();
			foreach ($filesBlock as $value) {
				array_push($params, $value['md5']);
			}
			$result = $this->linkID->createSuperFile($AppPath, $fn, $params);
			return $result;
		}
	}

	/**
	 *	@func 单文件上传
	 *	@param $content 内容
	 *	@param $moveTo  移动到的路径
	 *	@param $fn  	文件名
	 */
	public function singleUp($content, $moveTo, $fn){
		return $this->linkID->upload($content, $moveTo, $fn);
	}

  	/**
	 * @func zip压缩分片上传
	 * @param $content 内容
	 * @param $moveTo  移动到的路径
	 * @param $fn  	文件名
	 */
	public function burstZipUp($content, $moveTo, $fn){
		$blockSizeArr = str_split($content, 20480);//分段
      	unset($content);//销毁
		$isCreateSuperFile = true;//是否启用分片
		//分片上传文件成功后返回的md5值数组集合
		$filesBlock = array();
		foreach($blockSizeArr as $k=>$v){
			$temp = $this->linkID->upload($v, $moveTo,
				$fn, 'test.zip', $isCreateSuperFile);
          	unset($blockSizeArr[$k]);//销毁
			$temp = json_decode($temp, true);
			array_push($filesBlock, $temp);
		}
		if (count($filesBlock) > 1) {
			$params = array();
			foreach ($filesBlock as $value) {
				array_push($params, $value['md5']);
			}

			$result = $this->linkID->createSuperFile($moveTo, $fn, $params, 'test.zip');
			echo $result;exit;
		}
	
	}
  
	//压缩上传
	public function zipUp($dir){
      	//尝试创建目录
      	if(!$this->createDir($this->root_dir_test)){
      		exit('创建文件失败!!!获取PCS API没有开启(API管理->API列表->PCS API->开启(设置此应用保存文件名))');
        }
		include(PCS_BACKUP.'zip.class.php');
		$zip = new zip();
		$content = $zip->compress($dir, 'wordpress.zip');
      	
      	//大小在10M内容易传
      	//echo ((strlen($content)/1024)/1024),"MB\n";
      
      	//单文件形式上传
      	$info = $this->singleUp($content, $this->root_dir, 'wordpress_'.date('H-i-s').'.zip');
      	//分片形式上传(文件估计大于256*1024不能用分片上传)
      	//$info = $this->burstZipUp($content, $this->root_dir, 'wordpress_'.date('H-i-s').'.zip');
      	return $info;
	}

	public function up($dir){
      	set_time_limit(0);
      	//test
      	$test = $this->createDir($this->root_dir_test);
      	//var_dump($test);exit;
      	if(!$test){return false;}
      
      	//压缩上传
		if(BACKUP_ZIP){
			$info = $this->zipUp($dir);
          	//var_dump($info);
			if(isset($info['path'])){
				echo "backup zip good!!!\n";
              	return true;
			}else{
				echo "backup zip bad!!!\n";
              	return false;
			}
			return false;
		}
      
      	//源目录上传
		$list = $this->sortCatFlie($dir);
		//生成目录
		//去重复
		$dir = array_unique($list['dir']);
		foreach($dir as $k=>$v){
          	$info = $this->createDir($v);
          	if(!$info){exit('生成文件时产生错误!!!');}
        }
      	unset($dir);
      	echo "\nrunning...\n";
      	//var_dump($dir);exit;
      	//上传
		$mSize = 256*1024;	//秒传区间
		$bSize = 20*1024;	//分片区间
		//上传文件
		foreach($list['ori'] as $k=>$v){
			
			//秒传需要大于256k
          if($list['size'][$k]>$mSize){
            	//var_dump($list['path'][$k], $v, $list['size'][$k]);
            	$info = $this->cloudUp($list['path'][$k], $v);
            	//var_dump($info);
				if(!$info){
					$info =  $this->singleUp(file_get_contents($v), $list['dir'][$k], $list['filename'][$k]);
                  	$info = json_decode($info, true);
                  	if(isset($info['error_code'])){exit('备份失败');}
				}
			}
			//分片上传20k以上
          	if($list['size'][$k]>$bSize){
				$info = $this->burstUp($list['dir'][$k], $v, $list['filename'][$k]);
              	$info = json_decode($info, true);
              	//var_dump($info);
              	if(isset($info['error_code'])){
                	$info =  $this->singleUp(file_get_contents($v), $list['dir'][$k], $list['filename'][$k]);
                  	$info = json_decode($info, true);
                  	if(isset($info['error_code'])){exit('备份失败');}
                }
			}else{
          		//其他用单文件上传
              	//var_dump(file_get_contents($v), $list['dir'][$k], $list['filename'][$k]);
				$info = $this->singleUp(file_get_contents($v), $list['dir'][$k], $list['filename'][$k]);
          		$info = json_decode($info, true);
              	//var_dump($info);
              	if(isset($info['error_code'])){exit('备份失败');}
            }
		}
      	return true;
	}
}
?>
