<?php

namespace App\Console\Commands\GiftGenerators;

use DB;
use Exception;
use Illuminate\Console\Command;

class UpdateTaohaoCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ng:taohao';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update card to taohao.';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @throws Exception
     */
    protected function validateInput()
    {
        if (!preg_match('/^[a-z0-9-_]+$/i', $this->argument('name'))) {
            throw new Exception;
        }
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
//        $this->validateInput();
//
//        $name = $this->argument('name');
//        $type = 'taohao';
        $val = DB::select("SELECT id,title,total FROM hoho_events where set_tao = 1 and is_tao !=1 ORDER BY tao_num DESC");

        if($val){
            foreach ($val as $v){
                $arr = DB::select("SELECT event_id,count(id) as s FROM hoho_tickets where event_id = ? and visitor!='' and state=1 GROUP BY event_id",[(int)$v->id]);
                if($arr){
                    if($arr[0]->s == $v->total){
                        DB::update("update hoho_events set is_tao=1,get_num=? where id = ?", [(int)$arr[0]->s,(int)$v->id]);
                    }
                }
            }
            $this->info("Update success");
        }else{
            $this->info("No need to be updated");
        }
    }
}