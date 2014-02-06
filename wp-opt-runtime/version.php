<?php
//去除版本信息
/*function i_want_no_generators(){
	return '';
}
add_filter('the_generator','i_want_no_generators');
*/
//它是用来在 header 显示你的 WordPress 版本号
remove_action('wp_head', 'wp_generator');
