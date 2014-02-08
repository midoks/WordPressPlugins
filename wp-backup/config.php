<?php
//备份相关
//--------------------------------
//是否是否备份
define('BACKUP_BOOL', true);
//是否开启zip压缩上传(经我测试,开启压缩备份最安全)
define('BACKUP_ZIP', true);
//定义备份文件前缀
define('BACKUP_NAME_PREFIX', 'wp');
//要备份的目录(绝对路径)
define('BACKUP_DIR', ABSPATH);
?>
