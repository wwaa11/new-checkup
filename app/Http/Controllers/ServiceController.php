<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessClearTask;
use App\Jobs\ProcessCreateTask;
use App\Models\Patient;
use App\Models\Patienttask;
use DB;

class ServiceController extends Controller
{
    //
    function test()
    {
        $hour = (int)date('H');
        
        if($hour >= 5 && $hour <= 16){
            echo('Service time : ');
        }else{
            echo('Out of service time : ');
        }

        return response()->json('CHECKUP PRARAM9 HOSPITAL : '.date('Y-m-d H:i:s') , 200);
    }
    function startService()
    {
        $jobs = DB::table('jobs')->get();
        $datas = [];
        foreach($jobs as $data){
            $info = json_decode($data->payload);
            $datas[] = [
                'id' => $data->id,
                'name' => $info->displayName,
                'create' => date('d-m-Y H:i:s', $data->created_at),
            ];
        }

        return view('services')->with(compact('datas'));
    }
    function dispatchCreate()
    {
        ProcessCreateTask::dispatch();
        return response()->json('success', 200);
    }
    function dispatchClear()
    {
        ProcessClearTask::dispatch();
        return response()->json('success', 200);
    }
}
