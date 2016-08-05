<?php

namespace App\Api\v1\Controllers;

use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;
use Dingo\Api\Routing\Helpers;
use Illuminate\Support\Facades\DB;

class PcController extends Controller
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
     * get position data
     *
     * @param Request $request
     * @param $position_id
     * @return mixed
     */
    public function showPosition(Request $request,$position_id){
        $num = (int)$request->get('num', 10);
        $num = $num<=10?$num:10;
        $offset = (int)$request->get('offset', 0);
        $type = (int)$request->get('type', 0);

        $where = "type=? and position_id=?";
        $val = DB::select("SELECT * FROM hoho_position_data where $where ORDER BY orderid DESC limit ?,?",[$type,$position_id,$offset,$num]);
        return response()->json(['result' => $val,'status_code'=>1]);
    }

    /**
     * get index event data
     *
     * @param Request $request
     * @param $position_id
     * @return mixed
     */
    public function showEvents(Request $request){
        $num = (int)$request->get('num', 10);
        $num = $num<=15?$num:15;
        $offset = (int)$request->get('offset', 0);
        $device = (int)$request->get('device', 0);

        if($device==3)
            $where = "device=? or 1";
        else
            $where = "device=?";
//        $val = DB::select("SELECT id,title,icon,hot FROM hoho_events where $where ORDER BY hot DESC,start_date DESC limit ?,?",[$device,$offset,$num]);
        $val = DB::select("SELECT id,title,icon,hot,device FROM hoho_events where $where ORDER BY start_date DESC limit ?,?",[$device,$offset,$num]);
        return response()->json(['result' => $val,'status_code'=>1]);
    }

    /**
     * get index event data
     *
     * @param Request $request
     * @return mixed
     */
    public function showRandomEvents(Request $request){
        $num = (int)$request->get('num', 8);
        $num = $num<=8?$num:8;
        $data = date('Y-m-d H:i:s');
        $val = DB::select("SELECT id,title,icon,game FROM hoho_events where end_date>? OR is_forever = 1 ORDER BY end_date DESC limit 100",[$data]);
        shuffle($val);
        $val = array_slice($val,0,$num);
        return response()->json(['result' => $val,'status_code'=>1]);
    }

    /**
     * get index taohao data
     *
     * @param Request $request
     * @return mixed
     */
    public function showTaohaos(Request $request){
        $val = DB::select("SELECT id,title FROM hoho_events where is_tao = 1 ORDER BY tao_num DESC limit 7");
        return response()->json(['result' => $val,'status_code'=>1]);
    }

    /**
     * get event info
     *
     * @param Request $request
     * @param $event_id
     * @return mixed
     */
    public function showEventinfo(Request $request,$event_id){
        $val = DB::select("SELECT id,title,icon,game,game_id,get_num,total,device,content,description,zone_url,start_date,end_date,is_tao,is_forever FROM hoho_events where id=? OR is_forever = 1 ORDER BY tao_num DESC limit 1",[$event_id]);

        if($val){
            $game = $val[0]->game;
            $game = '%'.$game.'%';
            $val2 = DB::select("SELECT id,title,icon FROM hoho_events where game LIKE ? ORDER BY tao_num DESC limit 5",[$game]);
            if($val2)
                $val[0]->other = $val2;
        }

        return response()->json(['result' => $val,'status_code'=>1]);
    }

    /*
     * search
     * @param Request $request
     * @return mixed
     */
    public function pcSearch(Request $request){
        $wd = checkData($request->get('wd'));

        $wd = '%'.$wd.'%';
        if($wd){
            $val = DB::select("SELECT id,title,icon FROM hoho_events where title like ? ORDER BY id desc limit 15 ",[$wd]);
            if($val)
                return response()->json(['result' => $val,'status_code'=>1]);
            else
                return response()->json(['result' => 0,'status_code'=>1]);
        }
    }


























}
