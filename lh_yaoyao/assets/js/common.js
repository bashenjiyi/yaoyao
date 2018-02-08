window.load = load();

function load() {
	$('#loading').hide();
	$('#p0 .banner').addClass('bounceInDown');
	$('#p0 .red div').addClass('bounceInDown');
}

var swiper;
var voice = {
	localId: '',
	serverId: ''
};
var recordStatus = 0;
var waveLeft = 0;
var shakeStatus = 0;
var shakeTimes = 0;
var shakeTimesBefore = 0;
var shakePercent = 0;
var isSuccess = -1;
var resultStatus1 = 0;
var resultStatus2 = 0;
var resultStatus3 = 0;
var resultStatus4 = 0;

$(function(){

	var ua = navigator.userAgent.toLowerCase();

	swiper = new Swiper('.swiper-container',{
		onlyExternal : true,
		onSlideChangeEnd: function(swiper){
			var index = swiper.activeLoopIndex;
			switch(index){
				case 0:
					$('#p0 .banner').addClass('bounceInDown');
					$('#p0 .red div').addClass('bounceInDown');
					break;
				case 1:
					break;
				case 2:
					$('#p2 .slogan').addClass('tada');
					break;
				default:
			};
		}
	})

	$('.marquee').marquee({
		duration: 3000,
		duplicated: true
	});

	$('#p0 .btn-play').bind('touchend',function(event){
		swiper.swipeNext();
		document.getElementById('audio-firecrackers').load();
		shakeStatus = 1;
		shake();
		wx.startRecord();
	})

	$('#p0 .btn-rule a').bind('touchend',function(event){
		$('#p0 .rule').show();
		$('#p0 .rule-main').addClass('bounceInDown');
	})

	$('#p0 .btn-return').bind('touchend',function(event){
		$('#p0 .rule-main').removeClass('bounceInDown');
		$('#p0 .rule-main').addClass('bounceOutUp');
		setTimeout(function(){
			$('#p0 .rule').hide();
			$('#p0 .rule-main').removeClass('bounceOutUp');
		},800);
	})

	$('body').on('touchend','#p1 .btn-play',function(event){
		swiper.swipeNext();
		wx.stopRecord({
			success: function (res) {
				voice.localId = res.localId;
			}
		});
	})

	$('#p2 .btn-record').bind('touchstart',function(event){
		recordStatus = 1;
		waveRecord();
		$('#p2 .wave-record').show();
		document.getElementById('audio-music').pause();
		wx.startRecord({
			cancel: function () {
				alert('请您按住按钮大喊：' + params.word + '！');
			}
		});
	})
	$('#p2 .btn-record').bind('touchend',function(event){
		$('#p2 .wave-record').hide();
		document.getElementById('audio-music').play();
		recordStatus = 0;
		wx.stopRecord({
			success: function (res) {
				voice.localId = res.localId;
				
				wx.playVoice({
					localId: voice.localId,
					success: function (res) {
						console.log("playVoice success");
					},
					fail: function (res) {
						console.log("playVoice fail");
					},
				});

				console.log(voice.localId + ">>>>");
				wx.translateVoice({
					localId: voice.localId,
					isShowProgressTips: 1,
					success: function (res) {
						console.log(res);
						var result = res.translateResult;
						$('#voice-result').val(result);
						$('#voice-result2').val(params.word);
						var resultPY = $('#voice-result').toPinyin();
						if (resultPY == $('#voice-result2').toPinyin()) {
							swiper.swipeNext();
							saveVoice(voice.localId,username);
							
							// 主动触发播放事件
							$('#p3 .btn-play').trigger('touchend');
						} else {
							alert('您刚刚说的是：' + result + '请您按住按钮大喊：' + params.word + '！')
						};
					},
					fail: function (res) {
						console.log(res);
					}
				});
			}
		});
	})

	$('#p3 .btn-play').bind('touchend',function(event){
		if ( $(this).hasClass('pause') ) {
			$(this).removeClass('pause');
			wx.pauseVoice({
				localId: voice.localId
			});
		} else {
			$(this).addClass('pause');
			wx.playVoice({
				localId: voice.localId
			});
		};
	})

	$('#p3 .btn-back').bind('touchend',function(event){
		swiper.swipePrev();
		resultStatus1 = 0;
		resultStatus2 = 0;
		resultStatus3 = 0;
		resultStatus4 = 0;
	})

	$('#p3 .btn-open').bind('touchend',function(event){
		$('#p3 .red').show();
		$('#p3 .red-main').addClass('bounceInDown');
		var username = $('#p3 .tips .username').val();
	})

	$('#p4 .btn-play').bind('touchend',function(event){
		waveLeft = 0;
		$('#p4 .play').show();
		$('#p4 .play-main').addClass('bounceInDown');
	})

	$('#p4 .btn-input').bind('touchend',function(event){
		waveLeft = 0;
		swiper.swipeNext();
		$('#landscape').hide();
	})

	$('#p4 .btn-return').bind('touchend',function(event){
		$('#p4 .play-main').removeClass('bounceInDown');
		$('#p4 .play-main').addClass('bounceOutUp');
		recordStatus = 0;
		$('#p4 .btn-play-popup').removeClass('pause');
		wx.pauseVoice({
			localId: voice.localId
		});
		setTimeout(function(){
			$('#p4 .play').hide();
			$('#p4 .play-main').removeClass('bounceOutUp');
		},800);
	})

	$('#p4 .btn-play-popup').bind('touchend',function(event){
		if ( $(this).hasClass('pause') ) {
			recordStatus = 0;
			$(this).removeClass('pause');
			wx.pauseVoice({
				localId: voice.localId
			});
		} else {
			recordStatus = 1;
			waveRecord();
			$(this).addClass('pause');
			wx.playVoice({
				localId: voice.localId
			});
		};
	})

	$('button').bind('touchstart',function(event){
		event.preventDefault();
		$(this).addClass('hover');
	})
	$('button').bind('touchend',function(event){
		event.preventDefault();
		$(this).removeClass('hover');
	})

	$('.btn-music a').bind('touchend',function(event){
		var classname = $(this).attr('class');
		if ( classname == 'on' ) {
			document.getElementById('audio-music').pause();
			$(this).removeClass('on').addClass('off');
		} else if ( classname == 'off' ){
			document.getElementById('audio-music').play();
			$(this).removeClass('off').addClass('on');
		};
		return false;
	});

})

