<?php
import('Admin','controller');
class SupportUserGroupAction extends AdminAction{
    public function user(){
        $this->search_where['group_id'] = (int)$_GET['group_id'];
        $this->table_name = 'support_user';
        parent::index();
    }
    public function addUser(){
        $this->search_where['status'] = 1;
        $this->table_name = 'admin_user';
        parent::index();
    }
    public function insertUser(){
        $uname = $_POST['uname'];
        $where['group_id'] = (int)$_POST['group_id'];
        foreach($uname as $v){
            $where['uname'] = $v;
            $res = $this->db->table('support_user')->where($where)->find();
            if(!$res){
                $this->db->table('support_user')->data($where)->insert();
            }
        }
        $this->success('操作完成！');
    }
    public function removeUser(){
        $this->table_name = 'support_user';
        parent::delete();
    }
}//类结束
?>