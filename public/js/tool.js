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
                        $.getJSON('http://192.168.200.196:8060/api/games/auth_passport/?act=login' + para + '&callback=?', function(data) {

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
        var chk_token_url = 'http://192.168.200.196:8060/api/games/auth_passport/?act=weixin&open_id=' + open_id + '&token='+token+'&callback=?',
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
            ,percent
            ,str=''
            ,mid
            ,pre_url = this.domainURI(window.location.href);
        if(e==2)
            tag = $('#ListView2'),post_url = pre_url + "api/games/new/?offset="+offset;
        else
            tag = $('#ListView'),post_url = pre_url + "api/games/hot/?offset="+offset;

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
                        data[i]['total'] = data[i]['total']==0?1000000:data[i]['total'];
                        percent = Math.round(data[i]['get_num']/data[i]['total']*10000)/100.00 +"%";


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
            ,post_url = pre_url + 'api/'+ event_id + "/event";
        $.ajax({
            type: "GET",
            url: post_url,
            async:false,
            dataType: 'json',
            success: function(data) {
                if(data && data.status_code==1){
                    data = data.result;
                    var percent = data[0]['total']==0?data[0]['total']:parseInt(data[0]['get_num']/data[0]['total']*100,10),
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
    postEvent:function(event_id) {
        var pre_url = this.domainURI(window.location.href)
            ,post_url = pre_url + 'api/'+ event_id + "/event";

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
                        $('#card').html(data[0]['card']);
                        showWinFrame('.win__code');
                        _that.is_get++;
                    } else if (typeof(data.error) != "undefined") {

                        if(data.error.status_code==-2){
                            alert('已领取完');
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
            showWinFrame('.win__code');
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

    setCookie  : function(cookieName, cookieValue, seconds) {
        var expires = new Date();
        expires.setTime(expires.getTime() + parseInt(seconds)*1000);
        document.cookie = encodeURI(cookieName) + '=' + encodeURI(cookieValue) + (seconds ? ('; expires=' + expires.toGMTString()) : "") + '; path=/; domain=appgame.com;';
    },

    getNowTimeStamp:function () {
        return new Date().getTime();
    },

    getQueryString:function (str) {
        var LocString=String(window.document.location.href);
        var rs = new RegExp("(^|)"+str+"=([^/&]*)(/&|$)","gi").exec(LocString), tmp;

        if(tmp=rs){
            return tmp[2];
        }

        // parameter cannot be found
        return "";
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