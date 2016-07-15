<?php
import('Admin','controller');
class UserAction extends CommonAction{
    public function login(){
        if(isset($_SESSION[SESSION_NAME])){
            $url=U(array('m'=>'Home','a'=>'index'));
            redirect($url);
        }else{
            $this->display();
        }
    }
    public function check(){
       $verify_code=trim($_POST['verify_code']);
       if(!$verify_code || md5($verify_code)!=$_SESSION['verify_code']){
           $this->error('验证码错误！');
       }
       $where['uname']=trim($_POST['uname']);
       $where['pwd']=md5(trim($_POST['pwd']));

       $db=new db();
       $user=$db->table('admin_user')->where($where)->find();
       
       if($user){
           if($user['status']!=1) $this->error('此用户被锁定！');
           $role_info=$db->table('admin_role')->field('auth,game,status')->where('id='.$user['role_id'])->find();
           if($role_info){
               if($role_info['status']!=1) $this->error('此用户组被锁定！');
               if($role_info['auth'] && $user['auth']){ //合并用户组权限和用户单独权限
                   $auth = $role_info['auth'].','.$user['auth'];
               }else{
                   $auth = $user['auth']?$user['auth']:$role_info['auth'];
               }
               if($role_info['game'] && $user['game']){
                   $game= $role_info['game'].','.$user['game'];
               }else{
                   $game = $user['game']?$user['game']:$role_info['game'];
               }
               $auth_array = array();
               $game_array = array();
               if($auth){
                   $auth_array=explode(',',$auth);
                   $auth_array=array_unique($auth_array);//去重复
               }
               if($game){
                   $game_array=explode(',',$game);
                   $game_array=array_unique($game_array);//去重复
               }
               if($user['role_id']==1){ //系统管理员不用授权游戏便拥有所有游戏的操作权限
                   $game_list = $db->table('game')->field('id')->where('status=1')->select();
                   foreach($game_list as $v){
                       $game_array[] = $v['id'];
                   }
               }
               $_SESSION[SESSION_NAME]['auth']=$auth_array;
               $_SESSION[SESSION_NAME]['game']=$game_array;
           }else{
                $this->error('此用户组不存在！');
           }
           $_SESSION[SESSION_NAME]['role_id']=$user['role_id'];//主要是标识是否系统管理员，role_id为1便是系统管理员
           $_SESSION[SESSION_NAME]['uname']=$where['uname'];
           $_SESSION[SESSION_NAME]['uid']=$user['id'];
           $this->success('登录成功','refresh');
       }else{//登录失败，当前验证码失效。需要重新生成验证码。要不然验证码有毛用。
           unset($_SESSION['verify_code']);
           $this->error('账号不存在或密码错误！','refresh_code');
       }
    }
    public function logout(){
        unset($_SESSION[SESSION_NAME]);
        $url=U(array('m'=>'User','a'=>'login'));
        redirect($url);
    }
}
?>