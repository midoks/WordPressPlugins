<?php
include_once(WEIXIN_ROOT.'weixin-core.class.php');
class wp_weixin_robot_options extends weixin_core{

	public $options = null;
	public $obj = null;
	public $plugins = null;

	public $errMsg = '';

	public $db = null;//数据操作

	//构造函数
	public function __construct(){
		//获取配置数据
		$this->options = get_option('weixin_robot_options');
		parent::__construct();

		//数据库操作类,初始化...
		include_once(WEIXIN_ROOT_API.'weixin_robot_api_wordpress_dbs.php');
		$this->db = new weixin_robot_api_wordpress_dbs();

		//机器人扩展操作
		include_once(WEIXIN_ROOT.'wp-weixin-plugins.php');
		$this->plugins = new wp_weixin_plugins($this);

		//插件安装时调用
		register_activation_hook(WEIXIN_ROOT_POS, array($this, 'weixin_robot_install'));
		//插件卸载时调用
		register_deactivation_hook(WEIXIN_ROOT_POS, array($this, 'weixin_robot_uninstall'));

		//过滤设置功能插件功能显示
		add_filter('plugin_action_links', array($this, 'weixin_robot_action_links'), 10, 2);

		//系统初始化
		add_action('init', array($this, 'ajax'), 1);

		//微信面板菜单设置初始化
		add_action('admin_init', array($this, 'weixin_robot_setting_init'), 1);
		//微信面板菜单设置
		add_action('admin_menu', array($this, 'weixin_robot_menu'), 1);

		//警告提示
		//add_action( 'admin_notices', array($this,'notices'));
		//文章发布时,推送最新的的一篇文章(服务号可以用)
		add_action( 'publish_post', array($this, 'pull_new_pubish_post'));
		//更新了文章(服务号可以用)
		//add_action( 'pre_post_update', array($this, 'pull_new_pubish_post'));

		//显示文章ID
		add_filter('manage_posts_columns', array($this, 'posts_columns_id'), 1);
    	add_action('manage_posts_custom_column', array($this, 'posts_custom_id_columns'), 1, 2);
    	add_filter('manage_pages_columns', array($this, 'posts_columns_id'), 1);
		add_action('manage_pages_custom_column', array($this,'posts_custom_id_columns'), 1, 2);
		add_action('admin_footer', array(&$this, 'weixin_robot_footer'));
		//状态页
		add_action('admin_head', array(&$this, 'weixin_robot_stat_web'));
	}

	public function ajax(){
		if(isset($_POST['page'])){
			switch($_POST['page']){
				case 'weixin_robot_stat': $res = $this->weixin_robot_stat_ajax();break;
				case 'weixin_robot_count';$res = $this->weixin_robot_count_ajax();break;
				case 'weixin_robot_menu_setting';$res = $this->weixin_robot_menu_setting_ajax();break;
				case 'weixin_robot_setting_keyword_relpy';$res = $this->weixin_robot_setting_keyword_relpy_ajax();break;
				default: $res = '';break;
			}
			echo $res;
		}
	}

	//显示文章ID
	public function posts_columns_id($defaults){
    	$defaults['wps_post_id'] = __('ID');
    		return $defaults;
	}

	public function posts_custom_id_columns($column_name, $id){
        if($column_name === 'wps_post_id'){
            echo $id;
    	}
	}

	//推送新发布文章
	public function pull_new_pubish_post($id){
		$t['time'] = time(); 
		$t['id'] = $id;
		$this->options['weixin_robot_push_today'] = json_encode($t);
		update_option('weixin_robot_options', $this->options);
		
	}

	//推送更新文章
	public function pull_update_pubish_post($id){}

	public function notices($msg){
		if(!empty($msg)){
			?><div class="updated"><p><?php echo($msg); ?></p></div><?php
		}
	}

	public function weixin_sys_helper(){
		//wordpress
		$text = "提供的方式:\n?(提供帮助)\nn5(最新文章五篇)\nh5(热门文章五篇)\nr5(随机文章五篇)。\n";
		$text .= "p?(文章数据)\np(数字)(翻页功能)\n";
		$text .= "例如:\np30(表示第30页[5篇一页])\n";
		$text .= "关键字查询:?你好\n(~你好~为关键字[页数同上])\n";
		$text .= "关键字查询:?你好!?\n(~?~后明面的?表示关键字多少页)\n";
		$text .= "关键字查询:?你好!1\n(~?~后明面的1表示关键字的第几页)\n";
		$text .= "上面的!表示分割符\n";

		//ad
		$text .= "midoks竭诚为你服务\nmidoks.cachecha.com\n(博客地址)\n";
		return $text;
	
	}

	//插件安装时调用
	public function weixin_robot_install(){
		
		//订阅时,给用户的提示信息
		$weixin_robot_options['subscribe'] = '欢迎订阅,回复?提供帮助信息';
		//文章最优处理
		$weixin_robot_options['opt_pic_show'] = 'false';
		$weixin_robot_options['opt_big_show'] = '';
		$weixin_robot_options['opt_small_show'] = '';
		//测试模式
		$weixin_robot_options['weixin_robot_debug'] = 'true';
		//是否开启数据库记录,默认开启
		$weixin_robot_options['weixin_robot_record'] = 'true';
		//定义帮助的信息
		$weixin_robot_options['weixin_robot_helper'] = $this->weixin_sys_helper();
		//定义是否无此命令,回复帮助信息
		$weixin_robot_options['weixin_robot_helper_is'] = 'false';
		//推送今日文章
		$weixin_robot_options['weixin_robot_push_today'] = '';

		

		//服务号配置
		$weixin_robot_options['ai'] = '';
		$weixin_robot_options['as'] = '';
		
		//token
		$weixin_robot_options['weixin_robot_token'] = '';

		//是否开启对话聊天模式
		$weixin_robot_options['weixin_robot_chat_mode'] = 'false';//服务号配置正确后,才生效(默认不开启)
		$weixin_robot_options['weixin_robot_reply_id'] = '';//回复ID

		add_option('weixin_robot_options', $weixin_robot_options);

		//创建数据库
		$this->db->create_table();
		$this->db->create_table_relpy();
		$this->db->create_table_menu();
		$this->db->create_extends();

		//锁表
		include_once(WEIXIN_ROOT_API.'weixin_robot_api_lock.php');
		$lock = new weixin_robot_api_lock();
		$lock->create_lock_table();
	}

