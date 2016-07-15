var reg_rule = {
    'required'   :    /.+/,
    'email'      :    /^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/,
    'url'        :    /^http|https:\/\/[A-Za-z0-9]+\.[A-Za-z0-9]+[\/=\?%\-&_~`@[\]\':+!]*([^<>\"\"])*$/,
    'currency'   :    /^\d+(\.\d+)?$/,
    'number'     :    /^\d+$/,
    'zip'        :    /^\d{6}$/,
    'integer'    :    /^[-\+]?\d+$/,
    'double'     :    /^[-\+]?\d+(\.\d+)?$/,
    'english'    :    /^[A-Za-z]+$/
};

function showTips(msg,type){
    if(!document.getElementById('ajax_tips')){
        var obj = document.createElement("DIV");
        obj.id = 'ajax_tips';
        obj.className='ajax_success';
        document.body.insertBefore(obj,document.body.firstChild);
    }
    if(type=='error'){
        document.getElementById('ajax_tips').className='ajax_error';
    }else{
        document.getElementById('ajax_tips').className='ajax_success';
    }
    $("#ajax_tips").html(msg);
    $("#ajax_tips").fadeIn("fast");
    window.setTimeout(function (){$("#ajax_tips").fadeOut("slow");},5000);
}

function get_options(form){
    var options = {
        dataType : 'json',
        timeout : 20000,
        beforeSubmit : function (){
            $(form).find('.ajax_btn').attr("disabled","true");
        },
        complete:function(response,status){
            $(form).find('.ajax_btn').removeAttr("disabled");
            if(status!='success'){
                alertMsg.error('请求失败 ！');
            }
        },
        success:response
    };
    return options;
}

function submitByAjax(form){
  if(arguments[0]){//如果传入了form，马上对form进行ajax提交
      var form = $('#'+form);
      if(checkForm(form)==false) return false;
      var options = get_options(form);
      form.ajaxSubmit(options);
  }else{//否则，对标志有class="ajax_form"的表单进行ajax提交的绑定操作
      $('.ajax_form').bind('submit',function(){
            var form = $(this);
            if(checkForm(form)==false) return false;
            var options = get_options(form);
            form.ajaxSubmit(options);
            return false; //<-- important!
      });
  }
}

function checkForm(form){
    var check = true;
    $(form).find('input|textarea|select|checkbox|radio[dataType]').each(function(){
        var val = $.trim( $(this).val() );
        var type = $(this).attr('dataType');
        var title = $(this).attr('title');
        if(!reg_rule[type].test(val)){
            alertMsg.error(title);
            $(this).focus();
            check = false;
            return false;
        }
    });
    return check;
}

/*  百度编辑器调用示例。注意：1、第一个script要加class="ueditor"；2、第一个script的ID名称要和第二个script实例出来的名称相同。
<script type="text/plain" id="ueditor" name="content" style="width:99%;" class="ueditor"></script>
<script type="text/javascript" defer="defer">
    var ueditor = new baidu.editor.ui.Editor();
    ueditor.render("ueditor");
</script>
*/
function save_ueditor(form){ //百度编辑器同步内容
    $(form).find('.ueditor').each(function(){
        obj = $(this).attr('id');
        window[obj].sync();
    });
    return validateCallback(form,navTabAjaxDone)
}

//select的onchange提交表单
function selectSubmit(obj,form,url){
    var action = function(){
        document.getElementById(form).action = url ? url : obj.value;
        submitByAjax(form);
        obj.options[0].selected=true;//重置select，为了能够获取此select的值，故把它放后面
    }
    if(obj.options[obj.selectedIndex].title!=''){//如果某选项设置了title，那么弹出确认。
        alertMsg.confirm(obj.options[obj.selectedIndex].title,{
            okCall: action,
            cancelCall: function(){obj.options[0].selected=true;}
        });
        return false;
    }
    action();
    return false;
}

function myAjax(myurl,mytype,mydata){
    $.ajax({
        url: myurl,    //要提交到的地址
        type: mytype,   //提交的方式，GET或POST
        data: mydata, //这里是你要提交的数据
        dataType: "json",     //这里是返回数据的方式，可以是xml，text,html格式
        timeout: 20000,     //超时时间
        beforeSend: function(){ // 提交之前
        },
        error: function(){
            alertMsg.error('请求失败 ！');
        },
        success:response
    });
}

function response(response){
    if(response.type=='success'){
        typeof(success)=='function' ? success(response.message) : alertMsg.correct(response.message);
    }else if(response.type=='error'){
        typeof(error)=='function' ? error(response.message) : alertMsg.error(response.message);
    }else if(response.type=='refresh'){
        alertMsg.correct(response.message);
        typeof(navTab)=='object' ? navTab.reload() : window.location.href=window.location.href;
    }else if(response.type=='refresh_code'){
        alertMsg.warn(response.message);
        $('#verify_img').click();
    }else if(response.type=='reset'){
        alertMsg.correct(response.message);
        $(form)[0].reset();
    }else{
        alertMsg.info(response.message);
    }
}

//
if(typeof(alertMsg)!='object'){
    var alertMsg = [];
    alertMsg.correct = alertMsg.info = function(msg){showTips(msg,'success');}
    alertMsg.error = alertMsg.warn = function(msg){showTips(msg,'error');}
}
//
$(document).ready(function(){
    submitByAjax();
})