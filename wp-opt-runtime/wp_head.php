<?php
//这是 XML-RPC 客户端发现机制需要用到的，如果你不知道这个是什么意思，或者没有集成类似 Flickr 这类服务到你的站点，那么你可以安全的移除它
remove_action('wp_head', 'rsd_link');
//如果你没有使用 Windows Live Writer 来写日志，那就移除它吧
remove_action('wp_head', 'wlwmanifest_link');
//Post relational links（和日志相关的 Link）即使下面这一堆
remove_action('wp_head', 'start_post_rel_link');
remove_action('wp_head', 'index_rel_link');
remove_action('wp_head', 'adjacent_posts_rel_link');