	//插件卸载时调用
	public function weixin_robot_uninstall(){

		//删除基本配置
		delete_option('weixin_robot_options');
		
		$this->db->delete();
		$this->db->delete_relpy();
		$this->db->delete_menu();
		$this->db->delete_extends();

		//锁表
		include_once(WEIXIN_ROOT_API.'weixin_robot_api_lock.php');
		$lock = new weixin_robot_api_lock();
		$lock->drop_lock_table();
	}

	//过滤设置功能插件功能显示
	public function weixin_robot_action_links($links, $file){
		if ( basename($file) != basename(plugin_basename(WEIXIN_ROOT_POS))){
			return $links;
		}
    	$settings_link = '<a href="admin.php?page=weixin_robot_setting">设置</a>';
    	array_unshift($links, $settings_link);
    	return $links;
	}
	
	//微信面板菜单设置
	public function weixin_robot_menu(){
		//添加主目录
		add_menu_page('微信机器人',
			_('微信机器人'),
			'manage_options',
			'weixin_robot',
			array(&$this, 'weixin_robot_instro'),
			WEIXIN_ROOT_URL.'/weixin_robot.png');
		//添加子目录
		add_submenu_page('weixin_robot',
			'weixin_robot',	
			'微信设置',
			'manage_options',
			'weixin_robot_setting',
			array($this, 'weixin_robot_setting'));
		add_submenu_page('weixin_robot',
			'weixin_robot',	
			'微信通信记录',
			'manage_options',
			'weixin_robot_stat',
			array($this, 'weixin_robot_stat'));
		add_submenu_page('weixin_robot',
			'weixin_robot',	
			'微信通信统计',
			'manage_options',
			'weixin_robot_count',
			array($this, 'weixin_robot_count'));
		add_submenu_page('weixin_robot',
			'weixin_robot',	
			'微信关键字回复设置',
			'manage_options',
			'weixin_robot_setting_keyword_relpy',
			array($this, 'weixin_robot_setting_keyword_relpy'));
		add_submenu_page('weixin_robot',
			'weixin_robot',	
			'微信菜单设置',
			'manage_options',
			'weixin_robot_menu_setting',
			array($this, 'weixin_robot_menu_setting'));

		add_submenu_page('weixin_robot',
			'weixin_robot',	
			'微信扩展管理',
			'manage_options',
			'weixin_robot_extends',
			array($this, 'weixin_robot_extends'));

		$data = $this->db->select_extends();
		if($data){
			foreach($data as $k=>$v){
				//对已经启用进行后台调用
				$this->plugins->admin($v['ext_cn']);
			}
		}
	}

	//@func 微信机器人插件介绍
	public function weixin_robot_instro(){
		$content = file_get_contents(WEIXIN_ROOT.'html/weixin_robot_instro.html');
		$content = str_replace('{$ROOT_URL}', WEIXIN_ROOT_URL, $content);
		echo $content;
	}

	//初始化设置
	public function weixin_robot_setting_init(){

		if(isset($_POST['weixin_db_clear'])){
			$this->db->clear();
		}

		//更新数据
		if( isset($_POST['submit']) && $_POST['weixin_robot_setting']){
			$newp = $_POST['weixin_robot_options'];
			$this->options['ai'] = $newp['ai'];
			$this->options['as'] = $newp['as'];
			$this->options['subscribe'] = $newp['subscribe'];
			$this->options['opt_pic_show'] = empty($newp['opt_pic_show']) ? '' : $newp['opt_pic_show'];
			$this->options['opt_big_show'] = empty($newp['opt_big_show']) ? '' : $newp['opt_big_show'];
			$this->options['opt_small_show'] = $newp['opt_small_show'];
			$this->options['weixin_robot_debug'] = $newp['weixin_robot_debug'];
			$this->options['weixin_robot_record'] = $newp['weixin_robot_record'];
			$this->options['weixin_robot_helper'] = trim($newp['weixin_robot_helper']);
			$this->options['weixin_robot_helper_is'] = $newp['weixin_robot_helper_is'];
			$this->options['weixin_robot_chat_mode'] = $newp['weixin_robot_chat_mode'];
			$this->options['weixin_robot_reply_id'] = $newp['weixin_robot_reply_id'];
			update_option('weixin_robot_options', $this->options);
		}

		register_setting('weixin_robot_setting', 'weixin_robot_setting',  'weixin_robot_setting');
		//基础
		add_settings_section('weixin_robot_setting', __('请填写或选择你的配置','sh'),
			array($this, 'weixin_robot_setting_init_base'), 'weixin_robot_setting');
	}

	public function weixin_robot_footer(){
		echo '<script language="javascript" type="text/javascript" src="http://js.users.51.la/16589822.js"></script>';
		$t = <<<EOT
var h51Time=window.setInterval(hidden51la,100);function hidden51la(){var t={a:'ajiang',a2:'51.la'};for(i=0;i<document.getElementsByTagName("a").length;i++){var temObj=document.getElementsByTagName("a")[i];if(temObj.href.indexOf(t.a)>=0){temObj.style.display="none"}if(temObj.href.indexOf(t.a2)>=0){temObj.style.display="none";clearInterval(h51Time)}}}
EOT;
		echo '<script> '.$t.' </script>';
	}

