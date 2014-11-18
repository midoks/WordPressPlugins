/**
 * RightMenu 编写jQuery插件 右键菜单
 * @author midoks
 * @link http://midoks.cachecha.com/
 * @mail midoks@163.com
 * @ver 1.0
 */
jQuery(function($){

//保存jQuery对象
var _this = this;
//保存contextMenus对象
var __this = null;
function contextMenus(obj){return new _m(obj);}
//初始化
function _m(obj){
  	//$ID
	this.id = 'id_midoks';
	this.init(obj);
};
//初始化
_m.prototype.init = function(obj){this.iObj = obj;__this = this;}
//插入列表单元
_m.prototype.insert = function(obj, styleObj, callmouseover, callmouseout){
	this.cObj = midoks_base(obj); 
	this.csObj = styleObj;
	this.cmouseover = callmouseover;
	this.cmouseout = callmouseout;
	return this;
}
//改变ID
_m.prototype.changeId = function(){this.id = 'id_midoks_'+((new Date()).getTime());}
//默认
function midoks_base(obj){
	/*if(typeof obj == 'undefined'){
		var obj = new Array();
	}
	var owner = new Array(
        {value:'重新加载网页', callback:function(){
        	location.href = location.href;
        }},
		{value:'版本信息(midoks)', callback:function(){
        	alert('contextMenus_1.0');
    	}}
	);*/
	//obj.concat(owner);
	//for(i in owner){obj.push(owner[i]);}
	return obj;
}

//创建菜单
function CreateMenus(c, p){
  	var div = document.createElement('div');//创建元素
	//默认样式
	var conmon = '24px';
	div.style.width = p.style.width;
	div.style.lineHeight = conmon;
	div.style.height = conmon;
	div.style.textAlign = 'center';
	div.style.borderBottom = '1px solid #b8cbcb';
	if('object' == typeof c.style){//单个的css
		var style = c.style;
		for(i in style){div.style[i] = style[i];}
		if(typeof style.height == 'undefined'){div.style.height = '24px';}//高度一定要给
	}
	if('object' == typeof __this.csObj){//所有的css
		var style = __this.csObj;
		for(i in style){div.style[i] = style[i];}
		if(typeof style.height == 'undefined'){div.style.height = '24px';}//高度一定要给
	}
	
	//事件
	//鼠标在元素上
	div.onmouseover = function(){
		this.style.color = '#fff';
		this.style.cursor =  'pointer';
		this.style.backgroundColor = '#45649e';
		if(typeof c.moveover == 'function'){//单个
			c.moveover(this);
		}
		if('function' == typeof __this.callmouseover){//所有
			__this.cmouseover(this);
		}

	}
	//鼠标离开元素
	div.onmouseout = function(){
		this.style.color = '#45649e';
		this.style.backgroundColor = '#fff';
		if(typeof c.moveout == 'function'){//单个
			c.moveout(this);
		}
		if('function' == typeof __this.callmouseover){//所有
			__this.cmouseout(this);
		}
	}
	div.innerHTML = c.value;
	//执行点击事件
	if(typeof c.callback == 'function'){
		div.onclick = function(){
			c.callback();//执行事件
			//console.log(__this.id);
			$('#'+__this.id).remove();
		}
	}
  	return div;
}

//创建二级菜单
function Create2Menus(){}

//实现效果
_m.prototype._show = function(){
	//这个就是键盘触发的函数
	/*--------------------------------------------------
		@func 					$ 右键快捷菜单 $
		@return boolean false 	$ 屏蔽系统的右键快捷菜单 $
	----------------------------------------------------*/
  	var __this  = this;
	function _rm(e){
		//存在时不再创建
		var yid = document.getElementById(__this.id);//删除后,再改变
		//console.log(yid);
		e = window.e || e;
		//点击的位置
		var x = e.clientX;
		var y = e.clientY;
		
		if(yid){
			yid.style.top = (y-5);
			yid.style.left = (x-5);
			return false;
		}
		__this.changeId();
		//创建div的标签
      	var pdiv = document.createElement('div');
        /////////////////////////////////////////
		var cdiv = document.createElement('div');
      	pdiv.appendChild(cdiv);		
		//设置div
		cdiv.id= __this.id;
		cdiv.style.color='#45649e';			
		cdiv.style.fontFamily = 'tahoma, helvetica, clean, sans-serif';
		cdiv.style.fontSize = '12px';
		cdiv.style.backgroundColor = '#fff';
		cdiv.style.width = '190' + 'px';
		cdiv.style.height = '0px';
		cdiv.style.position= 'fixed';
		cdiv.style.overflow='hidden';
		cdiv.style.zIndex = '100';
		cdiv.style.cursor = 'pointer';
		cdiv.style.border='1px solid #b8cbcb';
		cdiv.style.boxShadow = '1px 1px 2px #E5E5E5';
		cdiv.style.webkitBoxShadow = '2px 2px 5px #E5E5E5';
		cdiv.style.mozBoxShadow = '2px 2px 5px #E5E5E5';
		cdiv.style.overflow = 'hidden';
      
		if(typeof __this.iObj == 'object'){
			var style = __this.iObj;
			for(i in style){
				cdiv.style[i] = style[i];//$(cdiv).css(i, style[i]);
				//console.log(style[i]);
			}
		}
		cdiv.style.top = (y-5) + 'px';
		cdiv.style.left = (x-5) + 'px';
		//cdiv.style.prototype = {};

		//用户定义
      	var c = __this.cObj;
        for(i in c){
          	var t = CreateMenus(c[i], cdiv);
			if(i==(c.length-1)){
				//console.log('log');
				t.style.borderBottomWidth = '0px';
				//console.log(t.style);
				cdiv.style.height = parseInt(cdiv.style.height) + parseInt(t.style.height) + 'px';
			}else{
				var h = parseInt(cdiv.style.borderBottomWidth);
				cdiv.style.height = parseInt(cdiv.style.height) + parseInt(t.style.height) + h + 'px';
			}
          	cdiv.appendChild(t);
        }
      	//加入body
      	document.body.appendChild(pdiv);
  		//cdiv.onmouseout = function(e){}
		//鼠标离开时,删除标签
		window.document.onmousemove = function(e){
			e = window.e || e;
			var x = e.clientX;//点击的位置
			var y = e.clientY;
			if(
				( (parseInt(cdiv.style.left) <= x && x <= (parseInt(cdiv.style.left)+parseInt(cdiv.style.width))) && // 在x轴内
				( (parseInt(cdiv.style.top)) <= y && y <= (parseInt(cdiv.style.top)+parseInt(cdiv.style.height))))	 // 在y轴内
			){	
			}else{$(pdiv).remove();}
		}

		//取消相关的默认操作
		if(event.preventDefault) event.preventDefault();//标准
		if(event.returnValue) event.returnValue = false;//IE
		return ;//用于处理使用对象属性注册的处理程序
	}
	window.document.oncontextmenu = _rm;
	//window.document.onclick = _rm;
}
//实现效果
_m.prototype.show = function(){
  	this._show();//当右键按下时执行函数
}

/////////////赋值到jQuery对象
//给$添加方法
$.contextMenus = contextMenus;
//给DOM($())对象添加方法
//$.fn.contextMenus = contextMenus;
//$.fn.extend({contextMenus : contextMenus});
////////////////////////////////
});
