this.screenshotPreview = function(){

    xOffset = 20;
    yOffset = 20;

    setTimeout(function(){ //发现在artDialog窗口中无法正确获取，于是这样解决
        winHeight = $(window).height();
        winWidth =  $(window).width();
    },500);

    $(".screenshot").hover(function(e){
        var pic = $(this).attr('rel') || $(this).attr('value') || $(this).attr('href');
        if(!pic) return;
        $("body").append("<div id='screenshot' style='position:absolute;top:0;left:0;visibility:hidden;color:#fff;z-index:10001;'><img style='position:absolute;left:0;top:0;z-index:10003;border:1px solid #333;' width='200' src='"+ pic +"' /><iframe width='202' style='position:absolute;border:none;filter:alpha(opacity=0);-moz-opacity:0;-khtml-opacity: 0;opacity:0;z-index:10002;'></iframe></div>");
        var top = (e.pageY+yOffset);
        var left = (e.pageX+xOffset);
        $("#screenshot img").load(function(){
            var objHeight = $(this).height()+2;
            var objWidth = $(this).width()+2;
            $("#screenshot iframe").height( objHeight );
            if(e.pageY>winHeight/2) top = (e.pageY-objHeight-yOffset);
            if(e.pageX>winWidth/2) left = (e.pageX-objWidth-xOffset);
            $("#screenshot").css('top',top+'px').css('left',left+'px').css('visibility','visible').fadeIn('fast');
        });
    },
    function(){
        $("#screenshot").remove();
    });
    $(".screenshot").mousemove(function(e){
        var top = (e.pageY+yOffset);
        var left = (e.pageX+xOffset);
        var objHeight = $("#screenshot iframe").height();
        var objWidth = $("#screenshot iframe").width();
        if(e.pageY>winHeight/2) top = (e.pageY-objHeight-yOffset);
        if(e.pageX>winWidth/2) left = (e.pageX-objWidth-xOffset);
        $("#screenshot").css('top',top+'px').css('left',left+'px');
    });
};

$(window).load(function(){
    screenshotPreview();
});