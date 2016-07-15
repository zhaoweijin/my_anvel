<?php
import('Admin','controller');
class AdminAuthAction extends AdminAction{
    protected function _after_delete($id){//父亲删除了，孩子也一起删除
        $where['pid']=$id;
        $this->db->table('admin_auth')->where($where)->delete();
    }
    public function tree(){
       $list=$this->db->table('admin_auth')->field('id,id as value,title as text,pid')->order('orderid')->select();
       $list = list_to_tree($list);
       $this->assign('list',$list);
       $this->display();
    }
    public function index(){
        $list=$this->db->table('admin_auth')->order('orderid')->select();
        $list=list_to_tree($list);//转换成Tree
        $this->assign('list',$list);
        $this->display();
    }
}
?>