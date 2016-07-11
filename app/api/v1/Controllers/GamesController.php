<?php

namespace App\Api\v1\Controllers;

use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;
use Dingo\Api\Routing\Helpers;
use Illuminate\Support\Facades\DB;

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

        $num = (int)$request->get('num', 5);
        $offset = (int)$request->get('offset', 0);

        $val = DB::select("SELECT title,icon,get_num,tao_num,start_date,end_date FROM events where hot=1 ORDER BY created_at limit ?,?",[$offset,$num]);
        return response()->json(['data' => $val]);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function showNewGames(Request $request){

        $num = (int)$request->get('num', 5);
        $offset = (int)$request->get('offset', 0);

        $val = DB::select("SELECT title,icon,get_num,tao_num,start_date,end_date FROM events ORDER BY created_at limit ?,?",[$offset,$num]);
        return response()->json(['data' => $val]);
    }

    /**
     * get event libao
     *
     * @param Request $request
     * @param $event_id
     * @return mixed
     */
    public function postEvent(Request $request, $event_id){

        $num = (int)$request->get('num', 5);
        $offset = (int)$request->get('offset', 0);

        $val = DB::select("SELECT title,icon,get_num,tao_num,start_date,end_date FROM events ORDER BY created_at limit ?,?",[$offset,$num]);
        return response()->json(['data' => $val]);
    }
}
