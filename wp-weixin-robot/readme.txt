=== WP微信机器人 ===
Contributors: midoks@163.com
Donate link: https://me.alipay.com/midoks
Tags: weixin robot
Requires at least: 3.3
Tested up to: 3.5.1
Stable tag: 5.1.8
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Weixin connected to the WordPress, use the information you faster
(微信连接Wordpress,是你的传播的信息更快)
== Description ==

Weixin connected to the WordPress, use the information you faster
By weixin APP (weixin.qq.com) information coming through the wordpress plugin wp-weixin-robot call back information. 
May use the advanced interface (https://api.weixin.qq.com/).


= 5.1.8 =
see: http://midoks.cachecha.com/?p=69

= 5.0 = 
see: http://midoks.cachecha.com/p/wp_plugin_weixin_robotv5.html

= 5.0 chat =
see: http://midoks.cachecha.com/p/wp_plug_weixin_robotv5chat.html

= 4.1 =
see: http://midoks.cachecha.com/p/wordpress_plugin_weixin_robotv4-1.html

= 4.0 =
see: http://midoks.cachecha.com/p/wordpress_plugin_weixin_robotv4.html

= 3.0 =
see: http://midoks.cachecha.com/p/wordpress_plugin_weixin_robotv3.html

= 2.0 =
see: http://midoks.cachecha.com/p/wordpress_plugin_weixin_robotv2.html

= 1.0 =
see: http://midoks.cachecha.com/p/wordpress_plugin_weixin_root.html

== Installation ==

1. 上传到 `/wp-content/plugins/` 目录
2. 在后台插件菜单激活该插件
3. 在微信后台，将接口配置信息中的 URL 设置为：http://你博客地址/?midoks 
4. Token 设置为:midoks




== Frequently asked questions ==
= 问题是无尽的 =
* 加我的QQ群:34063439
* 关注我的博客:midoks.cachecha.com

== Screenshots ==

1. 如何与微信连接
2. 后台介绍使用
3. 配置界面
4. 通信记录界面
5. 自定义关键回复设置
6. 菜单设置
7. 扩展管理

== Changelog ==

= 5.1.8 =
 * 增加 @分类名!页数
 * #标签获取(修复)
 * 菜单获取, 从微信更新到本地来。(click事件,要进行修改。)
 * 关键字回复设置(bug修复,并提升效率)
 * 整加字段 ALTER TABLE  `midoks_weixin_robot` ADD  `response_time` DOUBLE( 10, 6 ) NOT NULL DEFAULT 0.00 COMMENT  '响应时间';
 * 增加订阅插件类型(有些对订阅回复,有特殊需求的人)。
 * 增加all插件类型(可以根据自己的需求,随意定制,需谨慎使用);
 * 修改r,h,n方式。

= 5.0.0 =

* <strong>微信机器人使用</strong>
* 被动回复消息:
* 回复?,返回帮助信息(在设置中可改)
* 回复n(1-10),返回最新的图文信息
* 回复h(1-10),返回最热门的图文信息
* 回复r(1-10),返回随机的图文信息
* 回复?(关键字),返回搜索关键字的5篇图文信息
* 回复?(关键字)!?,回复关键字的有多少文章和有几页(5篇为一页)
* 回复?(关键字)!1,返回图文信息(5篇为一页),在搜索关键字多页的情况下!
* 回复p(数字),返回第几页的图文信息(5篇为一页)
* 回复p?,返回文章数据信息
* 回复@,返回分类列表!(http://t.qq.com/zhoudongfei)
* 回复@分类名,返回此分类下第一页图文列表!(http://t.qq.com/zhoudongfei)
* 回复@分类名!页数,返回此分类下第几页列表!(http://t.qq.com/zhoudongfei)
* 回复#,返回标签信息(10个)
* 回复#?,返回"查询有多少个标签"
* 回复#(1-n),返回第几页标签(每页10个标签,超过会有文本提示)
* 回复today,返回今日发布文章(图文信息),最多显示10篇


* 关键字回复设置
1. 有能力的，使用扩展机制实现,会更好

* 微信菜单回复设置
1. 有能力的，使用扩展机制实现,会更好

* WP微信机器人客服端聊天功能([服务号]我测试有效)
1. 开启聊天模式
2. 设置默认回复ID

* 图形化统计
1. 有时间再优化了。

* 微信机器人扩展功能(有开发能力的)
1. 阅读extends目录中readme.txt内容

* 检测是否能正常通信
1. 有人使用了插件,却无法使用(根据我的经验测试得出,你的rewrite配置可能有错)。
2. 详情请阅读:微信机器人测试.txt

* 程序调式
1. 在设置中开启调试模式.
2. http://youdomain/?midoks&debug=true&kw=?

* 感谢捐助我的的人(不分先后):
周东飞、李攀、张伟丁



== Upgrade notice ==

= now =
see: http://midoks.cachecha.com/p/wordpress_plugin_weixin_robotv4-1.html

= 4.0 =
see: http://midoks.cachecha.com/p/wordpress_plugin_weixin_robotv4.html

= 3.0 =
see: http://midoks.cachecha.com/p/wordpress_plugin_weixin_robotv3.html

= 2.0 =
see: http://midoks.cachecha.com/p/wordpress_plugin_weixin_robotv2.html

= 1.0 =
see: http://midoks.cachecha.com/p/wordpress_plugin_weixin_root.html
