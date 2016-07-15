<?php
//生成URL
function U($params=array(),$redirect=false){
    if(empty($params)){
        return APP_NAME.'.php';
    }
    if(isset($params['app'])){
        $file = $params['app'];
        unset($params['app']);
    }elseif(APP_NAME=='home'){
        $file = 'index';
    }else{
        $file = APP_NAME;
    }
    if(!isset($params['m'])){
        $params = array_merge(array('m'=>MODULE_NAME),$params);
    }
    return $file.'.php?'.http_build_query($params);
}
// 浏览器友好的变量输出（调试用）
function dump($var) {
    echo '<pre>';
    print_r($var);
    echo '</pre>';
}
// URL重定向
function redirect($url,$time=0,$msg=''){
    //多行URL地址支持
    $url = str_replace(array("\n", "\r"), '', $url);
    if(empty($msg))
        $msg = "系统将在{$time}秒之后自动跳转到{$url}！";
    if (!headers_sent()) {
        // redirect
        if(0===$time) {
            header("Location: ".$url);
        }else {
            header("refresh:{$time};url={$url}");
            echo($msg);
        }
        exit();
    }else{
        $str = "<meta http-equiv='Refresh' content='{$time};URL={$url}'>";
        if($time!=0)
            $str .= $msg;
        exit($str);
    }
}
// 快速文件数据读取和保存 针对简单类型数据 字符串、数组
function F($name,$value='',$path=CACHE_PATH){
    static $_cache = array();
    $filename = $path.$name.'.php';
    if('' !== $value){
        if(is_null($value)) {
            // 删除缓存
            return unlink($filename);
        }else{
            // 缓存数据
            $dir   =  dirname($filename);
            // 目录不存在则创建
            if(!is_dir($dir))  mkdir($dir);
            return file_put_contents($filename,"<?php\nreturn ".var_export($value,true).";\n?>");
        }
    }
    if(isset($_cache[$name])) return $_cache[$name];
    // 获取缓存数据
    if(is_file($filename)) {
        $value = include $filename;
        $_cache[$name] = $value;
    }else{
        $value = false;
    }
    return $value;
}
//设置缓存  针对简单类型数据 字符串、数组
function set_cache($name,$value,$path=''){
    if($path=='') $path = CACHE_PATH.'data/';
    $filename = $path.$name.'.php';
    if('' !== $value) {
        if(is_null($value)) {
            // 删除缓存
            return unlink($filename);
        }else{
            // 缓存数据
            $dir =  dirname($filename);
            // 目录不存在则创建
            if(!is_dir($dir)) mkdir($dir);
            return file_put_contents($filename,"<?php\nreturn ".var_export($value,true).";\n?>");
        }
    }
}
//获取缓存 针对简单类型数据 字符串、数组
function get_cache($name,$expire=7200,$path=''){
    if($path=='') $path = CACHE_PATH.'data/';
    static $_cache = array();
    $filename = $path.$name.'.php';
    if(isset($_cache[$name])) return $_cache[$name];
    // 获取缓存数据
    if(is_file($filename)) {
         if(time() > filemtime($filename) + $expire){
             $value = false;
         }else{
             $value = include $filename;
             $_cache[$name] = $value;
         }
    }else{
        $value = false;
    }
    return $value;
}
/**
 +----------------------------------------------------------
 * 字符串命名风格转换
 * type
 * =0 将Java风格转换为C的风格
 * =1 将C风格转换为Java的风格
 +----------------------------------------------------------
 */
