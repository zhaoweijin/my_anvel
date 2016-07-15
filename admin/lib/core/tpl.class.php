<?php
class tpl{

    public $debug = false;          //是否开启调试模式
    public $tpl_dir = '';           //模板目录
    public $tpl_cache = '';         //模板编译缓存目录
    public $tpl_replace = array();  //需要替换的模板字符串
    private $tpl_content;           //模板内容
    private $tpl_var = array();     //模板变量
    
    private function parse_include(){
        $reg = '/<include file=[\"\'](.*?)[\"\']\s*\/>/is';
        preg_match_all($reg,$this->tpl_content,$matches);
        if(!empty($matches[1])){
            foreach($matches[0] as $k=>$v){
                $tpl_file = $this->tpl_dir.$matches[1][$k];
                if(is_file($tpl_file)){
                    $replace = file_get_contents($tpl_file);
                    $this->tpl_content = str_replace($v,$replace,$this->tpl_content);
                    //检查被包含文件是否又有包含
                    preg_match($reg,$replace,$nested);
                    if(!empty($nested)){
                        $check_nested = true;
                    }
                }
            }
            if($check_nested){//被包含文件又有包含，则再解析一次，直到全部解析完毕
                $this->parse_include();
            }
        }
    }

    /** 解析开始标签 **/
    private function parse_tag_start($name){
        preg_match_all('/[{|<]'.$name.'(\s.*?)[}|>]/is',$this->tpl_content,$matches);
        if(!empty($matches[1])){
            foreach($matches[0] as $k=>$v){
                $params_arr = array(); //参数
                /*
                //preg_match_all('/\s(\w+)=[\"|\'](.*?)[\"|\']/',$matches[1][$k],$params); //参数值不能有单引号
                preg_match_all('/\s(\w+)=[\"|\'](\S*)[\"|\']/',$matches[1][$k],$params); //参数值不能有空格
                if(!empty($params[1])){
                    $size = sizeof($params[1]);
                    for($i=0;$i<$size;$i++){
                        $params_arr[$params[1][$i]] = $params[2][$i];
                    }
                }
                // 参数值中有单引号，空格，以上有问题
                */
                preg_match_all('/\s(\w+)=\'(.*?)\'/',$matches[1][$k],$params1);
                preg_match_all('/\s(\w+)=\"(.*?)\"/',$matches[1][$k],$params2);
                if(!empty($params1[1])){
                    $size = sizeof($params1[1]);
                    for($i=0;$i<$size;$i++){
                        $params_arr[$params1[1][$i]] = $params1[2][$i];
                    }
                }
                if(!empty($params2[1])){
                    $size = sizeof($params2[1]);
                    for($i=0;$i<$size;$i++){
                        $params_arr[$params2[1][$i]] = $params2[2][$i];
                    }
                }
                $tag_start = new tag_start();
                $fun = '_'.$name;
                $search = $v;
                $replace = $tag_start->$fun($params_arr);
                $this->tpl_content = str_replace($search,$replace,$this->tpl_content);
            }
        }
    }

    /** 解析结束标签 **/
    private function parse_tag_end($name){
        preg_match_all('/[{|<]\/'.$name.'[}|>]/is',$this->tpl_content,$matches);
        if(!empty($matches[0])){
            foreach($matches[0] as $v){
                $tag_end = new tag_end();
                $fun = '_'.$name;
                $search = $v;
                $replace = $tag_end->$fun();
                $this->tpl_content = str_replace($search,$replace,$this->tpl_content);
            }
        }
    }

    private function parse_tag_sigle($name){
        preg_match_all('/[{|<]'.$name.'\s*\/?[}|>]/is',$this->tpl_content,$matches);
        if(!empty($matches[0])){
            foreach($matches[0] as $v){
                $tag_sigle = new tag_sigle();
                $fun = '_'.$name;
                $search = $v;
                $replace = $tag_sigle->$fun();
                $this->tpl_content = str_replace($search,$replace,$this->tpl_content);
            }
        }
    }

    private function parse_fun(){
        $this->tpl_content = preg_replace('/\{~(\w+)\((.*?)\)}/','<?php echo \\1(\\2); ?>',$this->tpl_content);
        $this->tpl_content = preg_replace('/\{:(\w+)\((.*?)\)}/','<?php \\1(\\2); ?>',$this->tpl_content);
    }

    /** 解析变量 **/
    private function parse_var(){
        $this->tpl_content = preg_replace('/\$(\w+)\.(\w+)\.(\w+)\.(\w+)/is','$\\1[\'\\2\'][\'\\3\'][\'\\4\']',$this->tpl_content);
        $this->tpl_content = preg_replace('/\$(\w+)\.(\w+)\.(\w+)/is','$\\1[\'\\2\'][\'\\3\']',$this->tpl_content);
        $this->tpl_content = preg_replace('/\$(\w+)\.(\w+)/is','$\\1[\'\\2\']',$this->tpl_content);
    }

