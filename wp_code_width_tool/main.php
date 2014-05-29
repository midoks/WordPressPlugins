<?php
/**
	Plugin Name: 代码高亮(显示工具栏)
	Plugin URI: http://midoks.cachecha.com
	Description: 代码高亮并显示工具栏
	Version: 1.0
	Author: midoks
	Author URI: http://midoks.cachecha.com
	Email: midoks@163.com
**/

class syntax_highlighter{

	public $url;

	//架构函数
	public function __construct(){
			
		$this->url = plugin_dir_url(__FILE__);


		//本插件参数激活
		register_activation_hook(__FILE__, array($this, 'highlighter_activate'));

		
		add_action('admin_init', 'highlighter_admin_init');
		
		$this->home();
		$this->admin();
		
	}

	public function highlighter_activate(){
		$options['highlighter_style'] = 'Default';
		$options['highlighter_tagName'] = 'pre';
		$options['highlighter_autolinks'] = 'true';
		$options['highlighter_collapse'] = 'false';
		$options['highlighter_firstline'] = 1;
		$options['highlighter_gutter'] = 'true';
		$options['highlighter_smarttabs'] = 'true';
		$options['highlighter_tabsize'] = 4;
		$options['highlighter_toolbar'] = 'true';
		add_option('highlighter_options', $options);
	}


	public function home(){
		//主页添加资源
		add_action('wp_head', array($this, 'wp_css'));
		add_action('wp_footer',array($this, 'wp_js'));
	}

	public function wp_css(){
		//css
		echo '<link type="text/css" rel="stylesheet" href="'.$this->url.'SyntaxHighlighter/css/shCore.css" />';
		echo '<link type="text/css" rel="stylesheet" href="'.$this->url.'SyntaxHighlighter/css/shThemeDefault.css" />';
	}

	public function wp_js(){
		//js
		echo '<script type="text/javascript" src="'.$this->url.'SyntaxHighlighter/js/brush.js"></script>';
		echo '<script type="text/javascript">
				SyntaxHighlighter.config.clipboardSwf = "'.$this->url.'SyntaxHighlighter/image/clipboard_small.swf'.'";
				SyntaxHighlighter.all();
			  </script>';
	}

	public function admin(){

		add_action('init', array($this, 'highlighter_init'));
		add_action('admin_menu', array($this, 'meun'));
		//头部
		//add_action('admin_head', array($this, 'admin_head'));
		//底部
		//add_action('admin_footer',array($this, 'admin_footer'));
		//编辑框初始化|可以在文章发布/编辑页面的表单最后添加自己的表单域或者其他内容
		add_action('dbx_post_sidebar', array($this, 'admin_edit_meun_init'));
		//插件页连接设置
		add_filter( 'plugin_action_links', array($this, 'highlighter_action_links'), 10, 2);
	}

	public function highlighter_init() {
    	$plugin_dir = dirname(plugin_basename(__FILE__));
    	load_plugin_textdomain( 'sh', false , $plugin_dir.'/lang' );
	}

	//public function admin_head(){
		//echo '<script type="text/javascript" src="http://libs.baidu.com/jquery/1.8.2/jquery.min.js"></script>';
	//}

	//底部需要
	//public function admin_footer(){	
		//global $pagenow;
			//if('post.php' == $pagenow || 'post-new.php' == $pagenow){
			//页面编辑修改
			//wp_register_script('edit_js', plugins_url('edit_meun.js', __FILE__), '', '1', 'true');
			//wp_enqueue_script('edit_js');
		//}	
	//}
	
	//设置
	public function highlighter_action_links( $links, $file ) {
    	if ( $file != plugin_basename( __FILE__ ))
			return $links;
    	$settings_link = '<a href="options-general.php?page=midoks_dmgl">设定</a>';
    	array_unshift($links, $settings_link );
    	return $links;
	}

