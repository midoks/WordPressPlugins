<?php
/*
 * Plugin Name: WP运行优化
 * Plugin URI: http://midoks.cachecha.com/
 * Description: 优化你的wordpress,让它运行的更好，更安全!!!
 * Version: 1.0
 * Author: Midoks
 * Author URI: http://midoks.cachecha.com/
 */
define('WP_OPT_RUNTIME_ROOT', str_replace('\\', '/', dirname(__FILE__)).'/');










//function __disable_feature($data) { return false; }
//add_filter('comments_number', '__disable_feature');
//add_filter('comments_open', '__disable_feature');


add_action('init', 'wp_opt_runtion_func');

function wp_opt_runtion_func(){
	$option = get_option('wp_opt_runtime');
	foreach($option as $k=>$v){
		if('true' == $v){
			include(WP_OPT_RUNTIME_ROOT.$k.'.php');
		}
	}
}

new wp_opt_runtime();
//后台控制
class wp_opt_runtime{

	public function __construct(){
		$this->option = get_option('wp_opt_runtime');
		//插件安装时调用
		register_activation_hook(__FILE__, array($this, 'install'));
		//插件卸载时调用
		register_deactivation_hook(__FILE__, array($this, 'uninstall'));
		add_filter('plugin_action_links', array($this, 'wp_opt_rt_action_links'), 10, 2);
		add_action('admin_menu', array($this, 'opt_runtime_menu'), 1);
	}



	public function wp_opt_rt_action_links($links, $file){
		if(basename($file) != basename(__FILE__))
			return $links;
    	$settings_link = '<a href="options-general.php?page=wp-opt-runtime">设置</a>';
    	array_unshift($links, $settings_link);
    	return $links;
	}

	public function install(){
		$option = array();
		$option['wp_head'] = 'false';
		$option['update_notice'] = 'true';
		$option['cron'] = 'false';
		$option['version'] = 'false';
		$option['post_revisions'] = 'false';
		$option['xml_rpc'] = 'false';
		$option['post_embeds'] = 'false';
		add_option('wp_opt_runtime', $option);
	}

	public function uninstall(){
		add_option('wp_opt_runtime', $option);
	}

	public function opt_runtime_menu(){
		add_options_page('WP运行优化',
						'WP运行优化',
						'manage_options',
						'wp-opt-runtime',
						array($this, 'opt_runtime_menus'));
	}

	public function opt_runtime_menus(){
		$this->menus_submit();
		echo '<div class="wrap"><div class="narrow">';
		echo '<form  method="POST">';
		echo '<h2>WP运行优化配置</h2><table class="form-table">';
		echo $this->menus_table();
		echo '</table><p class="submit">';
		echo '<input name="submit" type="submit" class="button-primary" value="保存设置" />';
		echo '</p></form></div></div>';
		$this->readme();
	}

	public function menus_submit(){
		if(isset($_POST['submit'])){
			$o_option = $this->option;
			$option =  $_POST['wp_opt_runtime'];
			//var_dump($option, $o_option);
			if($o_option != $option){
				$option['cron'] = isset($option['cron']) ? 'true': 'false';
				$option['version'] = isset($option['version']) ? 'true': 'false';
				$option['update_notice'] = isset($option['update_notice']) ? 'true': 'false';
				$option['xml_rpc'] = isset($option['xml_rpc']) ? 'true': 'false';
				$option['post_embeds'] = isset($option['post_embeds']) ? 'true': 'false';
				$option['wp_head'] = isset($option['wp_head']) ? 'true': 'false';
				$option['post_revisions'] = isset($option['post_revisions']) ? 'true': 'false';

				update_option('wp_opt_runtime', $option);
				$this->option = $option;
			}
		}	
	}

	public function menus_table(){
		$options = $this->option;

		//移除无用的头信息
		echo '<tr  valign="top"><th scope="row">移除无用的头信息</th>';
		echo '<td><input type="checkbox" name="wp_opt_runtime[wp_head]"  value="true"';
		if( 'true' == $options['wp_head'] ){ echo ' checked="checked"'; }
		echo '/></td></tr>';

		//显示更新信息
		echo '<tr  valign="top"><th scope="row">禁止显示更新信息</th>';
		echo '<td><input type="checkbox" name="wp_opt_runtime[update_notice]"  value="true"';
		if('true' == $options['update_notice'] ){ echo ' checked="checked"'; }
		echo '/></td></tr>';

		//禁止计划任务
		echo '<tr  valign="top"><th scope="row">禁止计划任务</th>';
		echo '<td><input type="checkbox" name="wp_opt_runtime[cron]"  value="true"';
		if( 'true' == $options['cron']){ echo ' checked="checked"'; }
		echo '/><br>如果你在wp-config.php,已经设置:这个功能就不能实现!</td></tr>';

		//禁止自动修订
		echo '<tr  valign="top"><th scope="row">禁止自动修订</th>';
		echo '<td><input type="checkbox" name="wp_opt_runtime[post_revisions]"  value="true"';
		if( 'true' == $options['post_revisions'] ){ echo ' checked="checked"'; }
		echo '/><br />功能没有实现,你只需要在wp-config.php下添加即可'.
			':<br />define(\'WP_POST_REVISIONS\', false);<br />define(\'AUTOSAVE_INTERVAL\', false);</td></tr>';

		//禁止显示版本信息
		echo '<tr  valign="top"><th scope="row">禁止显示版本信息</th>';
		echo '<td><input type="checkbox" name="wp_opt_runtime[version]"  value="true"';
		if( 'true' == $options['version'] ){ echo ' checked="checked"'; }
		echo '/><br />防止通过漏洞,攻击你的网站,建议关闭</td></tr>';

		//关闭XML RPC功能
		echo '<tr  valign="top"><th scope="row">关闭XML RPC功能</th>';
		echo '<td><input type="checkbox" name="wp_opt_runtime[xml_rpc]"  value="true"';
		if( 'true' == $options['xml_rpc'] ){ echo ' checked="checked"'; }
		echo '/><br />如果你不需要APP客服端发布,建议关闭XML RPC功能</td></tr>';

		//禁用Post Embeds功能
		echo '<tr  valign="top"><th scope="row">禁用Post Embeds功能</th>';
		echo '<td><input type="checkbox" name="wp_opt_runtime[post_embeds]"  value="true"';
		if( 'true' == $options['post_embeds'] ){ echo ' checked="checked"'; }
		echo '/><br />Post Embeds功能基本上国内是不支持的,建议禁用</td></tr>';

	}


	public function readme(){?>
		<p>请关注我的博客:<a href="http://midoks.cachecha.com/" target="_blank">midoks.cachecha.com</a></p>
		<p><a href="http://me.alipay.com/midoks" target="_blank">捐助我</a></p>
		<p>能为你服务,我感到无限的兴奋</p><?php
	}
}
?>
