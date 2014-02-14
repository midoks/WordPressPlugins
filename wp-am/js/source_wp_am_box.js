//采用wordpress中jQuery编写
jQuery(function($){
///////////////////////////////////////
	//初始化界面
	function init_wp_am_view(){
		$('#wp_am_meta_box_show').css('width', 
			(parseInt($('#wp_am_meta_box_inline').width()) -
			parseInt($('#wp_am_meta_box_tool').width()) - 
			parseInt($('#wp_am_meta_box_show').css('margin-left')))
			+'px');
	
	}
	init_wp_am_view();
	//拖动时
	$('#wp_am_meta_box .hndle').mousedown(function(){
		//$(this).mousemove(function(){
			//$(this).mouseup(function(){
				console.log("尺寸变了,再执行以下");
				var t = 
					(parseInt($('#wp_am_meta_box_inline').width())) 
					+'px';

				console.log(t);
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
		console.log('上传操作!!!');
	});

	//复位
	$('#wp_am_meta_box_tool_reset').click(function(){
		get_root_file();
	});

	//备份
	$('#wp_am_meta_box_tool_bck').click(function(){
		console.log('备份操作!!!');
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
			css('heigth', '20px').css('lineHeigth', '20px').css('opacity', 1).css('-moz-opacity', '0.5').
			css('background', 'rgb(42, 149, 197)').mouseover(function(){
				$(this).css('background', 'rgb(27, 96, 127)');
			}).mouseout(function(){
				$(this).css('background', 'rgb(42, 149, 197)');
			}).text('nihao').attr('title','你肿么了么!!!');
		
		li = $(li).text('').css('height','60px').css('width', '60px').
			css('margin', 0).css('margin-left', '2px').css('padding',0).css('float', 'left').
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
	function get_common_func(obj,callback){
		var w = $('#wp_am_meta_box_show').width();
		var h = $('#wp_am_meta_box_show').height();
		$.ajax({
			url:'admin.php?page=wp_am&type=ajax',
			cache:false,
			async:false,//同步操作
			dataType: (typeof obj.type) ? obj.type:'text',//注释可调试
			type:'POST',
			data:{
				width:w,
				height:h,
				method:'get_root_file',//方法传递
				args:(typeof obj.args) ? obj.args:'text',	//参数传递
			},
			success:function(data){
				if(typeof callback =='function'){
					callback(data);
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

	

	//获取初始目录
	function get_root_file(){
		$($('#wp_am_meta_box_show ul')[0]).empty();
		get_common_func({type:'json',method:'get_root_file',args:null},function(data){
			//背景图选择
			function change_bg(fn){
				var t = fn.split('.');
				//console.log(t);
				if(t.length>1){
					switch(t[1]){
						case 'gif':
						case 'jpeg':
						case '':
						case 'png':
							return '../wp-content/plugins/wp-am/img/file_type_pic.png';break;
						default:
							return '../wp-content/plugins/wp-am/img/file_type_other.png';break;
					}
				}else{
					return '../wp-content/plugins/wp-am/img/file_type_dir.gif';
				}
			}

			var fn = data['fn'];//文件名
			var position = data['position'];//文件位置
			var uptime = data['uptime'];//上传时间
			var filetype = data['filetype'];//文件类似
			var wrx = data['wrx'];//权限
			var reffer = data['reffer'];//防盗链
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
					};
		
					$(data.s).text(fn[i].substr(0,10)).attr('title', fn[i]);
					$(data.l).css('background', 'url('+change_bg(fn[i])+') center center no-repeat');
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
							console.log('打开文件夹操作!!!');
						}else{
							console.log('!!!');
						}
					});
					//鼠标在上
					$(data.l).mouseover(function(){
						right_menu(function(){
							return [
								{value:'查看时间', callback:function(){
									alert(info.uptime);
								}},
										  
								{value:'打开文件', callback:function(){
									if('file'==info.filetype){
										window.open(info.position);
									}else{
										console.log(info.filetype);
										console.log(info.position);
										console.log('文件不能打开');
									}
								}},
							 
									{value:'查看权限', callback:function(){
									alert(info.wrx);  
								}},

								{value:'防盗链', callback:function(){
									alert(info.reffer);
								}},

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


	
	list_files('nihao');




	
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
				console.log(data);
				console.log('完成!!!');
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
