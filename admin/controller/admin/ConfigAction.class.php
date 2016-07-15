<?php
import('Admin','controller');
class ConfigAction extends AdminAction{
    public function agent(){
        if(isset($_GET['do']) && $_GET['do']=='save'){
            $where['id'] = (int)$_POST['id'];
            $data['game_id'] = (int)$_POST['game_id'];
            $data['agent_id'] = (int)$_POST['agent_id'];
            $data['content'] = trim($_POST['content']);
            if($where['id']>0){
                $result = $this->db->table('agent_config')->data($data)->where($where)->update();
            }else{
                $result = $this->db->table('agent_config')->data($data)->insert();
            }
            if($result>0){
                $this->success('编辑成功！','refresh');
            }elseif($result===0){
                $this->error('没有变化!');
            }else{
                $this->error('编辑失败！');
            }
        }
        $where['id'] = array('in',$_SESSION[SESSION_NAME]['game']);
        $game_list = $this->db->table('game')->field('id as value,title as text')->where($where)->order('orderid')->select();
        $game_id = isset($_POST['game_id']) ? $_POST['game_id'] : $_SESSION[SESSION_NAME]['game'][0];
        $this->gameAuth($game_id);
        $sql = 'SELECT DISTINCT A.id as value,A.short_name as text FROM '.DB_PREFIX.'agent A,'.DB_PREFIX.'server S WHERE A.id=S.agent_id AND S.game_id='.$game_id;
        $agent_list = $this->db->query($sql);
        $agent_id = isset($_POST['agent_id']) ? $_POST['agent_id'] : $agent_list[0]['value'];
        $where = array('game_id'=>$game_id,'agent_id'=>$agent_id);
        $config = $this->db->table('agent_config')->where($where)->find();
        $this->assign('config',$config);
        $this->assign('game_list',$game_list);
        $this->assign('agent_list',$agent_list);
        $this->assign('game_id',$game_id);
        $this->assign('agent_id',$agent_id);
        $this->display();
    }
}//类结束
?>