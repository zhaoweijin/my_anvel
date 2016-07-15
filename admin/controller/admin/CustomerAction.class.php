<?php
import('Admin','controller');
class CustomerAction extends AdminAction{
    public function __construct(){
        parent::__construct();
        $group = $this->db->table('customer_group')->field('id as value,title as text')->where('status=1')->order('orderid')->select();
        $this->assign('group',$group);
    }
    public function sendMsg(){
        $send = false;
        if(isset($_POST['content'])){
            $content=trim(strip_tags($_POST["content"]));
            $len=strlen($content);
            if($len<12||$len>600){
                $this->error('短信内容过短或过长！');
            }
            $where = array();
            $where['status'] = 1;
            $customer_group_id = (int)$_POST['group_id'];
            if($customer_group_id>0) $where['group_id'] = $customer_group_id;
            $total = $this->db->table('customer')->where($where)->count('distinct mobile');
            if($total>0){
                $send = true;
                $this->assign('total',$total);
                $this->assign('group_id',$customer_group_id);
                $this->assign('content',$content);
            }else{
                $this->error('发送对象为空！');
            }
        }
        $this->assign('send',$send);
        $this->display();
    }
    public function proccessMsg(){
        $limit = $_POST['start'].',1';
        $where = array();
        $where['status'] = 1;
        $customer_group_id = (int)$_POST['group_id'];
        if($customer_group_id>0) $where['group_id'] = $customer_group_id;
        $customer = $this->db->table('customer')->field('distinct true_name,mobile')->where($where)->limit($limit)->find();
        if($customer){
            import('PostMsg');
            $messageText = iconv('utf-8','gbk',$_POST['content']);
            $msgResult = Msg_PostSingle(MSG_UNAME,MSG_PWD,$customer['mobile'],$messageText,'');
            if($msgResult==0){
                echo "<font color=blue>{$customer['true_name']}，{$customer['mobile']}，发送成功！</font>";
            }else{
                echo "<font color=red>{$customer['true_name']}，{$customer['mobile']}，发送失败！{$msgResult}</font>";
            }
        }
    }
}//类结束
?>