<?php
/**
 * Plugin Name: 禁用WP的avatar功能
 * Plugin URI:  http://midoks.cachecha.com/
 * Description: 因为国內网速太慢,使用国外的服务,严重拖网速。
 * Author:      midoks
 * Author URI:  http://midoks.cachecha.com/
 * Version:     1.0
 * License:     GPL
 */

/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) exit;
add_filter('get_avatar', 'disable_get_avatar', 1, 4, false);
