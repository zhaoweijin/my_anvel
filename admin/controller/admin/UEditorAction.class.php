<?php
/** 百度编辑器上传操作 **/
import('Admin','controller');
class UEditorAction extends AdminAction{
    private $path; //上传目录
    public function __construct(){
        if (isset($_POST["PHPSESSID"])){//使用flash上传，非IE不能传递cookie，所以只能通过post把session_id传递过来。
            session_write_close();
            session_id($_POST["PHPSESSID"]);
            session_start();
        }
        $this->path = APP_ROOT.'upload/';
        parent::__construct();
    }
    /****  图片上传  ****/
    public function imageUp(){
        import('Uploader');
        //上传配置
        $config = array(
            "savePath" => $this->path,
            "maxSize" => 1000 , //单位KB
            "allowFiles" => array( ".gif" , ".png" , ".jpg" , ".jpeg" , ".bmp"  )
        );
        //上传图片框中的描述表单名称，
        $title = htmlspecialchars( $_POST[ 'pictitle' ] , ENT_QUOTES );
        //生成上传实例对象并完成上传
        $up = new Uploader( "upfile" , $config );
        /**
         * 得到上传文件所对应的各个参数,数组结构
         * array(
         *     "originalName" => "",   //原始文件名
         *     "name" => "",           //新文件名
         *     "url" => "",            //返回的地址
         *     "size" => "",           //文件大小
         *     "type" => "" ,          //文件类型
         *     "state" => ""           //上传状态，上传成功时必须返回"SUCCESS"
         * )
         */
        $info = $up->getFileInfo();
        /**
         * 向浏览器返回数据json数据
         * {
         *   'url'      :'a.jpg',   //保存后的文件路径
         *   'title'    :'hello',   //文件描述，对图片来说在前端会添加到title属性上
         *   'original' :'b.jpg',   //原始文件名
         *   'state'    :'SUCCESS'  //上传状态，成功时返回SUCCESS,其他任何值将原样返回至图片上传框中
         * }
         */
        echo "{'url':'" . $info["url"] . "','title':'" . $title . "','original':'" . $info["originalName"] . "','state':'" . $info["state"] . "'}";
    }
    /****  涂鸦上传  ****/
    public function scrawlUp(){
        import('Uploader');
        //上传配置
        $config = array(
            "savePath" => $this->path ,             //存储文件夹
            "maxSize" => 1000 ,                   //允许的文件最大尺寸，单位KB
            "allowFiles" => array( ".gif" , ".png" , ".jpg" , ".jpeg" , ".bmp" )  //允许的文件格式
        );
        //临时文件目录
        $tmpPath = CACHE_PATH . "ueditor/";

        //获取当前上传的类型
        $action = htmlspecialchars( $_GET[ "action" ] );
        if ( $action == "tmpImg" ) { // 背景上传
            //背景保存在临时目录中
            $config[ "savePath" ] = $tmpPath;
            $up = new Uploader( "upfile" , $config );
            $info = $up->getFileInfo();
            /**
             * 返回数据，调用父页面的ue_callback回调
             */
            echo "<script>parent.ue_callback('" . $info[ "url" ] . "','" . $info[ "state" ] . "')</script>";
        } else {
            //涂鸦上传，上传方式采用了base64编码模式，所以第三个参数设置为true
            $up = new Uploader( "content" , $config , true );
            //上传成功后删除临时目录
            if(file_exists($tmpPath)){
                $this->delDir($tmpPath);
            }
            $info = $up->getFileInfo();
            echo "{'url':'" . $info[ "url" ] . "',state:'" . $info[ "state" ] . "'}";
        }
    }
    /****  远程抓取图片  ****/
    function getRemoteImage() {
        //远程抓取图片配置
        $config = array(
            "savePath" => $this->path ,            //保存路径
            "allowFiles" => array( ".gif" , ".png" , ".jpg" , ".jpeg" , ".bmp" ) , //文件允许格式
            "maxSize" => 3000                    //文件大小限制，单位KB
        );
        $uri = htmlspecialchars( $_POST[ 'upfile' ] );
        $uri = str_replace( "&amp;" , "&" , $uri );
        //忽略抓取时间限制
        set_time_limit( 0 );
        //ue_separate_ue  ue用于传递数据分割符号
        $imgUrls = explode( "ue_separate_ue" , $uri );
        $tmpNames = array();
        foreach ( $imgUrls as $imgUrl ) {
            //http开头验证
            if(strpos($imgUrl,"http")!==0){
                array_push( $tmpNames , "error" );
                continue;
            }
            //获取请求头
            $heads = get_headers( $imgUrl );
            //死链检测
            if ( !( stristr( $heads[ 0 ] , "200" ) && stristr( $heads[ 0 ] , "OK" ) ) ) {
                array_push( $tmpNames , "error" );
                continue;
            }

            //格式验证(扩展名验证和Content-Type验证)
            $fileType = strtolower( strrchr( $imgUrl , '.' ) );
            if ( !in_array( $fileType , $config[ 'allowFiles' ] ) || stristr( $heads[ 'Content-Type' ] , "image" ) ) {
                array_push( $tmpNames , "error" );
                continue;
            }

            //打开输出缓冲区并获取远程图片
            ob_start();
            $context = stream_context_create(
                array (
                    'http' => array (
                        'follow_location' => false // don't follow redirects
                    )
                )
            );
            //请确保php.ini中的fopen wrappers已经激活
            readfile( $imgUrl,false,$context);
            $img = ob_get_contents();
            ob_end_clean();

            //大小验证
            $uriSize = strlen( $img ); //得到图片大小
            $allowSize = 1024 * $config[ 'maxSize' ];
            if ( $uriSize > $allowSize ) {
                array_push( $tmpNames , "error" );
                continue;
            }
            //创建保存位置
            $savePath = $config[ 'savePath' ] . date('Ymd') . '/';
            if ( !file_exists( $savePath ) ) {
                mkdir( "$savePath" , 0777 );
            }
            //写入文件
            $tmpName = $savePath . rand( 1 , 10000 ) . time() . strrchr( $imgUrl , '.' );
            try {
                $fp2 = @fopen( $tmpName , "a" );
                fwrite( $fp2 , $img );
                fclose( $fp2 );
                array_push( $tmpNames ,  $tmpName );
            } catch ( Exception $e ) {
                array_push( $tmpNames , "error" );
            }
        }
        /**
         * 返回数据格式
         * {
         *   'url'   : '新地址一ue_separate_ue新地址二ue_separate_ue新地址三',
         *   'srcUrl': '原始地址一ue_separate_ue原始地址二ue_separate_ue原始地址三'，
         *   'tip'   : '状态提示'
         * }
         */
        echo "{'url':'" . implode( "ue_separate_ue" , $tmpNames ) . "','tip':'远程图片抓取成功！','srcUrl':'" . $uri . "'}";
    }
    /****  附件上传  ****/
    public function fileUp(){
        import('Uploader');
        //上传配置
        $config = array(
            "savePath" => $this->path, //保存路径
            "allowFiles" => array( ".rar" , ".doc" , ".docx" , ".zip" , ".pdf" , ".txt" , ".swf" , ".wmv" ) , //文件允许格式
            "maxSize" => 100000 //文件大小限制，单位KB
        );
        //生成上传实例对象并完成上传
        $up = new Uploader( "upfile" , $config );
        /**
         * 得到上传文件所对应的各个参数,数组结构
         * array(
         *     "originalName" => "",   //原始文件名
         *     "name" => "",           //新文件名
         *     "url" => "",            //返回的地址
         *     "size" => "",           //文件大小
         *     "type" => "" ,          //文件类型
         *     "state" => ""           //上传状态，上传成功时必须返回"SUCCESS"
         * )
         */
        $info = $up->getFileInfo();
        /**
         * 向浏览器返回数据json数据
         * {
         *   'url'      :'a.rar',        //保存后的文件路径
         *   'fileType' :'.rar',         //文件描述，对图片来说在前端会添加到title属性上
         *   'original' :'编辑器.jpg',   //原始文件名
         *   'state'    :'SUCCESS'       //上传状态，成功时返回SUCCESS,其他任何值将原样返回至图片上传框中
         * }
         */
        echo '{"url":"' .$info[ "url" ] . '","fileType":"' . $info[ "type" ] . '","original":"' . $info[ "originalName" ] . '","state":"' . $info["state"] . '"}';
    }
    /****  在线图片管理  ****/
    public function imageManager(){
        $action = htmlspecialchars( $_POST[ "action" ] );
        if ( $action == "get" ) {
            $files = glob($this->path . date('Ymd').'/*.{gif,jpeg,jpg,png,bmp}',GLOB_BRACE); //只获取当天上传的图片，避免图片过多造成严重的带宽和性能消耗
            if ( !$files ) return;
            rsort($files,SORT_STRING);
            $str = "";
            foreach ( $files as $file ) {
                $str .= $file . "ue_separate_ue";
            }
            echo $str;
        }
    }
    public function getMovie(){
        $key =htmlspecialchars($_POST["searchKey"]);
        $type = htmlspecialchars($_POST["videoType"]);
        $html = file_get_contents('http://api.tudou.com/v3/gw?method=item.search&appKey=myKey&format=json&kw='.$key.'&pageNo=1&pageSize=20&channelId='.$type.'&inDays=7&media=v&sort=s');
        echo $html;
    }
    /****  删除整个目录  ****/
    private function delDir( $dir ) {
        //先删除目录下的所有文件：
        $dh = opendir( $dir );
        while ( $file = readdir( $dh ) ) {
            if ( $file != "." && $file != ".." ) {
                $fullpath = $dir . "/" . $file;
                if ( !is_dir( $fullpath ) ) {
                    unlink( $fullpath );
                } else {
                    $this->delDir( $fullpath );
                }
            }
        }
        closedir( $dh );
        //删除当前文件夹：
        return rmdir( $dir );
    }
}//类结束
?>