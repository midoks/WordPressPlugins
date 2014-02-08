<?php
/**
 *	@func 配置文件
 *
 *	@author midoks
 *	@blog midoks.cachecha.com
 *	@mail midoks@163.com
 */

//API Kye
define('BACKUP_API_KEY', '5vrkFPPC8p5URwh2Qq089PpD');
//Secret Key
define('BACKUP_SECRET_KEY', 'ykoLFYOXDpQznEYcS9HbGRucuuH2N5tM');
//应用名称
define('BACKUP_BP_APP_NAME', 'midoks');

define('BACKUP_ADDR', 'http://midoks.cachecha.com/');
//直达URL(前地址)
define('BACKUP_REDIRECT_URL_PREFIX', BACKUP_ADDR.'wp-cron.php?type=bakcode_go');
//直达URL
define('BACKUP_REDIRECT_URL', BACKUP_ADDR.'wp-cron.php?type=bakcode_netdisk_oauth');
?>
