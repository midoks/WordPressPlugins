-- 建立蜘蛛抓取记录表
create table if not exists `midoks_spider`(
	`id` int(10) not null auto_increment comment 'ID',
	`name` varchar(50) not null comment '蜘蛛名字',
	`time` varchar(13) not null comment '时间',
	`ip` varchar(15) not null comment 'IP地址',
	`url` varchar(255) not null comment '收录地址',
	primary key(`id`)
)engine=MyISAM default character set utf8 comment='管理员表' collate utf8_general_ci;