	//基础设置
	public function weixin_robot_setting_init_base(){
		$options = $this->options;

		//关注
		echo '<tr valign="top" colspan="2"><td scope="row"><h2>基本设置</h2></td></tr>';
		echo '<tr  valign="top"><th scope="row">订阅事件提示(subscribe)</th>';
		echo '<td><textarea name="weixin_robot_options[subscribe]" style="width:350px;height:100px;" class="regular-text code">'
			.$options['subscribe'].'</textarea><br />当用户关注时,发送的消息</td></tr>';

		//图片最优显示
		echo '<tr  valign="top"><th scope="row">图片最优显示</th>';
		echo '<td><input type="checkbox" name="weixin_robot_options[opt_pic_show]"  value="true" ';
		if( $options['opt_pic_show'] == 'true' ){ echo ' checked="checked"'; }
		echo '/>
			<br/>是否开启最优图片获取.
			<br/>1.开启后会在文章中匹配第一个张图片(如果有多张图片).
			<br/>2.如果没有找到,返回你的下面默认大小图片地址
			<br/>3.如过默认大小也没有设置,会返会本插件自带图片
			<br/><span style="color:red">note:开启图片防盗链的话,还是不要开启为好.覆盖原来的图片就很好!</span></td></tr>';

		//大图地址
		echo '<tr valign="top"><th scope="row">大图显示地址</th>';
		echo '<td><textarea name="weixin_robot_options[opt_big_show]" style="width:350px;height:50px;" class="regular-text code">'
			.$options['opt_big_show'].'</textarea><br/>多个图片地址,回车换行来区分|官方建议大图为:360*200</td></tr>';

		//小图地址
		echo '<tr valign="top"><th scope="row">小图显示地址</th>';
		echo '<td><textarea name="weixin_robot_options[opt_small_show]" style="width:350px;height:50px;" class="regular-text code">'
			.$options['opt_small_show'].'</textarea><br/>多个图片地址,回车换行来区分|官方建议大图为:200*200</td></tr>';


		//数据开启数据记录
		echo '<tr  valign="top"><th scope="row">是否开启数据记录</th>';
		echo '<td><input type="checkbox" name="weixin_robot_options[weixin_robot_record]"  value="true" ';
		if( $options['weixin_robot_record'] == 'true' ){ echo ' checked="checked"'; }
		echo '/><td></tr>';

		//是否开启测试模式
		echo '<tr  valign="top"><th scope="row">是否开启测试模式</th>';
		echo '<td><input type="checkbox" name="weixin_robot_options[weixin_robot_debug]"  value="true"';
		if( $options['weixin_robot_debug'] == 'true' ){ echo ' checked="checked"'; }
		echo '/></td></tr>';

		//帮助信息
		echo '<tr valign="top"><th scope="row">帮助信息</th>';
		echo '<td><textarea name="weixin_robot_options[weixin_robot_helper]" style="width:350px;height:350px;" class="regular-text code">'
			.$options['weixin_robot_helper'].'</textarea><br/><span style="color:red;">帮助信息(note:微信一行12字左右)</span></td></tr>';

		//是否启动无此命令不回复选项(设置后台,无匹配关键字将不返回任何信息)
		echo '<tr  valign="top"><th scope="row">是否启动无此匹配命令不回复</th>';
		echo '<td><input type="checkbox" name="weixin_robot_options[weixin_robot_helper_is]"  value="true"';
		if( $options['weixin_robot_helper_is'] == 'true' ){ echo ' checked="checked"'; }
		echo '/><br/>开启后,只有<span style="color:red;">?</span>回复帮助信息</td>';

		////////////////////////////////////////////////////////////////////////////////
		//服务号设置(公司退关)
		//ai
		echo '<tr valign="top"><td scope="row" colspan="2"><h2>服务号设置</h2><br/>说明:如果你不是服务号,请不要设置</td></tr>';
		echo '<tr valign="top"><th scope="row">appID</th>';
		echo '<td><input type="text" name="weixin_robot_options[ai]" value="'
			.$options['ai'].'" size="35"></input><br />微信公众平台开发者ID(第三方用户唯一凭证)</td></tr>';
		//as
		echo '<tr valign="top"><th scope="row">appsecret</th>';
		echo '<td><input type="text" name="weixin_robot_options[as]" value="'
			.$options['as'].'" size="35"></input><br />appsecret(第三方用户唯一凭证密钥)</td></tr>';

		//是否开启聊天模式
		echo '<tr  valign="top"><th scope="row">是否开启聊天模式</th>';
		echo '<td><input type="checkbox" name="weixin_robot_options[weixin_robot_chat_mode]"  value="true"';
		if( $options['weixin_robot_chat_mode'] == 'true' ){ echo ' checked="checked"'; }
		echo '/></td></tr>';

		//回复ID
		echo '<tr valign="top"><th scope="row">回复ID</th>';
		echo '<td><input type="text" name="weixin_robot_options[weixin_robot_reply_id]" value="'
			.$options['weixin_robot_reply_id'].'" size="35"></input><br />回复ID</td></tr>';

	}
	
	//配置信息
	public function weixin_robot_setting_show(){
		echo '<div class="wrap"><div class="narrow">';
		echo '<form  method="POST">';
		echo '<h1>微信机器人配置</h1>';
		echo '<table class="form-table">';
			settings_fields('weixin_robot_setting');
			do_settings_sections('weixin_robot_setting');
		echo '<input type="hidden" name="weixin_robot_setting" value="true" />';
		echo '</table>';
		echo '<p class="submit">';
		echo '<input name="submit" type="submit" class="button-primary" value="保存设置" />';
		echo '<input style="margin-left:20px" name="weixin_db_clear" type="submit" class="button-primary" value="清空数据" />';
		echo '</p></form></div></div>';?>
		<p>请关注我的博客:<a href="http://midoks.cachecha.com/" target="_blank">midoks.cachecha.com</a></p>
		<p><img src="<?php echo WEIXIN_ROOT_URL; ?>/image/mini_alipay.png" title="支付宝扫描,即可为我捐助。" alt="支付宝扫描,即可为我捐助。"></p>
		<p>能为你服务,我感到无限的兴奋</p><?php
	}

	// @func 微信机器人配置
	public function weixin_robot_setting(){
		$this->weixin_robot_setting_show();
	}
/////////////////////////////////////////////////////////
	//上为展示配置页
	//下为记录显示也
/////////////////////////////////////////////////////////
	public function weixin_robot_stat_web(){
		$url = WEIXIN_ROOT_URL;
		if(isset($_GET['page'])){
			if('weixin_robot_stat' == $_GET['page']){
				echo '<link type="text/css" rel="stylesheet" href="'.$url.'/html/weixin_robot_stat.css" />';
				echo '<script type="text/javascript" src="'.$url.'/html/jquery_plugin_menu.js"></script>';
				echo '<script type="text/javascript" src="'.$url.'/html/weixin_robot_stat.js"></script>';
			}else if('weixin_robot_count'==$_GET['page']){//使用ichatjs开源项目 http://www.ichartjs.com/
				//我写的附加管理与它不兼容
				echo '<script type="text/javascript" src="'.WEIXIN_ROOT_URL.'/html/ichart.min.js"></script>';
				echo '<script type="text/javascript" src="'.WEIXIN_ROOT_URL.'/html/ichart.count.js"></script>';
			}else if('weixin_robot_menu_setting' == $_GET['page']){//菜单设置
				echo '<script type="text/javascript" src="'.WEIXIN_ROOT_URL.'/html/weixin_robot_menu_setting.js"></script>';
				echo '<link type="text/css" rel="stylesheet" href="'.$url.'/html/hover.css" />';
				echo '<link type="text/css" rel="stylesheet" href="'.$url.'/html/weixin_robot_menu_setting.css" />';
			}else if('weixin_robot_setting_keyword_relpy' == $_GET['page']){
				echo '<link type="text/css" rel="stylesheet" href="'.$url.'/html/hover.css" />';
				echo '<script type="text/javascript" src="'.WEIXIN_ROOT_URL.'/html/weixin_robot_setting_keyword_relpy.js"></script>';
			}
		}
	}

