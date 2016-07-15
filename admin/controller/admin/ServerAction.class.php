<?php
import('Admin','controller');
class ServerAction extends AdminAction{

    private $game;
    private $agent;

    public function __construct(){
        parent::__construct();
        $where['id'] = array('in',$_SESSION[SESSION_NAME]['game']);
        $game_list = $this->db->table('game')->where($where)->order('orderid')->select();
        foreach($game_list as $v){
            $game[$v['id']] = getfirstchar($v['title']).'：'.$v['title'];
            $game_arr[$v['id']] = $v;
        }
        asort($game);
        $this->assign('game',$game);
        $this->assign('game_arr',$game_arr);
        $agent_list = $this->db->table('agent')->field('id,short_name')->where('status=1')->order('id')->select();
        foreach($agent_list as $v){
            $agent[$v['id']] = getfirstchar($v['short_name']).'：'.$v['short_name'];
        }
        asort($agent);
        $this->assign('agent',$agent);
        $this->game = $game;
        $this->agent = $agent;
    }
    private function getone($id){
        $server = $this->db->table('server')->where('id='.$id)->find();
        if(!$server) $this->error('不存在此条记录');
        $this->gameAuth($server['game_id']);
        return $server;
    }
    private function saveLog($server_id,$log_content){
        $log['server_id'] = $server_id;
        $log['uname'] = $_SESSION[SESSION_NAME]['uname'];
        $log['create_time'] = time();
        $log['content'] = $log_content;
        $this->db->table('log')->data($log)->insert();
    }
    public function index(){
        $time = time();
        $search['open_time'] = '0';
        $where['open_time'] = array('gt',$time);
        if(isset($_POST['search'])){
            $_SESSION['search_where'] = array();
            $_SESSION['search_value'] = array();
            $arr = array('agent_id','game_id','open_state');
            foreach($arr as $v){
                if($_POST[$v]!=''){
                    $_SESSION['search_value'][$v] = $_SESSION['search_where'][$v]= $_POST[$v];
                }
            }
            $ct_ip = trim($_POST['ct_ip']);
            if($ct_ip){
                $_SESSION['search_value']['ct_ip'] = $ct_ip;
                $_SESSION['search_where']['ct_ip'] = array('like','%'.$ct_ip.'%');
            }elseif($_POST['open_time_start'] && $_POST['open_time_end']){
                $_SESSION['search_where']['open_time'] = array(array('gt',strtotime($_POST['open_time_start'])),array('lt',strtotime($_POST['open_time_end'].' +1 day')));
                $_SESSION['search_value']['open_time_start'] = $_POST['open_time_start'];
                $_SESSION['search_value']['open_time_end'] = $_POST['open_time_end'];
            }elseif($_POST['open_time_start']){
                $_SESSION['search_where']['open_time'] = array('gt',strtotime($_POST['open_time_start']));
                $_SESSION['search_value']['open_time_start'] = $_POST['open_time_start'];
            }elseif($_POST['open_time_end']){
                $_SESSION['search_where']['open_time'] = array('lt',strtotime($_POST['open_time_end'].' +1 day'));
                $_SESSION['search_value']['open_time_end'] = $_POST['open_time_end'];
            }elseif($_POST['open_time']=='0'){
                $_SESSION['search_where']['open_time'] = array('gt',$time);
                $_SESSION['search_value']['open_time'] = '0';
            }elseif($_POST['open_time']=='1'){
                $_SESSION['search_where']['open_time'] = array('lt',$time);
                $_SESSION['search_value']['open_time'] = '1';
            }
        }
        if($_GET['do']=='search'){//查询
            $where = $_SESSION['search_where'];
            $search = $_SESSION['search_value'];
        }
        if(!$where['game_id']){
            $where['game_id'] = $search['game_id'] = (int)$_SESSION[SESSION_NAME]['game'][0];
        }
        $this->gameAuth($where['game_id']);
        $order = $search['open_time'] == '0' ? 'open_time asc' : 'open_time desc';
        $count = $this->db->table('server')->where($where)->count();
        $pageNum = isset($_POST['pageNum']) ? (int)$_POST['pageNum'] : 1;
        if(isset($_POST['numPerPage'])) $this->list_num = (int)$_POST['numPerPage'];
        $totalPage = ceil($count/$this->list_num);
        if($pageNum>$totalPage) $pageNum = $totalPage;
        if($pageNum<1) $pageNum = 1;
        $limit=($pageNum-1)*$this->list_num .','. $this->list_num;
        $page = array('totalCount'=>$count,'numPerPage'=>$this->list_num,'currentPage'=>$pageNum);
        $list = $this->db->table('server')->where($where)->order($order)->limit($limit)->select();
        //echo $this->db->getSql();
        $this->assign('page',$page);
        $handle = @fopen(APP_ROOT.'api/server_data/'.$where['game_id'].'.txt', 'r'); //读取服务器状态
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
        foreach($list as &$v){ //根据开服时间按周间隔开
            $server_data_k = md5(substr($this->agent[$v['agent_id']],4).$v['server_num']);
            if($server_data[$server_data_k]){
                $v['server_data'] = $server_data[$server_data_k];
            }
            $k = date('y',$v['open_time']).strftime('%W',$v['open_time']);
            $server_list[$k]['datetime'] = date('Y年',$v['open_time']).'第'.strftime('%W',$v['open_time']).'周';
            $server_list[$k]['_server'][] = $v;
        }
        $this->assign('list',$server_list);
        if($where['agent_id']) $total_where['agent_id']=$where['agent_id'];
        if($where['game_id']) $total_where['game_id']=$where['game_id'];
        if($search['open_time_start'] || $search['open_time_end']) $total_where['open_time'] = $where['open_time'];
        $total_where['open_state'] = array('lt',3);
        $total_all = $this->db->table('server')->where($total_where)->count();
        if($total_where['open_time']){ //指定开服时间范围内的已开服
            array_push($total_where['open_time'],array('lt',$time));
        }else{
            $total_where['open_time'] = array('lt',$time);
        }
        $total_opened = $this->db->table('server')->where($total_where)->count();
        //echo $this->db->getSql();
        $total_where['open_state'] = 2;
        $total_merge = $this->db->table('server')->where($total_where)->count();
        $this->assign('total_all',$total_all);
        $this->assign('total_opened',$total_opened);
        $this->assign('total_merge',$total_merge);
        $open_state = array(0=>'否',1=>'是',2=>'合',3=>'撤',4=>'关');
        $state = array(0=>'否',1=>'是');
        $week = array('日','一','二','三','四','五','六');
        $this->assign('open_state',$open_state);
        $this->assign('state',$state);
        $this->assign('week',$week);
        $this->assign('search',$search);
        $this->display();
    }
    public function step1(){
        $arr = array('game_id','agent_id','server_num','server_name','open_time','open_state');
        foreach($arr as $v){
            $data[$v] = trim($_POST[$v]);
        }
        if(!$data['game_id']) $this->error('请选择游戏');
        if(!$data['agent_id']) $this->error('请选择联运商');
        if($data['server_num']=='') $this->error('请填写区号');
        if(!$data['server_name']) $this->error('请填写名称');
        $this->gameAuth($data['game_id']);
        $data['open_time'] = strtotime($data['open_time']);
        $data['update_time'] = time();
        if($data['open_state']==4) $data['close_time'] = strtotime(trim($_POST['close_time']));
        if($data['open_state']==2){
            $server_merge = trim($_POST['server_merge']);
            if(!preg_match('/^[\d,]+$/',$server_merge)) $this->error('请检查合服区号是否填写正确');
            $servers = explode(',',$server_merge);
            foreach($servers as $s){
                if($s=='') $this->error('不能以逗号开始或结尾');
                $check = $this->db->table('server')->where('server_num='.$s)->find();
                if(!$check) $this->error("无法合服：区号{$s}不存在");
            }
            $data['server_merge'] = $server_merge;
        }
        $where['id'] = (int)$_POST['id'];
        $open_state = array(0=>'否',1=>'是',2=>'合',3=>'撤',4=>'关');
        if(!$where['id']){ //新增
            $server_id=$this->db->table('server')->data($data)->insert();
            if($server_id){
                $log_content .= '游戏名：'.substr($this->game[$data['game_id']],4).'<br />';
                $log_content .= '联运商：'.substr($this->agent[$data['agent_id']],4).'<br />';
                $log_content .= '区号：'.$data['server_num'].'<br />';
                $log_content .= '名称：'.$data['server_name'].'<br />';
                $log_content .= '开服日期时间：'.date('Y-m-d H:i',$data['open_time']).'<br />';
                $log_content .= '开服确认：'.$open_state[$data['open_state']];
                $this->saveLog($server_id,$log_content);
                $this->success('新增成功！');
            }else{
                $this->error('新增失败！');
            }
        }else{ //编辑
            $server = $this->getone($where['id']);
            if($data['open_state']==0){
                $data['setup_state'] = 0;
                $data['activity_state'] = 0;
                $data['api_state'] = 0;
                $data['test_state'] = 0;
                $data['clear_state'] = 0;
            }
            $result = $this->db->table('server')->where($where)->data($data)->update();
            if($result>0){
                if($server['game_id'] != $data['game_id']){
                    $log_content .= '游戏名：'.$this->game[$server['game_id']].' --> '.$this->game[$data['game_id']].'<br />';
                }
                if($server['agent_id'] != $data['agent_id']){
                    $log_content .= '联运商：'.$this->agent[$server['agent_id']].' → '.$this->agent[$data['agent_id']].'<br />';
                }
                if($server['server_num'] != $data['server_num']){
                    $log_content .= '区号：'.$server['server_num'].' → '.$data['server_num'].'<br />';
                }
                if($server['server_name'] != $data['server_name']){
                    $log_content .= '名称：'.$server['server_name'].' → '.$data['server_name'].'<br />';
                }
                if($server['open_time'] != $data['open_time']){
                    $log_content .= '开服日期时间：'.date('Y-m-d H:i',$server['open_time']).' → '.date('Y-m-d H:i',$data['open_time']).'<br />';
                }
                if($server['open_state'] != $data['open_state']){
                    $log_content .= '开服确认：'.$open_state[$server['open_state']].' → '.$open_state[$data['open_state']];
                }
                $this->saveLog($where['id'],$log_content);
                $this->success('编辑成功！');
            }elseif($result===0){
                $this->error('没有变化!');
            }else{
                $this->error('编辑失败！');
            }
        }
    }
    public function step2(){
        $where['id'] = (int)$_REQUEST['id'];
        $server = $this->getone($where['id']);
        if($server['open_state']==0) $this->error('请先确认开服！');
        if($_POST){
            $arr = array('domain'=>'主域名','ct_ip'=>'主电信IP','cnc_ip'=>'主网通IP','domain_secondary'=>'从域名','ct_ip_secondary'=>'从电信IP','cnc_ip_secondary'=>'从网通IP','server_state'=>'服务器状态','setup_state'=>'安装状态');
            foreach($arr as $k=>$v){
                $data[$k] = trim($_POST[$k]);
            }
            unset($data['id']);
            if($data['setup_state']==0){
                $data['activity_state'] = 0;
                $data['api_state'] = 0;
                $data['test_state'] = 0;
                $data['clear_state'] = 0;
            }
            $data['update_time'] = time();
            $result = $this->db->table('server')->where($where)->data($data)->update();
            if($result>0){
                foreach($arr as $k=>$v){
                    if($server[$k] != $data[$k]){
                        if($k=='server_state' || $k=='setup_state'){
                            $server[$k] = str_replace(array(0,1),array('否','是'),$server[$k]);
                            $data[$k] = str_replace(array(0,1),array('否','是'),$data[$k]);
                        }
                        $log_content .= $arr[$k].'：'.$server[$k].' → '.$data[$k].'<br />';
                    }
                }
                $this->saveLog($where['id'],$log_content);
                $this->success('编辑成功！');
            }elseif($result===0){
                $this->error('没有变化!');
            }else{
                $this->error('编辑失败！');
            }
        }else{
            $this->assign('vo',$server);
            $this->display();
        }
    }
    public function step3(){
        $where['id'] = (int)$_REQUEST['id'];
        $server = $this->getone($where['id']);
        if($server['activity_state'] == 1){
            $data['activity_state'] = 0;
            $log_content = '活动策划： 是 → 否';
        }else{
            if($server['setup_state']!=1) $this->error('请先确认安装状态');
            $data['activity_state'] = 1;
            $log_content = '活动策划： 否 → 是';
        }
        if($data['activity_state']==0){
            $data['api_state'] = 0;
            $data['test_state'] = 0;
            $data['clear_state'] = 0;
        }
        $result = $this->db->table('server')->where($where)->data($data)->update();
        if($result>0){
            $this->saveLog($where['id'],$log_content);
            $this->success('设置成功！','refresh');
        }else{
            $this->error('设置失败！');
        }
    }
    public function step4(){
        $where['id'] = (int)$_REQUEST['id'];
        $server = $this->getone($where['id']);
        if($server['api_state'] == 1){
            $data['api_state'] = 0;
            $log_content = '接口状态： 是 → 否';
        }else{
            if($server['activity_state']!=1) $this->error('请先确认活动策划');
            $data['api_state'] = 1;
            $log_content = '接口状态： 否 → 是';
        }
        if($data['api_state']==0){
            $data['test_state'] = 0;
            $data['clear_state'] = 0;
        }
        $result = $this->db->table('server')->where($where)->data($data)->update();
        if($result>0){
            $this->saveLog($where['id'],$log_content);
            $this->success('设置成功！','refresh');
        }else{
            $this->error('设置失败！');
        }
    }
    public function step5(){
        $where['id'] = (int)$_REQUEST['id'];
        $server = $this->getone($where['id']);
        if($server['test_state'] == 1){
            $data['test_state'] = 0;
            $log_content = '测试状态： 是 → 否';
        }else{
            if($server['api_state']!=1) $this->error('请先确认接口状态');
            $data['test_state'] = 1;
            $log_content = '测试状态： 否 → 是';
        }
        if($data['test_state']==0){
            $data['clear_state'] = 0;
        }
        $result = $this->db->table('server')->where($where)->data($data)->update();
        if($result>0){
            $this->saveLog($where['id'],$log_content);
            $this->success('设置成功！','refresh');
        }else{
            $this->error('设置失败！');
        }
    }
    public function step6(){
        $where['id'] = (int)$_REQUEST['id'];
        $server = $this->getone($where['id']);
        if($server['clear_state'] == 1){
            $data['clear_state'] = 0;
            $log_content = '清档状态： 是 → 否';
        }else{
            if($server['test_state']!=1) $this->error('请先确认测试状态');
            $data['clear_state'] = 1;
            $log_content = '清档状态： 否 → 是';
        }
        $result = $this->db->table('server')->where($where)->data($data)->update();
        if($result>0){
            $this->saveLog($where['id'],$log_content);
            $this->success('设置成功！','refresh');
        }else{
            $this->error('设置失败！');
        }
    }
    public function log(){
        $where['server_id'] = (int)$_GET['server_id'];
        $server = $this->getone($where['server_id']); //只为了检查有没有这个游戏的权限
        $list = $this->db->table('log')->where($where)->select();
        $this->assign('list',$list);
        $this->display();
    }
    public function remark(){
        $where['server_id'] = (int)$_GET['server_id'];
        $remark = $this->db->table('remark')->where($where)->find();
        $this->assign('server_id',$where['server_id']);
        $this->assign('vo',$remark);
        $this->display();
    }
    public function remark_update(){
        $arr = array('id','server_id','content');
        foreach($arr as $v){
            $data[$v] = $_POST[$v];
        }
        $where['id'] = $data['id'];
        unset($data['id']);
        if($where['id']>0){
            $result = $this->db->table('remark')->where($where)->data($data)->update();
            if($result>0){
                $this->success('更新成功！');
            }elseif($result===0){
                $this->error('没有变化！');
            }else{
                $this->error('更新失败！');
            }
        }else{
            $result = $this->db->table('remark')->data($data)->insert();
            if($result){
                $this->success('更新成功！','refresh');
            }else{
                $this->error('更新失败！');
            }
        }
    }
    public function rsync(){
        $id = (int)$_GET['id'];
        $vo = $this->getone($id);
        if($_POST){
            $where['id'] = (int)$_POST['id'];
            $data['ssh'] = (int)$_POST['ssh'];
            $data['rsync'] = trim($_POST['rsync']);
            $result = $this->db->table('server')->data($data)->where($where)->update();
            if($result>0){
                if($vo['ssh'] != $data['ssh']) $log_content .= "SSH：{$vo['ssh']} → {$data['ssh']} <br />";
                if($vo['rsync'] != $data['rsync']) $log_content .= "rsync：{$vo['rsync']} → {$data['rsync']}";
                $this->saveLog($where['id'],$log_content);
                $this->success('更新成功！');
            }elseif($result===0){
                $this->error('没有变化！');
            }else{
                $this->error('更新失败！');
            }
        }
        $this->assign('vo',$vo);
        $this->display();
    }
    public function delete(){
        //$this->error('删除功能已经关闭！！');
        $id = (int)$_GET['id'];
        $server = $this->getone($id);
        if($server['open_time']<time() && $_SESSION[SESSION_NAME]['role_id']!=1){
            $this->error('对不起，无权限删除该条记录'); //已开服只有管理员能删除
        }
        $result = $this->db->table('server')->where('id='.$id)->delete();
        if($result>0){
            $this->db->table('log')->where('server_id='.$id)->delete(); //删除日志
            $this->success('删除成功！','refresh');
        }
    }
    public function config(){
        if($_POST){
            $this->update();
        }else{
            $this->edit();
        }
    }
    public function export(){
        if($_POST){
            $msg = array();
            $list = array();
            $game_id = (int)$_POST['game_id'];
            $this->gameAuth($game_id);
            $arr = explode("\r\n",trim($_POST['command']));
            if($arr[0]){
                foreach($arr as &$v){
                    $where = array();
                    $where['game_id'] = $game_id;
                    if(!$v) continue;
                    $v = str_replace('：',':',$v);
                    $tmp = explode(':',$v);
                    if(!$tmp){
                        $msg[] = "无法识别的规则：{$v}";
                        continue;
                    }
                    //增加按日期导出
                    if($tmp[0]=='date'){
                        $where['open_time'] = array(array('gt',strtotime("{$tmp[1]} 00:00:00")),array('lt',strtotime("{$tmp[1]} 23:59:00")));
                        $server= $this->db->table('server')->where($where)->select();
                        $list = array_merge($list,$server);
                        continue;
                    }
                    $agent_name = $tmp[0];
                    $server_ids = $tmp[1];
                    $agent_where['short_name'] = $agent_name;
                    $agent = $this->db->table('agent')->field('id')->where($agent_where)->find();
                    if(!$agent){
                        $msg[] = "不存在的代理商：{$agent_name}";
                        continue;
                    }
                    $where['agent_id'] = $agent['id'];
                    $server_ids = explode(',',$server_ids);
                    if($server_ids){
                        foreach($server_ids as &$id){
                            if(preg_match('/^(\d+)-(\d+)$/',$id,$matches)){//匹配 1-9 这类
                                if($matches){
                                    for($i=$matches[1];$i<=$matches[2];$i++){
                                        $where['server_num'] = $i;
                                        $server = $this->db->table('server')->where($where)->find();
                                        if($server){
                                            //$list[] = $server;
                                            $list[$server['id']] = $server; //避免重复
                                        }else{
                                            $msg[] = "不存在的区号：{$agent_name}-{$i}";
                                        }
                                    }
                                }
                            }elseif(preg_match('/^\d+$/',$id)){
                                $where['server_num'] = $id;
                                $server = $this->db->table('server')->where($where)->find();
                                if($server){
                                    //$list[] = $server;
                                    $list[$server['id']] = $server; //避免重复
                                }else{
                                    $msg[] = "不存在的区号：{$agent_name}-{$id}";
                                }
                            }else{
                                $msg[] = "不存在的区号：{$agent_name}-{$id}";
                            }
                        }
                    }
                }
            }else{
                $list = $this->db->table('server')->where('setup_state=1 And game_id='.$game_id)->select();
            }
            foreach($list as $k=>&$v){ //过滤。不导出关服和撤服
                if(in_array($v['open_state'],array(3,4))){
                    unset($list[$k]);
                }
            }
            if($list){
                $file_name = APP_ROOT."game/{$game_id}.php";
                if(is_file($file_name)){
                    include($file_name);
                }else{
                    include(APP_ROOT."game/0.php");
                }
            }
            $this->assign('links',$links);
            $this->assign('game_id',$game_id);
            $this->assign('msg',$msg);
        }
        $this->display();
    }
}//类结束
?>