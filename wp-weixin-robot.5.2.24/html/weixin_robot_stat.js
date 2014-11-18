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