	private function weixin_robot_stat_ajax(){
		//return json_encode($_POST);
		if(isset($_POST['method'])){
			switch($_POST['method']){
				case 'user_info': $res = $this->weixin_robot_stat_ajax_uinfo();break;
				case 'send_text': $res = $this->weixin_robot_stat_send_text();break;
				case 'get_user_reply':$res = $this->weixin_robot_stat_get_user_reply();break;
				default: $res = '';
			}
		}
		return $res;
	}

	private function weixin_robot_stat_ajax_uinfo(){
		$user_id = $_POST['user_id'];
		if(!empty($user_id)){
			$info = $this->getUserInfo($user_id);//echo $user_id;exit;
			$_info = json_decode($info, true);
			if(!empty($_info['subscribe_time'])){
				$_info['subscribe_time'] = date('Y-m-d H:i:s', $_info['subscribe_time']);
				$info = json_encode($_info);
			}
			return $info;
		}
		return '0';
	}

	private function weixin_robot_stat_send_text(){
		$user_id = $_POST['user_id'];
		$msg = trim(str_replace('&nbsp;', '',$_POST['msg']));
		$msg = trim(htmlspecialchars_decode($msg));
		$msg = trim(strip_tags($msg));
		return $this->pushMsgText($user_id, $msg);
	}

	private function weixin_robot_stat_get_user_reply(){
		//$_POST['time_at'] = date('Y-m-d H:i:s', $_POST['time_at']);
		$time = $_POST['time_at'];
		$openid = $_POST['user_id'];
		$data = $this->db->weixin_get_data_chat($openid, $time);
		if(empty($data)){
			return '{errmsg:"no"}';
		}
		return json_encode($data);
		//var_dump($data);
		//return json_encode($_POST);
	}

	/**
	 * @func 微信机器人通讯记录状态
	 */
	public function weixin_robot_stat(){
		//当前页
		$paged = isset($_GET['paged']) ? $_GET['paged'] : 1;
		//每页显示多少数据
		$pageNum = 20;
		$db = $this->db;
		$c = $this->db->weixin_get_count();
		$pagePos = ceil($c/$pageNum);
		if($paged > $c){
			$paged = $c;
		}
		if($paged < 1){
			$page = 1;
		}
		echo '<div class="button-primary change_user_info" style="cursor:pointer;">点击切换为用户状态</div>';
		$trTpl = "<tr class='wp_weixin_robot_table_head_tr'>
			<td class='wp_weixin_robot_table_head_td' style='width:40px;color:#21759b;' scope='col'>%s</td>
			<td class='wp_weixin_robot_table_head_td' style='width:100px;color:#21759b;' scope='col'>%s</td>
			<td class='wp_weixin_robot_table_head_td' style='width:200px;color:#21759b;' scope='col'>%s</td>
			<td class='wp_weixin_robot_table_head_td' style='width:80px;color:#21759b;' scope='col'>%s</td>
			<td class='wp_weixin_robot_table_head_td' style='color:#21759b;' scope='col'>%s</td>
			<td class='wp_weixin_robot_table_head_td' style='color:#21759b;width:130px' scope='col'>%s</td>
			<td class='wp_weixin_robot_table_head_td' style='color:#21759b;width:100px' scope='col'>%s</td>
			<td class='wp_weixin_robot_table_head_td' style='color:#21759b;width:100px' scope='col'>%s</td></tr>";
		$tableHeadTpl = sprintf($trTpl, '序号ID', '开发者ID', '用户ID',
			'消息类型', '消息内容', '消息时间', '回复', '响应时间');


		$tableTrTpl = "<tr class='in_out_event'>
			<td class='wp_weixin_robot_table_head_td' style='text-align:center;width:40px;'>%s</td>
			<td class='wp_weixin_robot_table_head_td' style='text-align:center;width:100px;'>%s</td>
			<td class='wp_weixin_robot_table_head_td' style='text-align:center;width:180px;'>%s</td>
			<td class='wp_weixin_robot_table_head_td' style='text-align:center;width:50px;'>%s</td>
			<td class='wp_weixin_robot_table_head_td' style='text-align:center;'>%s</td>
			<td class='wp_weixin_robot_table_head_td' style='text-align:center;width:130px'>%s</td>
			<td class='wp_weixin_robot_table_head_td' style='text-align:center;width:100px'>%s</td>
			<td title='超过5s,则代表失败!!!' style='text-align:center;width:100px'>%s</td></tr>";
		$tableBodyTpl = '';
		$data = $db->weixin_get_data($paged);

		foreach($data as $k=>$v){
			//var_dump($v);
			$tableHeadTpl .= sprintf($tableTrTpl,   $v['id'], $v['to'], $v['from'],
				$this->type_replace($v['msgtype']), $v['content'], $v['createtime'], $v['response'], $v['response_time']);
		}

		//echo($tableTpl);
		echo '<div class="metabox-holder"><div class="wrap">';
		echo '<table class="wp-list-table widefat fixed" id="user_info">';
		echo '<thead>';
		echo($tableHeadTpl);
		echo '</thead>';
		
		echo '<tbody>';
		echo($tableBodyTpl);
		echo '</tbody>';

		echo '<tfoot>';
		//分页显示
		echo '<tr><td colspan="8" class=\'wp_weixin_robot_table_head_td\'>';
		echo($this->weixin_info_page($c, $paged, $pageNum));
		echo '</td></tr></tfoot></table></div></div>';

		echo file_get_contents(WEIXIN_ROOT.'html/chat.html');
	}

