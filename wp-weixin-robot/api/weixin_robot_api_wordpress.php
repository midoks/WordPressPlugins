<?php
/**
 * 关于wordpress文章信息类
 * Author: Midoks
 * Author URI: http://midoks.cachecha.com/
 */
class weixin_robot_api_wordpress{
	public $obj;

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

	//对中文名的图片路径进行urlencode编码
	public function path_url_encode($thumb){
		$pos = strrpos($thumb,'/');
		return substr($thumb, 0,$pos+1).urlencode(substr($thumb, $pos+1));
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
			return $this->path_url_encode($match[2]);
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
		return $this->obj->smallPic();
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
		return $this->obj->bigPic();
	}

	//对每个第一条消息,进行处理...
	public function head_one_line($c){
		$c = html_entity_decode($c, ENT_NOQUOTES, 'utf-8');
		$c = strip_tags($c);
		$c = mb_substr($c, 0, 50, 'utf-8').'...';
		return $c;
	}

	public function today(){
		$sql = 'showposts=10'.'&year='.date('Y').'&monthnum='.date('m').'&day='.date('d');
		$wp = new WP_query($sql);
		$info = array();
		$i = 0;
		while($wp->have_posts()){$wp->the_post();
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
		if(empty($info)){
			return $this->obj->toMsgText('今日暂未发表文章!!!');
		}
		return $this->obj->toMsgTextPic($info);//图文
	}

	public function news($n = 5){
		return $this->new_art($n);
	}

	public function new_art($int){
		$wp = new WP_query('showposts='.$int);
		$info = array();
		$i = 0;
		while($wp->have_posts()){$wp->the_post();
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
		return $this->obj->toMsgTextPic($info);//图文
	}

	/**
	 *	热门文章数
	 *	@param int $n
	 *	@ret  xml
	 */
	public function hot($n=5){
		return $this->hot_art($n);
	}

	public function hot_art($int){
		$wp = new WP_query(array(
			'post_status' => 'publish',		//选择公开的文章
			'post_not_in' => array(),		//排除当前文章
			'ignore_sticky_posts'=> 1,		//排除顶置文章
			'orderby' => 'comment_count', 	//依据评论排序
			'showposts' => $int,			//调用的数量
		));
		$info = array();
		$i = 0;
		while($wp->have_posts()){$wp->the_post();
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
		return $this->obj->toMsgTextPic($info);//图文
	}

	/**
	 * @func 随机5篇文章信息
	 */
	public function rand($n=5){
		return $this->rand_art($n);	
	}

	public function rand_art($int){
		$wp = new WP_query("showposts={$int}&orderby=rand");
		$info = array();
		$i = 0;
		while($wp->have_posts()){$wp->the_post();
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
		return $this->obj->toMsgTextPic($info);//图文
	}

	/**
	 * @func 指定文章回复
	 */
	public function Qid($id){
		$wp = new WP_query('p='.$id);
		$info = array();
		$i = 0;
		while($wp->have_posts()){$wp->the_post();
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
		if(empty($info)){
			return false;
		}
		return $this->obj->toMsgTextPic($info);//图文
	}

	public function QidResult($id){
		$wp = new WP_query('p='.$id);
		$info = array();
		
		while($wp->have_posts()){$wp->the_post();
			$a['title'] = get_the_title();
			$a['desc'] = get_the_content();
			$a['pic'] = get_the_content();
			$a['link'] = get_permalink();
			$info = $a;
		}
		return $info;
	}

	public function Qids($id){
		$string = array();
		$i = 0;
		foreach($id as $k){
			$res = $this->QidResult($k);
			if($res){
				++$i;
				if(1 == $i){
					$a['title'] = $res['title'];
					$a['desc'] = $this->head_one_line($res['desc']);
					$a['pic'] = $this->get_opt_pic_big($res['desc']);
					$a['link'] = $res['link'];
				}else{
					$a['title'] = $res['title'];
					$a['desc'] = $res['desc'];
					$a['pic'] = $this->get_opt_pic_small($res['desc']);
					$a['link'] = $res['link'];
				}
			}
			$string[] = $a;
		}
		return $this->obj->toMsgTextPic($string);//图文
	}

	//@param array $id 
	/*public function Qids($id){
		$wp = new WP_query(array(
				'post__in'=>$id,
				//'showposts' => 10,			//调用的数量
			)
		);
		$info = array();
		$i = 0;
		while($wp->have_posts()){$wp->the_post();
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
		if(empty($info)){
			return false;
		}
		return $this->obj->toMsgTextPic($info);//图文
	}*/

	//获取分类列表
	public function get_category_list(){
		global $wpdb;
		$sql = "SELECT t.term_id, t.name FROM {$wpdb->terms} as t,
			{$wpdb->term_taxonomy} as tt WHERE t.term_id = tt.term_id and tt.taxonomy='category'";
		$res = $wpdb->get_results($sql);

		$posts_page = ( 'page' == get_option( 'show_on_front' ) && get_option( 'page_for_posts' ) ) 
			? get_permalink( get_option( 'page_for_posts' ) ) : home_url( '/' );
		$posts_page = esc_url($posts_page);

		$info = array();
		$i = 0;

		$a['title'] = '欢迎光临,点击喜欢的栏目';
		$a['desc'] =  '介绍';
		$a['link'] = $posts_page;
		$info[] = $a;

		if($res){
			foreach($res as $k=>$v){
				++$i;
				if($i==1){
					$a['title'] = $v->name;
					$a['desc'] =  $v->name;
					$a['link'] = "{$posts_page}?cat={$v->term_id}";
				}else{
					$a['title'] = $v->name;
					$a['desc'] =  $v->name;
					$a['link'] = "{$posts_page}?cat={$v->term_id}";
				}
				$info[] = $a;
			}
		}
		if(empty($info)){
			return false;
		}
		return $this->obj->toMsgTextPic($info);//图文
	}


	public function get_tag_list($page = 1){
		$total = $this->get_tag_list_page_total();
		$page_num = ceil($total/10);

		if('?'==$page){
			$info = '共有标签'.$total.'个(每页10个标签),共有'.$page_num.'页标签';
			return $this->obj->toMsgText($info);
		}

		
		if($page<1){
			$page = 1;
		}else if($page > $page_num){
			$info = '超出了标签查询页,回复#?,查看多少页标签';
			return $this->obj->toMsgText($info);
		}
		return $this->get_tag_list_page(($page-1)*10);
	}

	public function get_tag_list_page_total(){
		global $wpdb;
		$sql = "SELECT count(t.term_id) as c FROM {$wpdb->terms} as t,
			{$wpdb->term_taxonomy} as tt WHERE t.term_id = tt.term_id and tt.taxonomy='post_tag' order by t.term_id";
		$res = $wpdb->get_results($sql);
		return $res[0]->c;
	}

	public function get_tag_list_page($pages = 0){
		global $wpdb;
		$sql = "SELECT t.term_id, t.name FROM {$wpdb->terms} as t,
			{$wpdb->term_taxonomy} as tt WHERE t.term_id = tt.term_id and tt.taxonomy='post_tag' order by t.term_id desc limit {$pages}, 10";
		$res = $wpdb->get_results($sql);
		
		$posts_page = ('page' == get_option( 'show_on_front' ) && get_option( 'page_for_posts' ) )
			? get_permalink( get_option( 'page_for_posts' ) ) : home_url( '/' );
		$posts_page = esc_url($posts_page);

		$info = array();
		if($res){
			foreach($res as $k=>$v){
				$a['title'] =  $v->name;
				$a['desc'] =  $v->name;
				$a['link'] = "{$posts_page}tag/{$v->name}";
				$info[] = $a;
			}
		}
		return $this->obj->toMsgTextPicList($info);
	}

	//@func 总文章数
	public function total(){
		$arg['posts_per_page'] = -1;
		$query = new WP_Query($arg);
		$total = $query->post_count;
		$page = ceil($total/5);
		$text = '共有:'.$total.'篇文章'."\n";
		$text .= $page.'页(5篇一页)';
		return $this->obj->toMsgText($text);
	}

	//@func 页面浏览
	public function pageView($kw){
		//先判断是否是关键字查询
		if($kq = $this->keyQuery($kw)){
			return $kq;
		}

		$word_prefix = substr($kw, 0, 1);
		if($word_prefix!='p'){
			return false;
		}
		//var_dump($word_prefix);
		$word_suffix = substr($kw, 1);
		//var_dump(is_numeric($word_suffix));
		if(!is_numeric($word_suffix)){
			//var_dump($word_prefix);
			return false;
		}
		//var_dump($word_prefix);

		$arg['posts_per_page'] = -1;
		$query = new WP_Query(array('posts_per_page'=>-1));
		$pageTotal = ceil($query->post_count/5);
		//var_dump($word_suffix,$pageTotal);
		if($word_suffix > $pageTotal){
			return $this->obj->toMsgText("你输入页数太大!!!");
		}

		$arg = array(
			'order' => 'DESC',		//ASC升序 DESC降序
			'showposts' => 5,		//获取指定的数据量
			'paged' => $word_suffix,//当前第几页
		);
		query_posts($arg);
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
		return $this->obj->toMsgTextPic($info);//图文
	}

	//关键字查询
	public function keyQuery($kw){
		//var_dump($kw);
		//$kw = $this->convert($kw);
		//var_dump($kw);
		$word_prefix = substr($kw, 0, 1);
		//var_dump($word_prefix);
		if($word_prefix != '?'){
			return false;
		}
		$word_suffix = substr($kw, 1);
		$keyWord = explode('!', $word_suffix);
		if(empty($keyWord[1])){
			$keyWord[1] = '1';
		}
		//var_dump($keyWord);
		if(count($keyWord)==1){//关键字前5篇文章
			//return $this->keyWordSoso($keyWord);
			return $this->keyWordSoso($keyWord[0]);
		}
		//询问文章数据
		if($keyWord[1]=='?'){
			return $this->keyWordSoso($keyWord[0], '?');
		}
		//翻页功能
		if(is_numeric($keyWord[1])){
			return $this->keyWordSoso($keyWord[0], $keyWord[1]);
		}
	}

	/**
	 *	@func 关键字搜索(新 5篇)
	 *	@param $key 关键字
	 *	@ret string xml
	 */
	public function keyWordSoso($key, $sign=''){
		//判断$sign
		$res = $this->keySoso($key, $sign);

		if($sign == '?'){
			$num = count($res);
			$p = ceil($num/5);
			return $this->obj->toMsgText("~{$key}~关键字,共{$num}篇有{$p}页!!!");
		}
		if(!$res){
			if(empty($sign)){
				return $this->obj->toMsgText("未有~{$key}~关键字");
			}
			return $this->obj->toMsgText("~{$key}~关键字,没有第{$sign}页!!!");
		}
		//var_dump($res);
		$info = array();
		foreach($res as $k=>$v){
			if($k==0){
				$a['title'] = $v->post_title;
				$a['desc'] = $this->head_one_line($v->post_content);
				$a['pic'] = $this->get_opt_pic_big($v->post_content);
				$a['link'] = $v->guid;
			}else{
				$a['title'] = $v->post_title;
				$a['desc'] = $v->post_title;
				$a['pic'] = $this->get_opt_pic_small($v->post_content);
				$a['link'] = $v->guid;
			}
			$info[] = $a;
		}
		if(count($info)==0){
			return $this->obj->toMsgText("~{$key}~关键字,没有数据!!!");
		}
		return $this->obj->toMsgTextPic($info);
	}

	/**
	 *	@func 关键查询
	 *	@param string $k 关键字
	 *	@ret array
	 */
	public function keySoso($k, $sign){
		global $wpdb;
		$limit = '';
		//limit
		//判断$sign
		if(empty($sign)){
			$limit = 'limit 0, 5';
		}else if($sign=='?'){
			$limit = '';
		}else{
			$p = 5*($sign-1);
			$limit = "limit {$p},5";
		}
	
		$sql = "SELECT p.post_title,p.guid,p.post_content from {$wpdb->posts} p ".
			"where p.post_status='publish' ".
			"and 1=1 ".
			//关键字处
			"and ((p.post_title like '%{$k}%') ".
			"or (p.post_content like '%{$k}%'))".
			"order by p.id desc ";

		if($sign=='?'){
			$sql_num = $sql.$limit;
			$res = $wpdb->get_results($sql_num);
		}else{
			$num = $wpdb->get_results($sql);
			//var_dump($num);
			$p = ceil(count($num)/5);
			//var_dump($p);
			if($sign>$p){
				return false;
			}
			$sql_num = $sql.$limit;
			$res = $wpdb->get_results($sql_num);
		}
		return $res;
	}	
}
?>
