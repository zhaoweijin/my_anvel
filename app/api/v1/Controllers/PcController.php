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
        $val = DB::select("SELECT * FROM hoho_position_data where $where ORDER BY created_at limit ?,?",[$type,$position_id,$offset,$num]);
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

        $where = "device=?";
        $val = DB::select("SELECT id,title,icon,hot FROM hoho_events where $where ORDER BY hot DESC,start_date DESC limit ?,?",[$device,$offset,$num]);
        return response()->json(['result' => $val,'status_code'=>1]);
    }


























}