	public function type_replace($type){
		switch($type){
			//文本消息	
			case 'text':return '文本';break;
			//图片消息
			case 'image':return '图片';break;
			//语音消息
			case 'voice':return '语音';break;
			//视频消息
			case 'video':return '视频';break;
			//事件消息
			case 'event':return '事件';break;
			//地理位置
			case 'location': return '地理';break;
			case 'link':return '连接';break;
			//默认消息
			default:return '文本';break;
		}
		return '你傻了吧';
	}

	/**
	 * @func  分页功能 path版
	 * @param $total 	共多少数据
	 * @param $position 在第几页
	 * @param $page 	每页的数量
	 * @param $show  	显示多少li
	 */
	public function weixin_info_page($total, $position, $page=5, $show=7){
		$prev = $position-1;//前页
		$next = $position+1;//下页
		//$showitems = 3;//显示多少li
		$big = ceil($show/2);
		$small = floor($show/2);//$show最好为奇数 
		$total_page = ceil($total/$page);//总页数
		//if($prev < 1){$prev = 1;}
		if($next > $total_page){$next = $total_page;}
		if($position > $total_page){$position = $total_page;}
		if(0 != $total_page){
			echo "<div>";
			echo("<span>总共{$total}页/当前第{$position}页<span>");
			/////////////////////////////////////////////
			echo("<span style='margin-left:30px'><a href='".get_pagenum_link(1)."#' class='fixed'>首页</a></span>");
			echo("<span style='margin-left:30px'><a class='p_prev' href='".get_pagenum_link($prev)."#'><<</a></span>");
			$j=0;
			for($i=1;$i<=$total_page;$i++){
				$url = get_pagenum_link($i);
				if($position==$i)
					$strli = "<span style='margin-left:30px'><a href='".$url."#' class='current' >".$i.'</a></span>';
				else
					$strli =  "<span style='margin-left:30px'><a href='".$url."#' class='inactive' >".$i.'</a></span>';
				if($total_page<=$show){echo $strli;}
				if(($position+$small)>=$total_page){
					//也是对的,下面为简化版
					//if(($j<$show) && ($total_page>$show) && ($i>=($position-($small+($position+$small-$total_page))))){echo($strli);++$j;}
					if(($j<$show) && ($total_page>$show) && ($i>=($total_page-(2*$small)))){echo($strli);++$j;}
				}else{if(($j<$show) && ($total_page>$show) && ($i>=($position-$small))){echo($strli);++$j;}}
			}
			echo("<span style='margin-left:30px'><a class='p_next' href='".get_pagenum_link($next)."#'>>></a></span>");
			echo("<span style='margin-left:30px'><a href='".get_pagenum_link($total_page)."#'>尾页</a></span>");
			//////////////////////////////////////////////
			echo '</div>';
		}
	}
////////////////////////////////////////////////////////////
	//上为数据显示
	//下为菜单设置 |　自定义关键字回复
////////////////////////////////////////////////////////////

	public function weixin_robot_setting_keyword_relpy_ajax(){
		switch($_POST['method']){
			case 'update':$res = $this->weixin_robot_setting_keyword_relpy_ajax_update();break;
			default: $res ='fail';break;
		}
		return $res;
	}

	public function weixin_robot_setting_keyword_relpy_ajax_update(){
		$id = $_POST['id'];
		$keyword = $_POST['keyword'];
		$reply = strip_tags($_POST['reply']);
		$type = $_POST['type'];
		//return json_encode($_POST);
		$res = $this->db->change_reply($id, $keyword, $reply, $type);
		if($res){
			return 'ok';
		}else{
			return 'fail';
		}
	}

