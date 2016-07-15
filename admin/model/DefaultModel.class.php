<?php
class DefaultModel{
    public function get_list($params){
        $db = new db();
        $where = $this->get_where($params);
        if(isset($params['where'])) $where['_string'] = $params['where'];
        if(isset($params['page'])){
            $count = $db->table($params['table'])->where($where)->count('id');
            import('Page');
            $Page = new Page($count,$params['page']);
            $params['limit'] = $Page->firstRow.','.$Page->listRows;
            $arr['total'] = $count;
            $arr['page'] = $Page->show();
        }
        if(!isset($params['order'])) $params['order'] = 'orderid desc,id desc';
        if(!isset($params['limit'])) $params['limit'] = '20';
        if(!isset($params['field'])) $params['field'] = 'id,title';
        $arr['data'] = $db->table($params['table'])->field($params['field'])->where($where)->order($params['order'])->limit($params['limit'])->select();
        return $arr;
    }
    public function get_one($params){
        $db = new db();
        $where = $this->get_where($params);
        if(isset($params['where'])) $where['_string'] = $params['where'];
        if(!isset($params['field'])) $params['field'] = '*';
        return $db->table($params['table'])->field($params['field'])->where($where)->find();
    }
    private function get_where($params){
        $where = array();
        foreach($params as $k=>$v){
            if(substr($k,0,1)=='_'){ //下横线开头的为查询字段
                $k = substr($k,1);
                $where[$k] = $v;
            }
        }
        return $where;
    }
}
?>