//采用wordpress中jQuery编写
jQuery(function($){
///////////////////////////////////////

	$.fn.Toast = Toast;//对外接口
	function Toast(info, time){
		//console.log('q:');
		if(typeof time == 'undefined'){
			var time = 3000;
		}

		//var pdiv =  document.createElement('div');
		//pdiv.position = 'relative';
		//console.log(pdiv);
		var div =  document.createElement('div');
		div.id = 'midoks_toast_'+((new Date()).getTime());

		var t = (parseInt($('body').height())/2)+'px';
		var l = (parseInt($('body').width())/2)+'px';

		$('body').append(div);
		$('#'+ div.id).attr('id', 'midoks_toast').addClass('button-primary').
			css('position', 'fixed').css('top', t).css('left', l).
			fadeIn(1000,function(){//淡入
 			}).fadeOut(time, function(){
				$(this).remove();
			}).text(info);
	}

	//初始化界面
	function init_wp_am_view(){
		$('#wp_am_meta_box_show').css('width', 
			(parseInt($('#wp_am_meta_box_inline').width()) -
			parseInt($('#wp_am_meta_box_tool').width()) - 
			parseInt($('#wp_am_meta_box_show').css('margin-left')))	+'px');
	
	}
	init_wp_am_view();
	window.onresize = function(){
		init_wp_am_view();
	}
	//拖动时
	$('#wp_am_meta_box .hndle').mousedown(function(){
		//$(this).mousemove(function(){
			//$(this).mouseup(function(){
				//console.log("尺寸变了,再执行以下");
				var t = 
					(parseInt($('#wp_am_meta_box_inline').width()))
					+'px';

				//console.log(t);
			//});
		//});
		/*var t  = (parseInt($('#wp_am_meta_box_inline').width()) -
			parseInt($('#wp_am_meta_box_tool').width()) - 
			parseInt($('#wp_am_meta_box_show').css('margin-left')))
			+'px';*/
		
		/*$('#wp_am_meta_box_show').css('width',
			(parseInt($('#wp_am_meta_box_inline').width()) -
			parseInt($('#wp_am_meta_box_tool').width()) - 
			parseInt($('#wp_am_meta_box_show').css('margin-left')))
			+'px');*/
	});

/*工具栏功能 start*/
	//上传
	$('#wp_am_meta_box_tool_up').click(function(){
		 $('#upload').uploadifyUpload();
	});

	//复位
	$('#wp_am_meta_box_tool_reset').click(function(){
		get_root_file();
	});
	$(window).resize(function(){
		//get_root_file();
	});

	//备份
	$('#wp_am_meta_box_tool_bck').click(function(){
		console.log('备份操作!!!');
		Toast('你好');
	});

	$('#wp_am_meta_box_tool_test').click(function(){
		list_files();
		console.log('test 操作');
	});
/*工具栏功能 end*/



/* 文件内容操作 start*/

	//(填充|显示)本地文件
	function list_files(callback){
		var li = document.createElement('li');
		var span = document.createElement('span');
		var span = $(span).css('position','absolute').css('overflow','hidden').
			css('bottom',0).css('width', '60px').css('text-align', 'center').css('color', 'white').
			css('heigth', '20px').css('lineHeigth', '20px').css('opacity', 0.5).css('-moz-opacity', '0.5').
			css('background', 'rgb(42, 149, 197)').mouseover(function(){
				$(this).css('background', 'rgb(27, 96, 127)');
			}).mouseout(function(){
				$(this).css('background', 'rgb(42, 149, 197)');
			}).text('nihao').attr('title','你肿么了么!!!');
		
		li = $(li).text('').css('height','60px').css('width', '60px').//默认高和宽
			css('margin', 0).css('padding',0).css('float', 'left').
			css('background', 'url('+'../wp-content/plugins/wp-am/img/file_type_dir.gif'+') center center no-repeat').
			css('position','relative').css('cursor','pointer').attr('class', 'li_class_'+((new Date()).getTime())).
			mouseover(function(){
				$(span).css('background', 'rgb(27, 96, 127)');
			}).mouseout(function(){
				$(span).css('background', 'rgb(42, 149, 197)');
			}).click(function(){
				//$($('#wp_am_meta_box_show ul')[0]).empty();//清空
			}).append(span);
		//添加元素
		var ul = $($('#wp_am_meta_box_show ul')[0]).append(li);
		//多元素进行操作
		if(typeof callback == 'function'){
			callback({u:ul,l:li,s:span});
		}
	}
	
	/**
	 *	@param obj 对象
	 *	@param callback 回调函数
	 */
	function get_common_func(obj, callback){
		//console.log(obj); 
		//callback);
		var w = $('#wp_am_meta_box_show').width();
		var h = $('#wp_am_meta_box_show').height();
		$.ajax({
			url:'admin.php?page=wp_am&type=ajax',
			cache:true,
			async:true,//同步操作
			dataType: (typeof obj.type) ? obj.type:'text',//注释可调试
			type:'POST',
			data:{
				width:w,
				height:h,
				method:(typeof obj.method != 'undefined') ?obj.method:'get_root_file',//方法传递
				args:(typeof obj.args != 'undefined') ? obj.args: '',	//参数传递
			},
			
			error:function(XMLHttpRequest, textStatus, errorThrown){
				console.log(XMLHttpRequest.responseText, textStatus, errorThrown);
			},

			success:function(dt){
				$($('#wp_am_meta_box_show ul')[0]).empty();
				if(typeof callback =='function'){
					callback(dt);
				}
			}
		});
	}

	function right_menu(callback){
		//console.log(callback);
		$.contextMenus({
			color:'#45649e',
			boxShadow :'1px 1px 2px rgb(50, 50, 50)',
			fontFamily : 'tahoma, helvetica, clean, sans-serif',
			fontSize : '12px',
			backgroundColor : '#fff',
			width : '190px',
			position: 'fixed',
			overflow:'hidden',
			cursor : 'pointer',
			border:'1px solid #b8cbcb',
			webkitBoxShadow : '2px 2px 5px rgb(50, 50, 50)',
			mozBoxShadow : '2px 2px 5px rgb(50, 50, 50)',
			overflow : 'hidden',
		}).insert(callback()).show();
	}

	
	$.fn.get_root_file = get_root_file;//对外接口
	//获取初始目录
	function get_root_file(filename,obj){
		var typename = 'json';
		if(typeof filename != 'undefined'){
			var file_method = filename;	//typename = 'text';
		}else{
			var file_method = 'get_root_file';	
		}
		//console.log(file_method, typename);
		Toast('请稍等片刻...', 4000);
		get_common_func({type:typename,method:file_method,args:obj},function(data){
			//背景图选择
			function change_bg(fn){
				var t = fn.split('.');
				//console.log(t);
				if(t.length>1){
					switch(t[1]){
						case 'gif':
						case 'jpeg':
						case 'jpg':
						case 'png':
							return '../wp-content/plugins/wp-am/img/file_type_pic.png';break;
						default:
							return '../wp-content/plugins/wp-am/img/file_type_other.png';break;
					}
				}else{
					return '../wp-content/plugins/wp-am/img/file_type_dir.gif';
				}
			}

			//console.log(data);
			var fn = data['fn'];//文件名
			var position = data['position'];//文件位置
			var position_local = data['position_local'];//基本属性
			var uptime = data['uptime'];//上传时间
			var filetype = data['filetype'];//文件类似
			var wrx = data['wrx'];//权限
			var reffer = data['reffer'];//防盗链
			var config = data['config'];//配置信息
			//console.log(data);
			//保存当前状态值
			$('#wp_am_meta_box_inline_current_status').data('current', data);
			/////////////////////////////////
			for(i in fn){
				list_files(function(data){
					var info = {
						position:position[i],
						filetype:filetype[i],
						wrx:wrx[i],
						reffer:reffer[i],
						uptime:uptime[i],
						fn:fn[i],
						position_local:position_local[i],
					};
					
					//文件开始管理缩略图
					//console.log(config);
					if(typeof config.pic_preview != 'undefined' && config.pic_preview == 'true' ){
						if(info.filetype == 'file'){//文件开始管理缩略图
							var img = document.createElement('img');
							var img_s = $(img).attr('src', info.position).css('height', config.height+'px').
								css('width', config.height+'px').css('overflow', 'hidden');
							$(data.l).append(img_s);
						}
					}

					$(data.s).css('width', config.width+'px').
						text(fn[i].substr(0,10)).attr('title', fn[i]);
					$(data.l).css('height', config.height+'px').css('width', config.width+'px').
						css('background', 'url('+change_bg(fn[i])+') center center no-repeat');
					/*$(data.l).data('info', {
						p:position[i],
						f:filetype[i],
						w:wrx[i],
						r:[reffer],
						u:uptime[i],
						f:fn[i],
					});*///循环太快,数据遗失,保存数据

					$(data.l).click(function(){//开发文件夹
						//var obj = $(this).data('info');
						//console.log(info);
						if(info.filetype == 'dir'){
							//console.log('打开文件夹操作!!!');
							get_root_file('get_child_file', info);
						}else{
							//console.log('!!!');
						}
					});
					//鼠标在上
					$(data.l).mouseover(function(){
						right_menu(function(){
							return [
								{value:'查看时间', callback:function(){
									Toast('创建时间:'+info.uptime);
								}},
										  
								{value:'打开文件及目录', callback:function(){
									if('file'==info.filetype){
										window.open(info.position);
									}else{
										get_root_file('get_child_file', info);
									}
								}},
							 
								{value:'查看权限', callback:function(){
									Toast(info.wrx);  
								}},

								{value:'防盗链', callback:function(){
									Toast(info.reffer);
								}},

								{value:'删除文件', callback:function(){
									if(info.filetype == 'dir'){
										Toast('为了防止错删目录,禁止删除目的(空目录可以删除,暂时不支持!!!)');
									}else{
										get_common_func({type:'json',method:'delete_file',args:info},function(data){
											if(data.rmfile == 1){
												//console.log(data);
												info.position_local = data.refresh;
												get_root_file('get_child_file', info);
											}
										});
									}
								}},

								/*{value:'强力删除文件', callback:function(){
									//get_root_file('delete_file', info);
									if(info.filetype == 'dir'){
										console.log('为了防止错删目录,禁止删除目的(空目录可以删除)');
									}
									get_common_func({type:'json',method:'delete_file',args:info},function(fk_d_data){
										console.log(fk_d_data);
										if(fk_d_data.rmfile == 1){
											info.position_local = data.refresh;
											console.log(info);
											get_root_file('get_child_file', info);
										}
									});
								}},*/

								{value:'父级目录', callback:function(){
									get_common_func({type:'json',method:'get_parent_file',args:info},function(fj_data){
										if(fj_data.err == '1'){
											Toast('已经最顶端,无法再上父级目录');
											get_root_file();
										}else{
											get_root_file('get_child_file', fj_data);
										}
									});
								}},

								/*{value:'本目录上传', callback:function(){
									
								}},*/

								//必须要
								{value:'捐助我', callback:function(){
    								window.open('http://me.alipay.com/midoks');
								}},
							];
						});
					});
					$(data.l).mouseout(function(){window.document.oncontextmenu = true;});//鼠标离开
					////
				});
			}


		/////////////////////////////////////////////////////////////////////////////////////////
		});
	}
	
/*

*/

	//获取目录子目录和文件
	function get_am_dir_child(){}
	//list_files('nihao');




	
	$('#wp_am_meta_box .hndle').click(function(){
		var w = $('#wp_am_meta_box_show').width();
		var h = $('#wp_am_meta_box_show').height();
		$.ajax({
			url:'admin.php?page=wp_am&type=ajax',
			cache:false,
			//dataType:'',
			type:'POST',
			data:{
				width:w,
				height:h,
				method:'get_root_file',
			},
			success:function(data){
				//var d = eval(data);
				//console.log(data);
				//console.log('完成!!!');
			}
		
		});
	});
/* 文件内容操作 start*/


/* 翻页 start */

$('#wp_am_meta_box_showinfo .next').click(function(){
	console.log('下页');
});

$('#wp_am_meta_box_showinfo .prev').click(function(){
	console.log('上页');
});
/* 翻页 start */
//////////////////////////////////


///////////////////////////////
});
