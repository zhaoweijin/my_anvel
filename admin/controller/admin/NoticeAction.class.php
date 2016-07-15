<?php
import('Admin','controller');
class NoticeAction extends AdminAction{
    protected $order_by='orderid desc,id desc';
    public function __construct(){
        parent::__construct();
        $where['id'] = array('in',$_SESSION[SESSION_NAME]['game']);
        $game = $this->db->table('game')->field('id as value,title as text')->where($where)->order('orderid')->select();
        $this->assign('game',$game);
    }
    protected function _search(){
        parent::_search();
        $this->gameAuth($_SESSION['search_where']['game_id']);
    }
    public function index(){
        $this->search_where['game_id'] = $_SESSION[SESSION_NAME]['game'][0];
        $this->gameAuth($this->search_where['game_id']);
        parent::index();
    }
    public function view(){
        $vo = $this->info();
        $this->gameAuth($vo['game_id']);
        $this->assign('vo',$vo);
        $this->display('Article_view');
    }
    protected function _before_insert(){
        $this->gameAuth($this->db_data['game_id']);
        $this->db_data['create_time']=strtotime($_POST['create_time']);
        $this->db_data['uname']=$_SESSION[SESSION_NAME]['uname'];
    }
    protected function _before_update(){
        $this->gameAuth($this->db_data['game_id']);
        $this->db_data['create_time']=strtotime($_POST['create_time']);
    }
}//类结束
?>