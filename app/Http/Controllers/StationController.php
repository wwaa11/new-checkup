<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Patienttask;
use App\Models\Patientlogs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Station;
use App\Models\Substation;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use DB;

class StationController extends Controller
{
    function test()
    {
        
        return response()->json('index new check up page '.date('Y-m-d H:i:s') , 200);
    }
    function Auth()
    {

        return view('station.login');
    }
    function AuthCheck(Request $request)
    {
        $userid = $request->userid;
        $password = $request->password;
        $response = Http::withHeaders([
            'token' => env('API_TOKEN_STAFF')
        ])->post('http://172.20.1.12/dbstaff/api/auth', [
            'userid' => $userid,
            'password' => $password,
        ])->object();
        if($response->status == 1){
            session(['userid' => $response->user->userid , 'name' => $response->user->name]);

            $user = User::firstOrCreate([
                'userid' => $response->user->userid,
                'name' => $response->user->name,
            ]);

            if (Auth::loginUsingId($user->id)) {

                return response()->json(['status' => 1 , 'text' => 'Authentication Success!'],200);
            }else{

                return response()->json(['status'=> 0,'text'=> 'Authentication Success , User not found!'],200);
            }
        }

        return response()->json(['status' => 0 , 'text' => 'Authentication Failed!'],200);

    }
    
    function StationIndex()
    {
        $stations = [];
        $substations = Substation::all();
        foreach ($substations as $subst) {
            $stations[$subst->station->name][] = [
                'id'=> $subst->id,
                'name'=> $subst->name
            ];
        }
        
        return view('station.index', ['stations' => $stations]);
    }
    function Substation($id)
    {
        $substation = Substation::find($id);
        if($substation->now !== null){
            $patient = Patient::where('date', date('Y-m-d'))->where('hn', $substation->now)->first();
        }else{
            $patient = false;
        }

        return view('station.substation')->with(compact('substation','patient'));
    }
    function getTask(Request $request)
    {
        $substation = Substation::find($request->substation_id);
        $tasks = Patienttask::join('patients', 'patients.id','patienttasks.patient_id')
            ->whereDate('patients.date', date('Y-m-d'))
            ->where('patienttasks.code', $substation->station->code)
            ->where('patienttasks.type', $request->type)
            ->whereNotNull('patienttasks.assign')
            ->whereNull('patienttasks.success')
            ->orderBy('patienttasks.assign','asc')
            ->select(
                'patients.hn',
                'patients.name',
                'patienttasks.assign',
                'patienttasks.type',
                'patienttasks.memo1',
                'patienttasks.memo2',
                'patienttasks.memo3',
                'patienttasks.memo4 as reason',
                'patienttasks.memo5',
                )
            ->get();
            $now_time = date_create(date('Y-m-d H:i:s'));
            foreach ($tasks as $task) {
                $pre_time = date_create($task->assign);
                $diff = $now_time->diff($pre_time);
                $task->Time = ($diff->h * 60) + $diff->i;
                $task->assign = substr($task->assign, 11, 5);
            }

        return response()->json(['status' => 'success', 'tasks' => $tasks], 200);
    }
    function allTask(Request $request)
    {
        $substation = Substation::find($request->substation_id);
        $tasks = Patienttask::join('patients', 'patients.id','patienttasks.patient_id')
            ->whereDate('patients.date', date('Y-m-d'))
            ->where('patienttasks.code', $substation->station->code)
            ->whereNull('patienttasks.assign')
            ->orderBy('patienttasks.created_at','asc')
            ->select(
                'patients.hn',
                'patients.name',
                )
            ->get();

        return response()->json(['status' => 'success', 'tasks' => $tasks], 200);
    }
    function callTask(Request $request)
    {
        $substation = Substation::find($request->substation_id);
        if($substation->now !== null){
            $task = Patienttask::whereDate('date', date('Y-m-d'))
                ->where('code', $substation->station->code)
                ->orderBy('assign','asc')
                ->first();
            $task->type = 'wait';
            $task->assign = date('Y-m-d H:i:s');
            $task->call = null;
            $task->memo4 = 'เรียกคิวอื่นโดยไม่ได้กด Hold';
            $task->save();

            $patient = Patient::whereDate('date', date('Y-m-d'))->where('hn', $substation->now)->first();

            $newPatientLog = new Patientlogs;
            $newPatientLog->patient_id = $patient->id;
            $newPatientLog->date = date('Y-m-d');
            $newPatientLog->hn = $patient->hn;
            $newPatientLog->text = 'hold '. $substation->name;
            $newPatientLog->user = Auth::user()->userid;
            $newPatientLog->save();
        }
        $type = ($request->hn == 'undefined') ? false : $request->hn;
        if($type){
            $task = Patienttask::whereDate('date', date('Y-m-d'))
                ->where('code', $substation->station->code)
                ->where('hn', $type)
                ->orderBy('assign','asc')
                ->first();
        }else{
            $task = Patienttask::whereDate('date', date('Y-m-d'))
                ->where('code', $substation->station->code)
                ->orderBy('assign','asc')
                ->first();
        }
        $task->type = 'work';
        $task->call = date('Y-m-d H:i:s');
        $task->save();

        $substation->now = $task->hn;
        $substation->save();

        $patient = Patient::whereDate('date', date('Y-m-d'))->where('hn', $task->hn)->first();

        $newPatientLog = new Patientlogs;
        $newPatientLog->patient_id = $patient->id;
        $newPatientLog->date = date('Y-m-d');
        $newPatientLog->hn = $patient->hn;
        $newPatientLog->text = 'call '. $substation->name;
        $newPatientLog->user = Auth::user()->userid;
        $newPatientLog->save();
        
        response()->json(['status' => 'success'], 200);
    }
    function holdTask(Request $request)
    {
        $substation = Substation::find($request->substation_id);
        
        
    }
    function successTask(Request $request)
    {

    }
    function deleteTask(Request $request)
    {

    }
}
