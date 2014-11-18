<?php
//后台操作需要
class weixin_robot_api_wordpress_options{


	public $opt_big_show = array();
	public $opt_small_show = array();


	//架构函数
	public function __construct($obj){
		$this->obj = $obj;
		//最优图片选择是否开启
		if($this->obj->options['opt_pic_show']){
			$this->opt_pic_sign = true;
			$this->str_to_arr();//数组

		}else{
			$this->opt_pic_sign = false;
		}
	}

	public function str_to_arr(){
		//小图
		$small = $this->obj->options['opt_small_show'];
		if(!empty($small)){
			$this->opt_small_show = false;
		}
		$s_arr = explode("\r\n", $small);
		$tmp = array();
		foreach($s_arr as $k=>$v){
			$tmp[] = trim($v);
		}
		$this->opt_small_show = $tmp;
		//大图
		$big = $this->obj->options['opt_big_show'];
		if(!empty($big)){
			$this->opt_big_show = false;
		}
		$s_arr = explode("\r\n", $big);
		$tmp = array();
		foreach($s_arr as $k=>$v){
			$tmp[] = trim($v);
		}
		$this->opt_big_show = $tmp;

		//var_dump($this->opt_big_show, $this->opt_small_show);
	}

	public function get_opt_pic($c, $type){
		//图片格式
		//$picType = array('jpg','gif','png','bmp');
		//foreach($picType as $v){
			//$u = '/http:\/\/(.*)\.'.$v.'/iUs';
		$u2 = '/(<img[^>]+src\s*=\s*\"?([^>\"\s]+)\"?[^>]*>)/im';
		//echo $u2;
		$p_sign = preg_match($u2 ,$c, $match);
		if($p_sign){
			//var_dump($match);
			return $match[2];
		}
		//}

		//上面执行过,选择默认自定义的图片
		if('small' == $type){
			$num = count($this->opt_small_show);
			$t = $num - 1;
			$mt = mt_rand(0, $t);
			if($num){
				return $this->opt_small_show[$mt];
			}
			//$tmp = $this->obj->options['opt_small_show'];
		}else if('big' == $type){
			$num = count($this->opt_big_show);
			$t = $num - 1;
			$mt = mt_rand(0, $t);
			if($num){
				return $this->opt_big_show[$mt];
			}
			//$tmp = $this->obj->options['opt_big_show'];
		}
		//midoks 默认
		return false;
	}


	/**
	 *	获取最优图片地址
	 */
	public function get_opt_pic_small($content = ''){
		if($this->opt_pic_sign){
			$pic = $this->get_opt_pic($content, 'small');
			if(!empty($pic)){
				return $pic;
			}
		}
		return $this->smallPic();
	}

	/**
	 *	获取最优图片地址
	 */
	public function get_opt_pic_big($content = ''){
		if($this->opt_pic_sign){
			$pic = $this->get_opt_pic($content, 'big');
			if(!empty($pic)){
				return $pic;
			}
		}
		return $this->bigPic();
	}


	//大图片地址
  	public function bigPic(){
  		return WEIXIN_ROOT_NA.'640_320/'.mt_rand(1,5).'.jpg';
    }
  
  	//小图片地址
  	public function smallPic(){
  		return WEIXIN_ROOT_NA.'80_80/'.mt_rand(1,10).'.jpg';
  	}

	//对每个第一条消息,进行处理...
	public function head_one_line($c){
		//var_dump($c);exit;
		$c = html_entity_decode($c, ENT_NOQUOTES, 'utf-8');
		$c = strip_tags($c);
		$c = mb_substr($c, 0, 50, 'utf-8').'...';
		return $c;
	}


	/**
	 * @func 指定文章回复
	 */
	public function Qid($id){
		query_posts('p='.$id);
		$info = array();
		$i = 0;
		while(have_posts()){the_post();
			++$i;
			if($i==1){
				$a['title'] = get_the_title();
				$a['desc'] = $this->head_one_line(get_the_content());
				$a['pic'] = $this->get_opt_pic_big(get_the_content());
				$a['link'] = get_permalink();
			}else{
				$a['title'] = get_the_title();
				$a['desc'] = get_the_title();
				$a['pic'] = $this->get_opt_pic_small(get_the_content());
				$a['link'] = get_permalink();
			}
			$info[] = $a;
		}
		return $info;
	}

	public function today(){
		return $this->new_art(1);
	}

	public function new5(){
		return $this->new_art(5);
	}

	public function new_art($int){
		query_posts('showposts='.$int);
		$info = array();
		$i = 0;
		while(have_posts()){the_post();
			++$i;
			if($i==1){
				$a['title'] = get_the_title();
				$a['desc'] = $this->head_one_line(get_the_content());
				$a['pic'] = $this->get_opt_pic_big(get_the_content());
				$a['link'] = get_permalink();
				$a['author'] = get_the_author();
				$a['content'] = get_the_content();
			}else{
				$a['title'] = get_the_title();
				$a['desc'] = get_the_title();
				$a['pic'] = $this->get_opt_pic_small(get_the_content());
				$a['link'] = get_permalink();
				$a['author'] = get_the_author();
				$a['content'] = get_the_content();
			}
			$info[] = $a;
		}
		return $info;//图文
	}


}
?>
