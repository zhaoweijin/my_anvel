<?php
import('Admin','controller');
class SupportAction extends AdminAction{
    private $category;
    private $user_group;
    private $status = array(0=>'未处理',1=>'已处理',2=>'已作废');
    protected function _before_insert(){
        $this->before_update_insert();
        $this->db_data['status']=0;
        $this->db_data['create_time']=time();
        $this->db_data['from_name']=$_SESSION[SESSION_NAME]['uname'];
    }
    protected function _after_insert($id,$data){
        $content = '新建问题。';
        $content .= $data['to_name'] == '' ? '' : '提交给用户：'.$_POST['to_name'].'；';
        $content .= $data['to_group'] == '' ? '' : '提交给用户组：'.$_POST['to_group_title'].'。';
        $this->save_history($id,$content);
    }
    protected function _before_update(){
        //如果需要编辑提交给的用户或用户组，在转交问题那里操作。
        $fields = array('from_name','to_name','to_group','status','create_time');
        $this->unset_db_data($fields);
    }
    private function before_update_insert(){
        if($this->db_data['to_name']!='') $this->db_data['to_name'] .= ',';
        if($this->db_data['to_group']!='') $this->db_data['to_group'] .= ',';
        if(!$this->db_data['to_group'] && !$this->db_data['to_name']){
            $this->error('用户和用户组必须选择一个！');
        }
    }
    protected function _after_delete($id){
        $where['support_id'] = $id;
        $this->db->table('support_history')->where($where)->delete(); //删除历史记录
    }
    //列表页模板渲染前格式化数据
    protected function format_list(&$list){
        $this->get_category();
        $this->get_user_group();
        foreach($list as &$v){
            $v['category'] = $this->category[1];
            if($v['to_name']!='') $v['to_name'] = substr($v['to_name'],0,-1);
            $v['to_group'] = $this->get_group_name($v['to_group']);
            $v['status_title'] = $this->status[$v['status']];
        }
    }
    //查询问题分类
    private function get_category(){
        $cate = $this->db->table('support_category')->field('id as value,title as text')->where('status=1')->order('orderid')->select();
        foreach($cate as $v){
            $this->category[$v['value']] = $v['text'];
        }
        $this->assign('category',$cate);
    }
    //查询用户组
    private function get_user_group(){
        $user_group = $this->db->table('support_user_group')->where('status=1')->order('orderid')->select();
        foreach($user_group as $v){
            $this->user_group[$v['id']] = $v['title'];
        }
    }
    //保存在数据库里面的是组的ID，通过ID字符串格式化为组名称
    private function get_group_name($id_str){
        if(!$id_str) return '';
        $id_str = substr($id_str,0,-1);
        $group_id = explode(',',$id_str);
        foreach($group_id as $v){
            $group_name[] = $this->user_group[$v];
        }
        return implode(',',$group_name);
    }
    public function add(){
        $this->get_category();
        $this->display();
    }
    public function edit(){
        $this->get_category();
        $vo = $this->info();
        if($this->check_post($vo)==false) $this->error('抱歉您无权编辑别人创建的问题！');
        $this->assign('vo',$vo);
        $this->display();
    }
    public function delete(){
        $vo = $this->info();
        if($this->check_post($vo)==false) $this->error('抱歉您无权删除别人创建的问题！');
        $result = $this->db->table('support')->where('id='.$vo['id'])->delete();
        if($result>0){
            $this->_after_delete($vo['id']);
            $this->success('删除成功！','refresh');
        }else{
            $this->error('删除失败！');
        }
    }
    //查看问题详情
    public function view(){
        $vo = $this->info();
        $check_post = $this->check_post($vo);
        $check_deal = $this->check_deal($vo);
        if($check_post==false && $check_deal==false){ //非由我创建或提交给我的，无权查看
            $this->error('抱歉您无权限查看该问题！');
        }
        $this->assign('vo',$vo);
        $this->display('Article_view');
    }
    //选择用户组
    public function selectGroup(){
        $this->table_name = 'support_user_group';
        $this->search_where['status'] = 1;
        parent::index();
    }
    //选择用户
    public function selectUser(){
        $group = $this->db->table('support_user_group')->field('id as value,title as text')->where('status=1')->select();
        $this->assign('group',$group);
        $this->table_name = 'support_user';
        $this->field = 'distinct uname';
        parent::index();
    }
    //提交给我处理的问题列表
    public function deal(){
        $where['uname'] = $_SESSION[SESSION_NAME]['uname'];
        $this->search_where['_string'] = "to_name LIKE '%{$where['uname']},%'";
        $my_group = $this->db->table('support_user')->field('group_id')->where($where)->select();
        foreach($my_group as $v){ //一个用户可能属于多个组
            $where_arr[] = "to_group LIKE '%{$v['group_id']},%'";
        }
        if($where_arr){
            $where_str = implode(' OR ',$where_arr);
            $this->search_where['_string'] .= ' OR ' .$where_str;
        }
        parent::index('Support_index');
    }
    //我提交的问题列表
    public function post(){
        $this->search_where['from_name'] = $_SESSION[SESSION_NAME]['uname'];
        parent::index('Support_index');
    }
    //处理或转交问题
    public function deliver(){
        $vo = $this->info();
        $check_post = $this->check_post($vo);
        $check_deal = $this->check_deal($vo);
        if($check_post==false && $check_deal==false){ //非由我创建或提交给我的，无权查看
            $this->error('抱歉您无权限查看该问题！');
        }
        if($_POST['do']=='deal'){ //处理问题
            $content = trim($_POST['content']);
            $status = (int)$_POST['status'];
            if($vo['status']==$status){ //状态未发生改变，必须填写备注说明
                if(!$content) $this->error('请填写备注说明！');
            }else{
                $content = "<div>状态：{$this->status[$vo[status]]} → {$this->status[$status]}</div><div>{$content}</div>";
                $result = $this->db->table('support')->data(array('status'=>$status))->where('id='.$vo['id'])->update();
                if(!($result>0)) $this->error('提交失败了！');
            }
            $this->save_history($vo['id'],$content);
            $this->success('操作成功！！');
        }
        if($_POST['do']=='deliver'){ //转交问题
            $this->getPostData();
            $this->before_update_insert();
            $data['to_name'] = $this->db_data['to_name'];
            $data['to_group'] = $this->db_data['to_group'];
            $result = $this->db->table('support')->data($data)->where('id='.$vo['id'])->update();
            if($result>0){
                $content = $data['to_name'] == '' ? '' : '转交给用户：'.$_POST['to_name'].'；';
                $content .= $data['to_group'] == '' ? '' : '转交给用户组：'.$_POST['to_group_title'].'。';
                $this->save_history($vo['id'],$content);
                $this->success('转交成功！！');
            }elseif($result===0){
                $this->error('没有变化！');
            }else{
                $this->error('抱歉转交失败！');
            }
        }
        $history = $this->db->table('support_history')->where('support_id='.$vo['id'])->select();
        $this->assign('history',$history);
        $this->assign('vo',$vo);
        $this->display();
    }
    //检查是否是提交给我处理的问题
    private function check_deal($data){
        if($_SESSION[SESSION_NAME]['role_id']==1) return true; //系统管理员不验证权限了
        $my_group = $this->db->table('support_user')->field('group_id')->where($where)->select();
        if(!$my_group) return false;
        if($data['to_group']){
            $to_group = explode( ',' , substr($data['to_group'],0,-1) );
            foreach($my_group as $v){
                if(in_array($v['group_id'],$to_group)) return true;
            }
        }
        if($data['to_name']){
            $to_name = explode( ',' , substr($data['to_name'],0,-1) );
            if(in_array($_SESSION[SESSION_NAME]['uname'],$to_name)) return true;
        }
        return false;
    }
    //检查是否是由我创建的问题
    private function check_post($data){
        if($_SESSION[SESSION_NAME]['role_id']==1) return true; //系统管理员不验证权限了
        return $data['from_name'] == $_SESSION[SESSION_NAME]['uname'] ? true : false;
    }
    private function save_history($support_id,$content){
        $data['support_id'] = $support_id;
        $data['uname'] = $_SESSION[SESSION_NAME]['uname'];
        $data['content'] = $content;
        $data['create_time'] = time();
        return $this->db->table('support_history')->data($data)->insert();
    }
}//类结束
?>