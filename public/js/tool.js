/**
 * Created by zwj on 16/7/12.
 */
var TOOL = {
    //passport登录
    site_id:11,
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
                $('#username').html(login_name_tmp);
            } else {
                $('.regLogTab0').hide();
                $('.regLogTab1').show();
                $('#username').html(login_name_tmp);
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
                        $.getJSON('http://192.168.200.196:8000/api/games/auth_passport/?act=login' + para + '&callback=?', function(data) {
                            console.log(data);
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