<?php

class wp_cache_html_options{

	public function __construct(){

		//插件安装时调用
		register_activation_hook(WP_CACHE_HTML_POS, array($this, 'install'));
		//插件卸载时调用
		register_deactivation_hook(WP_CACHE_HTML_POS, array($this, 'uninstall'));

		//菜单初始化
		add_action('admin_init', array($this, 'wp_cache_html_menu_init'));
		//展示
		add_action('admin_menu', array($this, 'wp_cache_html_menu'));
		add_filter('plugin_action_links', array($this, 'wp_cache_html_action_links'), 10, 2);
	}

	public function wp_cache_html_action_links($links, $file){
		if(basename($file) != basename(WP_CACHE_HTML_POS))
			return $links;
    	$settings_link = '<a href="options-general.php?page=wp_cache_html">设置</a>';
    	array_unshift($links, $settings_link);
    	return $links;
	}

	public function install(){
		$option['method'] = 'local'; 	//保存的方式
		$option['bucket_name'] = '';	//是否支持插件启用
		$option['ak'] = '';				//(根据情况填写)
		$option['sk'] = '';				//(根据情况填写)
		$option['timeout'] = 180;		//保存的时间(分钟)
		add_option('wp_cache_html_options', $option);
	}

	public function uninstall(){
		delete_option('wp_cache_html_options');
	}

	public function wp_cache_html_menu_init(){
		$this->validate();

		register_setting('wp_cache_html', 'wp_cache_html',  '');
		add_settings_section('wp_cache_html', __('','sh'), array($this, 'wp_cache_html_method_section'), 'wp_cache_html');

		//使用哪种方式处理
		add_settings_field('method', __('处理形式','sh'), array($this, 'wp_cache_html_method'), 'wp_cache_html', 'wp_cache_html');
		add_settings_field('bucket_name', __('文件名','sh'), array($this, 'wp_cache_html_bucket_name'), 'wp_cache_html', 'wp_cache_html');
		//ak
		add_settings_field('ak', __('ak','sh'), array($this, 'wp_cache_html_ak'), 'wp_cache_html', 'wp_cache_html');
		//sk
		add_settings_field('sk', __('sk','sh'), array($this, 'wp_cache_html_sk'), 'wp_cache_html', 'wp_cache_html');
		add_settings_field('timeout', __('保存时间','sh'), array($this, 'wp_cache_html_timeout'), 'wp_cache_html', 'wp_cache_html');
	
	}

	public function validate(){
		if(isset($_GET['page']) && 'wp_cache_html'==$_GET['page']){
			if(isset($_POST['submit'])){
				$options = get_option('wp_cache_html_options');
				$newopt = $_POST['wp_cache_html_options'];
				if($options != $newopt){
					update_option('wp_cache_html_options', $newopt);
				}
			}
		}
	}

	public function wp_cache_html_method_section(){
	}
	public function wp_cache_html_method(){
		$options = get_option('wp_cache_html_options');?>
		<select name="wp_cache_html_options[method]" id="method" />
			<option value="local" <?php if('local'==$options['method']) echo "selected='selected'"; ?>>local</option>
			<option value="Memcache" <?php if('Memcache'==$options['method']) echo "selected='selected'"; ?>>memcache</option>
			<option value="baidubcs" <?php if('baidubcs'==$options['method']) echo "selected='selected'"; ?>>baidubcs(百度云储存)</option>
			<option value="NFS" <?php if('NFS'==$options['method']) echo "selected='selected'"; ?>>NFS(百度云)</option>
			<option value="qiniu" <?php if('qiniu'==$options['method']) echo "selected='selected'"; ?>>七牛(云存储)</option>
			<option value="aliyun" <?php if('aliyun'==$options['method']) echo "selected='selected'"; ?>>阿里云存储</option>
			<option value="SaeStorage" <?php if('SaeStorage'==$options['method']) echo "selected='selected'"; ?>>SaeStorage(新浪Storage)</option>
		</select><br /><?php
	}

	public function wp_cache_html_bucket_name(){
		$options = get_option('wp_cache_html_options');?>
		<input type="text" name="wp_cache_html_options[bucket_name]" id="timeout" value="<?php echo $options['bucket_name']; ?>" />
			<br />必须填写(bucket,domain)<?php
	}

	public function wp_cache_html_ak(){
		$options = get_option('wp_cache_html_options');?>
		<input type="text" name="wp_cache_html_options[ak]" id="timeout" value="<?php echo $options['ak']; ?>" />
			<br />(根据情况填写)<?php
	}
	public function wp_cache_html_sk(){
		$options = get_option('wp_cache_html_options');?>
		<input type="text" name="wp_cache_html_options[sk]" id="timeout" value="<?php echo $options['sk']; ?>" />
			<br />(根据情况填写)<?php
	
	}

	public function wp_cache_html_timeout(){
		$options = get_option('wp_cache_html_options');?>
		<input type="text" name="wp_cache_html_options[timeout]" id="timeout" value="<?php echo $options['timeout']; ?>" />(分钟)<br /><?php
	}

	public function wp_cache_html_menu(){

		add_options_page('WP页面静态化',
						'页面静态化',
						'manage_options',
						'wp_cache_html',
						array($this, 'menu'));
	}

	public function menu(){
		echo '<div class="wrap"><h2>页面静态化</h2><div class="narrow"><form  method="post">';
		echo '<p>页面静态化设置</p>';
			settings_fields('wp_cache_html');
			do_settings_sections('wp_cache_html');
		echo '<p class="submit"><input name="submit" type="submit" class="button-primary" value="保存设置" /></p>';
		echo '</form></div></div>';
		$this->readme();
	}

	public function readme(){
		echo '<p>使用说明:(如果你服务器支持,使用本地化(local))<p>';
		echo '<p>1.使用local不需要填写下面的选项,但是可修改时间<p>';
		echo '<p>2.使用百度云存储,都要填写(是我测试速度快的)<p>';
		echo '<p>3.使用百度NFS,什么都不写(是我测试速度快的)<p>';
		echo '<p>4.使用七牛云存储,都要填写(速度有点慢,但是能忍受)<p>';
		echo '<p>5.使用阿里云存储,都要填写(速度中等)<p>';
		echo '<p>6.使用新浪云存储,只需填写文件名(较好,也许是我在本地环境)<p>';
		echo '<p>7.增加了Memcache支持(也许是我在本地环境)<p>';
		echo '<p>note:如果会PHP,可直接在lib下增加你的扩展!!!<p>';
		echo '<p>note:如果你发现有BUG,请立即通知我!!!<p>';
		echo '<hr/>';
		echo '<p>请关注我的博客:<a href="http://midoks.cachecha.com/" target="_blank">midoks.cachecha.com</a></p>';
		echo '<p><a href="http://me.alipay.com/midoks" target="_blank">捐助我</a></p>';
		echo '<p>能为你服务,我感到无限的兴奋</p>';
			
	}
}

new wp_cache_html_options();
?>
