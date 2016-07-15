<?php
/**
///  运行时文件
**/

if(!defined('APP_NAME')) exit();

$runtime_cache_file = CACHE_PATH.'~runtime.php';//本文件只在第一次运行时被加载，以后只加载这个缓存文件

// 加载系统基础函数库
require APP_ROOT.'include/functions.php';
$runtime_content = compile(APP_ROOT.'include/functions.php');//将需要加载的文件代码全部读出来，然后生成一个缓存文件，以后只包含缓存文件
//需要加载的文件列表
$list = array(
    APP_ROOT.'include/config.php',
    APP_ROOT.'lib/core/tpl.class.php',
    APP_ROOT.'include/config.'.APP_NAME.'.php',
);
if(USE_DB){
    $arr = array(
        APP_ROOT.'include/config.db.php',
        APP_ROOT.'lib/core/db.class.php',
     );
     $list = array_merge($list,$arr);
}
//加载文件
foreach ($list as $file){
    if(is_file($file)){
        require_cache($file);
        $runtime_content .= compile($file);
    }
}
//需要创建的缓存目录
$list = array(
    CACHE_PATH.'data',//数据缓存
    CACHE_PATH.'fields',//数据库结构缓存
    CACHE_PATH.'compile',//模板编译缓存
    CACHE_PATH.'images',//图片缓存
);
//创建缓存目录
foreach ($list as $path){
    if(!is_dir($path)){
        if(!@mkdir($path,0777)) throw_error('缓存目录'.$path.'不可写！');
    }
}

file_put_contents($runtime_cache_file,'<?php '.$runtime_content);//生成缓存文件

//[functions]
// 编译PHP文件，用来生成缓存
function compile($filename) {
    $content = php_strip_whitespace($filename);//去除空白、注释等。用PHP自带函数，不支持PHP代码中含有heredoc
    $content = substr(trim($content), 5);
    if ('?>' == substr($content, -2))
        $content = substr($content, 0, -2);
    return $content;
}
//[functions]
?>