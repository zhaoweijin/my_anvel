<?php

namespace App\Api\v1\Controllers;

use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;
use Dingo\Api\Routing\Helpers;
use Illuminate\Support\Facades\DB;
session_start();
ini_set("display_errors", "On");
error_reporting(E_ALL | E_STRICT);
class GamesController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
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
        $val = DB::select("SELECT id,title,icon,get_num,tao_num,total,start_date,end_date FROM hoho_events where hot=1 ORDER BY created_at limit ?,?",[$offset,$num]);
        return response()->json(['result' => $val,'status_code'=>1]);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function showNewGames(Request $request){

        $num = (int)$request->get('num', 10);
        $offset = (int)$request->get('offset', 0);


        $val = DB::select("SELECT id,title,icon,get_num,tao_num,total,start_date,end_date FROM hoho_events ORDER BY created_at limit ?,?",[$offset,$num]);
        return response()->json(['result' => $val]);
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
                    $back['errNum']  =  -1;
                    $back['errMsg']  = '网络异常～';
                    return response()->json($back);
                }

                $_SESSION['activity_login_name']     =  checkData($username);      //用户名
                $_SESSION['activity_login_user_id']     =  $user_id;      //用户id
                $_SESSION['activity_type']     =  1;      //type 1.passport 2.weixin
                $_SESSION['activity_login_info']     =  $_SERVER['REMOTE_ADDR'].','.$_SERVER["HTTP_USER_AGENT"];   //用户的 IP  浏览器信息

                $back['errNum']  = 1;
                $back['errMsg']  = $sign_g;
//                return response()->json($back)->setCallback($request->get('callback'));
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
            $type = $_SESSION['activity_type'];
            if (!$type) {
                $back['errNum'] = -1;
                $back['errMsg'] = '未登录～';
                jsonBack($back);
            }

            if ($type == 1) {
                $visitor = $_SESSION['activity_login_user_id'];
            } elseif ($type == 2) {
                $visitor = $_SESSION['activity_open_id']; //weixin
            }
            $val = DB::select("SELECT card FROM hoho_tickets where visitor=? and event_id=? limit 1", [$visitor, $event_id]);

            if (!$val) {
                $val = DB::select("SELECT card FROM hoho_tickets where event_id=? and state=0 and is_tao=0 and deleted_at is null ORDER BY id limit 1", [$event_id]);
                if($val){
                    $card = $val[0]->card;
                    DB::update("update hoho_tickets set visitor=?,state=1,updated_at=? where card = ?", [$visitor, date("Y-m-d h:i:s"), $card]);
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

    /*
     * get event
     * @param Request $request
     * @param $event_id
     * @return mixed
     */
    public function getEvent(Request $request,$event_id)
    {
        $event_id      =  (int)$event_id;
        $val = DB::select("SELECT id,game_id,title,icon,get_num,tao_num,total,description,zone_url,start_date,end_date FROM hoho_events where id=? limit 1",[$event_id]);
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
            $val = DB::select("SELECT a.id,game_id,title,icon,start_date,end_date FROM hoho_events a LEFT JOIN hoho_tickets b ON a.id=b.event_id where b.visitor = ? limit ?,?",[$visitor,$offset,$num]);
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
}
