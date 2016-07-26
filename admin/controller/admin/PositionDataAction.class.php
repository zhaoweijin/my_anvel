<?php
import('Admin','controller');
class PositionDataAction extends AdminAction{
	protected $table_name='position_data';//表名

	public function index(){

		$val=$this->db->table('position')->field('name')->where(array('id'=>$_GET['position_id']))->find();

		$this->assign('ptitle',$val['name']);
		$this->assign('type',$_GET['type']);
		$this->assign('position_id',$_GET['position_id']);
		$this->search_where = array('position_id'=>$_GET['position_id']);
		parent::index();
	}

	public function add(){
		$this->assign('type',$_GET['type']);
		$this->assign('position_id',$_GET['position_id']);
		$this->display();
	}

	public function getEventData(){
		$event_id = (int)$_GET['event_id'];
		if(!$event_id) {
			echo json_encode(array('data' => -1));
		}else{
			$where = array('id' => $event_id);
			$list = $this->db->table('events')->field('id,title,icon,device')->where($where)->limit(1)->select();
			echo json_encode(array('data' => $list));
		}
	}

	public function insert(){
		$cwd = getcwd();
		$imagePath = $cwd . '/resource/upload/image/';
		$dateP = date('Y') . '/' . date('m') . '/';
		$imagePath = $imagePath . $dateP;

		$data['position_id'] = $_POST['position_id'];
		$data['title'] = $_POST['title'];
		$data['url'] = $_POST['url'];
		$data['type'] = $_POST['type'];
		$data['icon'] = $_POST['icon'];
		$data['device'] = $_POST['device'];
		$data['orderid'] = $_POST['orderid'];
		$data['event_id'] = $_POST['event_id'];
		$data['created_at'] = date("Y-m-d h:i:s");

		$size = 300 * 1024; //200k
		if ($_FILES["thumb"]['size']!=0) {
			$val = $this->upFile('thumb', $imagePath, '', $size);
			if (!is_array($val))
				$thumb = $val;
		}

		if(is_array($val) && $val['error'])
			$this->error($val['error']);
		$thumb && $data['thumb'] = $thumb;



		$result = $this->db->table('position_data')->data($data)->insert();
		if ($result > 0) {
			$this->success('更新成功！');
		} elseif ($result === 0) {
			$this->error('没有变化！');
		} else {
			$this->error('更新失败！');
		}
	}

	public function upFile($file, $dir = '../upload/image/', $filetype = '', $size = '', $newname = '') {
		/*
          foreach($_FILES as $key=>$value){
          $file = $key;
          break;
          } */


		if ($_FILES["$file"]['error'] == '0') {
			switch ($filetype) {
				case 1:$filetype = 'application/octet-stream';
					break; //压缩类型
				case 2:$filetype = 'text/plain,application/pdf,application/msword';
					break; //txt,pdf,doc类型
				default:$filetype = 'image/jpeg,image/gif,image/png'; //图片类型
			}
			//判断上传文件的类型
			if (substr_count($filetype, $_FILES["$file"]['type'])) {
				//如果限制了文件大小则判断文件的大小
				if (!empty($size)) {
					if ($_FILES["$file"]['size'] > $size) {
						$result['error'] = '上传文件的大小不符合要求';
						return $result;
					}
				}
				//执行上传
				if (empty($newname)) {
					$hou = strrchr($_FILES["$file"]['name'], '.');
					$fir = substr($file, 0, 1);
					$newname = $fir . date('YmdHis', time()) . $hou;
					$upload = $dir . $newname;
				}

				$n_dir = explode('/', $dir);
				$w_dir = '';
				foreach ($n_dir as $k => $v) {
					$w_dir .= $v;
					if ($w_dir && !is_dir($w_dir)) {
						mkdir($w_dir);
					}
					$w_dir .='/';
				}

				if (is_uploaded_file($_FILES["$file"]['tmp_name'])) {
					if (move_uploaded_file($_FILES["$file"]['tmp_name'], $upload)) {
						$upload = strrchr($upload, 'upload');
//						return 'http://' . $_SERVER['SERVER_NAME'] . '/' . $upload;
						return 'http://' . $_SERVER['HTTP_HOST'] . '/resource/' . $upload;
					} else {
						$result['error'] = '上传失败';
						return $result;
					}
				}
			} else {
				$result['error'] = '上传文件的类型不符合要求';
				return $result;
			}
		} else {
			switch ($_FILES["$file"]['error']) {
				case 1:$tmp = '上传文件大小超出配置文件中的限制';
					break;
				case 2:$tmp = '上传文件大小超出HTML表单中max_file_size的限制';
					break;
				case 3:$tmp = '文件只上传了一部分';
					break;
				case 4:$tmp = '没有上传任何文件';
					break;
			}
			$result['error'] = $tmp;
			return $result;
		}
	}
}//类结束
?>