<?php
import('Admin','controller');
class UploadAction extends AdminAction{
    private $path;//上传目录
    private $date_path = true;//是否分日期保存
    public function __construct(){
        if (isset($_POST["PHPSESSID"])){//使用flash上传，非IE不能传递cookie，所以只能通过post把session_id传递过来。
            session_write_close();
            session_id($_POST["PHPSESSID"]);
            session_start();
        }
        $this->path = APP_ROOT.'upload/';
        parent::__construct();
    }
    protected function checkAuth(){
    }
    //上传图片操作
    public function uploadpic(){
        ini_set('memory_limit','128M');
        import('UploadFile');
        $upload = new UploadFile();
        $upload->maxSize  = 3145728; //3M 设置上传大小
        $upload->allowExts = array('jpg','gif','png','jpeg','bmp'); //设置上传类型
        if($this->date_path){
            //按日期分目录保存
            $myDir=date('Ymd');
            //如果目录不存在，上传类会自动尝试创建
            $upload->savePath = $this->path.$myDir.'/'; // 设置上传目录
        }else{
            $upload->savePath = $this->path; // 设置上传目录
        }
        $upload->saveRule=uniqid();//上传文件的保存规则
        //生成缩略图
        $upload->thumb = true;
        $upload->thumbMaxWidth = "1000";
        $upload->thumbMaxHeight = "1000";
        $upload->thumbPrefix='';//不需要缩略图前缀,相当于覆盖原图，所以以下这句注释
        //$upload->thumbRemoveOrigin=true;//生成缩略图后删除原图
        if(!$upload->upload()) {
            $this->error($upload->getErrorMsg());
        }else{
             //成功上传
            $uploadList = $upload->getUploadFileInfo();
            //var_dump($uploadList);
            $this->success($upload->savePath.$uploadList[0]['savename']);
         }
    }
    //上传附加图
    public function morepic(){
        $this->date_path = false;
        $this->path .= $_GET['type'].'_'.$_GET['id'].'/';//附加图目录
        $this->uploadpic();
    }
}//类结束
?>