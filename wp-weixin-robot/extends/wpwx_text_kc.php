<?php
/**
 *  @func 课程信息查询
 *  @author clothand 
 *  @version 1.0
 *  @weibo clothand
 */
class wpwx_text_kc{
	public $obj;

    public function __construct($obj){
        $this->obj = $obj;
    }
 
    public function start($kw){
        if('课程'==substr($kw,0,6)){
            $kw = str_replace('课程','', $kw);
            return $this->getData($kw);
        }
        return false;
    }
 
 
    public function getData($kw){
    	global $wpdb;
		$result = $wpdb->get_results("SELECT post_title, post_content FROM kc WHERE post_title like '%{$kw}%'");
		if(empty($result)){
			return $this->obj->toMsgText("未找到!!");
		}

		$ret = '';
		foreach($result as $k=>$v){
			$r = '课程名词|教师|类型|学分|考核方式：'.strip_tags(trim($result[$k]->post_title))."\n".
				'课程评价：'.strip_tags(trim($result[$k]->post_content))."\n";

			$c = strlen($ret);
			$rc = strlen($r);

			if(($c+$rc)>2048){
				$w = '以上信息来自深大淘课平台';
				$wc = strlen($w);
				if(($c+$wc) > 2048){
					return $this->obj->toMsgText($ret);
				}else{
					return $this->obj->toMsgText($ret.$w);
				}
			}else{
				$ret .= $r;
			}		
		}
    	
        /*$result = '课程名词|教师|类型|学分|考核方式：'.$result[0]->post_title."\n".
		        '课程评价：'.$result[0]->post_content."\n".
                '以上信息来自深大淘课平台'."\n";
		*/        
        return $this->obj->toMsgText($ret);
	}
}
?>