	/*  */
	public function admin_edit_meun_init(){
		$options = get_option('highlighter_options');
		?>
		<div id="codebox" class="meta-box-sortables ui-sortable" style="position: relative;"><div class="postbox">
		<div class="handlediv" title="Click to toggle"></div>
		<h3 class="hndle"><span><?php echo "代码添加"; ?></span></h3>
		<div class="inside">
		<?php echo '语言'; ?>
		<select id="language">
				<option value="as3">ActionScript3</option>
                <option value="bash">Bash/Shell</option>
                <option value="cpp">C/C++</option>
                <option value="css">Css</option>
                <option value="cf">CodeFunction</option>
                <option value="c#">C#</option>
                <option value="delphi">Delphi</option>
                <option value="diff">Diff</option>
                <option value="erlang">Erlang</option>
                <option value="groovy">Groovy</option>
                <option value="html">Html</option>
                <option value="java">Java</option>
                <option value="jfx">JavaFx</option>
                <option value="js">Javascript</option>
                <option value="pl">Perl</option>
                <option value="php">Php</option>
                <option value="plain">Plain Text</option>
                <option value="ps">PowerShell</option>
                <option value="python">Python</option>
                <option value="ruby">Ruby</option>
                <option value="scala">Scala</option>
                <option value="sql">Sql</option>
                <option value="vb">Vb</option>
                <option value="xml">Xml</option>			
		</select>
		<br>
		<?php echo 'CODE'; ?><br><textarea id="code" rows="8" cols="70" style="width:97%;"></textarea><br>
		<input type="button" value="<?php echo '插入CODE'; ?>" onclick="javascript:settext();">

		<script>
		function settext(){
			var str='<<?php echo $options['highlighter_tagName']?$options['highlighter_tagName']:'pre';?> class="brush:';
			var lang=document.getElementById("language").value;
			var code=document.getElementById("code").value;
			str=str+lang;
			str=str+'">';
			str=str+filter(code)+"</<?php echo $options['highlighter_tagName']?$options['highlighter_tagName']:'pre';?>><p>&nbsp;</p>";
			var win = window.dialogArguments || opener || parent || top;
			if((typeof win.send_to_editor) != "undefined") {
				win.send_to_editor(str);
			} else if((typeof CKEDITOR.instances.content.insertHtml) != "undefined") {
				CKEDITOR.instances.content.insertHtml(str);
			} else if((typeof KindEditor.instances[0].insertHtml) != "undefined") {
				KindEditor.instances[0].insertHtml(str);
			} else if((typeof UE.instants.ueditorInstant0.execCommand) != "undefined") {
				UE.instants.ueditorInstant0.execCommand("insertHtml",str);
			} else {
				alert("<?php echo __('This plugin can not insert code to your editor','sh'); ?>");
			}
			document.getElementById("code").value="";
		}
		function filter(str){
			str = str.replace(/&/g, '&amp;');
			str = str.replace(/</g, '&lt;');
			str = str.replace(/>/g, '&gt;');
			str = str.replace(/'/g, '&#39;');
			str = str.replace(/"/g, '&quot;');
			//str = str.replace(/\|/g, '&brvbar;');
			return str;
		}
		</script>
		</div></div></div>
		<script>document.getElementById("postdivrich").appendChild(document.getElementById("codebox"));</script>
		<?php
	}

	



	

	/* 加载其他需要的文件 */
	public function need(){
		//加入本地jquery
		//wp_enqueue_script('jquery');
		//加入google jquery cdn
		//wp_deregister_script('jquery');
		//wp_register_script('jquery', 'http://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js', '', '1.8.2', true);
		//wp_enqueue_script('jquery'); //调用谷歌托管
		//加入百度 jquery
		//wp_deregister_script('jquery');
		//wp_register_script('jquery', 'http://libs.baidu.com/jquery/1.8.2/jquery.min.js', '', '1.8.2', true);
		//wp_enqueue_script('jquery'); //百度托管
		
	}


 	/* 菜单管理添加 */
	public function meun(){
		add_options_page('代码高亮设置',	//当菜单被选中时候，这个文本被显示在title标记中
			'代码高亮设置', 				//菜单的文本显示
			'manage_options', 				//当前用户是否有权利浏览这个页面
			'midoks_dmgl', 					//菜单将引用这个slug的名字（对于菜单来说应该是唯一的）
			array($this, 'meun_config'));	//这个方法中的的内容将调用输出到菜单所点击的页面
	}

	public function meun_config(){
        $options = get_option('highlighter_options');
			echo '<div class="wrap">';
			echo '<h2><'.'代码高亮设置'.'</h2>';
			echo '<div class="narrow">';
			echo '<form action="options.php" method="post">';
			echo '<p>SyntaxHighlighter的是一个全功能的自足JavaScript开发的代码语法高亮</p>';
				settings_fields('highlighter_options');
				do_settings_sections('highlighter');
			echo '<p class="submit">';
			echo '<input name="submit" type="submit" class="button-primary" value="保存设置" />';
			echo '</p>';
			echo '</form>';
			echo '</div>';
			echo '</div>';
	}



}




function highlighter_admin_init(){
    register_setting( 'highlighter_options', 'highlighter_options', 'highlighter_options_validate' );
    add_settings_section('highlighter_main', __('Settings','sh'), 'highlighter_section', 'highlighter');
    add_settings_field('style', __('style','sh'), 'highlighter_style', 'highlighter', 'highlighter_main');
    add_settings_field('tagName', __('tagName','sh'), 'highlighter_tagName', 'highlighter', 'highlighter_main');
    add_settings_field('autolinks', __('autolinks','sh'), 'highlighter_autolinks', 'highlighter', 'highlighter_main');
    add_settings_field('collapse', __('collapse','sh'), 'highlighter_collapse', 'highlighter', 'highlighter_main');
    add_settings_field('firstline', __('firstline','sh'), 'highlighter_firstline', 'highlighter', 'highlighter_main');
    add_settings_field('gutter', __('gutter','sh'), 'highlighter_gutter', 'highlighter', 'highlighter_main');
    add_settings_field('smarttabs', __('smarttabs','sh'), 'highlighter_smarttabs', 'highlighter', 'highlighter_main');
    add_settings_field('tabsize', __('tabsize','sh'), 'highlighter_tabsize', 'highlighter', 'highlighter_main');
    add_settings_field('toolbar', __('toolbar','sh'), 'highlighter_toolbar', 'highlighter', 'highlighter_main');
}
function highlighter_section() {
    echo '请配置你的设置'.__('<p>Please enter your config.</p>','sh');
}
function highlighter_style()
{
$options = get_option( 'highlighter_options' );
?>
    <select name="highlighter_options[highlighter_style]" id="highlighter_style" />
        <option value="Default" <?php if("Default"==$options['highlighter_style']) echo "selected='selected'"; ?>>Default</option>
        <option value="Django" <?php if("Django"==$options['highlighter_style']) echo "selected='selected'"; ?>>Django</option>
        <option value="Emacs" <?php if("Emacs"==$options['highlighter_style']) echo "selected='selected'"; ?>>Emacs</option>
        <option value="FadeToGrey" <?php if("FadeToGrey"==$options['highlighter_style']) echo "selected='selected'"; ?>>FadeToGrey</option>
        <option value="Midnight" <?php if("Midnight"==$options['highlighter_style']) echo "selected='selected'"; ?>>Midnight</option>
        <option value="RDark" <?php if("RDark"==$options['highlighter_style']) echo "selected='selected'"; ?>>RDark</option>
    </select><br />
<?php
}
function highlighter_tagName(){
$options = get_option( 'highlighter_options' );
?>
<input type="text" name="highlighter_options[highlighter_tagName]" id="highlighter_tagName" value="<?php echo $options['highlighter_tagName']; ?>" /><br />
<?php
}
function highlighter_autolinks(){
$options = get_option( 'highlighter_options' );
?>
<select name="highlighter_options[highlighter_autolinks]" id="highlighter_autolinks" />
    <option value="true" <?php if("true"==$options['highlighter_autolinks']) echo "selected='selected'"; ?>>Yes</option>
    <option value="false" <?php if("false"==$options['highlighter_autolinks']) echo "selected='selected'"; ?>>No</option>
</select><br />
<?php
}
function highlighter_collapse(){
$options = get_option( 'highlighter_options' );
?>
<select name="highlighter_options[highlighter_collapse]" id="highlighter_collapse" />
    <option value="true" <?php if("true"==$options['highlighter_collapse']) echo "selected='selected'"; ?>>Yes</option>
    <option value="false" <?php if("false"==$options['highlighter_collapse']) echo "selected='selected'"; ?>>No</option>
</select><br />
<?php
}
function highlighter_firstline(){
$options = get_option( 'highlighter_options' );
?>
 <input type="text" name="highlighter_options[highlighter_firstline]" id="highlighter_firstline" value="<?php echo $options['highlighter_firstline']; ?>" /><br />
<?php
}
function highlighter_gutter(){
$options = get_option( 'highlighter_options' );
?>
<select name="highlighter_options[highlighter_gutter]" id="highlighter_gutter" />
    <option value="true" <?php if("true"==$options['highlighter_gutter']) echo "selected='selected'"; ?>>Yes</option>
    <option value="false" <?php if("false"==$options['highlighter_gutter']) echo "selected='selected'"; ?>>No</option>
</select><br />
<?php
}
function highlighter_smarttabs(){
$options = get_option( 'highlighter_options' );
?>
<select name="highlighter_options[highlighter_smarttabs]" id="highlighter_smarttabs" />
    <option value="true" <?php if("true"==$options['highlighter_smarttabs']) echo "selected='selected'"; ?>>Yes</option>
    <option value="false" <?php if("false"==$options['highlighter_smarttabs']) echo "selected='selected'"; ?>>No</option>
</select><br />
<?php
}
function highlighter_tabsize(){
$options = get_option( 'highlighter_options' );
?>
 <input type="text" name="highlighter_options[highlighter_tabsize]" id="highlighter_tabsize" value="<?php echo $options['highlighter_tabsize']; ?>" /><br />
<?php
}
function highlighter_toolbar(){
$options = get_option( 'highlighter_options' );
?>
<select name="highlighter_options[highlighter_toolbar]" id="highlighter_toolbar" />
    <option value="true" <?php if("true"==$options['highlighter_toolbar']) echo "selected='selected'"; ?>>Yes</option>
    <option value="false" <?php if("false"==$options['highlighter_toolbar']) echo "selected='selected'"; ?>>No</option>
</select><br />
<?php
}
function highlighter_options_validate($input) {
    $input['highlighter_tagName'] = $input['highlighter_tagName'] ? $input['highlighter_tagName'] : "pre";
    $input['highlighter_firstline'] = is_int($input['highlighter_firstline']) ? $input['highlighter_firstline'] : 1;
    $input['highlighter_tabsize'] = is_int($input['highlighter_tabsize']) ? $input['highlighter_tabsize'] : 4;
    return $input;
}

//实例化
new syntax_highlighter();
?>
