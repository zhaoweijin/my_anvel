<?php
import('Common','controller');
class AdminAction extends CommonAction{

    protected $db; //数据库类对象
    protected $search_where = array();//列表页面查询条件，用于构造sql语句
    protected $search_value = array();//列表页面查询条件，用于表单赋值
    protected $db_data = array();//用于保存insert,update的数据
    protected $table_name='';//表名
    protected $field='*';//查询字段
    protected $order_by='id desc';//排序
    protected $list_num=30;//每页显示记录数

    public function __construct(){
        parent::__construct();
        if(!isset($_SESSION[SESSION_NAME])){//登录检测
            $url=U(array('m'=>'User','a'=>'login'));
            if($this->isAjax()){
                $this->ajaxReturn(array('statusCode'=>301));
            }else{
                redirect($url);
            }
        }else{
            $this->db = new db();
            if($_SESSION[SESSION_NAME]['role_id']!=1){//非系统管理员需要验证权限
                $this->checkAuth();//权限验证
            }
            $this->assign('session',$_SESSION[SESSION_NAME]);
        }
        //为避免多个navTab页面或dialog页面ID冲突，所以ID名称还是加上前缀吧。
        $this->assign('id_pre',MODULE_NAME.ACTION_NAME); //为方便书写
        $this->assign('ueditor_id',MODULE_NAME.ACTION_NAME.'ueditor');//百度编辑器ID名称，原因同上
    }
    //权限验证
    protected function checkAuth(){
        $where['auth_module']=MODULE_NAME;
        $where['auth_action']=array(array('like','%'.ACTION_NAME.',%'),array('eq','*'),'OR'); //auth_action为*表示拥有本模块全部权限
        $auth=$this->db->table('admin_auth')->field('id,status')->where($where)->find();
        if(!$auth) $this->error('此操作没有设置权限，不能进行操作！');
        if($auth['status']!=1) $this->error('此操作被禁用！');
        if(!in_array($auth['id'],$_SESSION[SESSION_NAME]['auth'])) $this->error('对不起，您无权限进行此操作！');
    }
    protected function gameAuth($game_id){
        $game_id = (int)$game_id;
        if(!in_array($game_id,$_SESSION[SESSION_NAME]['game']) || empty($_SESSION[SESSION_NAME]['game'])){
            $this->error('你没有操作此游戏的权限！');
        }
    }
    //获取字段信息.有缓存,更改数据表结构记得删除缓存
    protected function getFields($table=''){
        $cache_path = CACHE_PATH.'fields/';
        if(!$table){
            $table=$this->table_name?$this->table_name:parse_name(MODULE_NAME);
        }
        $fields=F($table,'',$cache_path);
        if($fields) return $fields;
        $query = $this->db->query('SHOW COLUMNS FROM '.DB_PREFIX.$table);
        $result = $this->db->getAll($query);
        $fields=array();
        foreach($result as $v){
            $fields[]=$v['Field'];
        }
        F($table,$fields,$cache_path);
        return $fields;
    }
    //根据数据表字段信息获取POST数据
    protected function getPostData($table=''){
        $fields=$this->getFields($table);
        foreach($fields as $k){
            if(isset($_GET[$k])) $this->db_data[$k]=trim($_GET[$k]); //增加支持GET方式，如果同时存在，以POST为准
            if(isset($_POST[$k])) $this->db_data[$k]=trim($_POST[$k]);
        }
        return $this->db_data;
    }
    //更新，插入的前置操作
    //通常用来验证数据，或填充数据
    protected function _before_update(){}
    protected function _before_insert(){}
    //插入后的前置操作，比如插入一条记录到关联表中
    protected function _after_insert($id,$data){}
    //删除后置操作 (通常用来删除关联数据)
    protected function _after_delete($id){}
    //对查询到的列表数据进行格式化后，再进行模板赋值
    protected function format_list(&$list){}
    //有些字段不能由表单提交来更改，所以为防万一在更新之前可以调用此方法注销字段值。
    protected function unset_db_data($fields=array()){
        foreach($fields as $v){
            if(isset($this->db_data[$v])) unset($this->db_data[$v]);
        }
    }
    //检查、得到要操作的ID，比如删除、设置状态时用到
    protected function check_id($id){}
    protected function get_id(){
        $id=$_REQUEST['id'];
        if(empty($id)){
            $this->error('请选择要操作的记录！');
        }
        if(!is_array($id)){//批量支持，不是数组搞成数组，这样就一视同仁了
            $id=(array)$id;
        }
        $this->check_id($id);
        return $id;
    }
    //公用的列表页查询方法
    protected function _search(){
        if($_POST['search_where']){
            $where=array();
            foreach($_POST['search_where'] as $key=>$val){
                $arr=array();
                foreach($val as $k=>$v){
                   if($v=='') continue;
                   if($k=='like'){
                       $arr[]=array($k,'%'.$v.'%');
                   }else{
                       $arr[]=array($k,$v);
                   }
                }
                $num=sizeof($arr);
                if($num==1){
                   $where[$key]=$arr[0];
                }elseif($num>1){
                   $where[$key]=$arr;
                }
            }
            //注意这里$this->search_where放在了后面，以便各自的action固定查询条件而不被这里的查询条件覆盖
            $this->search_where = array_merge($where,$this->search_where); 
            $this->search_value = array_merge($_POST['search_where'],$this->search_value);
        }
        $_SESSION['search_where'] = $this->search_where;
        $_SESSION['search_value'] = $this->search_value;
    }
    //默认列表页
    public function index($tpl=''){
        $table=$this->get_table();
        if($_GET['do']=='search'){//查询
            $this->_search();
            $this->search_where = $_SESSION['search_where'];
            $this->search_value = $_SESSION['search_value'];
        }
        $count=$this->db->table($table)->where($this->search_where)->count('id');
        $pageNum = isset($_POST['pageNum']) ? (int)$_POST['pageNum'] : 1;
        if(isset($_POST['numPerPage'])) $this->list_num = (int)$_POST['numPerPage'];
        $totalPage = ceil($count/$this->list_num);
        if($pageNum>$totalPage) $pageNum = $totalPage;
        if($pageNum<1) $pageNum = 1;
        $limit=($pageNum-1)*$this->list_num .','. $this->list_num;
        $list=$this->db->table($table)->field($this->field)->where($this->search_where)->order($this->order_by)->limit($limit)->select();
        //echo $this->db->getSql();
        $this->format_list($list);
        $page = array('totalCount'=>$count,'numPerPage'=>$this->list_num,'currentPage'=>$pageNum);
        $this->assign('search_where',$this->search_value);
        $this->assign('list',$list);
        $this->assign('page',$page);
        $this->display($tpl);
    }
    protected function info(){
        $table=$this->get_table();
        $id=(int)$_GET['id'];
        $vo=$this->db->table($table)->where('id='.$id)->find($id);
        return $vo;
    }
    //默认内容页
    public function view($tpl=''){
        $this->edit($tpl);
    }
    //默认编辑页
    public function edit($tpl=''){
        $vo=$this->info();
        $this->assign('vo',$vo);
        $this->display($tpl);
    }
    //默认更新操作
    public function update(){
        $table=$this->get_table();
        $where['id']=(int)$_POST['id'];
        //收集数据
        $this->getPostData();
        //更新之前操作
        $this->_before_update();
        unset($this->db_data['id']);//id是更新条件，不是要更新的字段
        //进行更新

        $result=$this->db->table($table)->where($where)->data($this->db_data)->update();
        
        if($result>0){
            $this->success('更新成功！');
        }elseif($result===0){
            $this->error('没有变化！');
        }else{
            $this->error('更新失败！');
        }
    }
    //默认插入操作
    public function insert(){
        $table=$this->get_table();
        //收集数据
        $this->getPostData();
        //插入之前操作
        $this->_before_insert();
        //进行插入
        $result=$this->db->table($table)->data($this->db_data)->insert();
        if($result>0){
            $this->_after_insert($result,$this->db_data); //插入后操作
            $this->success('新增成功！');
        }else{
            $this->error('新增失败！');
        }
    }
    //默认删除操作
    public function delete(){
        $table=$this->get_table();
        $id = $this->get_id();
        foreach($id as $v){
            $where['id']=(int)$v;
            $this->db->table($table)->where($where)->delete();
            $this->_after_delete($where['id']);
        }
        $this->success('删除成功！','refresh');
    }
    //默认设置状态操作
    public function status(){
        $table=$this->get_table();
        $id = $this->get_id();
        $data['status']=(int)$_GET['status'];
        foreach($id as $v){
            $where['id']=(int)$v;
            $this->db->table($table)->where($where)->data($data)->update();
        }
        $this->success('设置成功！','refresh');
    }
}//类定义结束
?>