function parse_name($name,$type=0){
    if($type){
        return ucfirst(preg_replace("/_([a-zA-Z])/e", "strtoupper('\\1')", $name));
    }else{
        $name = preg_replace("/[A-Z]/", "_\\0", $name);
        return strtolower(trim($name, "_"));
    }
}
// * 把返回的数据集转换成Tree
function list_to_tree($list, $pk='id',$pid = 'pid',$child = '_child',$root=0){
    // 创建Tree
    $tree = array();
    if(is_array($list)) {
        // 创建基于主键的数组引用
        $refer = array();
        foreach($list as $key => $data){
            $refer[$data[$pk]] =& $list[$key];
        }
        foreach($list as $key => $data){
            // 判断是否存在parent
            $parentId = $data[$pid];
            if($root == $parentId) {
                $tree[] =& $list[$key];
            }else{
                if(isset($refer[$parentId])){
                    $parent =& $refer[$parentId];
                    $parent[$child][] =& $list[$key];
                }
            }
        }
    }
    return $tree;
}
//将Tree转换成List，以便模板赋值输出
function tree_to_list($tree,$depth=0,$left=10){
    static $myarray=array();
    static $depth;
    foreach($tree as $v){
        $space='margin-left:'.$left*$depth.'px';//下级缩进
        $depth % 2==0?$color='':$color='color:green';//下级和上级分开颜色
        $temp=$v;
        unset($temp['_child']);
        $temp['space']=$space;
        $temp['color']=$color;
        $myarray[]=$temp;
        empty($v['_child'])?'':tree_to_list($v['_child'],++$depth);
    }
    $depth!=0?--$depth:'';
    return $myarray;
}
//抛出错误
function throw_error($msg){
    header("Content-type: text/html; charset=utf-8");
    echo $msg;
    exit();
}
// 优化的require_once
function require_cache($filename,$path=''){
    static $_importFiles = array();
    $filename = realpath($path.$filename);
    if (!isset($_importFiles[$filename])) {
        if(file_exists($filename)){
            require $filename;
            $_importFiles[$filename] = true;
        }else{
            $_importFiles[$filename] = false;
        }
    }
    return $_importFiles[$filename];
}
//加载类库
function import($class,$type='extend',$ext='.class.php'){
    if($type=='controller' || $type=='model'){
        $ext = $type == 'controller' ? 'Action.class.php' : 'Model.class.php';
        $file = APP_ROOT.$type.'/'.APP_NAME.'/'.$class.$ext;
        if(!is_file($file)){
            $file = APP_ROOT.$type.'/'.$class.$ext;
        }
    }elseif($type=='core' || $type=='extend'){
        $file = APP_ROOT.'lib/'.$type.'/'.$class.$ext;
    }else{
        return false;
    }
    return require_cache($file);
}
//实例化模型
function M($name){
    if(!$name) $name = 'Default';
    static $_model = array();
    if(isset($_model[$name])) return $_model[$name];
    $load = import($name,'model');
    if($load){
        $Model = $name.'Model';
        $_model[$name] = new $Model();
        return $_model[$name];
    }
    return false;
}
//实例化action，跨模块操作
function A($name){
    static $_action = array();
    if(isset($_action[$name])) return $_action[$name];
    $load = import($name,'controller');
    if($load){
        $action = $name.'Action';
        $_action[$name] = new $action();
        return $_action[$name];
    }
    return false;
}
//获取列表
function get_list($params=array()){
    if(isset($params['cache'])){
        $key = md5(serialize($params).'get_list');
        $list = get_cache($key,$params['cache']);
        if($list === false){
            $model = M($params['model']);
            $list = $model->get_list($params);
            set_cache($key,$list);
        }
        return $list;
    }else{
        $model = M($params['model']);
        return $model->get_list($params);
    }
}
//获取一条记录
function get_one($params=array()){
    if(isset($params['cache'])){
        $key = md5(serialize($params).'get_one');
        $list = get_cache($key,$params['cache']);
        if($list === false){
            $model = M($params['model']);
            $list = $model->get_one($params);
            set_cache($key,$list);
        }
        return $list;
    }else{
        $model = M($params['model']);
        return $model->get_one($params);
    }
}
//生成静态缓存图片
function getpic($pic,$width=1000,$height=1000,$water=false,$nopic='nopic.jpg'){
    if(!file_exists($pic)){//缺省图像
        $pic = APP_ROOT.'template/common/images/nopic.jpg';
    }
    //取得图片后缀
    $pathinfo = pathinfo($pic);
    $picext = $pathinfo['extension'];
    $picname = md5($pic);
    $picpath = substr($picname,0,1);
    $picpath = CACHE_PATH.'images/'.$picpath;
    if(!is_dir($picpath)){
        mkdir($picpath,0777);
    }
    $newpic=$picpath.'/'.$picname.'.'.$width.'x'.$height.'.'.$picext;//新图名称
    if(file_exists($newpic)){
        //如果存在此图片，则返回它
        return $newpic;
    }else{//否则生成新图片，并返回
        import('Image');
        $info=Image::thumb($pic,$newpic,'',$width,$height);
        //如果设置了水印，生成水印的缩略图
        $water==true?Image::water($newpic,APP_ROOT.'template/common/images/water.png',null,80):'';
        return $newpic;
    }
}
/*************输出空格**********************/
//用于select的下级缩进
function writespace($num){
    for($i=0;$i<$num;$i++){
        $content.='&nbsp;';
    }
    return $content;
}
/*************输出下拉列表选项***************/
//$arr可以是数组，树，或者以下格式
//男:0,女:1
function myselect($arr,$default=false,$depth=0,$left=2){
    if(!$arr) return false;
    static $depth=0;//深度
    //如果传入的数据是 0:男,1:女 这样的格式，先转换成数组
    if(!is_array($arr)){
        $tmp=explode(',',$arr);
        foreach($tmp as $v){
            $tt=explode(':',$v);
            $a[]=array(
                'text'=>$tt[0],
                'value'=>$tt[1],
            );
        }
        myselect($a,$default);
    }else{
        foreach($arr as $v){
            (string)$v['value']===(string)$default?$selected='selected':$selected='';
            $space=writespace($left*$depth);//下级缩进
            $depth % 2==0?$color='':$color='style="color:green"';//下级和上级分开颜色
            echo '<option value="'.$v['value'].'" '.$selected.' '.$color.'>'.$space.$v['text'].'</option>';
            empty($v['_child'])?'':myselect($v['_child'],$default,$depth++);
        }
    }
    $depth!=0?--$depth:'';//退回上层，深度减1
}

 /*************输出多项或单项选择***************/
