<?php
import('Admin','controller');
class GiftAction extends AdminAction{
	protected $table_name='events';//表名


	public function getGameData(){
		$num = (int)$_GET['num'];
		if(!$num)
			echo json_encode(array('data'=>-1));
		else
			echo get_game_data($num);
	}

	public function view(){
		$event_id = (int)$_GET['id'];
		if($event_id){
			$where = array('event_id' => $event_id);
			$list = $this->db->table('tickets')->field('id,card,visitor,created_at')->where($where)->limit(50)->order('id')->select();
			$this->assign('list',$list);
	        $this->display();
	    }
	}

	public function addGame(){
		if($_FILES && ($_FILES["file"]["type"] == "text/csv"||$_FILES["file"]["type"] == "application/vnd.ms-excel")){
			// $path = $_FILES['file']['tmp_name'];

   //          $handle = fopen($path,"r");
   //          while ($data = fgetcsv($handle, 10000)) {
   //              $num_added++;
   //              $num = count($data);
   //              if($num!==1){
   //                  return response()->json([
   //                      'status'   => 'error',
   //                      'messages' => 'Format error',
   //                  ]);
   //              }
   //              /**
   //               * Create the ticket
   //               */
   //              $attendee = new Ticket();
   //              $attendee->event_id = $event_id;
   //              $attendee->card = $data[0];
   //              $attendee->state = 0;
   //              $attendee->account_id = Auth::user()->account_id;
   //              $attendee->is_tao = 0;
   //              $attendee->save();
   //          }
   //          fclose($handle);

            $filename = $_FILES['file']['tmp_name'];
            if (empty ($filename)) { 
		        echo '请选择要导入的CSV文件！'; 
		        exit; 
		    } 
		    $handle = fopen($filename, 'r'); 
		    $result = input_csv($handle); //解析csv 
		    $len_result = count($result); 
		    if($len_result==0){ 
		        $this->error('没有任何数据!');
		    } 
		    for ($i = 0; $i < $len_result; $i++) { //循环获取各字段值 
		        $card = iconv('gb2312', 'utf-8', $result[$i][0]); //中文转码 
		        $event_id = (int)$_POST['event_id'];
		        $state = 0;
		        $created_at = date("Y-m-d h:i:s");
		        $updated_at = date("Y-m-d h:i:s");
		        $user_id = $_SESSION[SESSION_NAME]['uid'];
		        // $sex = iconv('gb2312', 'utf-8', $result[$i][1]); 
		        // $age = $result[$i][2]; 
		        $data_values .= "('$event_id','$card','$state','$user_id','$created_at','$updated_at'),"; 
		       
		    } 
		    $data_values = substr($data_values,0,-1); //去掉最后一个逗号 
		    fclose($handle); //关闭指针 

		    $sql = "insert into hoho_tickets (event_id,card,state,user_id,created_at,updated_at) values $data_values";
        	$query = $this->db->query($sql);
        	$result=$this->db->table('events')->where(array('id' => $event_id))->data(array('total'=>$len_result))->update();

		    // $query = mysql_query("insert into student (name,sex,age) values $data_values");//批量插入数据表中 
		    if($query){ 
		        $this->success('导入成功！','refresh');
		    }else{ 
		       	$this->error('导入失败!');
		    } 
        }else{
        	$this->assign('id',$_GET['id']);
            $this->display();
        }
	}

	public function deleteGameData(){
        $table='tickets';
        $id = $this->get_id();
        foreach($id as $v){
            $where['event_id']=(int)$v;
            $this->db->table($table)->where($where)->delete();
            $this->_after_delete($where['event_id']);
            $this->db->table('events')->where(array('id' => $where['event_id']))->data(array('get_num'=>0))->update();
        }



        $this->success('删除成功！','refresh');
    }

	public function getEvent(){
		$event_id = (int)$_GET['event_id'];
		if(!$event_id) {
			echo json_encode(array('data' => -1));
		}else{
			$where = array('id' => $event_id);
			$list = $this->db->table('events')->field('title,game,get_num,total')->where($where)->limit(1)->select();
			echo json_encode(array('data' => $list));
		}
	}

    public function export(){
        if($_POST){
            $num = (int)$_POST['num'];
            $event_id = (int)$_POST['event_id'];
            $where = array(
                'event_id' => $event_id,
                'state' => 0
            );
            $card = $this->db->table('tickets')->field('card')->where($where)->limit($num)->select();
            if(!$_POST['export']) {
                $this->assign('num', $num);
                $this->assign('card', $card);
                $this->assign('event_id', $event_id);
                $this->assign('export', 1);
                $this->display();
            }else{

                $str = "";

                foreach ($card as $row){
                    $card_num = iconv('utf-8','gb2312',$row['card']); //中文转码
                    $str .= $card_num."\n"; //用引文逗号分开
                }

                $filename = date('Ymd').'.csv'; //设置文件名
                $this->export_csv($filename,$str); //导出
            }
        }else {
            $this->display();
        }
    }

    protected function export_csv($filename,$data)
    {
        header("Content-type:text/csv");
        header("Content-Disposition:attachment;filename=".$filename);
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        echo $data;
    }
}//类结束
?>