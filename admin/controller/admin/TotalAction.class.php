<?php
import('Admin','controller');
class TotalAction extends AdminAction{

    private $game;
    private $agent;

    public function __construct(){
        parent::__construct();
        $where['id'] = array('in',$_SESSION[SESSION_NAME]['game']);
        $game_list = $this->db->table('game')->where($where)->order('orderid')->select();
        foreach($game_list as $v){
            $game[$v['id']] = $v['title'];
        }
        $this->assign('game',$game);
        $agent_list = $this->db->table('agent')->field('id,short_name')->where('status=1')->order('id')->select();
        foreach($agent_list as $v){
            $agent[$v['id']] = $v['short_name'];
        }
        $this->assign('agent',$agent);
        $this->game = $game;
        $this->agent = $agent;
    }
    
    private function get_servers(){
        $where['game_id'] = $search['game_id'] = (int)$_SESSION[SESSION_NAME]['game'][0];
        if($_POST){
            $_SESSION['search_where'] = array();
            $_SESSION['search_value'] = array();
            $arr = array('game_id');
            foreach($arr as $v){
                if($_POST[$v]!=''){
                    $_SESSION['search_value'][$v] = $_SESSION['search_where'][$v]= $_POST[$v];
                }
            }
            if($_POST['open_time_start'] && $_POST['open_time_end']){
                $_SESSION['search_where']['open_time'] = array(array('gt',strtotime($_POST['open_time_start'])),array('lt',strtotime($_POST['open_time_end'].' +1 day')));
                $_SESSION['search_value']['open_time_start'] = $_POST['open_time_start'];
                $_SESSION['search_value']['open_time_end'] = $_POST['open_time_end'];
            }elseif($_POST['open_time_start']){
                $_SESSION['search_where']['open_time'] = array('gt',strtotime($_POST['open_time_start']));
                $_SESSION['search_value']['open_time_start'] = $_POST['open_time_start'];
            }elseif($_POST['open_time_end']){
                $_SESSION['search_where']['open_time'] = array('lt',strtotime($_POST['open_time_end'].' +1 day'));
                $_SESSION['search_value']['open_time_end'] = $_POST['open_time_end'];
            }
        }
        if($_GET['do']=='search'){//查询
            $where = $_SESSION['search_where'];
            $search = $_SESSION['search_value'];
        }
        $this->gameAuth($where['game_id']);
        $servers = $this->db->table('server')->field('server_num,agent_id,open_state')->where($where)->select();
        $this->assign('search',$search);
        return $servers;
    }

    public function agent(){
        $servers = $this->get_servers();
        foreach($servers as &$v){
            $list[$v['agent_id']]['name'] = $this->agent[$v['agent_id']];
            $list[$v['agent_id']]['total']++;
            $list[$v['agent_id']]["state_{$v['open_state']}"]++;
            $total['all']++;
            $total["state_{$v['open_state']}"]++;
        }
        $this->assign('list',$list);
        $this->assign('total',$total);
        $this->display();
    }
    public function monitor(){
        $servers = $this->get_servers();
        $game_id = isset($_SESSION['search_where']['game_id']) ? $_SESSION['search_where']['game_id'] : (int)$_SESSION[SESSION_NAME]['game'][0];
        $handle = @fopen(APP_ROOT.'api/server_data/'.$game_id.'.txt', 'r'); //读取服务器状态
        if ($handle) {
            while (!feof($handle)){
                $line = fgets($handle, 4096);
                $line_arr = explode('#',$line);
                $server_data_k = md5($line_arr[0].$line_arr[1]);
                unset($line_arr[0]);
                unset($line_arr[1]);
                if(is_array($line_arr)){
                    foreach($line_arr as $val){
                        $server_data[$server_data_k] .= $val.' '; 
                    }
                }
            }
            fclose($handle);
        }
        foreach($servers as &$v){
            $server_data_k = md5($this->agent[$v['agent_id']].$v['server_num']);
            $list_key = md5($v['agent_id'].$server_data[$server_data_k]);
            $list[$list_key]['name'] = $this->agent[$v['agent_id']];
            $list[$list_key]['server_data'] = $server_data[$server_data_k];
            $list[$list_key]['sum']++;
            $list[$list_key]['servers'][] = $v['server_num'];
            $total++;
        }
        $this->assign('list',$list);
        $this->assign('total',$total);
        $this->display();
    }
}//类结束
?>