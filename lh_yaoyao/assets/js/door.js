/**
author : zhupinglei
desc : 开门效果
**/
function door(type, picUrl){
	this.init(type, picUrl);
}
door.prototype = {
	init : function(type, picUrl){
		var _this = this;
//		$('.weiba-content').hide();
//		$('.weiba-footer').hide();
		//通过页面数据获取相关参数
    	var Door = true;
    	if( Door ){
    		//解决先显现body内容后出现图片问题
    		var str = '<div id="wDoor" style="position:fixed; left:0; top:0; z-index:999999998; background:#fff; width:'+$(window).width()+'px; height:'+$(window).height()+'px;"></div>';
    		$('body').prepend(str);

			_this.getImgSize(type, picUrl);
     	}
	},
	getImgSize : function(type,aimg){
		var _this = this;
		var nImg = new Image();
		nImg.src = aimg;
		nImg.onload = function(){
			var winWidth = $(window).width(),
				winHeight = $(window).height(),
				winPercent = winWidth/winHeight;
			var picWidth = nImg.width,
				picHeight = nImg.height,
				picPercent = picWidth/picHeight;
			
			if( winPercent < picPercent ){
				nImg.height = winHeight;
				nImg.width = winHeight * picPercent;
			}else if( winPercent > picPercent ){
				nImg.width = winWidth;
				nImg.height = winWidth / picPercent;
			}else{
				nImg.height = winHeight;
				nImg.width = winWidth;
			}
			if( type == 'h' ){
				_this.createPicH('pic',aimg,nImg.width,nImg.height);
			}else{
				_this.createPicW('pic',aimg,nImg.width,nImg.height);
			}
		}
	},
	createPicH : function(sty,page,w,h){
		var _this = this;
		var winWidth = $(window).width(),
			winHeight = $(window).height();
		var cssStr = '<style id="doorCss">'+
						'#doorMask{width:'+winWidth+'px; height:'+winHeight+'px; overflow:hidden; position:fixed; z-index:999999999; left:0; top:0;}'+
						'.doorLeft{width:100%; height:100%; overflow:hidden; position:absolute; left:-50%; top:0; -webkit-transform : rotateY(0deg); -webkit-transition : -webkit-transform 1s ease-in;}'+
						'.doorRight{width:100%; height:100%; overflow:hidden; position:absolute; left:50%; top:0; -webkit-transform : rotateY(0deg); -webkit-transition : -webkit-transform 1s ease-in;}'+
					 '</style>';
		if( sty == 'color' ){
			var strCon = '<div class="doorLeft" style="background:'+page+'; opacity:0.7;"></div><div class="doorRight" style=" background:'+page+'; opacity:0.7;"></div>';
		}else{
			var harfWidth = winWidth/2;
			var strCon = '<div class="doorLeft"><img style="position:absolute; left:'+harfWidth+'px; top:0;" src="'+page+'" width="'+w+'" height="'+h+'" /></div><div class="doorRight"><img style="position:absolute; left:-'+harfWidth+'px; top:0;" src="'+page+'" width="'+w+'" height="'+h+'" /></div>';
		}
		var str = cssStr + '<div id="doorMask">'+ strCon+'</div>';
		$('body').prepend(str);
		setTimeout(function(){
			$('.weiba-content').show();
			$('.weiba-footer').show();
			if( $('#wDoor').size() ){ $('#wDoor').remove(); }
		},500)
		_this.openDoorH();
	},
	createPicW : function(sty,page,w,h){
		var _this = this;
		var winWidth = $(window).width(),
			winHeight = $(window).height();
		var cssStr = '<style id="doorCss">'+
						'#doorMask{width:'+winWidth+'px; height:'+winHeight+'px; overflow:hidden; position:fixed; z-index:999999999; left:0; top:0;}'+
						'.doorLeft{width:100%; height:100%; overflow:hidden; position:absolute; left:0; top:-50%; -webkit-transform : rotateX(0deg); -webkit-transition : -webkit-transform 1s ease-in;}'+
						'.doorRight{width:100%; height:100%; overflow:hidden; position:absolute; left:0; top:50%; -webkit-transform : rotateX(0deg); -webkit-transition : -webkit-transform 1s ease-in;}'+
					 '</style>';
		if( sty == 'color' ){
			var strCon = '<div class="doorLeft" style="background:'+page+'; opacity:0.7;"></div><div class="doorRight" style=" background:'+page+'; opacity:0.7;"></div>';
		}else{
			var harfHeight = winHeight/2;
			var strCon = '<div class="doorLeft"><img style="position:absolute; left:0; top:'+harfHeight+'px;" src="'+page+'" width="'+w+'" height="'+h+'" /></div><div class="doorRight"><img style="position:absolute; left:0; top:-'+harfHeight+'px;" src="'+page+'" width="'+w+'" height="'+h+'" /></div>';
		}
		var str = cssStr + '<div id="doorMask">'+ strCon+'</div>';
		$('body').prepend(str);
		setTimeout(function(){
			$('.weiba-content').show();
			$('.weiba-footer').show();
			if( $('#wDoor').size() ){ $('#wDoor').remove(); }
		},500)
		_this.openDoorW();
	},
	openDoorH : function(){
		var _this = this;
		$('#doorMask').bind('click',function(){
			$('#doorMask .doorLeft').css({'webkitTransform':'rotateY(-90deg)'});;
			$('#doorMask .doorRight').css({'webkitTransform':'rotateY(-90deg)'});
			_this.removeDom();
		});
	},
	openDoorW : function(){
		var _this = this;
		$('#doorMask').bind('click',function(){
			$('#doorMask .doorLeft').css({'webkitTransform':'rotateX(-90deg)'});;
			$('#doorMask .doorRight').css({'webkitTransform':'rotateX(-90deg)'});
			_this.removeDom();
		});
	},
	removeDom : function(){
		setTimeout(function(){
			$('#doorCss').remove();
			$('#doorMask').remove();
		},1000);
	}
}
