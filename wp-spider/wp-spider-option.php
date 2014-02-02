<?php
class wp_spider_option{

	//架构函数
	public function __construct(){
		if(is_admin()){
			add_action('admin_menu', array(&$this, 'spider_menu'));
		}
	}

	//设置面板
	public function spider_menu(){
		define('SPIDER_URL', plugins_url('', __FILE__));
		//添加主目录
		add_menu_page('蜘蛛记录',
			'蜘蛛记录',
			'manage_options',
			'wp-spider',
			array(&$this, 'spider_basic_intro'),
			SPIDER_URL.'/zz.jpg');
		//添加子目录
		add_submenu_page('wp-spider',
			'蜘蛛记录显示',	
			'蜘蛛记录显示',
			'manage_options',
			'wp-show-records',
			array($this, 'spider_show_record'));
	}

	//插件介绍
	public function spider_basic_intro(){
		echo file_get_contents(SPIDER_ROOT.'/spiderIntroduction.txt');

		echo '<hr />';
		echo '<form  method="post">';
		echo '<input style="margin-left:10px;" name="submit" type="submit" class="button-primary" value="清空记录" /></p>';
		echo '</form>';
	}

	

	//显示抓取记录
	public function spider_show_record(){

		if(isset($_GET['del_id'])){
			$this->del_func($_GET['del_id']);
		}

		global $wpdb, $spider;
		$table = $spider->tname;//表名

		$p = isset($_GET['p'])?$_GET['p']:'1';//当前页
		if((!is_numeric($p)) || ($p<=0)){$p = 1;}//页数的正确修改

		//总页数
		$sql = "select count(id) as count from {$table}";
		//echo $sql;
		$data = $wpdb->get_results($sql);
		$countNum = $data[0]->count;
		$pageNum = ceil($countNum/20);
		//总页数判断
		if($p>=$pageNum){$p=$pageNum;}

		$trTpl = "<tr><td width='60px' style='text-align:center;'>%s</td>".
				"<td width='60px' style='text-align:center;'>%s</td>".
				"<td width='118px'>%s</td><td width='90px'>%s</td><td>%s</td>".
				"<td width='40px' style='text-align:center;'>%s</td>".
				"</tr>";
		$tableHeadTpl = sprintf($trTpl, '序号', '蜘蛛名', '时间', 'IP地址', '收录页', '操作');

		$tableBodyTpl = '';
		$data = $this->spider_data($p, $table);
		
		if(empty($data)){
			$tableHeadTpl .= '<tr><td style="text-align:center;" colspan="6">无记录</td></tr>';
		}
		
		foreach($data as $k=>$v){
			$tableHeadTpl .= sprintf($trTpl, $v['id'], $v['name'], $v['time'], $v['ip'], htmlentities($v['url'], ENT_QUOTES), $this->del_page($v['id']));
		}
		//var_dump($data);
		//echo($tableTpl);
		echo '<table class="wp-list-table widefat fixed posts">';
		echo '<thead>';
		echo($tableHeadTpl);
		echo '</thead>';
		echo '<tbody>';
		echo($tableBodyTpl);
		echo '</tbody>';
		
		echo($this->Pagination($countNum, $p, 20, 7, 'p'));
		
		echo '</table>';
		//echo $this->Pagination($p);
	}

	//蜘蛛抓取记录数据获取
	public function spider_data($page=1, $table='midoks_spider'){
		global $wpdb, $spider;
		if($page<1){
			$page = 1;
		}
		$start = ($page-1)*20;//开始数据
		//var_dump($start);	
		$sql = "select * from {$table} order by id desc limit {$start},20";
		$data = $wpdb->get_results($sql);
		$newData = array();
		//echo '<pre>';
		foreach($data as $k=>$v){
			$arr = array();
			$arr['id'] = $v->id;
			$arr['name'] = $v->name;
			$arr['time'] = date('Y-m-d H:i:s', $v->time);
			$arr['ip'] = $v->ip;
			$arr['url'] = urldecode($v->url);
			$newData[] = $arr;
		}
		//var_dump($newData);
		return $newData;
	}

	public function spider_del_id($id, $table='midoks_spider'){
		global $wpdb, $spider;
		$sql = "delete from {$table} where id='{$id}'";
		$data = $wpdb->query($sql);
		if($data){
			echo '删除成功!!!';
		}else{
			echo '删除失败@!!';
		}
	}

	public function del_page($id){
		$url = $_SERVER['REQUEST_URI'];
		$r_url = str_replace(strstr($url, '&'), '', $url);
		$thisPageUrl = 'http://'.$_SERVER['HTTP_HOST'].$r_url.'&'.'del_id='.$id;
		//echo '<pre>';echo $thisPageUrl; echo '<pre>';
		$h = '<span><a href="'.$thisPageUrl.'">删除</a></span>';
		return $h;
	}
	
	public function del_func($id){
		$prev = $_SERVER['HTTP_REFERER'];

		$this->spider_del_id($id);
		//header('location: '.$prev);
		echo "<script>window.location.href='{$prev}';</script>";
		exit;
	}

	public function Pagination($total, $position, $page=5, $show=7, $pn = 'nav'){
		//当前页
		$url = $_SERVER['REQUEST_URI'];
		$r_url = str_replace(strstr($url, '&'), '', $url);
		$thisPageUrl = 'http://'.$_SERVER['HTTP_HOST'].$r_url.'&'.$pn.'=';
		echo('<tr><td colspan="6">');	
		
		$prev = $position-1;//前页
		$next = $position+1;//下页
		//$showitems = 3;//显示多少li
		$big = ceil($show/2);
		$small = floor($show/2);//$show最好为奇数 
		$total_page = ceil($total/$page);//总页数
		//if($prev < 1){$prev = 1;}
		if($next > $total_page){$next = $total;}
		if($position > $total_page){$position = $total_page;}
		if(0 != $total_page){
			//echo "<div id='page'><div class='center'><ul>";
			/////////////////////////////////////////////
			echo("<span><a href='".$thisPageUrl."1#' class='fixed'>首页</a></span>");
			echo("<span style='margin-left:5px;'><a href='".$thisPageUrl.$prev."#'><<</a></span>");
			$j=0;
			for($i=1;$i<=$total_page;$i++){
				$url = $thisPageUrl.$i;
				if($position==$i)
					$strli = "<span style='margin-left:5px;'><a href='".$url."#' class='current' >".$i.'</a></span>';
				else
					$strli = "<span style='margin-left:5px;'><a href='".$url."#' class='inactive' >".$i.'</a></span>';
				if($total_page<=$show){echo $strli;}
				if(($position+$small)>=$total_page){
					if(($j<$show) && ($total_page>$show) && ($i>=($total_page-(2*$small)))){echo($strli);++$j;}
				}else{if(($j<$show) && ($total_page>$show) && ($i>=($position-$small))){echo($strli);++$j;}}
			}
			echo("<span style='margin-left:5px;'><a href='".$thisPageUrl.$next."#'>>></a></span>");
			echo("<span style='margin-left:5px;'><a href='".$thisPageUrl.$total_page."#' class='fixed'>尾页</a></span>");

			echo("<span style='margin-left:30px;'>共{$total}条数据|</span>");
			echo("<span>当前第{$position}页</span>");
			//////////////////////////////////////////////
			//echo '</ul></div></div>';
		}
		echo('</td></tr>');
	}

}



if((isset($_POST['submit'])) && ($_POST['submit']=='清空记录') && isset($_GET['page']) && ('wp-spider' == $_GET['page'])){
	global $spider;
	$spider->clear();
}
?>
