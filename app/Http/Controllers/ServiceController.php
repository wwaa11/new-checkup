<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\ProcessCreateTask;

class ServiceController extends Controller
{
    //
    function startService()
    {
        ProcessCreateTask::dispatch();
    }
}