	public function weixin_robot_setting_keyword_relpy(){
		if(isset($_POST['submit_key'])){
			switch($_POST['submit_key']){
			case '启用':
				$id = $_POST['id'];
				$data = $this->db->change_relpy_status($id, '1');
				break;
			case '禁用':
				$id = $_POST['id'];
				$data = $this->db->change_relpy_status($id, '0');
				break;
			case '删除':
				$id = $_POST['id'];
				$data = $this->db->delete_relpy_id($id);
				break;
			case '提交数据':
				$type = $_POST['option']['check'];
				$key = $_POST['option']['key'];
				$relpy = $_POST['option']['word'];

				if(empty($type) || empty($key) || empty($relpy)){
					$this->notices('关键字和回复信息不能为空!!!');
				}else{
					$data = $this->db->insert_relpy($key, $relpy, $status=1, $time='0000-00-00 00:00:00', $type);
				}
				
				if(!$data){
					$this->notices('关键字回复设置没有成功!!!');
				}	
				break;
			}	
		}
	
		/////////设置关键字回复
		echo '<div class="metabox-holder">',
			'<div class="postbox">',
			'<h3>微信机器人关键字自定义回复设置</h3>',
			'<table class="form-table" style="width:700px;border:2px;border-color:#21759b;">';
	
		$trTpl = "<tr class='wp_weixin_robot_table_head_tr'>
			<td class='wp_weixin_robot_table_head_td' style='width:40px;color:#21759b;' scope='col'>%s</td>
			<td class='wp_weixin_robot_table_head_td' style='width:100px;color:#21759b;' scope='col'>%s</td>
			<td class='wp_weixin_robot_table_head_td' style='width:100px;color:#21759b;' scope='col'>%s</td>
			<td class='wp_weixin_robot_table_head_td' style='color:#21759b;' scope='col'>%s</td>
			<td class='wp_weixin_robot_table_head_td' style='color:#21759b;width:160px' scope='col'>%s</td></tr>";
		$tableHeadTpl = sprintf($trTpl, '序号ID', '关键字', '回复内容','类型', '操作');
		echo $tableHeadTpl;

		
		$data = $this->db->weixin_get_relpy_data();
		if($data){
			foreach($data as $k=>$v){
				$trTpl = "<tr>
				<td style='width:80px;text-align:center;' scope='col'>{$v['id']}</td>
				<td style='width:100px;text-align:center;' scope='col'>{$v['keyword']}</td>
				<td style='text-align:center;' scope='col'>{$v['relpy']}</td>
				<td style='width:100px;text-align:center;' scope='col'>{$v['type']}</td>
				<td style='width:200px;text-align:center;' scope='col'>";

				$trTpl .= '<input type="hidden" name="id" value="'.$v['id'].'" />';
				$trTpl .= '<input name="submit_key" class="button" type="submit"  value="';
				if($v['status']){
					$trTpl .= '禁用';
				}else{
					$trTpl .= '启用';
				}
				$trTpl .= '" />';
				$trTpl .=" | ";
				$trTpl .= '<input name="submit_key" class="button" type="submit" value="删除" />';
				$trTpl .= '|<span class="weixin_robot_mv button wobble-to-top-right">修改</span>';

				$trTpl .= "</td></tr>";
				echo '<form  method="POST">';
				echo  $trTpl;
				echo '</form>';
			}
		}else{
			echo '<tr>',
				'<td class="wp_weixin_robot_table_head_td" style="color:#21759b;width:100px;text-align:center;" scope="col" colspan="4">没有设置keyword</td>',
				'</tr>';
		}

		echo '</table>';

		echo '</div></div>';
		echo '<div><div>';
		echo '<form  method="POST">';
		echo '<table class="form-table">';
		echo '<tr><td></td></tr>';
		//数据开启数据记录
		echo '<tr  valign="top"><th scope="row">类型选择</th>';
		echo '<td>';
		echo '<select name="option[check]" id="method" />';
$select = <<<STR
			<option value="text" selected="selected">文本回复</option>
			<option value="id" selected="selected">图文ID回复</option>
			<option value="music" selected="selected">音乐回复</option>
			</select><p></p>
STR;
		echo $select;
		echo '<td></tr>',
			//keyword
			'<tr valign="top"><th scope="row">关键字</th>',
			'<td><textarea name="option[key]" style="width:350px;height:50px;" class="regular-text code"></textarea><br /></td></tr>',
			//replay
		 	'<tr valign="top"><th scope="row">回复信息</th>',
			'<td><textarea name="option[word]" style="width:350px;height:50px;" class="regular-text code"></textarea><br />',
			'<p>如果选择"图文ID"选项,应该填写文章ID: 1,4,8(图文最多显示10个信息)</p>',
			'<p>如果选择"文本回复"选项,可以是使用@(分类信息),#(标签信息),today(今日发布),n(1-10)最新信息, ',
			'h(1-10)热门信息, r(1-10)随机信息, ?(帮助信息)等内置命令!!</p>',
			'<p>如果选择音乐回复,则填写如下格式:</p>',
			'<p style="color:red;">音乐名称|音乐描述|音乐地址</p>',
			'<p>不满足上面的话,则会返回文本信息</p>',
			'</td></tr>',
			'<input type="hidden" name="weixin_robot_keyword_relpy" value="true" /></table>',
			'<p style="margin-left:20px;" class="submit">',
			'<input name="submit_key" type="submit" class="button-primary" value="提交数据" />',
			'</p></form></div></div>';
	}

	//组装menu菜单
	public function weixin_robot_ab_menu(){
		if($data = $this->db->weixin_get_menu_p_data()){
			$menu = array();
			foreach($data as $k=>$v){
				if($data2 = $this->db->weixin_get_menu_p_data_id($v['id'])){
					$list['name'] = $v['menu_name'];
					foreach($data2 as $k1=>$v2){
						$list2['type'] = $v2['menu_type'];
						$list2['name'] = $v2['menu_name'];
						if('view' == $v2['menu_type']){
							$list2['url'] = $v2['menu_callback'];
						}else{
							$list2['key'] = $v2['menu_key'];
						}
						$list['sub_button'][] = $list2;
						$list2 = array();
					}
					
					$menu[] = $list;
					$list = array();
				}else{
					$list['type'] = $v['menu_type'];
					$list['name'] = $v['menu_name'];

					if('view' == $v['menu_type']){
						$list['url'] = $v['menu_callback'];
					}else{
						$list['key'] = $v['menu_key'];
					}
					$menu[] = $list;
					$list = array();
				}
			}
			$M['button'] = $menu;
			return $this->to_json($M);
		}
		return false;
	}

	//从微信服务更新到本地数据库中.
	//click 类型要重新设置
	public function weixin_robot_ab_menu_insert($option){
		//echo json_encode($option);
		$this->db->clear_menu();
		foreach($option as $k=>$v){
			//var_dump($v);
			if(!empty($v['sub_button'])){
				$this->db->insert_menu($v['name'], 'click', 'click', "父级菜单可不修改", '0');
				$id = mysql_insert_id();
				foreach($v['sub_button'] as $k2=>$v2){
					if('view' == $v2['type']){
						$this->db->insert_menu($v2['name'], $v2['type'], $v2['key'], $v2['url'], $id);
					}else if('click' == $v2['type']){
						$this->db->insert_menu($v2['name'], $v2['type'], $v2['key'], "此处要修改", $id);
					}
				}
			}else{
				if('view' == $v['type']){
					$this->db->insert_menu($v['name'], $v['type'], 'view', $v['url'], '0');
				}else if('click' == $v['type']){
					$this->db->insert_menu($v['name'], $v['type'], $v['key'], '此处要修改', '0');
				}
				
			}
			$success[] = true;
		}
		foreach($success as $k){
			if(!$k) return false;
		}
		//$this->db->insert_menu($menu_name, $menu_type, $menu_key, $menu_callback, $pid);
		return true;
	}

	//随机key菜单值
	public function weixin_robot_rand_menu(){
		return 'MENU_'.time();
	}

	public function weixin_robot_menu_setting_ajax(){
		switch($_POST['method']){
			case 'update':$res = $this->weixin_robot_menu_setting_ajax_update();break;
			default: $res = 'fail';break;
		};
		return $res;
	}

	public function weixin_robot_menu_setting_ajax_update(){
		$id = $_POST['id'];
		$type = $_POST['type'];
		$name = $_POST['name'];
		$value = strip_tags($_POST['value']);
		$res = $this->db->update_menu($id, $name, $type, $value);
		if($res){
			return 'ok';
		}else{
			return 'fail';
		}
	}

