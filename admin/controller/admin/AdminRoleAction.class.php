<?php
import('Admin','controller');
class AdminRoleAction extends AdminAction{
    //用户角色授权
    public function auth(){
        $where['id']=(int)$_GET['role_id'];
        if($_POST['update']==1){
            if(is_array($_POST['auth_id'])){
                $data['auth']=implode(',',$_POST['auth_id']);
            }else{
                $data['auth']='';
            }
            if(is_array($_POST['game_id'])){
                $data['game']=implode(',',$_POST['game_id']);
            }else{
                $data['game']='';
            }
            $result=$this->db->table('admin_role')->data($data)->where($where)->update();
            if($result>0){
                $this->success('更新成功！');
            }elseif($result===0){
                $this->error('没有变化！');
            }else{
                $this->error('更新失败！');
            }
        }
        $role_info=$this->db->table('admin_role')->field('auth,game')->where($where)->find();
        $role_auth=explode(',',$role_info['auth']);
        $role_game=explode(',',$role_info['game']);
        $this->assign('role_auth',$role_auth);
        $this->assign('role_game',$role_game);
        $auth_list=$this->db->table('admin_auth')->field('id,title,pid')->where('status=1')->order('orderid')->select();
        $auth_list=list_to_tree($auth_list);//转换成Tree
        $this->assign('auth_list',$auth_list);
        $game_list=$this->db->table('game')->where('status=1')->order('orderid')->select();
        $this->assign('game_list',$game_list);
        $this->display();
    }
    protected function _before_update(){
        $id = (int)$_POST['id'];
        if( $id==1 && $_SESSION[SESSION_NAME]['role_id']!=1 ){
            $this->error('无法更改系统管理员！'); //role id 为1是系统管理员，只有系统管理员本身能修改此条记录的信息
        }
    }
    protected function check_id($id){
        if( in_array(1,$id) ) $this->error('无法更改系统管理员！'); // role id为1是系统管理员，无法删除和锁定等
    }
}
?>