if(window.DeviceMotionEvent) {
	var speed = 10;
	var x = y = z = lastX = lastY = lastZ = 0;
	window.addEventListener('devicemotion', function(){
		if ( shakeStatus == 1 ) {
			var acceleration = event.accelerationIncludingGravity;
			x = acceleration.x;
			y = acceleration.y;
			if(Math.abs(x-lastX) > speed || Math.abs(y-lastY) > speed) {
				shakeTimes += 1; 
			};
			lastX = x;
			lastY = y;
		}
	}, false);
} else {
	$('#p1 .btn-1').show();
	$('#p1 .shake-tips').hide();
}

function shake(){
	if ( shakePercent >= 5 ) {
		setTimeout(function(){
			swiper.swipeNext();
			wx.stopRecord({
				success: function (res) {
					voice.localId = res.localId;
				}
			});
			document.getElementById('audio-firecrackers').pause();
		},1000);
		return false;
	} else if ( shakePercent >= 3 ) {
		$('#p1 .firecrackers').removeAttr('class').addClass('firecrackers f3');
		$('#p1 .tips span').html('60');
	} else if ( shakePercent >= 1.5 ) {
		$('#p1 .shake-tips').hide();
		$('#p1 .car').css({'opacity':1});
		$('#p1 .firecrackers').removeAttr('class').addClass('firecrackers f2');
		$('#p1 .tips span').html('30');
	};
	if ( shakeStatus == 1 ) {
		if ( shakeTimes > shakeTimesBefore ) {
			document.getElementById('audio-firecrackers').play();
			shakePercent += 0.5;
		} else {
			document.getElementById('audio-firecrackers').pause();
		};
		shakeTimesBefore = shakeTimes;
		setTimeout(function(){
			shake();
		},250);
	} else {
		return false;
	}
}

function waveRecord(){
	if ( recordStatus == 1 ) {
		$('.wave-record').css('background-position', waveLeft + 'px 0px');
		waveLeft = waveLeft - 1;
		// console.log(waveLeft);
		setTimeout('waveRecord()',20);
	};
}

wx.ready(function () {

	wx.onVoicePlayEnd({
		complete: function (res) {
			$('#p3 .btn-play').removeClass('pause');
			$('#p4 .btn-play-popup').removeClass('pause');
			recordStatus = 0;
		}
	});

});