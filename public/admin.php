<?php
$_beginTime = microtime(TRUE);

session_start();
define('APP_NAME','admin');//项目名称
define('APP_TITLE','维动联运综合后台');
define('APP_ROOT',__DIR__.'/../admin/');//项目路径
define('CACHE_PATH',APP_ROOT.'cache/');//缓存目录
define('DEBUG_MODE',true);//调试模式，生产环境一定要关闭
define('USE_DB',true);//是否使用数据库
define('THEME','admin');//模板主题
define('TPL_PATH',APP_ROOT.'template/'.THEME.'/');//模板目录
define('SESSION_NAME','admin');//session名称
//包含运行时文件
$runtime_cache_file = CACHE_PATH.'~runtime.php';//运行时缓存文件
if(DEBUG_MODE||!is_file($runtime_cache_file)){//调试模式下不加载缓存文件
    require(APP_ROOT.'include/runtime.php');
}else{
    require($runtime_cache_file);
}

//获取模块
$module = empty($_GET['m']) ? 'Home' : $_GET['m'];
if(!import($module,'controller')){
    $module = 'Home';
}
import($module,'controller');
define('MODULE_NAME',$module);//模块名称
//获取操作
$action = empty($_GET['a']) ? 'index' : $_GET['a'];
define('ACTION_NAME',$action);//操作名称
$module = $module.'Action';
$m = new $module();
if(!method_exists($m,$action)){
    $action = '_empty';
}

//不需要自动转义，db类构建的sql语句已自动转义，如果要写原生sql语句，请自行转义
if(get_magic_quotes_gpc()){
    function stripslashes_deep($value){
        $value = is_array($value) ?
            array_map('stripslashes_deep', $value) :
            stripslashes($value);
        return $value;
    }
    $_POST = array_map('stripslashes_deep', $_POST);
    $_GET = array_map('stripslashes_deep', $_GET);
    $_COOKIE = array_map('stripslashes_deep', $_COOKIE);
    $_REQUEST = array_map('stripslashes_deep', $_REQUEST);
}

//运行
$m->$action();

$_endTime = microtime(TRUE);
//echo 'Processed in '.($_endTime-$_beginTime).' second(s)';
?>