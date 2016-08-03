/**
 * Created by Administrator on 2016/6/30.
 */


;$(function(){

	// console.log('debug');



	//document.addEventListener('touchmove', function(event) {
	//	if($(".mask").is(":visible")){
	//		event.preventDefault();
	//	}
	//});

	function cretaMask(){
		window.$mask = window.$mask || $('<div class="mask" style="position: absolute;top:0;left: 0;right: 0;bottom: 0;z-index:9;min-width: 100%;background-color: rgb(0, 0, 0);opacity: .8;"></div>');
        $mask.css({"height":$(document).height()});
		$('body').append($mask);
		$mask.on('click',function(){
			hideWinFrame();
		});
		$mask.fadeIn();
	}

//隐藏窗口
	function hideWinFrame(){
		$mask.fadeOut();
		$('.win').fadeOut();
	}

	window.showWinFrame = function (type){
		cretaMask();
		$(type).show();
	}

//关闭窗口
	$('.J_close_btn').on('click', function () {
		$mask.fadeOut(function(){
			$(this).remove()
		});
		$('.win').fadeOut();
	});

	

	$('#ListView').on('click','.button__rotate',function(){
		var type = this.className.split(' ')[1];
		switch (type){
			case 'button__rotate-tao':
				// showWinFrame('.win__code');
				return true;
				break;
			case 'button__rotate-get':
				// showWinFrame('.win__code');
				return true;
				break;
			case 'button__blue':
				// $(this).unbind('click');
				return true;
				break;
			case 'button__rotate-end':
				showWinFrame('.win__end');
				break;
		}
		//showWinFrame('.win__share');
	  return false;
	});

	//领号
	$('#get1').on('click',function(){
		TOOL.postEvent($(this).attr('event_id'),1);

		return false;
	});

	//淘号
	$('#get2').on('click',function(){
		TOOL.postEvent($(this).attr('event_id'),2);

		return false;
	});

	//已领
	$('#get3').on('click',function(){
		showWinFrame('.win__code');
		return false;
	});

	//$('.button__rotate-end').on('click',function(){
	//	showWinFrame('.win__end');
	//	return false;
	//});


	//返回顶部
	$(".j_toTop").on('click', function () {
		$('html,body').animate({ "scrollTop": 0 }, 220);
		$(this).hide();
	});


	var wxData = {
		appId:'' ,//appid，可不用这项
		// imgUrl:'http://www.appgame.com/wp-content/uploads/2016/07/zhuxian-zf-tst.jpg', // 缩略图地址
		link: TOOL.domainURI(window.location.href)+'mobile/',// 链接地址
		title: '礼包中心_任玩堂',// 标题
		desc: '礼包中心_任玩堂' // 详细描述
	}
	WeixinApi.ready(function (Api) {
		// 分享的回调
		var wxCallbacks = {
			ready : function() {
			},
			cancel : function(resp) {
			},
			fail : function(resp) {
			},
			confirm : function(resp) {
			},
			all : function(resp) {
			}
		};
		Api.generalShare(wxData, wxCallbacks);
		Api.shareToFriend(wxData, wxCallbacks);
		Api.shareToTimeline(wxData, wxCallbacks);
		Api.shareToWeibo(wxData, wxCallbacks);

		if(typeof(wx) != "undefined"){
			wx.hideMenuItems({
				menuList: ['menuItem:copyUrl','menuItem:openWithQQBrowser','menuItem:openWithSafari']
			});
		}
	});


	$(window).scroll(function () {
		var scrolls = $(this).scrollTop();
		if (scrolls > 100) {
			console.log(scrolls);
			$(".j_toTop").show();
		} else {
			$(".j_toTop").hide();
		}

	});

	if(TOOL.isSys.weixin){
		$('#regLogTab0').hide();
	}
	

});