//$arr可以是数组，或者以下格式
//男:0,女:1
function mycheckbox($name,$arr,$default=false,$parameters='',$type='radio'){
    if(!$arr) return false;
    //如果传入的数据是 0:男,1:女 这样的格式，先转换成数组
    if(!is_array($arr)){
        $tmp=explode(',',$arr);
        foreach($tmp as $v){
            $tt=explode(':',$v);
            $a[]=array(
                'text'=>$tt[0],
                'value'=>$tt[1],
            );
        }
        mycheckbox($name,$a,$default,$parameters,$type);
    }else{
        if(!is_array($default)){
            $default=explode(',',$default);
        }
        foreach($arr as $v){
            in_array($v['value'],$default)?$checked='checked':$checked='';
            echo '<input name="'.$name.'" type="'.$type.'" value="'.$v['value'].'" '.$checked.' '.$parameter.' /> '.$v['text'];
        }
    }
}
function out_select($name,$arr,$default=false,$parameters=''){
    echo '<select name="'.$name.'" '.$parameters.' >';
    myselect($arr,$default);
    echo '</select>';
}
function out_checkbox($name,$arr,$default=false,$parameters=''){
    mycheckbox($name,$arr,$default,$parameters,'checkbox');
}
function out_radio($name,$arr,$default='',$parameters=''){
    mycheckbox($name,$arr,$default,$parameters,'radio');
}
function out_input($name,$value,$parameters=''){
    echo '<input type="text" name="'.$name.'" value="'.$value.'" '.$parameters.' />';
}
function out_text($name,$value,$parameters=''){
    echo '<textarea name="'.$name.'" '.$parameters.' >'.$value.'</textarea>';
}
//获取字符串的首字母（utf-8）
function getfirstchar($str){
    $asc = ord($str{0});
    if($asc >= ord('A') && $asc <= ord('z')) return strtoupper($str{0});
    if($asc >= ord(0) && $asc <= ord(9)) return $str{0};
    $s = iconv('UTF-8','gb2312', $str);
    $asc = ord($s{0}) * 256 + ord($s{1}) - 65536;
    if($asc >= -20319 and $asc <= -20284) return 'A';
    if($asc >= -20283 and $asc <= -19776) return 'B';
    if($asc >= -19775 and $asc <= -19219) return 'C';
    if($asc >= -19218 and $asc <= -18711) return 'D';
    if($asc >= -18710 and $asc <= -18527) return 'E';
    if($asc >= -18526 and $asc <= -18240) return 'F';
    if($asc >= -18239 and $asc <= -17923) return 'G';
    if($asc >= -17922 and $asc <= -17418) return 'H';
    if($asc >= -17417 and $asc <= -16475) return 'J';
    if($asc >= -16474 and $asc <= -16213) return 'K';
    if($asc >= -16212 and $asc <= -15641) return 'L';
    if($asc >= -15640 and $asc <= -15166) return 'M';
    if($asc >= -15165 and $asc <= -14923) return 'N';
    if($asc >= -14922 and $asc <= -14915) return 'O';
    if($asc >= -14914 and $asc <= -14631) return 'P';
    if($asc >= -14630 and $asc <= -14150) return 'Q';
    if($asc >= -14149 and $asc <= -14091) return 'R';
    if($asc >= -14090 and $asc <= -13319) return 'S';
    if($asc >= -13318 and $asc <= -12839) return 'T';
    if($asc >= -12838 and $asc <= -12557) return 'W';
    if($asc >= -12556 and $asc <= -11848) return 'X';
    if($asc >= -11847 and $asc <= -11056) return 'Y';
    if($asc >= -11055 and $asc <= -10247) return 'Z';
    //这里添加生僻字
    $s = substr($str,0,3);
    if(in_array($s,array('麒'))){
        return 'Q';
    }
    if(in_array($s,array('蝌'))){
        return 'K';
    }
    return '~';
}
function remove_xss($data){
    do
    {
        $old_data = $data;
        // Fix &entity\n;
        $data = str_replace(array('&amp;','&lt;','&gt;'), array('&amp;amp;','&amp;lt;','&amp;gt;'), $data);
        $data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
        $data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
        $data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');

        // Remove any attribute starting with "on" or xmlns
        $data = preg_replace('#(<.*?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $data);

        // Remove javascript: and vbscript: protocols
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $data);
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $data);
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $data);

        // Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
        $data = preg_replace('#(<.*?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
        $data = preg_replace('#(<.*?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
        $data = preg_replace('#(<.*?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $data);

        // Remove namespaced elements (we do not need them)
        $data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);

        // Remove really unwanted tags
        $data = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $data);
    }
    while ($old_data !== $data);

    // we are done...
    return $data;
}
function remove_xss_deep($data){
    $data = is_array($data) ? array_map('remove_xss_deep',$data) : remove_xss($data);
    return $data;
}
if(!function_exists('get_game_data')){
    function get_game_data($game_id){
        $apiUrl = 'http://app.appgame.com/api/game/';

        $appkey = "M6GHa56MJxy2S3Pc";
        $url_path = '/api/game/gamelist';

        $paramsArray['c'] = 'game';
        $paramsArray['a'] = 'gamelist';

        $paramsArray['timestamp'] = time();
        $paramsArray['page_num'] = 1;//查询页数[即查询第几页] 查内容时为0
        $paramsArray['num_pre_page'] = 50;//每页条数,默认50条

        $paramsArray['game_id'] = $game_id; //游戏id

        //签名生成串
        $strs = strtoupper('GET') . '&' . rawurlencode($url_path) . '&';
        ksort($paramsArray);
        $query_string = array();
        foreach ($paramsArray as $key => $val ){
            array_push($query_string, $key . '=' . $val);
        }
        $query_string = join('&', $query_string);
        $mk = $strs . str_replace('~', '%7E', rawurlencode($query_string));
        $my_sign = hash_hmac("sha1", $mk, $appkey, true);
        $trans = array('+' => '-', '/' => '_', '=' => '');
        $sig = strtr(base64_encode($my_sign), $trans);



        $paramsArray['sig'] = $sig;
        $query_string = array();
        foreach ($paramsArray as $key => $val ){
            array_push($query_string, $key . '=' . $val);
        }
        $query_string = join('&', $query_string);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl.'?'.$query_string);
        ob_start();
        curl_exec($ch);
        $result = ob_get_contents() ;
        ob_end_clean();

        return $result;
    }
}

if(!function_exists('input_csv')){
    function input_csv($handle) { 
        $out = array (); 
        $n = 0; 
        while ($data = fgetcsv($handle, 10000)) { 
            $num = count($data); 
            for ($i = 0; $i < $num; $i++) { 
                $out[$n][$i] = $data[$i]; 
            } 
            $n++; 
        } 
        return $out; 
    } 
}
?>