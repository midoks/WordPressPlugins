<?php
//不做视频网站的可以把这个功能给禁用，加快WordPress对文章的处理速度
remove_filter( 'the_content', array( $GLOBALS['wp_embed'], 'autoembed' ), 8 );
