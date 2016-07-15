<?php
foreach($list as &$v){
    $content_ip .= $v['ct_ip']."\r\n";
}
file_put_contents(CACHE_PATH.'/'.$game_id.'_ip.txt',$content_ip);
$path = dirname($_SERVER['SCRIPT_NAME'])!='/' ? dirname($_SERVER['SCRIPT_NAME']) : '';
$links = array(
    0 => array('txt'=>'IP列表','link'=>"http://{$_SERVER['SERVER_NAME']}{$path}/cache/{$game_id}_ip.txt"),
);
?>