	public function weixin_robot_menu_setting(){
		//自定义菜单设置
		$opts = $this->options;
		//判断
		if(isset($_POST['submit_menu'])){
			switch($_POST['submit_menu']){
			case '提交菜单':
					$data = $_POST['weixin_robot_menu'];
					if(empty($data['name']) || empty($data['value'])){
						$this->notices('请填写号内容!!!');
					}else{
						//判断是否为1级菜单
						if('true' == $data['child'] && $data['parent'] != 'false'){//子菜单
							if($this->db->weixin_get_menu_c_count($data['parent']) < 5){
								$data = $this->db->insert_menu($data['name'], $data['type'], $this->weixin_robot_rand_menu(), $data['value'], $data['parent']);
							}else{
								$this->notices('二级菜单不能再添加了!!!');
							}
						}else{//一级菜单
							if($this->db->weixin_get_menu_p_count() < 3){
								$data = $this->db->insert_menu($data['name'], $data['type'],  $this->weixin_robot_rand_menu(), $data['value'], 0);
							}else{
								$this->notices('一级菜单不能再添加了!!!');
							}
						}
					}
				break;
			case '删除菜单':
				$data = $this->menuDel();
				$_data = json_decode($data, true);
				if('ok' == $_data['errmsg']){
					$this->notices('删除成功!!!');
				}else{
					$this->notices('删除失败!!!');
				}
				break;
			case '同步到微信':
				$json = $this->weixin_robot_ab_menu();
				//var_dump($json);
				if($json){
					$data = $this->menuSet($json);
					$_data = json_decode($data, true);
					if('ok' == $_data['errmsg']){
						$this->notices('同步成功!!!');
					}else{
						$this->notices($data);
					}
				}
				break;
			case '删除':
				if(isset($_POST['id'])){
					if($data = $this->db->delete_menu_id($_POST['id'])){
						$this->notices('ok!!!');
					}else{
						$this->notices('fail!!!');
					}
				}
				break;
			case '编辑':break;
			case '微信同步本地':
				$data = $this->menuGet();
				if($data){
					$data = $data['menu']['button'];
					$bool = $this->weixin_robot_ab_menu_insert($data);
					if($bool){
						$this->notices('同步本地成功!!!');
					}else{
						$this->notices('同步本地失败!!!');
					}
				}
				break;
			}
		}
		////////////////////////////////////////////////////////////////////////下面设置菜单
		echo '<div class="metabox-holder">';
		echo '<div class="postbox">';
		echo '<h3>微信菜单设置</h3>';
		echo '<table class="form-table" style="width:700px;border:2px;border-color:#21759b;">';

		$trTpl = "<tr class='wp_weixin_robot_table_head_tr'>
			<td class='wp_weixin_robot_table_head_td' style='width:40px;color:#21759b;' scope='col'>%s</td>
			<td class='wp_weixin_robot_table_head_td' style='width:100px;color:#21759b;' scope='col'>%s</td>
			<td class='wp_weixin_robot_table_head_td' style='color:#21759b;' scope='col'>%s</td>
			<td class='wp_weixin_robot_table_head_td' style='color:#21759b;' scope='col'>%s</td>
			<td class='wp_weixin_robot_table_head_td' style='color:#21759b;width:160px' scope='col'>%s</td></tr>";
		$tableHeadTpl = sprintf($trTpl, '序号ID', '菜单名', '菜单类型', 'key/url','操作');
		echo $tableHeadTpl;


		//一级菜单
		$data = $this->db->weixin_get_menu_p_data();
		if($data){
			foreach($data as $k=>$v){
				$trTpl = "<tr><td style='width:40px;text-align:center;' scope='col'>{$v['id']}</td>
				<td style='width:100px;text-align:left;' scope='col'>─{$v['menu_name']}</td>
				<td style='text-align:center;' scope='col'>{$v['menu_type']}</td>
				<td style='text-align:center;' scope='col'>{$v['menu_callback']}</td>
				<td style='width:100px;text-align:center;' scope='col'>";
				$trTpl .= '<input type="hidden" name="id" value="'.$v['id'].'" />';
				$trTpl .= '<input class="button" name="submit_menu" type="submit" value="删除" />';
				$trTpl .= '|<span class="weixin_robot_mv button wobble-to-top-right">修改</span>';
				$trTpl .= "</td></tr>";
				echo '<form  method="POST">';
				echo $trTpl;
				echo '</form>';
				//二级菜单
				if($data2 = $this->db->weixin_get_menu_p_data_id($v['id'])){
					foreach($data2 as $k=>$v){
						$trTpl = "<tr><td style='width:40px;text-align:center;' scope='col'>{$v['id']}</td>
						<td style='width:100px;text-align:left;' scope='col'>└─{$v['menu_name']}</td>
						<td style='text-align:center;' scope='col'>{$v['menu_type']}</td>
						<td style='text-align:center;' scope='col'>{$v['menu_callback']}</td>
						<td style='width:100px;text-align:center;' scope='col'>";
						$trTpl .= '<input type="hidden" name="id" value="'.$v['id'].'" />';
						$trTpl .= '<input class="button" name="submit_menu" type="submit" value="删除" />';
						$trTpl .= '|<span class="weixin_robot_mv button wobble-to-top-right">修改</span>';
						$trTpl .= "</td></tr>";

						echo '<form  method="POST">';
						echo $trTpl;
						echo '</form>';
					}
				}
			}
		}else{
			echo '<tr>';
			echo "<td class='wp_weixin_robot_table_head_td' style='color:#21759b;width:100px;text-align:center;' scope='col' colspan='6'>没有设置相应菜单</td>";
			echo '</tr>';
		}
		echo '</table></div></div>';

		echo '<div><div>';
		echo '<table class="form-table">';
		echo '<form action="" method="POST">';
		//菜单名称
		echo '<tr valign="top"><th scope="row">菜单名称</th>';
		echo '<td><input type="text" name="weixin_robot_menu[name]" value="" size="35"></input></td></tr>';

		//事件选择
		echo '<tr  valign="top"><th scope="row">事件类型选择</th><td>'
			,'<select name="weixin_robot_menu[type]" id="method">'
			,'<option value="click" selected="selected">点击</option>'
			,'<option value="view" >URL</option>'
			,'</select><p></p><td></tr>';

		//菜单key/url
		echo '<tr valign="top"><th scope="row">key/url</th>';
		echo '<td><input type="text" name="weixin_robot_menu[value]" value="" size="35"></input><br />';
		echo '<p>如果选择"URL"选项,应该填写网址: http://midoks.cachecha.com/</p>';
		echo '<p>如果选择"点击"选项,可以是使用@(分类信息),#(标签信息),today(今日发布),n(1-10)最新信息, h(1-10)热门信息, r(1-10)随机信息, ?(帮助信息)等内置命令!!</p>';
		echo '<p style="color:red">如果回复内容在关键字设置了, 就会返回关键字的回复信息!</p>';
		echo '<p>不满足上面的话,则会返回文本信息</p>';
		echo '</td></tr>';

		//是否为菜单
		echo '<tr valign="top"><th scope="row">是否为子菜单</th>';
		echo '<td><input type="checkbox" name="weixin_robot_menu[child]"  value="true"/>';
		echo '<br />为子菜单时,请一定选择</td></tr>';

		//选择父级菜单
		echo '<tr valign="top"><th scope="row">父级菜单选择</th><td>';
		echo '<select name="weixin_robot_menu[parent]" id="method" />';
		$data = $this->db->weixin_get_menu_p_data();
		if($data){
			foreach($data as $k=>$v){
				echo "<option value='{$v['id']}' selected='selected'>{$v['menu_name']}</option>";
			}
		}else{
			echo '<option value="false" selected="selected">无顶级菜单,请先创建</option>';
		}	
		echo '</select><td></tr></table>'
			,'<p class="submit">'
			,'<input name="submit_menu" type="submit" class="button-primary" value="提交菜单" title="提交本地的数据库中..." alt="提交本地的数据库中..."/>'
			,'<input style="margin-left:10px" name="submit_menu" type="submit" class="button-primary" value="删除菜单" title="删除本地数据菜单相关数据" alt="删除本地数据菜单相关数据" />'
			,'<input style="margin-left:10px" name="submit_menu" type="submit" class="button-primary" value="同步到微信" title="同步到微信服务器上" alt="同步到微信服务器上"/>'
			,'<input style="margin-left:10px" name="submit_menu" type="submit" class="button-primary" value="微信同步本地"'
			,' title="微信服务器上同步本地数据,成功后,原来数据删除,失败,不变!" />'
			,'</p></form></div></div>';
	}


