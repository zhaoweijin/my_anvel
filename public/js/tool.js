/**
 * Created by zwj on 16/7/12.
 */
var TOOL = {
    //passport登录
    site_id:11,

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
        this.login_type(cmd, sso_token);                 //登录验证
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

    /**
     * 游戏栏目页 获取搜索数据
     */
    getGame:function(e,offset) {
        var tag
            ,id
            ,icon
            ,title
            ,end_date
            ,percent
            ,str=''
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
                if(data){
                    data = data.result;
                    for(var i in data){
                        id = data[i]['id'];
                        icon = data[i]['icon'];
                        title = data[i]['title'];
                        end_date = data[i]['end_date'].substr(0,10);
                        data[i]['total'] = data[i]['total']==0?1000000:data[i]['total'];
                        percent = Math.round(data[i]['get_num']/data[i]['total']*10000)/100.00 +"%";

                        str += '<li><img class="ico" src="'+icon+'" alt=""><div class="text"><h2>'+title+'</h2><div class="bar"><span style="width:'+percent+'"></span></div><p><span>礼包有效期：</span>'+end_date+'</p></div><a href="package-page.html?id='+id+'" class="button__rotate button__blue">领取</a></li>';
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
     * 游戏栏目页 获取搜索数据
     */
    getGame:function(e) {
        var pre_url = this.domainURI(window.location.href)
            ,post_url = pre_url + "appgame/index.php?m=api&c=search&a=get_category"
            ,offset = typeof(arguments[0])=='number'?arguments[0]:0
            ,str = ''            
            ,parm = 'device='+gameListDevice+'&inter='+gameListInter+'&order='+gameListOrder+'&offset='+offset;

        //console.log(parm);
        if(offset-24<0)
            _offset=0;
        else
            _offset=offset-24;

        $.ajax({
            type: "POST",
            url: post_url,
            data:parm,
            async:true,
            dataType: 'json',
            success: function(data) {
                if(data && data.status==1){
                    host = data.host;
                    data = data.result;
                    for(i in data){
                        data[i]['jumpUrl']=data[i]['jumpUrl']?data[i]['jumpUrl']:host+'game/'+data[i]['game_id']+'.html';
                        url = data[i]['jumpUrl'];
                        name = data[i]['game_name_cn']?data[i]['game_name_cn']:data[i]['game_name_en'];
                        str += '<li><a href="'+url+'" title="'+name+'"><span><img src="'+pre_url+data[i]['icon']+'"></span><b>'+name+'</b><p>'+data[i]['types']+'</p></a></li>';
                    }

                    str += "<div class=\"Pagination\"><a href=\"javascript:;\" onclick=\"TOOL.getGame(0)\" class=\"homePage\">首页</a><a href=\"javascript:;\" onclick=\"TOOL.getGame("+_offset+")\" class=\"PagePrev\">上一页</a><div class=\"pagesnum\"></div><a href=\"javascript:;\" onclick=\"TOOL.getGame("+(offset+24)+")\" class=\"PageNext\">下一页</a>";
                    $('.iosGameUl').html(str);
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

    getQueryString:function (name) {
        var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
        var r = window.location.search.substr(1).match(reg);
        if (r != null){
            return unescape(r[2]);
        }else{
            return null;
        }
    }
}