    /** 解析输出变量 **/
    private function parse_echo_var(){
        $this->tpl_content = preg_replace('/\{\$(\w|\[|\]+)\.(\w+)\.(\w+)\.(\w+)\.(\w+)\}/is','<?php echo $\\1[\'\\2\'][\'\\3\'][\'\\4\'][\'\\5\'];?>',$this->tpl_content);
        $this->tpl_content = preg_replace('/\{\$(\w+)\.(\w+)\.(\w+)\.(\w+)\}/is','<?php echo $\\1[\'\\2\'][\'\\3\'][\'\\4\'];?>',$this->tpl_content);
        $this->tpl_content = preg_replace('/\{\$(\w+)\.(\w+)\.(\w+)\}/is','<?php echo $\\1[\'\\2\'][\'\\3\'];?>',$this->tpl_content);
        $this->tpl_content = preg_replace('/\{\$(\w+)\.(\w+)\}/is','<?php echo $\\1[\'\\2\'];?>',$this->tpl_content);
        $this->tpl_content = preg_replace('/\{\$(\w+\[*.*?\]*)\}/is','<?php echo $\\1;?>',$this->tpl_content);//{$name},{$name[$k]}
        $this->tpl_content = preg_replace('/\{__([A-Z_]+)__\}/is','<?php echo \\1;?>',$this->tpl_content);//增加对常量输出的支持：{__NAME__}
    }

    /** 编译模板 **/
    private function compile(){//注意解析顺序
        //解析模板包含
        $this->parse_include();
        //解析输出变量
        $this->parse_echo_var();//这个放最前
        //解析开始标签
        $tag_start = new tag_start();
        $fun = get_class_methods($tag_start);
        foreach($fun as $name){
            if(substr($v,0,1)=='_') continue; //非下横线开头的不算
            $name = substr($name,1);
            $this->parse_tag_start($name);
        }
        //解析结束标签
        $tag_start = new tag_start();
        $fun = get_class_methods($tag_start);
        foreach($fun as $name){
            $name = substr($name,1);
            $this->parse_tag_end($name);
        }
        //解析单标签
        $tag_sigle = new tag_sigle();
        $fun = get_class_methods($tag_sigle);
        foreach($fun as $name){
            $name = substr($name,1);
            $this->parse_tag_sigle($name);
        }
        //解析函数
        $this->parse_fun();
        //解析变量
        $this->parse_var();//这个放最后
        //替换特定的字符串
        if($this->tpl_replace){
            foreach($this->tpl_replace as $v){
                $this->tpl_content = str_replace($v['search'],$v['replace'],$this->tpl_content);
            }
        }
    }

    /** 模板变量赋值 **/
    public function assign($name,$value){
        $this->tpl_var[$name] = $value;
    }

    /** 输出模板内容 **/
    public function display($file,$ext='.html'){
        if(!is_dir($this->tpl_cache)){
            if(!@mkdir($this->tpl_cache,0777)) throw_error('模板编译缓存目录'.$this->tpl_cache.'不可写！');
        }
        // var_dump($this->tpl_cache);exit;
        extract($this->tpl_var,EXTR_OVERWRITE);
        $compile_file = $this->tpl_cache.'/'.$file.'.php';
        if(is_file($compile_file) && !$this->debug) return include($compile_file);
        $tpl_file = $this->tpl_dir.$file.$ext;
        if(!is_file($tpl_file)) throw_error('模板文件'.$this->tpl_file.'不存在！');
        $this->tpl_content = file_get_contents($tpl_file);
        $this->compile();
        file_put_contents($compile_file,"<?php if(!defined('APP_NAME')) exit();?>\r\n".$this->tpl_content);
        include($compile_file);
    }
}
////////////////////////////////////////////
class tag_start{
    public function _foreach($params=array()){
        $default = array('name'=>'$list','key'=>'key','id'=>'val');
        $params = array_merge($default,$params);
        $code = '<?php if(is_array([name])){foreach([name] as $[key]=>$[id]){?>';
        foreach($params as $k => $v){
            $code = str_replace('['.$k.']',$v,$code);
        }
        return $code;
    }
    public function _if($params=array()){
        return '<?php if('.$params['condition'].'){ ?>';
    }
    public function _notempty($params=array()){
        return '<?php if(!empty('.$params['name'].')){ ?>';
    }
    public function _url($params=array()){
        $arr = $this->parse_params($params,$has_var);
        if(!$has_var) return U($params); //没出现变量或常量，直接输出
        $str = implode(',',$arr);
        return '<?php echo U(array('.$str.')); ?>';
    }
    public function _getlist($params=array()){
        if(!isset($params['name'])) $params['name'] = '$list';
        $var = $params['name'];
        unset($params['name']);
        $arr = $this->parse_params($params);
        $str = implode(',',$arr);
        return '<?php '.$var.' = get_list(array('.$str.'));?>';
    }
    public function _getone($params=array()){
        if(!isset($params['name'])) $params['name'] = '$vo';
        $var = $params['name'];
        unset($params['name']);
        $arr = $this->parse_params($params);
        $str = implode(',',$arr);
        return '<?php '.$var.' = get_one(array('.$str.'));?>';
    }
    private function parse_params($params,&$has_var=false){
        if(empty($params)) return array();
        foreach($params as $k=>&$v){
            if(preg_match('/__([A-Z_]+)__/is',$v,$m)===1){ // __NAME__ 这样表示常量
                $v = str_replace($m[0],'\'.'.$m[1].'.\'',$v);
                $has_var = true;
            }
            if(preg_match('/[^\\\]*?(\$[\w\.]+)/is',$v,$m)===1){ // test_$var 变量存在，test_\$var 变量不存在
                $v = str_replace($m[0],'\'.'.$m[1].'.\'',$v);
                $has_var = true;
            }
            $arr[] = '\''.$k.'\'=>\''.$v.'\'';
        }
        return $arr;
    }
}
///////////////////////////////////////////
class tag_end{
    public function _foreach(){
        return '<?php } } ?>';
    }
    public function _if(){
        return '<?php } ?>';
    }
    public function _notempty(){
        return '<?php } ?>';
    }
}
///////////////////////////////////////////
class tag_sigle{
    public function _else(){
        return '<?php }else{ ?>';
    }
}
?>