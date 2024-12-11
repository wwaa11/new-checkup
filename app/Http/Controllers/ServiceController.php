<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessClearTask;
use App\Jobs\ProcessCreateTask;

class ServiceController extends Controller
{
    //
    function startService()
    {
        ProcessCreateTask::dispatch();
        ProcessClearTask::dispatch();
    }
}