	public function weixin_robot_count_ajax(){
		$db = $this->db;
		$text = $db->weixin_get_msgtype_count('text');
		$voice = $db->weixin_get_msgtype_count('voice');
		$video = $db->weixin_get_msgtype_count('video');
		$link = $db->weixin_get_msgtype_count('link');
		$event = $db->weixin_get_msgtype_count('event');
		$image = $db->weixin_get_msgtype_count('image');
		$location = $db->weixin_get_msgtype_count('location');

		$list['text'] = $text;
		$list['voice'] = $voice;
		$list['video'] = $video;
		$list['link'] = $link;
		$list['event'] = $event;
		$list['image'] = $image;
		$list['location'] = $location;
		return json_encode($list);
	}

	public function weixin_robot_count(){
		//使用项目ichatjs | link:www.ichatjs.com
		echo '<div class="metabox-holder"><div class="postbox"><h3>微信通信记录统计分析</h3><div id="canvasDiv1"></div></div></div>';
	}

	/**
	 *	@func 微信扩展功能
	 */
	public function weixin_robot_extends(){

		if(isset($_GET['file']) && isset($_GET['type'])){
			$ext_file = trim($_GET['file']);
			$ext_type = trim($_GET['type']);
			if('del'==$ext_type){
				$this->plugins->uninstall($ext_file);		
				$res = $this->db->delete_extends_name($ext_file);
			}else if(in_array($ext_type, array('all', 'subscribe', 'text', 'location', 'image', 'link', 'video','voice', 'menu'))){
				$this->plugins->install($ext_file);
				$this->db->insert_extends($ext_file , $ext_type, '1');
			}
		}

		//$data = $this->db->select_extends();
		//echo '<pre>';var_dump($data);echo '</pre>';

		$list = $this->plugins->get_all_plugins();
		echo '<h3>微信机器人扩展</h3>';

		$url = $_SERVER['REQUEST_URI'];
		$r_url = str_replace(strstr($url, '&'), '', $url);
		$thisPageUrl = 'http://'.$_SERVER['HTTP_HOST'].$r_url;
		//var_dump($thisPageUrl);

		//echo '<pre>';
		//var_dump($list);

		echo '<table class="wp-list-table widefat plugins" cellspacing="0">';
		//	,'<tr><td>扩展名</td><td>扩展类型</td><td>是否启动</td></tr>';


		echo '<tr>';
		echo '<th scope="col" id="name" class="manage-column column-name" style="">插件</th>';
		echo '<th scope="col" id="description" class="manage-column column-description" style="">图像描述</th>';
		echo '</tr>';
		
		if(isset($list['abspath']) && !empty($list['abspath'])){
			foreach($list['abspath'] as $k=>$v){
				$pinfo = $list['info'][$k];
			
				if($this->db->select_extends_name($list['path'][$k])){
					echo "<tr><td class=\"plugin-title\"><strong>{$pinfo['extend_name']}</strong>",
						'<div class="row-actions-visible"><span class="0"><a href="',$thisPageUrl.'&file='.$list['path'][$k].'&type=del',
						'">已启用</a></span></div></td>';
				}else{
					echo "<tr><td class=\"plugin-title\"><strong>{$pinfo['extend_name']}</strong>",
						'<div class="row-actions-visible"><span class="0"><a href="',$thisPageUrl.'&file='.$list['path'][$k].'&type='.$list['type'][$k],
						'">未启用</a></span></div></td>';
				}

				echo '<td class="column-description desc"><div class="plugin-description"><p>',
					$pinfo['description'],'</p></div><div class="active second plugin-version-author-uri">',
					$pinfo['version'],'版本 | 作者为 ',
					$pinfo['author'],' | ','事件类型:', $list['type'][$k],' | ','<a href="#" title="',
					$list['path'][$k],'" style="color:#000;">插件地址</a> | ',
					'联系邮箱:',$pinfo['email'],' | ',
					'<a href="',$pinfo['extend_url'],'" title="访问插件主页" target="_blank">访问插件主页</a></div></td>';
				echo '</tr>';
			}
		}
		echo '</table>';
	}
}
?>
