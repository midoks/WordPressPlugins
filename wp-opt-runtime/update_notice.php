<?php
//禁止更新提示
add_filter('pre_site_transient_update_core',    create_function('$a', "return null;")); // 关闭核心提示
add_filter('pre_site_transient_update_plugins', create_function('$a', "return null;")); // 关闭插件提示
add_filter('pre_site_transient_update_themes',  create_function('$a', "return null;")); // 关闭主题提示
remove_action('admin_init', '_maybe_update_core');    // 禁止 Wordpress 检查更新
remove_action('admin_init', '_maybe_update_plugins'); // 禁止 Wordpress 更新插件
remove_action('admin_init', '_maybe_update_themes');  // 禁止 Wordpress 更新主题
