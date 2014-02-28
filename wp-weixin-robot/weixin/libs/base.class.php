<?php
/**
 *  weixin sdk core
 *  @time 2014-2-16
 *  @author midoks@163.com
 *	@version 1.0
 */
class Weixin_BaseCore{

	/**
 	* @func get remote data
	* @param string $url
	* @param string $json
	* @ret string $response
 	*/
	private function get($url, $json = ''){
		$go = curl_init();
		curl_setopt($go, CURLOPT_URL, $url);
		curl_setopt($go, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($go, CURLOPT_MAXREDIRS, 30);
		curl_setopt($go, CURLOPT_HEADER, 0);
		curl_setopt($go, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($go, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($go, CURLOPT_TIMEOUT, 30);
		if(!empty($json)){//POST Data
			curl_setopt($go, CURLOPT_POST, 1);
			curl_setopt($go, CURLOPT_POSTFIELDS ,$json);
		}
		$response = curl_exec($go);
		curl_close($go);
		return $response;
	}

	public function getToken($app_id, $app_sercet){
		$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$app_id}&secret={$app_sercet}";
		return $this->get($url);
	}

	public function pushMsgText($token, $open_id, $msg){
		$info['touser'] = $open_id;
		$info['msgtype'] = 'text';
		$info['text']['content'] = $msg;

		$url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token={$token}";
		return $this->get($url, $this->to_json($info));
	}

	public function pushMsgImage($token, $open_id, $media_id){
		$info['touser'] = $open_id;
		$info['msgtype'] = 'image';
		$info['image']['media_id'] = $media_id;
		$url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token={$token}";
		return $this->get($url, json_encode($info));
	}

	public function pushMsgVoice($token, $open_id, $media_id){
		$info['touser'] = $open_id;
		$info['msgtype'] = 'voice';
		$info['voice']['media_id'] = $media_id;
		$url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token={$token}";
		return $this->get($url, json_encode($info));
	}

	public function pushMsgVideo($token, $open_id, $media_id, $title, $desc){
		$info['touser'] = $open_id;
		$info['msgtype'] = 'video';
		$info['video']['media_id'] = $media_id;
		$info['video']['title'] = $title;
		$info['video']['description'] = $desc;
		$url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token={$token}";
		return $this->get($url, json_encode($info));
	}

	public function pushMsgMusic($token, $open_id, $thumb_media_id, $title, $desc, $musicurl, $hqmusicurl){
		$info['touser'] = $open_id;
		$info['msgtype'] = 'music';
		$info['music']['title'] = $title;
		$info['music']['description'] = $desc;
		$info['music']['thumb_media_id'] = $thumb_media_id;
		$info['music']['musicurl'] = $musicurl;
		$info['music']['hqmusicurl'] = $hqmusicurl;
		$url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token={$token}";
		return $this->get($url, json_encode($info));
	}

	public function pushMsgNews($token, $open_id, $info){
		$info['touser'] = $open_id;
		$info['msgtype'] = 'news';
		$info['news']['articles'] = $info;
		$url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token={$token}";
		return $this->get($url, json_encode($info));
	}

	//meun setting
	public function menuGet($token){
		$url = "https://api.weixin.qq.com/cgi-bin/menu/get?access_token={$token}";
		return $this->get($url);
	}

	public function menuSet($token, $json){
		$url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token={$token}";
		return $this->get($url, $json);
	}

	public function menuDel($token){
		$url = "https://api.weixin.qq.com/cgi-bin/menu/delete?access_token={$token}";
		return $this->get($url);
	}

	//upload and download
	public function download($token, $media_id){
		$url = "http://file.api.weixin.qq.com/cgi-bin/media/get?access_token={$token}&media_id={$media_id}";
		return $this->get($url);
	}

	public function upload($token, $type, $file){
		$info['media'] = '@'.$file;
		$url = "http://file.api.weixin.qq.com/cgi-bin/media/upload?access_token={$token}&type={$type}";
		//$url = "http://127.0.0.1/hello.php";
		return $this->get($url, $info);
	}

	public function uploadUrl($token, $type, $fn, $mime, $content){
		$url = "http://file.api.weixin.qq.com/cgi-bin/media/upload?access_token={$token}&type={$type}";
		return $this->uploadContents($url, $fn, $mime, $content, $token, $type);
	}


	public function uploadContents($url, $fn, $mime, $content, $token, $type){
		$boundary = substr(md5(rand(0,32000)), 0, 10);
		$boundary = '--WebKitFormBoundary'.$boundary;
	
		$data .= "--$boundary\n";
		$data .= "Content-Disposition: form-data; name=\"media\"; filename=\"{$fn}\";\r\n";
		$data .= 'Content-Type: '.$mime."\r\n";
		$data .= $content."\r\n\r\n";
		$data .= "--$boundary--\r\n";
		
		return	$this->get($url, $data);
		
	}

	public function uploadContent5($url, $fn, $mime, $content, $token, $type){
		$boundary = substr(md5(rand(0,32000)), 0, 10);
		$boundary = '--WebKitFormBoundary'.$boundary;

		//$data .= "--$boundary\n";
		//$data .= "Content-Disposition: form-data; name=\"media\"\n";
		//$data .= "media\n";
		
		$data .= "--$boundary\n";
		$data .= "Content-Disposition: form-data; name=\"media\"; filename=\"{$fn}\";\r\n";
		$data .= 'Content-Type: '.$mime."\r\n\r\n\r\n\r\n";
		$data .= $content."\r\n\r\n";
		$data .= "--$boundary--\r\n";

		//$fp = fsockopen('127.0.0.1', 80, $errno, $errstr, 10);
		$fp = fsockopen('file.api.weixin.qq.com', 80, $errno, $errstr, 10);

		$postStr = "POST /cgi-bin/media/upload?access_token={$token}&type={$type} HTTP/1.1\r\n";
		//$postStr = "POST /hello.php HTTP/1.1\r\n";
		//$postStr .= "Host: 127.0.0.1\r\n";
		$postStr .= "Host: file.api.weixin.qq.com\r\n";
		$postStr .= "User-Agent: {$_SERVER['HTTP_USER_AGENT']}\r\n";
		$postStr .= "Content-Length: ".strlen(trim($data))."\r\n";
		$postStr .= "Content-Type: multipart/form-data; boundary={$boundary}\r\n";
		$postStr .= "Accept-Encoding: gzip,deflate,sdch;\r\n";
		

		/*foreach($_COOKIE as $k=>$v){
			$cookiestr .= "{$k}:{$v};";
		}

		$postStr .= "Cookie: {$cookiestr}\r\n";*/
		$postStr .= "\r\n\r\n";

		$postStr .= $data;

		echo '<pre>';
		echo $postStr;
		echo "</pre>";

		if($fp){
			fwrite($fp, $postStr);
			while (!feof($fp)) {
        		echo fgets($fp, 128);
    		}			
		}else{
			return false;
		}
		
	}

	private function uploadContents4($url, $fn, $mime, $content){
		$boundary = substr(md5(rand(0,32000)), 0, 10);
		  
		//$data .= "--$boundary\n";
		//$data .= "Content-Disposition: form-data; name=\"media\"\n";
		//$data .= "media\n";
		  
		$data .= "--$boundary\n";
		$data .= "Content-Disposition: form-data; name=\"media\"; filename=\"{$fn}\"\r\n";
		$data .= 'Content-Type: '.$mime."\r\n";    
		$data .= 'Content-Transfer-Encoding: binary'."\r\n\r\n\r\n";
		$data .= ($content)."\r\n";
		$data .= "--$boundary--\r\n";
		
		echo '<pre>';
		echo $data;
		echo '</pre>';

		$context = stream_context_create(array(
			'http'=> array(
				'method' => 'POST',
				'timeout'=> 10,
				'user_agent'=>$_SERVER['HTTP_USER_AGENT'],
				'header' =>"Content-Type: multipart/form-data; boundary={$boundary}".
					"\r\nContent-Length: ".strlen($content).
					"\r\nReferer: http://mp.weixin.qq.com/".
					"\r\n\r\n",
				'content'=> $data
			)
		));
		$ret = file_get_contents($url, false, $context);
		return $ret;
	}

	private function uploadContents1($url, $fn, $mime, $content){

		$info['media'] = '@'.$content;
		$temp_headers = array(
			"Content-Disposition: attachment; form-data; name=\"media\";filename='{$fn}'",
			'Content-Type: application/x-www-form-urlencoded',
			'Content-Length: '.strlen($content),
		);

		$go = curl_init();
		curl_setopt($go, CURLOPT_URL, $url);
		curl_setopt($go, CURLOPT_HTTPHEADER, $temp_headers);
		curl_setopt($go, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($go, CURLOPT_MAXREDIRS, 30);
		curl_setopt($go, CURLOPT_HEADER, 0);
		curl_setopt($go, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($go, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($go, CURLOPT_TIMEOUT, 30);


		//curl_setopt($go, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($go, CURLOPT_POST, 1);
		curl_setopt($go, CURLOPT_POSTFIELDS, $content);
		$response = curl_exec($go);
		curl_close($go);
		return $response;
	}

	private function uploadContents3($url, $fn, $mime, $content){

		$boundary = substr(md5(rand(0,32000)), 0, 10);
		  
		$data .= "--$boundary\n";
		$data .= "Content-Disposition: form-data; name=\"media\"\n\n";
		$data .= "media\n";
		  
		$data .= "--$boundary\n";
		$data .= "Content-Disposition: form-data; name=\"media\";filename=\"{$fn}\"\n";
		$data .= 'Content-Type: '.$mime."\n";    
		$data .= 'Content-Transfer-Encoding: binary'."\n\n";
		$data .= $content."\n";
		$data .= "--$boundary--\n";


		$go = curl_init();
		curl_setopt($go, CURLOPT_URL, $url);
		curl_setopt($go, CURLOPT_HTTPHEADER, array("Content-Type: multipart/form-data; boundary=".$boundary,
			'Referer: https://mp.weixin.qq.com'));
		curl_setopt($go, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($go, CURLOPT_MAXREDIRS, 30);
		curl_setopt($go, CURLOPT_HEADER, 0);
		curl_setopt($go, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($go, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($go, CURLOPT_TIMEOUT, 30);


		//curl_setopt($go, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($go, CURLOPT_POST, 1);
		curl_setopt($go, CURLOPT_POSTFIELDS, $data);
		$response = curl_exec($go);
		curl_close($go);
		return $response;
	}

	//user info about
	public function getUserInfo($token, $open_id){
		$url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token={$token}&openid={$open_id}";
		return $this->get($url);
	}

	public function getUserList($token, $next_openid){
		$url = "https://api.weixin.qq.com/cgi-bin/user/get?access_token={$token}&next_openid={$next_openid}";
		return $this->get($url);
	}

	public function setUserGroup($token, $json){
		$url = "https://api.weixin.qq.com/cgi-bin/groups/create?access_token={$token}";
		return $this->get($url, $json);
	}

	public function getUserGroup($token){
		$url = "https://api.weixin.qq.com/cgi-bin/groups/get?access_token={$token}";
		return $this->get($url);
	}

	public function getUserGroupPosition($token, $json){
		$url = "https://api.weixin.qq.com/cgi-bin/groups/getid?access_token={$token}";
		return $this->get($url, $json);
	}

	public function modUserGroup($token, $json){
		$url = "https://api.weixin.qq.com/cgi-bin/groups/update?access_token={$token}";
		return $this->get($url, $json);
	}

	public function movUserGroup($token, $json){
		$url = "https://api.weixin.qq.com/cgi-bin/groups/members/update?access_token={$token}";
		return $this->get($url, $json);
	}

	public function to_json($array){
        $this->arrayRecursive($array, 'urlencode', true);
        $json = json_encode($array);
        return urldecode($json);
	}

	/**************************************************************
     *
     *  使用特定function对数组中所有元素做处理
     *  @param  string  &$array     要处理的字符串
     *  @param  string  $function   要执行的函数
     *  @return boolean $apply_to_keys_also     是否也应用到key上
     *  @access public
     *
     *************************************************************/
    public function arrayRecursive(&$array, $function, $apply_to_keys_also = false){
        static $recursive_counter = 0;
        if (++$recursive_counter > 1000) {
            die('possible deep recursion attack');
        }
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $this->arrayRecursive($array[$key], $function, $apply_to_keys_also);
            } else {
                $array[$key] = $function($value);
            }
            if ($apply_to_keys_also && is_string($key)) {
                $new_key = $function($key);
                if ($new_key != $key) {
                    $array[$new_key] = $array[$key];
                    unset($array[$key]);
                }
            }
        }
        $recursive_counter--;
    }
}
?>
