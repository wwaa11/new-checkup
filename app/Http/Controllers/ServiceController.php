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
        ProcessCreateTask::dispatch();
        ProcessClearTask::dispatch();
    }
}
