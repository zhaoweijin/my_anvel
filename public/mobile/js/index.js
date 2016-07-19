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
		console.log(type);
		switch (type){
			case 'button__rotate-tao':
				showWinFrame('.win__code');
				break;
			case 'button__rotate-get':
				showWinFrame('.win__code');
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

	$('#get1').on('click',function(){
		TOOL.postEvent($(this).attr('event_id'));

		return false;
	});

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

	$(window).scroll(function () {
		var scrolls = $(this).scrollTop();
		if (scrolls > 100) {
			console.log(scrolls);
			$(".j_toTop").show();
		} else {
			$(".j_toTop").hide();
		}

	});


	

});

