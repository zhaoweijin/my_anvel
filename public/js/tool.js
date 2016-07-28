/**
 * Created by zwj on 16/7/12.
 */
var TOOL = {
    //passport登录
    site_id:11,
    is_get:0,
    //域名匹配
    domainURI:function(str){
        var durl=/http:\/\/([^\/]+)([\/$])?/i;
        domain = str.match(durl);
        if(domain!=null)
            return domain[0];
        else
            return 'http://app.appgame.com/';
    },

    chkLoginStatus : function(cmd) {
        var sso_token = this.getQueryString('sso_token');       //获取sso_token
        this.login_url(cmd);                              //地址拼接
        sso_token && this.login_type(cmd, sso_token);                 //登录验证
    },
    // username显示到页面
    login_tmp : function(login_name_tmp) {
        if (login_name_tmp) {
            if ($('#regLogTab0').length > 0 && $('#regLogTab1').length > 0) {
                $('#regLogTab0').hide();
                $('#regLogTab1').show();
                // $('#username').html(login_name_tmp);
            } else {
                $('.regLogTab0').hide();
                $('.regLogTab1').show();
                // $('#username').html(login_name_tmp);
            }
        }
    },
    // 注销
    loginOut : function(cmd) {
        var chk_passport_loginout_url = 'http://passport.appgame.com/sso?sso_action=logout&return_url=' + cmd.return_url;

        if (cmd.type == 'session') {
            $.getJSON('http://activity.appgame.com/activity/common/userLogin.php?act=logout&callback=?', function(data) {
                if (data.errNum == 1) {
                    // 通知passport退出
                    $.getJSON(chk_passport_loginout_url + '&callback=?');
                    location.href = cmd.return_url;
                }
            })
        }

        if (cmd.type == 'cookie') {
            // 通知passport退出
            $.getJSON(chk_passport_loginout_url + '&callback=?');
            // 清除cookie
            this.setCookie('activity_login_name', '', -1);
            location.href = cmd.return_url;
        }
    },

    // 地址拼接
    login_url : function(cmd) {
        var timestamp = this.getNowTimeStamp(),
            _this     = this;
        cmd.return_url = cmd.return_url ? cmd.return_url : 'http://www.appgame.com/';
        // 登录地址
        $('.login_url').attr('href', 'http://passport.appgame.com/sso?site_id='+this.site_id+'&sso_action=login&time_stamp=' + timestamp + '&return_url=' + cmd.return_url);
        // 注册地址
        $('.reg_url').attr('href', 'http://passport.appgame.com/user/create?return_url=' + cmd.return_url);
        // 注销
        $('.out_url').click(function() {
            _this.loginOut(cmd);
        });
    },

    // passport 登录验证
    login_type : function(cmd, sso_token) {
        switch (cmd.type) {
            case 'session':
                // session登录
                if (!cmd.login_name_tmp) { // 若无session
                    var chk_token_url = 'http://passport.appgame.com/sso?site_id='+this.site_id+'&sso_action=check&sso_token='+sso_token,
                        _this         =  this;

                    $.getJSON(chk_token_url + '&callback=?', function(data) {
                        var para     =  '',
                            username =  data.username;

                        $.each(data, function(key, val) { para += '&' + key + '=' + val; });

                        // 验证并创建session
                        $.getJSON(_this.domainURI(window.location.href)+'api/games/auth_passport/?act=login' + para + '&callback=?', function(data) {

                            if (data.errNum == 1)  cmd.callback ?  cmd.callback(username) : _this.login_tmp(username); // 显示用户名

                        });
                    });
                } else { // 如果有session就直接用

                    cmd.callback ?  cmd.callback() : this.login_tmp(cmd.login_name_tmp); // 显示用户名

                }
                break;

            case 'cookie':
                if (!cmd.login_name_tmp) { // 若无cookie
                    var chk_token_url  =  'http://passport.appgame.com/sso?site_id='+this.site_id+'&sso_action=check&sso_token='+sso_token,
                        _this          =  this;
                    $.getJSON(chk_token_url + '&callback=?', function(data) {
                        if (!data.error) {     // 已登录
                            _this.setCookie('activity_login_name',data.username);
                            cmd.callback ?  cmd.callback() : _this.login_tmp(data.username); // 显示用户名
                        }
                    });
                }else{
                    cmd.callback ?  cmd.callback() : this.login_tmp(cmd.login_name_tmp); // 显示用户名

                }
                break;
        }
    },

    check_weixin : function(open_id,token) {
        var chk_token_url = this.domainURI(window.location.href)+'api/games/auth_passport/?act=weixin&open_id=' + open_id + '&token='+token+'&callback=?',
            _this         =  this;

        // 验证并创建session
        $.getJSON(chk_token_url, function(data) {
            if (data.errNum == 1){
                $('#regLogTab1').show();
            }

        });


    },

    /**
     * 礼包列表
     */
    getGame:function(e,offset) {
        var tag
            ,id
            ,icon
            ,title
            ,end_date
            ,surplus
            ,percent
            ,str=''
            ,mid
            ,pre_url = this.domainURI(window.location.href);
        if(e==2)
            tag = $('#ListView2'),post_url = pre_url + "api/games/new/?offset="+offset;
        else
            tag = $('#ListView'),post_url = pre_url + "api/games/hot/?offset="+offset;

        if(this.isSys.weixin)
            post_url = post_url+'&type=1';
        else if(this.isSys.mobile)
            post_url = post_url+'&type=2';

        $.ajax({
            type: "GET",
            url: post_url,
            // data:parm,
            async:false,
            dataType: 'json',
            success: function(data) {
                if(data && data.status_code==1){
                    data = data.result;
                    for(var i in data){
                        id = data[i]['id'];
                        icon = data[i]['icon'];
                        title = data[i]['title'];
                        end_date = data[i]['end_date'].substr(0,10);
                        surplus =  parseInt(data[i]['total'])>parseInt(data[i]['get_num'])?parseInt(data[i]['total'])-parseInt(data[i]['get_num']):0;
                        data[i]['total'] = data[i]['total']==0?1000000:data[i]['total'];
                        percent = parseInt(surplus/data[i]['total']*100,10) +"%";


                        if(data[i]['is_tao']==1){
                            mid = '<a href="package-page.html?id='+id+'" class="button__rotate button__rotate-tao">淘号</a>';
                        }else if(new Date(end_date)<new Date()){
                            mid = '<a href="javascript:void(0);" class="button__rotate button__rotate-end">结束</a>';
                        }else{
                            mid = '<a href="package-page.html?id='+id+'" class="button__rotate button__rotate-get">领取</a>';
                        }

                        str += '<li><img class="ico" src="'+icon+'" alt=""><div class="text"><h2>'+title+'</h2><div class="bar"><span style="width:'+percent+'"></span></div><p><span>礼包有效期：</span>'+end_date+'</p></div>'+mid+'</li>';
                    }

                    tag.append(str);
                }
            },
            error: function() {
                console.log('网络故障，验证失败！');
                return false;
            }
        });
    },

    /**
     * 获取礼包数据
     */
    getEvent:function(event_id) {

        var pre_url = this.domainURI(window.location.href)
            ,percent
            ,surplus
            ,end_date
            ,down_url
            ,post_url = pre_url + 'api/'+ event_id + "/event";
        $.ajax({
            type: "GET",
            url: post_url,
            async:false,
            dataType: 'json',
            success: function(data) {
                if(data && data.status_code==1){
                    data = data.result;
                    surplus =  parseInt(data[0]['total'])>parseInt(data[0]['get_num'])?parseInt(data[0]['total'])-parseInt(data[0]['get_num']):0;
                    data[0]['total'] = data[0]['total']==0?1000000:data[0]['total'];
                    percent = parseInt(surplus/data[0]['total']*100,10),
                    end_date = data[0]['end_date'].substr(0,10)
                    down_url = 'http://app.appgame.com/game/'+data[0]['game_id']+'.html';
                    $('#title').html(data[0]['title']);
                    $('#icon').attr('src',data[0]['icon']);
                    $('#end_date').html(end_date);
                    $('#percent').attr('data-percent',percent);
                    $('#down_url').attr('href',down_url);
                    $('#content').html(data[0]['description']);
                    // $('.j_get_btn').attr('event_id',data[0]['id']);
                    if(data[0]['zone_url'])
                        $('#zone_url').attr('href',data[0]['zone_url']);



                    if(data[0]['card']){
                        $('#card').html(data[0]['card']);
                        $('#get1').hide();
                        $('#get3').attr('event_id',data[0]['id']).show();
                    }else if(data[0]['is_tao']==1){
                        $('#get1').hide();
                        $('#get2').attr('event_id',data[0]['id']).show();
                    }else if(new Date(end_date)<new Date()){
                        $('#get1').hide();
                        $('#get4').show();
                    }else{
                        $('#get1').attr('event_id',data[0]['id']);
                    }


                }
            },
            error: function() {
                console.log('网络故障，验证失败！');
                return false;
            }
        });
    },



    /**
     * 获取礼包卡号
     */
    postEvent:function(event_id,type) {
        var pre_url = this.domainURI(window.location.href);


        post_url = type==1?pre_url + 'api/pc/'+ event_id + "/event":pre_url + 'api/'+ event_id + "/event/tao";

        _that = this;

        if (!this.isSys.mobile) {
            alert('请用手机浏览器或微信端访问');
            history.back();
        }


        if(_that.is_get==0) {
            $.ajax({
                type: "POST",
                url: post_url,
                async: false,
                dataType: 'json',
                success: function (data) {
                    if (data && typeof(data.result) != "undefined") {
                        data = data.result;
                        if(type==1) {
                            $('#card').html(data[0]['card']);
                            showWinFrame('.win__code');
                        }else {
                            $('#card_tao').html(data['card']);
                            showWinFrame('.win__tao');
                        }
                        _that.is_get++;
                    } else if (typeof(data.error) != "undefined") {

                        if(data.error.status_code==-2){
                            alert(data.error.message);
                            return false;
                        }

                        if(data.error.status_code==-1){
                            if(TOOL.isSys.weixin){
                                showWinFrame('.win__share');
                            }else{
                                alert('请先登陆');
                                history.back();
                            }
                            return false;
                        }

                        if (this.isSys.weixin) {
                            showWinFrame('.win__share');
                        }
                    }
                },
                error: function () {
                    console.log('网络故障，验证失败！');
                    return false;
                }
            });
        }else{

            type==1?showWinFrame('.win__code'):showWinFrame('.win__tao');
        }
    },

    /**
     * 我的礼包
     */
    getMyPackage:function(offset) {
        var tag
            ,id
            ,icon
            ,title
            ,end_date
            ,card
            ,str=''
            ,pre_url = this.domainURI(window.location.href)
            ,tag = $('#ListView')
            ,post_url = pre_url + "api/my/package?offset="+offset;

        $.ajax({
            type: "GET",
            url: post_url,
            // data:parm,
            async:false,
            dataType: 'json',
            success: function(data) {
                if(data && data.status_code==1){
                    data = data.result;
                    if(data || offset>0) {
                        for (var i in data) {
                            id = data[i]['id'];
                            icon = data[i]['icon'];
                            title = data[i]['title'];
                            end_date = data[i]['end_date'].substr(0, 10);
                            card = data[i]['card'];

                            str += '<li><img class="ico" src="' + icon + '" alt=""><div class="text"><h2>' + title + '</h2><p><span>礼包有效期：</span>' + end_date + '</p><p class="code-text"><span>长按制礼包码复：</span><b class="code-id">' + card + '</b></p></div></li>';
                        }
                    }else{
                        $('#my_pack').hide();
                        $('#my_pack2').show();
                    }

                    tag.append(str);
                }else{
                    alert('请先登陆');
                    history.back();
                }
            },
            error: function() {
                console.log('网络故障，验证失败！');
                return false;
            }
        });
    },

    getSearch:function (offset) {
        var wd = decodeURIComponent(TOOL.getQueryString('wd'))
            ,tag
            ,id
            ,icon
            ,title
            ,end_date
            ,str=''
            ,mid
            ,percent
            ,pre_url = this.domainURI(window.location.href)
            ,tag = $('#ListView')
            ,post_url = pre_url + "api/search?wd="+wd+"&offset="+offset;
        $('#search').val(wd);
        $('#title').html(wd);
        $.ajax({
            type: "GET",
            url: post_url,
            // data:parm,
            async:false,
            dataType: 'json',
            success: function(data) {
                if(data && data.status_code==1){
                    $('.index_url').attr('href',data.other.url);
                    data.other.login && $('#regLogTab1').show();
                    data = data.result;
                    if(data || offset>0) {
                        var num = data.length;
                        for (var i in data) {
                            id = data[i]['id'];
                            icon = data[i]['icon'];
                            title = data[i]['title'];
                            end_date = data[i]['end_date'].substr(0, 10);
                            data[i]['total'] = data[i]['total']==0?1000000:data[i]['total'];
                            percent = Math.round(data[i]['get_num']/data[i]['total']*10000)/100.00 +"%";

                            if(data[i]['is_tao']==1){
                                mid = '<a href="package-page.html?id='+id+'" class="button__rotate button__rotate-tao">淘号</a>';
                            }else if(new Date(end_date)<new Date()){
                                mid = '<a href="javascript:void(0);" class="button__rotate button__rotate-end">结束</a>';
                            }else{
                                mid = '<a href="package-page.html?id='+id+'" class="button__rotate button__rotate-get">领取</a>';
                            }

                            str += '<li><img class="ico" src="' + icon + '" alt=""><div class="text"><h2>' + title + '</h2><div class="bar"><span style="width:'+percent+'"></span></div><p><span>礼包有效期：</span>' + end_date + '</p></div>'+mid+'</li>';
                        }
                        $('#num').html(num);
                    }else{
                        $('#my_pack').hide();
                        $('#my_pack2').show();
                    }


                    tag.append(str);
                }
            },
            error: function() {
                console.log('网络故障，验证失败！');
                return false;
            }
        });
    },

    /**
     * pc获取
     */
    pcGetPosition:function (position_id,type,num,async) {
        var id
            ,icon
            ,thumb
            ,title
            ,device
            ,url
            ,str=''
            ,pre_url = this.domainURI(window.location.href)
            ,post_url = pre_url + "api/pc/"+position_id+"/pcposition/?type="+type+"&num="+num;

        $.ajax({
            type: "GET",
            url: post_url,
            // data:parm,
            async:async,
            dataType: 'json',
            success: function(data) {
                if(data && data.status_code==1){
                    data = data.result;

                    if(data)
                        switch (position_id){
                            case 1:
                                str = '<ul>';
                                for (var i in data) {
                                    id = data[i]['id'];
                                    thumb = data[i]['thumb'];
                                    url = data[i]['url'];
                                    title = data[i]['title'];
                                    str += '<li><a href="'+url+'" class="global-link"><em style="background-image:url('+thumb+')"><img src="images/b-700-290.png"></em><i>'+title+'</i></a></li>';
                                }
                                str += '</ul>';
                                $('#slide2').html(str);
                                break;
                            case 4:
                                for (var i in data) {
                                    id = data[i]['id'];
                                    icon = data[i]['icon'];
                                    url = data[i]['url'];
                                    title = data[i]['title'];
                                    device = data[i]['device'];

                                    if(device==1)
                                        var mobile_type = '<a class="down_iphone" href="'+url+'" target="_blank"></a>';
                                    else if(device==2)
                                        var mobile_type = '<a class="down_android" href="'+url+'" target="_blank"></a>';
                                    else
                                        var mobile_type = '<a class="down_iphone" href="'+url+'" target="_blank"></a><a class="down_android" href="'+url+'" target="_blank"></a>';

                                    str += '<li><em style="background-image:url('+icon+')"><img src="images/b-82-82.png"><p>'+mobile_type+'</p></em><i>'+title+'</i><a class="libao_receive" href="'+url+'" target="_blank">领 取</a></li>';
                                }
                                $('.recommended-list').html(str);
                                break;
                            case 6:
                            case 8:
                            case 5:
                                id = data[0]['id'];
                                thumb = data[0]['thumb'];
                                url = data[0]['url'];
                                title = data[0]['title'];
                                str += '<a href="'+url+'" title="'+title+'"><em style=" background-image:url('+thumb+')"><img src="images/b-280-120.png"></em></a>';
                                $('.cleck-link').html(str);
                                break;
                        }
                }
            },
            error: function() {
                console.log('网络故障，验证失败！');
                return false;
            }
        });
    },

    /**
     * pc获取
     */
    pcGetEvent:function (device,num,offset) {
        var id
            ,icon
            ,title
            ,url
            ,str=''
            ,hot
            ,_offset = 0
            ,__offset = 0
            ,pre_url = this.domainURI(window.location.href)
            ,post_url = pre_url + "api/pc/pcevents/?device="+device+"&num="+num+"&offset="+offset;

        if(offset-15<0)
            _offset=0;
        else
            _offset=offset-15;

        $.ajax({
            type: "GET",
            url: post_url,
            async:true,
            dataType: 'json',
            success: function(data) {
                if(data && data.status_code==1){
                    data = data.result;
                    if(data) {
                        str += '<ul class="newest-list clear">';
                        for (var i in data) {
                            id = data[i]['id'];
                            icon = data[i]['icon'];
                            url = pre_url + '/pc/content.html?id=' + id;
                            title = data[i]['title'];
                            hot = data[i]['hot'];

                            if (hot == 1)
                                str += '<li class="ios hot">';
                            else
                                str += '<li>';

                            str += '<em style="background-image:url(' + icon + ')"><img src="images/b-82-82.png"></em><i>' + title + '</i><a class="libao_receive" href="' + url + '" target="_blank">领 取</a></li>';
                        }
                        __offset = offset+15;
                        str += '</ul><div class="page_box_t2"><a class="btn-page prev-page" datapage="" href="javascript:TOOL.pcGetEvent(3,15,'+_offset+');">&lt;</a><a class="cur" href="javascript:TOOL.pcGetEvent(3,15,0);">首页</a><a href="javascript:TOOL.pcGetEvent(3,15,'+_offset+');">上一页</a><a href="javascript:TOOL.pcGetEvent(3,15,'+__offset+');">下一页</a>  <a class="btn-page next-page" datapage="" href="javascript:TOOL.pcGetEvent(3,15,'+__offset+');">&gt;</a></div>';

                        if (device == 1)
                            $('#ios').html(str);
                        else if (device == 2)
                            $('#android').html(str);
                        else
                            $('#all').html(str);
                    }
                }
            },
            error: function() {
                console.log('网络故障，验证失败！');
                return false;
            }
        });
    },

    /**
     * pc获取
     */
    pcRevents:function (num) {
        var id
            ,icon
            ,title
            ,url
            ,str=''
            ,game
            ,pre_url = this.domainURI(window.location.href)
            ,post_url = pre_url + "api/pc/pcrevents/?num="+num;
        $.ajax({
            type: "GET",
            url: post_url,
            async:false,
            dataType: 'json',
            success: function(data) {
                if(data && data.status_code==1){
                    data = data.result;
                    if(data)
                        for (var i in data) {
                            id = data[i]['id'];
                            icon = data[i]['icon'];
                            url = pre_url + 'pc/content.html?id=' + id;
                            title = data[i]['title'];
                            game = data[i]['game'];

                            if(i==0)
                                str += '<li class="cur">';
                            else
                                str += '<li>';

                            str += '<div class="box_a"><span></span><a class="ling" href="'+url+'"></a>'+title+'</div><div class="box_b"><em style="background-image:url('+icon+')"><img src="images/b-72-72.png"></em><a class="ling" href="'+url+'"></a><p class="game_name">'+game+'</p><p class="libao_name">独家礼包</p></div></li>';
                        }

                    $('.grab-list').html(str);
                }
            },
            error: function() {
                console.log('网络故障，验证失败！');
                return false;
            }
        });
    },

    /**
     * 淘号排行
     */
    pcTaohaos:function (num) {
        var id
            ,title
            ,url
            ,str=''
            ,pre_url = this.domainURI(window.location.href)
            ,post_url = pre_url + "api/pc/pctaohaos";
        $.ajax({
            type: "GET",
            url: post_url,
            async:true,
            dataType: 'json',
            success: function(data) {
                if(data && data.status_code==1){
                    data = data.result;
                    if(data)
                        for (var i in data) {
                            id = data[i]['id'];
                            url = pre_url + 'pc/content.html?id=' + id;
                            title = data[i]['title'];

                            str += '<li><a href="'+url+'"><i></i><p>'+title+'</p></a></li>';
                        }

                    $('.amoy-list').html(str);
                }
            },
            error: function() {
                console.log('网络故障，验证失败！');
                return false;
            }
        });
    },

    /**
     * 淘号排行
     */
    pcEventInfo:function (event_id) {
        var id
            ,title
            ,zone_url
            ,down_url
            ,str=''
            ,icon
            ,percent
            ,end_date
            ,start_date
            ,percent_path
            ,surplus
            ,device
            ,pre_url = this.domainURI(window.location.href)
            ,post_url = pre_url + "api/pc/"+event_id+"/eventinfo";
        $.ajax({
            type: "GET",
            url: post_url,
            async:false,
            dataType: 'json',
            success: function(data) {
                if(data && data.status_code==1){
                    data = data.result;
                    if(data){

                        id = data[0]['id'];
                        icon = data[0]['icon'];
                        zone_url = data[0]['zone_url']?data[0]['zone_url']:'';
                        title = data[0]['title'];
                        surplus =  parseInt(data[0]['total'])>parseInt(data[0]['get_num'])?parseInt(data[0]['total'])-parseInt(data[0]['get_num']):0;
                        data[0]['total'] = data[0]['total']==0?1000000:data[0]['total'];
                        percent = parseInt(surplus/data[0]['total']*100,10);
                        end_date = data[0]['end_date'].substr(0,10);
                        start_date = data[0]['start_date'].substr(0,10);
                        if(data[0]['device']==1)
                            device = 'IOS';
                        else if(data[0]['device']==2)
                            device = '安卓';
                        else
                            device = 'IOS、安卓通用';

                        down_url = 'http://app.appgame.com/game/'+data[0]['game_id']+'.html';

                        if(data[0]['is_tao']==1)
                            percent_path = '淘';
                        else
                            percent_path = '<i>'+percent+'</i>%';

                        str += '<div class="surplus-percen"><div class="pie_left"><div class="left"></div></div><div class="pie_right"><div class="right"></div></div><div class="mask">'+percent_path+'</div></div><img src="'+icon+'"><h2>'+title+'</h2><p>剩余<i>'+surplus+'份</i></p><span>礼包有效期：<i>'+end_date+'</i></span>';


                        $('.gamelibao-item').html(str);

                        /********************************************************************************************/


                        str = '<div class="plate-about"><img src="'+icon+'"><h2>'+title+'</h2><p class="game-time">'+end_date+'</p></div><div class="plate-down"><a class="down1" href="'+down_url+'" target="_blank">游戏下载</a><a class="down2" href="'+zone_url+'" target="_blank">游戏专区</a></div>';

                        $('.plate-detail').html(str);



                        str = '<p><span>· 礼包有效期</span></p><p>'+start_date+'——'+end_date+'</p><p><span>· 使用平台</span></p><p>'+device+'</p><p><span>· 礼包内容</span></p><p>'+data[0]['content']+'</p><p><span>· 兑换方式</span></p><p>'+data[0]['description']+'</p>';

                        $('#content').html(str);
                        /********************************************************************************************/

                        str = '';
                        if(typeof(data[0].other) != "undefined"){
                            for(var i in data[0].other){
                                var url = pre_url + 'pc/content.html?id=' + data[0].other[i].id;
                                str += '<li><em style="background-image:url('+data[0].other[i].icon+')"><img src="images/b-82-82.png"></em><i>'+data[0].other[i].title+'</i><a class="libao_receive" href="'+url+'" target="_blank">领 取</a></li>';
                            }
                        }

                        $('.newest-list').html(str);

                    }
                }
            },
            error: function() {
                console.log('网络故障，验证失败！');
                return false;
            }
        });
    },


    /**
     * 最新活动
     */
    pcActivitys:function (num,callback) {
        var title
            ,url
            ,str=''
            ,thumbnail
            ,post_url = "http://hd.appgame.com/?json=1&count="+num;

        $.getJSON(post_url + '&callback=?', function(data) {
            if(data){
                data = data.posts;
                for(var i in data){
                    title = data[i].title;
                    thumbnail = data[i].thumbnail;
                    url = data[i].url;

                    if(i==0)
                        str += '<li class="cur">';
                    else
                        str += '<li>';

                    str += '<a href="'+url+'"><em style="background-image: url('+thumbnail+')"><img src="images/b-280-120.png"></em><i>·'+title+'</i></a></li>';
                }
                $('#activity').html(str);
                callback();
            }
        });
    },

    /**
     * 最新活动
     */
    pcNews:function (num,callback) {
        var title
            ,url
            ,str=''
            ,thumbnail
            ,post_url = "http://www.appgame.com/archives/category/v5/v5-hot2?json=1&count="+num;

        $.getJSON(post_url + '&callback=?', function(data) {
            if(data){
                data = data.posts;
                for(var i in data){
                    title = data[i].title;
                    thumbnail = data[i].thumbnail;
                    url = data[i].url;

                    if(i==0)
                        str += '<li class="cur">';
                    else
                        str += '<li>';

                    str += '<a href="'+url+'"><em style="background-image: url('+thumbnail+')"><img src="images/b-280-120.png"></em><i>·'+title+'</i></a></li>';
                }
                $('#news').html(str);
                callback();
            }
        });
    },

    pcSearch:function () {
        var wd = decodeURIComponent(TOOL.getQueryString('wd'))
            ,id
            ,icon
            ,title
            ,device
            ,str=''
            ,str2=''
            ,pre_url = this.domainURI(window.location.href)
            ,post_url = pre_url + "api/pc/pcsearch?wd="+wd;
        $.ajax({
            type: "GET",
            url: post_url,
            async:true,
            dataType: 'json',
            success: function(data) {
                if(data && data.status_code==1){
                    data = data.result;
                    if(data) {
                        var num = data.length;
                        for (var i in data) {
                            id = data[i]['id'];
                            icon = data[i]['icon'];
                            url = pre_url + 'pc/content.html?id=' + id;
                            title = data[i]['title'];
                            device = data[i]['device'];

                            if(device==1)
                                var mobile_type = '<a class="down_iphone" href="'+url+'" target="_blank"></a>';
                            else if(device==2)
                                var mobile_type = '<a class="down_android" href="'+url+'" target="_blank"></a>';
                            else
                                var mobile_type = '<a class="down_iphone" href="'+url+'" target="_blank"></a><a class="down_android" href="'+url+'" target="_blank"></a>';

                            str += '<li><em style="background-image:url('+icon+')"><img src="images/b-82-82.png"><p>'+mobile_type+'</p></em><i>'+title+'</i><a class="libao_receive" href="'+url+'" target="_blank">领 取</a></li>';
                        }

                        str2 = '<p>为您找到<i>'+num+'款</i> 与“<span>'+wd+'</span>”相关的礼包</p>';
                        $('.search-text').html(str2);
                        $('.recommended-list2').html(str);
                    }else{

                        if(wd != 'null')
                            str2 = '<p>没有为您找到与“<span>'+wd+'</span>”相关的礼包</p>';
                        else
                            str2 = '<p>请输入关键字为您搜索相关的礼包</p>';
                        $('.search-text').html(str2);
                    }
                }
            },
            error: function() {
                console.log('网络故障，验证失败！');
                return false;
            }
        });
    },

    setCookie  : function(cookieName, cookieValue, seconds) {
        var expires = new Date();
        expires.setTime(expires.getTime() + parseInt(seconds)*1000);
        document.cookie = encodeURI(cookieName) + '=' + encodeURI(cookieValue) + (seconds ? ('; expires=' + expires.toGMTString()) : "") + '; path=/; domain=appgame.com;';
    },

    getNowTimeStamp:function () {
        return new Date().getTime();
    },

    getQueryString:function (str) {
        // var LocString=String(window.document.location.href);
        // var rs = new RegExp("(^|)"+str+"=([^/&]*)(/&|$)","gi").exec(LocString), tmp;
        //
        // if(tmp=rs){
        //     return tmp[2];
        // }
        //
        // // parameter cannot be found
        // return "";
        var reg = new RegExp("(^|&)" + str + "=([^&]*)(&|$)","i");
        var result = window.location.search.substr(1).match(reg);
        if (result!=null) {
            return result[2];
        } else {
            return null;
        }
    },


    /**
     *系统判断
     */
    isSys:function(){
        var u = navigator.userAgent, app = navigator.appVersion,u_lower=u.toLowerCase();
        return {         //移动终端浏览器版本信息
            trident: u.indexOf('Trident') > -1, //IE内核
            presto: u.indexOf('Presto') > -1, //opera内核
            webKit: u.indexOf('AppleWebKit') > -1, //苹果、谷歌内核
            gecko: u.indexOf('Gecko') > -1 && u.indexOf('KHTML') == -1, //火狐内核
            mobile: u.indexOf('Mobile')> -1 || u.indexOf('Android')> -1 || u.indexOf('Silk/')> -1 || u.indexOf('Kindle')> -1 || u.indexOf('BlackBerry')> -1 || u.indexOf('Opera Mini')> -1 || u.indexOf('Opera Mobi')> -1, //是否为移动终端
            ios: !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/), //ios终端
            android: u.indexOf('Android') > -1 || u.indexOf('Linux') > -1, //android终端或uc浏览器
            iPhone: u.indexOf('iPhone') > -1 , //是否为iPhone或者QQHD浏览器
            iPad: u.indexOf('iPad') > -1, //是否iPad
            iPod: u.indexOf('iPod') > -1, //是否iPod
            webApp: u.indexOf('Safari') == -1, //是否web应该程序，没有头部与底部
            windowsPhone: !!u.match(/Windows\sPhone.*/),
            weixin: u_lower.match(/MicroMessenger/i)=="micromessenger"
        };
    }()
}