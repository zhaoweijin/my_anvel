<?php
import('Admin','controller');
class HomeAction extends AdminAction{

    protected function checkAuth(){//无需验证权限
    }
    public function index(){
        $where='pid=0 AND menu_name!=""';
        $list=$this->db->table('admin_auth')->where($where)->order('orderid')->select();
        if($_SESSION[SESSION_NAME]['game']){
            $where=array();
            $where['game_id']=array('in',$_SESSION[SESSION_NAME]['game']);
            $notice=$this->db->table('notice')->where($where)->order('orderid')->find();
            $this->assign('notice',$notice);
        }
        $this->assign('menu',$list);
        $this->display();
    }
    public function left(){
        $where['pid']=(int)$_GET['id'];
        $list=$this->db->table('admin_auth')->where($where)->order('orderid')->select();
        foreach($list as &$v){
            if($_SESSION[SESSION_NAME]['role_id']==1){//role id 为1是系统管理员，显示所有菜单
                $where='menu_name!="" AND pid='.$v['id'];
            }else{//其他角色根据权限查询菜单列表
                $ids=implode(',',$_SESSION[SESSION_NAME]['auth']);
                if(!$ids) continue;
                $where='(id IN('.$ids.') AND pid='.$v['id'].') AND menu_name!=""';
            }
            $v['_child']=$this->db->table('admin_auth')->where($where)->order('orderid')->select();
        }
        foreach($list as $key=>$val){//过滤子菜单为空的一级菜单
            if(!$val['_child']) unset($list[$key]);
        }
        $this->assign('menu',$list);
        $this->display();  
    }
    public function right(){
        $url=U(array('m'=>'Server'));
        redirect($url);
        $info = array(
            '操作系统'=>PHP_OS,
            '运行环境'=>$_SERVER["SERVER_SOFTWARE"],
            'PHP运行方式'=>php_sapi_name(),
            '上传附件限制'=>ini_get('upload_max_filesize'),
            '执行时间限制'=>ini_get('max_execution_time').'秒',
            '内存使用限制'=>ini_get('memory_limit'),
            '脚本时间'=>date("Y年n月j日 H:i:s"),
            '北京时间'=>gmdate("Y年n月j日 H:i:s",time()+8*3600),
            //'服务器域名/IP'=>$_SERVER['SERVER_NAME'].' [ '.gethostbyname($_SERVER['SERVER_NAME']).' ]',
            'safe_mode'=>(1===get_cfg_var('safe_mode'))?'Off':'On',
            'register_globals'=>get_cfg_var("register_globals")=="1" ? "On" : "Off",
            'magic_quotes_gpc'=>(1===get_magic_quotes_gpc())?'YES':'NO',
            'magic_quotes_runtime'=>(1===get_magic_quotes_runtime())?'YES':'NO',
            'phpinfo()'=>'<a target="_blank" href="'.U(array('m'=>'Home','a'=>'myphpinfo')).'">查看</a>',
        );
        $this->assign('info',$info);
        $this->display();
    }
    public function cache(){ //清空缓存
        if($_POST['cache']){
            $path = array(0=>'data',1=>'compile',2=>'fields',3=>'images');
            foreach($_POST['cache'] as $k=>$v){
                $this->_cache(CACHE_PATH.$path[$k]);
                if($k==1) @unlink(CACHE_PATH.'~runtime.php');
            }
            $this->success('清理缓存完毕！');
        }else{
            $this->display();
        }
    }
    private function _cache($directory=CACHE_PATH){
        $handle = opendir($directory);
        while (($file = readdir($handle)) !== false){
            if ($file != "." && $file != ".."){
                is_dir("$directory/$file") ? $this->_cache("$directory/$file") : unlink("$directory/$file");
            }
        }
        closedir($handle);
    }
}
?>