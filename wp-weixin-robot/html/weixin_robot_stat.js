/**
 * @func 对每行进行操作
 * @param object obj
 */
var _this = null;
jQuery(function($){
////////////////////////////////////////////////


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
$.fn.Toast = Toast;//对外接口
////////////////////////////////////////////////
_this = $;
});

/***
 *	@func 右键初始化
 */
function menu_init(callback){
	//console.log(callback);
	_this.contextMenus({
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

function weixin_robot_stat_on(obj){
	//_this(obj).css('backgroud','red');
	//console.log("开发中...");
	_this(obj).css('background', 'gray');
	/*_this(obj).mouseover(function(){
		menu_init(function(){
			return [
				{value:'查看时间', callback:function(){
					_this.fn.Toast("dd");
				}},
			];
		});
	});*/
}

//离开
function weixin_robot_stat_out(obj){
	_this(obj).css('background', '');
	window.document.oncontextmenu = true;
}



