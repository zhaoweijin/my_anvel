<?php

namespace App\Api\v1\Controllers;

use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;
use Dingo\Api\Routing\Helpers;
use Illuminate\Support\Facades\DB;
session_start();

class GamesController extends Controller
{
//    protected $redis;//redis类对象
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
//        $this->redis = new \Redis();
//        $this->redis->connect('localhost', 6379);
    }

    /**
     * @param Request $request
     * @param $event_id
     * @return mixed

    public function showHotGames(Request $request,$num = ''){
        $data = array(
            array(),
            array(),
            array(),
        );
        $q = $request->get('q', '');
        $num = $num?$num:6;
        return response()->json(['data' => $num,'asd'=>$q]);
    }*/

    /**
     * @param Request $request
     * @return mixed
     */
    public function showHotGames(Request $request){
        $num = (int)$request->get('num', 10);
        $num = $num<=10?$num:10;
        $offset = (int)$request->get('offset', 0);
        $val = DB::select("SELECT a.id,a.title,a.icon,a.get_num,a.tao_num,a.total,a.is_tao,a.start_date,a.end_date FROM hoho_events a LEFT JOIN hoho_position_data b ON a.id=b.event_id where b.position_id=4 ORDER BY b.orderid DESC limit ?,?",[$offset,$num]);

//        $val = DB::select("SELECT id,title,icon,get_num,tao_num,total,is_tao,start_date,end_date FROM hoho_events where $where ORDER BY created_at limit ?,?",[$offset,$num]);
        return response()->json(['result' => $val,'status_code'=>1]);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function showNewGames(Request $request){

        $num = (int)$request->get('num', 10);
        $num = $num<=10?$num:10;
        $offset = (int)$request->get('offset', 0);
        $type = (int)$request->get('type', 0);
        if($type)
            $where = "type=$type or type=3";
        else
            $where = "type=3";
        $val = DB::select("SELECT id,title,icon,get_num,tao_num,total,is_tao,start_date,end_date FROM hoho_events where $where ORDER BY start_date desc limit ?,?",[$offset,$num]);
        return response()->json(['result' => $val,'status_code'=>1]);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function authPassport(Request $request){
        $action    =  $request->get('act');
        switch($action){
            case 'login':   //登录
                $sign      =  $request->get('sign');
                $avatar      =  $request->get('avatar');
                $email      =  $request->get('email');
                $nickname      =  $request->get('nickname');
                $site_id      =  $request->get('site_id');
                $sso_token      =  $request->get('sso_token');
                $time      =  (int)$request->get('time');
                $user_id      =  (int)$request->get('user_id');
                $username      =  $request->get('username');
                $warning     =  $request->get('warning');

                $data=array(
                    'avatar'    =>  $avatar,
                    'email'     =>  $email,
                    'nickname'  =>  $nickname,
                    'site_id'   =>  $site_id,
                    'sso_token' =>  $sso_token,
                    'time'      =>  $time,
                    'user_id'   =>  $user_id,
                    'username'  =>  $username,
                    'warning'   =>  $warning
                );
                $sign_g = apiSign($data,env('SITE_SECRET'));  //根据信息生成sign签名

                if($sign != $sign_g){
                    unset($_SESSION);
                    session_destroy();   //注销session
                    $back['errNum']  =  -1;
                    $back['errMsg']  = '网络异常～';
                    return response()->json($back);
                }

                $_SESSION['activity_login_name']     =  checkData($username);      //用户名
                $_SESSION['activity_login_user_id']     =  $user_id;      //用户id
                $_SESSION['activity_type']     =  1;      //type 1.passport 2.weixin
                $_SESSION['activity_index']     =  'http://'.$_SERVER['HTTP_HOST'].'/mobile/?sso_token='.$sso_token;      //type 1.passport 2.weixin
                $_SESSION['activity_login_info']     =  $_SERVER['REMOTE_ADDR'].','.$_SERVER["HTTP_USER_AGENT"];   //用户的 IP  浏览器信息

                $back['errNum']  = 1;
                $back['errMsg']  = $sign_g;
//                return response()->json($back)->setCallback($request->get('callback'));
                jsonBack($back);
                break;

            case 'weixin':
                $user_id      =  checkData($request->get('open_id'));
                $token      =  $request->get('token');
                $key = 'renwan19213213221renwan';
                $token_t = base64_encode(md5($key.$user_id));
                if($token_t!=$token){
                    unset($_SESSION);
                    session_destroy();   //注销session
                    $back['errNum']  =  -1;
                    $back['errMsg']  = '网络异常～';
                    return response()->json($back);
                }
                $_SESSION['activity_login_user_id']     =  $user_id;      //用户id
                $_SESSION['activity_type']     =  2;      //type 1.passport 2.weixin
                $back['errNum']  = 1;
                $back['errMsg']  = $token_t;
                jsonBack($back);
                break;
            case 'logout':     //注销

                unset($_SESSION);
                session_destroy();   //注销session

                $back['errNum']  = 1;
                $back['errMsg']  = '注销成功～';
//                return response()->json($back)->setCallback($request->get('callback'));
                jsonBack($back);
                break;
        }
    }

    /**
     * get event libao
     *
     * @param Request $request
     * @param $event_id
     * @return mixed
     */
    public function postEvent(Request $request, $event_id){

        if(isset($_SESSION['activity_type'])) {
            $event_id = (int)$event_id;

            $visitor = $_SESSION['activity_login_user_id'];
            $val = DB::select("SELECT card FROM hoho_tickets where visitor=? and event_id=? limit 1", [$visitor, $event_id]);

            if (!$val) {
                $val = DB::select("SELECT card FROM hoho_tickets where event_id=? and state=0 and is_tao=0 and deleted_at is null ORDER BY id limit 1", [$event_id]);
                if($val){
                    $card = $val[0]->card;
                    DB::update("update hoho_tickets set visitor=?,state=1,updated_at=? where card = ?", [$visitor, date("Y-m-d h:i:s"), $card]);
                    DB::update("update hoho_events set get_num=get_num+1 where id = ?", [$event_id]);
                    return response()->json(['result' => $val,'status_code'=>1]);
                }else{
                    $back['status_code'] = -2;
                    $back['message'] = '已领取完';
                    return response()->json(['error' => $back]);
                }
            } else {
                return response()->json(['result' => $val,'status_code'=>2,'message'=>'已领取']);
            }
        }else{
            $back['status_code'] = -1;
            $back['message'] = '未登陆～';
            return response()->json(['error' => $back]);
        }
    }

    /**
     * get event taohao libao
     *
     * @param Request $request
     * @param $event_id
     * @return mixed
     */
    public function postTaohaoEvent(Request $request, $event_id){

        if(isset($_SESSION['activity_type'])) {
            $event_id = (int)$event_id;

            $val = DB::select("SELECT card FROM hoho_tickets where event_id=? and visitor!='' limit 100",[(int)$event_id]);
            DB::update("update hoho_events set tao_num=tao_num+1 where id = ?", [(int)$event_id]);

            if (!$val) {
                $back['status_code'] = -2;
                $back['message'] = '该礼包没有淘号码';
                return response()->json(['error' => $back]);
            } else {
                return response()->json(['result' => $val[array_rand($val)],'status_code'=>1]);
            }
        }else{
            $back['status_code'] = -1;
            $back['message'] = '未登陆～';
            return response()->json(['error' => $back]);
        }
    }

    /*
     * get event
     * @param Request $request
     * @param $event_id
     * @return mixed
     */
    public function getEvent(Request $request,$event_id)
    {
        $event_id      =  (int)$event_id;
        $card = '';
        if(isset($_SESSION['activity_login_user_id'])) {
            $visitor = $_SESSION['activity_login_user_id'];
            $val = DB::select("SELECT card FROM hoho_tickets where event_id=? and visitor=? limit 1",[$event_id,$visitor]);
            if($val)
                $card = $val[0]->card;
        }


        $val = DB::select("SELECT id,game_id,title,icon,get_num,tao_num,total,content,description,zone_url,is_tao,device,start_date,end_date FROM hoho_events where id=? limit 1",[$event_id]);

        $val[0]->card = $card;
        return response()->json(['result' => $val,'status_code'=>1]);
    }

    /*
     * get my package
     * @param Request $request
     * @return mixed
     */
    public function getMyPackage(Request $request){

        if(isset($_SESSION['activity_login_user_id'])) {

            $num = (int)$request->get('num', 10);
            $num = $num<=10?$num:10;
            $offset = (int)$request->get('offset', 0);

            $visitor = $_SESSION['activity_login_user_id'];
            $val = DB::select("SELECT a.id,game_id,title,icon,start_date,end_date,b.card FROM hoho_events a LEFT JOIN hoho_tickets b ON a.id=b.event_id where b.visitor = ? limit ?,?",[$visitor,$offset,$num]);
            if($val)
                return response()->json(['result' => $val,'status_code'=>1]);
            else
                return response()->json(['result' => 0,'status_code'=>1]);
        }else{
            $back['status_code'] = -1;
            $back['message'] = '未登陆～';
            return response()->json(['error' => $back]);
        }
    }

    /*
     * search
     * @param Request $request
     * @return mixed
     */
    public function search(Request $request){


        if(isset($_SESSION['activity_index'])) {
            $url = $_SESSION['activity_index'];
            $login = 1;
        }else {
            $url = 'http://' . $_SERVER['HTTP_HOST'] . '/mobile/';
            $login = 0;
        }
        $num = (int)$request->get('num', 10);
        $num = $num<=10?$num:10;
        $offset = (int)$request->get('offset', 0);
        $wd = checkData($request->get('wd'));

        $wd = '%'.$wd.'%';
        if($wd){
            $val = DB::select("SELECT id,game_id,title,icon,get_num,total,is_tao,end_date FROM hoho_events where title like ? ORDER BY end_date desc limit ?,? ",[$wd,$offset,$num]);
            if($val)
                return response()->json(['result' => $val,'status_code'=>1,'other'=>array('url'=>$url,'login'=>$login)]);
            else
                return response()->json(['result' => 0,'status_code'=>1,'other'=>array('url'=>$url,'login'=>$login)]);
        }
    }
}
