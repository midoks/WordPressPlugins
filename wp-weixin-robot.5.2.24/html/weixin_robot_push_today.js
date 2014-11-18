/* 今日文章推送功能 */
jQuery(function($){
///////////////////////////////////

//初始化值	
//var url = location.href;
//通过标签传来的值
var dataVal = $('#weixin_robot_push_today').html();
console.log(dataVal);
var dataE = eval("("+dataVal+")");
function init(next_id, c){
	$.ajax({
		type:'POST',
		"url":"admin.php",
		data:"page=weixin_robot_push_today&method=userlist&ai="+dataE.ai+"&as="+dataE.as+"&next_id="+(typeof next_id=='undefined'?'':next_id),
		success:function(data){
			var S = eval("("+data+")");
			if(typeof S.errcode == 'undefined'){
				console.log(S);
				if(S.total>10000){
					var SU = $("#weixin_robot_pt_data").data('list');
					$("#weixin_robot_pt_data").data('list', SU.concat(S.data.openid));
					//var Slen = S.data.openid.length;
					if('' != (S.next_openid)){
						init(S.next_openid, c+1);
					}
				}else{
					$("#weixin_robot_pt_data").data('list', S.data.openid);
				}
			}else{
				$("#weixin_robot_pt_data").data('list', 'error');
			}
		}
	});
}

//初始化数据
$('#init').click(function(){
	init();
});


//推送
$("#push").click(function(){
//////////////////////////////
var timer = setInterval(function(){
	var usrlist = $("#weixin_robot_pt_data").data('list');
	//不存在时
	if(typeof usrlist == 'undefined'){
		console.log("不存在数据!!!,缺憾收场");
		clearInterval(timer);
	}
	//推送开始
	var id = dataE.id;
	for(i in usrlist){
		pushpost(id, usrlist[i]);
	}
	
	console.log(usrlist);
	if(usrlist.length<1){
		console.log('完美结束!!');
		clearInterval(timer);
	}
}, 3000);


//////////////////////////////
});


$('#stop').click(function(){
	console.log('停止执行!!!');
	clearInterval(timer);
});


//推送文章
function pushpost(id,userid){
		$.ajax({
		type:'POST',
		"url":"admin.php",
		data:"page=weixin_robot_push_today&method=pushpost&id="+id+"&userid="+userid+"&ai="+dataE.ai+"&as="+dataE.as,
		success:function(data){
			console.log(data);
			if('true'==data){
				var pt = $("#weixin_robot_pt_data").data('list');
				pt.shift();
				$("#weixin_robot_pt_data").data('list', pt);
			}
		}
	});
}
/////////////////////////////////////
});
