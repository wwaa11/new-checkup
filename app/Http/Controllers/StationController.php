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
            if($subst->station->code == 'b12_vitalsign' || $subst->station->code == 'b12_lab'){
                $stations[$subst->station->name][] = [
                    'station_id' => $subst->station->id,
                    'id'=> $subst->id,
                    'name'=> $subst->name,
                ];
            }
        }
        
        return view('station.index', ['stations' => $stations]);
    }
    function Substation($id)
    {
        $substation = Substation::find($id);
        if($substation->now !== null){
            $patient = Patient::where('date', date('Y-m-d'))
                ->where('hn', $substation->now)
                ->first();
            if($patient == null){
                $substation->now = null;
                $substation->save()
                ;
                $patient = (object)[
                    'enabled' => 0
                ];
            }else{
                $patient->enabled = 1;
            }
        }else{
            $patient = (object)[
                'enabled' => 0
            ];
        }

        return view('station.substation')->with(compact('substation','patient'));
    }
    function Register($id)
    {
        $station = Station::find($id);

        return view('station.register')->with(compact('station'));
    }
    function registerTask(Request $request)
    {
        $hn = $request->hn;
        $station = Station::find($request->station_id);

        $patient = Patient::whereDate('date', date('Y-m-d'))
            ->where('hn', $hn)
            ->first();

        if($patient == null){
            $ssbVN = DB::connection('SSB')
                ->table('HNOPD_MASTER')
                ->whereDate('VisitDate', date('Y-m-d'))
                ->where('HN', $hn)
                ->select('VisitDate', 'VN', 'HN', )
                ->first();
            if($ssbVN !== null){
                $ssbInfo = DB::connection('SSB')
                    ->table('HNPAT_INFO')
                    ->join('HNPAT_NAME', 'HNPAT_INFO.HN','HNPAT_NAME.HN')
                    ->where('HNPAT_INFO.HN', $ssbVN->HN)
                    ->orderBy('HNPAT_NAME.SuffixSmall', 'asc')
                    ->first();

                $patient = new Patient;
                $patient->date = date('Y-m-d');
                $patient->hn = $ssbInfo->HN;
                $patient->name = mb_substr($ssbInfo->FirstName,1).' '.mb_substr($ssbInfo->LastName,1);
                $patient->lang = ($ssbInfo->NationalityCode == 'THA') ? 'th' : 'en';
                $patient->vn = $ssbVN->VN;
                $patient->save();

                $newPatientLog = new Patientlogs;
                $newPatientLog->patient_id = $patient->id;
                $newPatientLog->date = date('Y-m-d');
                $newPatientLog->hn = $ssbInfo->HN;
                $newPatientLog->text = 'new Patient form Register';
                $newPatientLog->user = Auth::user()->userid;
                $newPatientLog->save();
            }else{
                return response()->json(['status'=>'unsuccess', 'text' => 'VN not found!'],200);
            }
        }

        $task = Patienttask::whereDate('date', date('Y-m-d'))
            ->where('code', $station->code)
            ->where('hn', $patient->hn)
            ->first();
        if($task == null){
            $task = new Patienttask;
            $task->patient_id = $patient->id;
            $task->date = date('Y-m-d');
            $task->hn = $patient->hn;
            $task->code = $station->code;
        }
        $task->type = 'process';
        $task->assign = date('Y-m-d H:i:s');
        $task->save();

        $newPatientLog = new Patientlogs;
        $newPatientLog->patient_id = $patient->id;
        $newPatientLog->date = date('Y-m-d');
        $newPatientLog->hn = $patient->hn;
        $newPatientLog->text = 'register manual '.$station->name;
        $newPatientLog->user = Auth::user()->userid;
        $newPatientLog->save();

        return response()->json(['status'=>'success'],200);
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
            $now_task = Patienttask::whereDate('date', date('Y-m-d'))
                ->where('hn', $substation->now)
                ->where('code', $substation->station->code)
                ->first();

            $now_task->type = 'wait';
            $now_task->assign = date('Y-m-d H:i:s');
            $now_task->call = null;
            $now_task->memo4 = 'เรียกคิวอื่นโดยไม่ได้กด Hold';
            $now_task->save();

            $newPatientLog = new Patientlogs;
            $newPatientLog->patient_id = $now_task->patient->id;
            $newPatientLog->date = date('Y-m-d');
            $newPatientLog->hn = $now_task->patient->hn;
            $newPatientLog->text = 'hold '. $substation->name;
            $newPatientLog->user = Auth::user()->userid;
            $newPatientLog->save();
        }
        $type = ($request->hn == 'undefined') ? false : $request->hn;
        if($type){
            $task = Patienttask::whereDate('date', date('Y-m-d'))
                ->where('code', $substation->station->code)
                ->where('hn', $type)
                ->whereNotNull('assign')
                ->orderBy('assign','asc')
                ->first();
        }else{
            $task = Patienttask::whereDate('date', date('Y-m-d'))
                ->where('code', $substation->station->code)
                ->where('type' , 'process')
                ->whereNotNull('assign')
                ->orderBy('assign','asc')
                ->first();
        }
        if($task !== null){
            $task->type = 'work';
            $task->call = date('Y-m-d H:i:s');
            $task->save();

            $substation->now = $task->hn;
            $substation->save();

            $newPatientLog = new Patientlogs;
            $newPatientLog->patient_id = $task->patient->id;
            $newPatientLog->date = date('Y-m-d');
            $newPatientLog->hn = $task->patient->hn;
            $newPatientLog->text = 'call '. $substation->name;
            $newPatientLog->user = Auth::user()->userid;
            $newPatientLog->save();
        }
        
        return response()->json(['status' => 'success'], 200);
    }
    function holdTask(Request $request)
    {
        $substation = Substation::find($request->substation_id);
        if($substation->now == $request->hn){
            $substation->now = null;
            $substation->save();
        }
        $task = Patienttask::whereDate('date', date('Y-m-d'))
            ->where('hn', $request->hn)
            ->where('code', $substation->station->code)
            ->first();

        $task->type = 'wait';
        $task->assign = date('Y-m-d H:i:s');
        $task->call = null;
        $task->memo4 = $request->reason;
        $task->save();

        $newPatientLog = new Patientlogs;
        $newPatientLog->patient_id = $task->patient->id;
        $newPatientLog->date = date('Y-m-d');
        $newPatientLog->hn = $task->patient->hn;
        $newPatientLog->text = 'hold '. $substation->name;
        $newPatientLog->user = Auth::user()->userid;
        $newPatientLog->save();

        return response()->json(['status' => 'success'], 200);
    }
    function successTask(Request $request)
    {
        $substation = Substation::find($request->substation_id);
        $substation->now = null;
        $substation->save();

        $task = Patienttask::whereDate('date', date('Y-m-d'))
            ->where('hn', $request->hn)
            ->where('code', $substation->station->code)
            ->first();

        $task->type = 'success';
        $task->success = date('Y-m-d H:i:s');
        $task->save();

        $newPatientLog = new Patientlogs;
        $newPatientLog->patient_id = $task->patient->id;
        $newPatientLog->date = date('Y-m-d');
        $newPatientLog->hn = $task->patient->hn;
        $newPatientLog->text = 'success '. $substation->name;
        $newPatientLog->user = Auth::user()->userid;
        $newPatientLog->save();

        if($substation->station->code == 'b12_vitalsign'){
            $task = Patienttask::whereDate('date', date('Y-m-d'))
                ->where('hn', $request->hn)
                ->where('code', 'b12_lab')
                ->first();

            if($task !== null){
                $task->assign = date('Y-m-d H:i:s');
                $task->save();

                $newPatientLog = new Patientlogs;
                $newPatientLog->patient_id = $task->patient->id;
                $newPatientLog->date = date('Y-m-d');
                $newPatientLog->hn = $task->patient->hn;
                $newPatientLog->text = 'assign '. $substation->name;
                $newPatientLog->user = Auth::user()->userid;
                $newPatientLog->save();
            }
        }

        return response()->json(['status' => 'success'], 200);
    }
    function deleteTask(Request $request)
    {
        $substation = Substation::find($request->substation_id);
        $task = Patienttask::whereDate('date', date('Y-m-d'))
            ->where('hn', $request->hn)
            ->where('code', $substation->station->code)
            ->first();

        $task->type = 'success';
        $task->success = date('Y-m-d H:i:s');
        $task->save();

        $newPatientLog = new Patientlogs;
        $newPatientLog->patient_id = $task->patient->id;
        $newPatientLog->date = date('Y-m-d');
        $newPatientLog->hn = $task->patient->hn;
        $newPatientLog->text = 'delete '. $substation->name;
        $newPatientLog->user = Auth::user()->userid;
        $newPatientLog->save();

        return response()->json(['status' => 'success'], 200);
    }
    function checksuccessTask(Request $request)
    {
        $code = $request->code;
        $isSuccess = false;
        if($request->code == 'b12_vitalsign'){
            $getVS = DB::connection('SSB')
                ->table("HNOPD_VITALSIGN")
                ->whereDate('VisitDate', date('Y-m-d'))
                ->where('VN', $request->vn)
                ->first();

            if($getVS !== null){
                $isSuccess = true;
            }
        }else if($request->code == 'b12_lab'){
            $getLabReq = DB::connection('NewUI')
                ->table("HIS_CHECKUP_STATION_DETAIL")
                ->whereDate('VisitDate', date('Y-m-d'))
                ->where('VN', $request->vn)
                ->where('StationCode', '011')
                ->first();

            $blood = DB::connection('SSB')
                ->table('HNLABREQ_HEADER')
                ->where('RequestNo', $getLabReq->FacilityRequestNo)
                ->first();

            if($blood !== null && $blood->SpecimenReceiveDateTime !== null){
                $isSuccess = true;
            }
        }

        if($isSuccess){
            $substation = Substation::find($request->substation_id);
            $substation->now = null;
            $substation->save();

            $task = Patienttask::whereDate('date', date('Y-m-d'))
                ->where('hn', $request->hn)
                ->where('code', $code)
                ->first();
            $task->type = 'success';
            $task->success = date('Y-m-d H:i:s');
            $task->save();

            $newPatientLog = new Patientlogs;
            $newPatientLog->patient_id = $task->patient->id;
            $newPatientLog->date = date('Y-m-d');
            $newPatientLog->hn = $task->patient->hn;
            $newPatientLog->text = 'success '. $code;
            $newPatientLog->user = Auth::user()->userid;
            $newPatientLog->save();

            if($substation->station->code == 'b12_vitalsign'){
                $task = Patienttask::whereDate('date', date('Y-m-d'))
                    ->where('hn', $request->hn)
                    ->where('code', 'b12_lab')
                    ->first();
    
                if($task !== null){
                    $task->assign = date('Y-m-d H:i:s');
                    $task->save();
    
                    $newPatientLog = new Patientlogs;
                    $newPatientLog->patient_id = $task->patient->id;
                    $newPatientLog->date = date('Y-m-d');
                    $newPatientLog->hn = $task->patient->hn;
                    $newPatientLog->text = 'assign '. $substation->name;
                    $newPatientLog->user = Auth::user()->userid;
                    $newPatientLog->save();
                }
            }

            return response()->json(['status' => 'success'], 200);
        }

        return response()->json(['status' => 'unsuccess'], 200);
    }
}
