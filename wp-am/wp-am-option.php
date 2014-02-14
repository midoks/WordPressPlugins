<?php
//AM配置
class wp_am_option{

	public $option  =  array();

	public function config_init(){

		$this->option = get_option('wp_am_option');

		//file_put_contents(AM_ROOT.'d.txt', json_encode($this->option));
		//配置修改	
		if(isset($_POST['submit']) && isset($_POST['option_page']) &&$_POST['option_page'] == 'wp_am_option' ){
			update_option('wp_am_option', $_POST['wp_am_option']);
		}

		register_setting('wp_am_option', 'wp_am_option',  'wp_am_option');
		add_settings_section('wp_am_option', __('','sh'), array($this, 'null_section'), 'wp_am_option');	
		//宽度
		add_settings_field('width', __('图片宽度','sh'), array($this, 'pic_width'), 'wp_am_option', 'wp_am_option');
		//高度
		add_settings_field('height', __('图片高度','sh'), array($this, 'pic_height'), 'wp_am_option', 'wp_am_option');
		//是否开启图片预览
		add_settings_field('preview', __('开启图片预览','sh'), array($this, 'pic_preview'), 'wp_am_option', 'wp_am_option');
		
		
		//管理方式
		add_settings_field('method', __('管理方式','sh'), array($this, 'am_method'), 'wp_am_option', 'wp_am_option');

		//AK
		add_settings_field('ak', __('AK','sh'), array($this, 'am_ak'), 'wp_am_option', 'wp_am_option');
		//SK
		add_settings_field('sk', __('SK','sh'), array($this, 'am_sk'), 'wp_am_option', 'wp_am_option');

		//bucket名字
		add_settings_field('bucket', __('Bucket名字','sh'), array($this, 'am_bucket_name'), 'wp_am_option', 'wp_am_option');

		//am_file_prefix名字
		add_settings_field('file_prefix', __('文件前缀','sh'), array($this, 'am_file_prefix'), 'wp_am_option', 'wp_am_option');

		//文件格式
		add_settings_field('file_type', __('文件格式','sh'), array($this, 'am_file_type'), 'wp_am_option', 'wp_am_option');

		//防盗链设置
		add_settings_field('file_referer', __('反盗链','sh'), array($this, 'am_referer'), 'wp_am_option', 'wp_am_option');
		
		//是否开启本地保存
		add_settings_field('local_backup', __('是否开启本地备份', 'sh'), array($this, 'am_local_backup'), 'wp_am_option', 'wp_am_option');
	}

	public function null_section(){
	}

	//管理附件的宽度
	public function pic_width(){
		///var_dump($this->option);
		$option = $this->option;?>
		<input type="text" name="wp_am_option[width]"  value="<?php
			echo $option['width'];
		?>" />px<br /><?php
	}

	//管理附件的高度
	public function pic_height(){
		$option = $this->option;?>
		<input type="text" name="wp_am_option[height]"  value="<?php
			echo $option['height'];
		?>" />px<br /><?php
	}

	public function pic_preview(){
		$option = $this->option;?>
		<input type="checkbox" name="wp_am_option[pic_preview]"  value="<?php
			echo ($option['pic_preview'] == false ?
			'true"'  : 
			'true'.'" checked="checked"');
		?> /><br /><?php
	}

	//选择哪种
	public function am_method(){
		$options = $this->option;?>
		<select name="wp_am_option[position]" id="method" />
			<option value="local" <?php if('local'==$options['position']) echo "selected='selected'"; ?>>local</option>
			<option value="baidu" <?php if('baidu'==$options['position']) echo "selected='selected'"; ?>>百度云</option>
			<option value="aliyun" <?php if('aliyun'==$options['position']) echo "selected='selected'"; ?>>阿里云</option>
			<!--<option value="qiniu" <?php if('qiniu'==$options['position']) echo "selected='selected'"; ?>>七牛</option>-->
		</select><p>(除了local(本地),其他的都要填了下面的选项,尤其为AK,SK)</p><?php
	}

	//AK
	public function am_ak(){
		$option = $this->option;?>
		<input type="text" name="wp_am_option[ak]"  value="<?php
			echo $option['ak'];
		?>" /><br /><?php
	}

	//SK
	public function am_sk(){
		$option = $this->option;?>
		<input type="text" name="wp_am_option[sk]"  value="<?php
			echo $option['sk'];
		?>" /><br /><?php
	}

	//文件bucket
	public function am_bucket_name(){
		$option = $this->option;?>
		<input type="text" name="wp_am_option[bucket_name]"  value="<?php
			echo $option['bucket_name'];
		?>"/>	
			<p>(<b style="color:red;">不能为空</b>)Bucket命名规范：只能包括小写字母，数字和短横线（-）必须以小写字母和数字开头长度必须在2-63之间</p>
		<?php
	}

	//文件前缀
	public function am_file_prefix(){
		$option = $this->option;?>
		<input type="text" name="wp_am_option[file_prefix]"  value="<?php
			echo $option['file_prefix'];
		?>"/><p style="color:red;">一般不要写</p><?php
	}

	//文件格式
	public function am_file_type(){
		$options = $this->option;?>
		<select name="wp_am_option[file_type]" id="method" />
			<option value="0" <?php if('0' == $options['file_type']) echo "selected='selected'"; ?>>Y_m_d_H_i_s</option>
			<option value="1" <?php if('1' == $options['file_type']) echo "selected='selected'"; ?>>YmdHis_Unix</option>
			<option value="2" <?php if('2' == $options['file_type']) echo "selected='selected'"; ?>>根据上传文件命名</option>
		</select><br /><?php
	}

	//反盗链设置
	public function am_referer(){
		$option = $this->option;?>
		<textarea name="wp_am_option[file_referer]" style="width:350px;height:50px;" class="regular-text code"><?php echo $option['file_referer']; ?></textarea><div style="color:red;">实例:</div>http://midoks.cachecha.com/* <br/>http://wwww.cachecha.com/*<br>暂时对百度云有效<?php
	}

	//是否开你本地备份
	public function am_local_backup(){
		$option = $this->option;?>
		<input type="checkbox" name="wp_am_option[local_backup]"  value="<?php
			echo ($option['local_backup'] == false ?
			'true"'  : 
			'true'.'" checked="checked"');
		?> /><br /><?php
	}

	//配置显示
	public function config_show(){

		echo '<div class="wrap">';
		echo '<h2>'.'附件管理配置(midoks)'.'</h2>';
		echo '<div class="narrow">';
		echo '<form  method="post">';
		echo '<p>数据配置</p>';
			settings_fields('wp_am_option');
			do_settings_sections('wp_am_option');
		echo '<p class="submit"><input name="submit" type="submit" class="button-primary" value="保存设置" />';
		echo '</form>';
		echo '</div>';
		echo '</div>';?>
		<p>请关注我的博客:<a href="http://midoks.cachecha.com/" target="_blank">midoks.cachecha.com</a></p>
		<p><a href="http://me.alipay.com/midoks" target="_blank">捐助我</a></p>
		<p>能为你服务,我感到无限的兴奋</p><?php
	}

}
?>
