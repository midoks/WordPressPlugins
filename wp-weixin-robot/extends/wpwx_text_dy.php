<?php
class wpwx_text_dy{

	private $obj = null;

	//构造函数 | init
	public function __construct($obj){
		$this->obj = $obj;
	}

	//深大语音导游插件
	//by：致远clothand
	public function start($kw){
	//设定音乐链接
	    $MusicUrl101='http://6.szubaike.duapp.com/101.mp3';
		$MusicUrl102='http://6.szubaike.duapp.com/102.mp3';
		$MusicUrl103='http://6.szubaike.duapp.com/103.mp3';
		$MusicUrl104='http://6.szubaike.duapp.com/104.mp3';
		$MusicUrl105='http://6.szubaike.duapp.com/105.mp3';
		$MusicUrl106='http://6.szubaike.duapp.com/106.mp3';
		$MusicUrl107='http://6.szubaike.duapp.com/107.mp3';
		$MusicUrl108='http://6.szubaike.duapp.com/108.mp3';
		$MusicUrl109='http://6.szubaike.duapp.com/109.mp3';
		$MusicUrl110='http://6.szubaike.duapp.com/110.mp3';
		$MusicUrl111='http://6.szubaike.duapp.com/111.mp3';
		$MusicUrl112='http://6.szubaike.duapp.com/112.mp3';
		$MusicUrl113='http://6.szubaike.duapp.com/113.mp3';
		$MusicUrl114='http://6.szubaike.duapp.com/114.mp3';
		$MusicUrl115='http://6.szubaike.duapp.com/115.mp3';
		$MusicUrl116='http://6.szubaike.duapp.com/116.mp3';
		$MusicUrl117='http://6.szubaike.duapp.com/117.mp3';
		$MusicUrl118='http://6.szubaike.duapp.com/118.mp3';
		$MusicUrl119='http://6.szubaike.duapp.com/119.mp3';
		$MusicUrl120='http://6.szubaike.duapp.com/120.mp3';
		$MusicUrl121='http://6.szubaike.duapp.com/121.mp3';
		$MusicUrl122='http://6.szubaike.duapp.com/122.mp3';
		$MusicUrl123='http://6.szubaike.duapp.com/123.mp3';
		$MusicUrl124='http://6.szubaike.duapp.com/124.mp3';
		$MusicUrl125='http://6.szubaike.duapp.com/125.mp3';
		$MusicUrl126='http://6.szubaike.duapp.com/126.mp3';
		//开始判断
		if('101' == $kw){return $this->obj->toMsgMusic('【导游词】日晷','导游：:-)难 如果无法听到声音可以回复：导游词101 获取文字版',$MusicUrl101,$MusicUrl101);}
		elseif('102' == $kw){return $this->obj->toMsgMusic('【导游词】演会中心','导游：竹子 如果无法听到声音可以回复：导游词102 获取文字版', $MusicUrl102, $MusicUrl102);}
		elseif('103' == $kw){return $this->obj->toMsgMusic('【导游词】办公楼','导游：致远 如果无法听到声音可以回复：导游词103 获取文字版', $MusicUrl103, $MusicUrl103);}
		elseif('103' == $kw){return $this->obj->toMsgMusic('【导游词】图书馆北馆','导游： 昕昕 如果无法听到声音可以回复：导游词104 获取文字版', $MusicUrl104, $MusicUrl104);}
		elseif('103' == $kw){return $this->obj->toMsgMusic('【导游词】中心广场','导游：陈梓祺 如果无法听到声音可以回复：导游词105 获取文字版', $MusicUrl105, $MusicUrl105);}
		elseif('103' == $kw){return $this->obj->toMsgMusic('【导游词】教学楼','导游：小思 如果无法听到声音可以回复：导游词106 获取文字版', $MusicUrl106, $MusicUrl106);}
		elseif('103' == $kw){return $this->obj->toMsgMusic('【导游词】图书馆南馆','导游：面具 如果无法听到声音可以回复：导游词107 获取文字版', $MusicUrl107, $MusicUrl107);}
		elseif('103' == $kw){return $this->obj->toMsgMusic('【导游词】杜鹃山','导游：克莉丝汀 如果无法听到声音可以回复：导游词108 获取文字版', $MusicUrl108, $MusicUrl108);}
		elseif('103' == $kw){return $this->obj->toMsgMusic('【导游词】文山湖','导游：Cm 如果无法听到声音可以回复：导游词109 获取文字版', $MusicUrl109, $MusicUrl109);}
		elseif('103' == $kw){return $this->obj->toMsgMusic('【导游词】西南学生区','导游：叮咚嗒 如果无法听到声音可以回复：导游词110 获取文字版', $MusicUrl110, $MusicUrl110);}
		elseif('103' == $kw){return $this->obj->toMsgMusic('【导游词】南区运动场','导游：麦子 如果无法听到声音可以回复：导游词111 获取文字版', $MusicUrl111, $MusicUrl111);}
		elseif('103' == $kw){return $this->obj->toMsgMusic('【导游词】石头坞广场','导游：呆逼魁 如果无法听到声音可以回复：导游词112 获取文字版', $MusicUrl112, $MusicUrl112);}
		elseif('103' == $kw){return $this->obj->toMsgMusic('【导游词】学生活动中心','导游：佳佳 如果无法听到声音可以回复：导游词113 获取文字版', $MusicUrl113, $MusicUrl113);}
		elseif('103' == $kw){return $this->obj->toMsgMusic('【导游词】斋区宿舍','导游：珏子 如果无法听到声音可以回复：导游词114 获取文字版', $MusicUrl114, $MusicUrl114);}
		elseif('103' == $kw){return $this->obj->toMsgMusic('【导游词】科技楼','导游：鬼斧 如果无法听到声音可以回复：导游词115 获取文字版', $MusicUrl115, $MusicUrl115);}
		elseif('103' == $kw){return $this->obj->toMsgMusic('【导游词】文科楼','导游：茶茶 如果无法听到声音可以回复：导游词116 获取文字版', $MusicUrl116, $MusicUrl116);}
		elseif('103' == $kw){return $this->obj->toMsgMusic('【导游词】建规楼','导游：狼布 如果无法听到声音可以回复：导游词117 获取文字版', $MusicUrl117, $MusicUrl117);}
		elseif('103' == $kw){return $this->obj->toMsgMusic('【导游词】深大高尔夫球场','导游：小思 如果无法听到声音可以回复：导游词118 获取文字版', $MusicUrl118, $MusicUrl118);}
		elseif('103' == $kw){return $this->obj->toMsgMusic('【导游词】元平体育馆','导游：昕昕 如果无法听到声音可以回复：导游词119 获取文字版', $MusicUrl119, $MusicUrl119);}
		elseif('103' == $kw){return $this->obj->toMsgMusic('【导游词】田径运动场','导游：狼布 如果无法听到声音可以回复：导游词120 获取文字版', $MusicUrl120, $MusicUrl120);}
		elseif('103' == $kw){return $this->obj->toMsgMusic('【导游词】小东门','导游：陈梓祺 如果无法听到声音可以回复：导游词121 获取文字版', $MusicUrl121, $MusicUrl121);}
		
		return false;
	}

	
}


?>
