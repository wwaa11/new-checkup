<?php

namespace App\Http\Controllers;

use App\Models\Number;
use App\Models\Patient;
use App\Models\Patientlogs;
use App\Models\Patienttask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use DB;

class PatientController extends Controller
{
    //
    function verify()
    {

        return view("patient.index");
    }
    function verifySearch(Request $request)
    {
        $findData = Patient::where("date", date('Y-m-d'))
            ->where('pre_vn_finish', true)
            ->where(function($query) use ($request) {
                $query->where('request_input', $request->input)
                    ->orWhere('hn', $request->input);
            })
            ->get();
        if(count($findData) > 0){
            $data = [];
            foreach($findData as $patient){
                $data[] = [
                    'input' => $request->input,
                    'type' => 1,
                    'hn' => $patient->hn,
                    'name' => $patient->name,
                    'app' => $patient->app,
                    'app_time' => $patient->app_time,
                    'number' => $patient->pre_vn,
                    'lang' => $patient->lang
                ];
            }
        }else{
            // Search HIS Data
            $SSB = DB::connection('SSB')
                ->table('HNPAT_INFO')
                ->leftjoin('HNPAT_NAME', 'HNPAT_INFO.HN', '=', 'HNPAT_NAME.HN')
                ->leftjoin('HNPAT_REF', 'HNPAT_INFO.HN', '=', 'HNPAT_REF.HN')
                ->leftjoin('HNPAT_ADDRESS', 'HNPAT_INFO.HN', '=', 'HNPAT_ADDRESS.HN')
                ->whereNull('HNPAT_INFO.FileDeletedDate')
                ->where('HNPAT_ADDRESS.SuffixTiny', 1)
                ->where('HNPAT_NAME.SuffixSmall', 0)
                ->where(function ($query) use ($request) {
                    $query->where('HNPAT_REF.RefNo', $request->input)
                        ->orwhere('HNPAT_INFO.HN', $request->input)
                        ->orwhere('HNPAT_ADDRESS.MobilePhone', $request->input);
                })
                ->select(
                    'HNPAT_INFO.HN',
                    'HNPAT_INFO.BirthDateTime',
                    'HNPAT_NAME.FirstName',
                    'HNPAT_NAME.LastName',
                    'HNPAT_INFO.NationalityCode'
                )
                ->groupBy(
                    'HNPAT_INFO.HN',
                    'HNPAT_INFO.BirthDateTime',
                    'HNPAT_NAME.FirstName',
                    'HNPAT_NAME.LastName',
                    'HNPAT_INFO.NationalityCode'
                )
                ->get();
            if(count($SSB) > 0){
                foreach($SSB as $patient){
                    $findData = Patient::where("date", date('Y-m-d'))->whereNotNull('pre_vn')->where('hn', $patient->HN)->first();
                    if($findData !== null){
                        $data[] = [
                            'input' => $request->input,
                            'type' => 1,
                            'hn' => $findData->hn,
                            'name' => $findData->name,
                            'app' => $findData->app,
                            'app_time' => $findData->app_time,
                            'number' => $findData->pre_vn,
                            'lang' => $findData->lang
                        ];
                    }else{
                        $app = DB::connection('SSB')->table('HNAPPMNT_HEADER')
                                ->whereDate('HNAPPMNT_HEADER.AppointDateTime', date('Y-m-d'))
                                ->where('HNAPPMNT_HEADER.Clinic', '1800')
                                ->where('HNAPPMNT_HEADER.HN', $patient->HN)
                                ->select(
                                    'AppointmentNo',
                                    'AppointDateTime',
                                    'AppmntProcedureCode1',
                                    'AppmntProcedureCode2',
                                    'AppmntProcedureCode3',
                                    'AppmntProcedureCode4',
                                    'AppmntProcedureCode5',
                                    )
                                ->first();
                        if($app == null){
                            $data[] = [
                                'input' => $request->input,
                                'type' => 0,
                                'hn' => $patient->HN,
                                'name' => mb_substr($patient->FirstName, 1).' '.mb_substr($patient->LastName, 1),
                                'lang' => ($patient->NationalityCode == 'THA')? 'th' : 'end',
                                'app' => 'walkin',
                                'app_time' => date('H:i'),
                            ];
                        }else{
                            $followUP = ['A1', 'A2', 'A3', 'A4', 'A7', 'A10', 'AI', 'AB2', 'AB3', 'AG2', 'AG3', 'A31', 'A129'];
                            if( 
                                in_array($app->AppmntProcedureCode1, $followUP) ||
                                in_array($app->AppmntProcedureCode2, $followUP) ||
                                in_array($app->AppmntProcedureCode3, $followUP) ||
                                in_array($app->AppmntProcedureCode4, $followUP) ||
                                in_array($app->AppmntProcedureCode5, $followUP)
                            ){
                                $time = 'U';
                            }else{
                                $time = date('H:i', strtotime($app->AppointDateTime));
                            }
                            $data[] = [
                                'input' => $request->input,
                                'type' => 0,
                                'hn' => $patient->HN,
                                'name' => mb_substr($patient->FirstName, 1).' '.mb_substr($patient->LastName, 1),
                                'lang' => ($patient->NationalityCode == 'THA')? 'th' : 'end',
                                'app' => $app->AppointmentNo,
                                'app_time' => $time,
                            ];
                        }
                    }

                }
            }else{
            // Nodata
                $data = [
                    [
                        'input' => $request->input,
                        'type' => 0,
                        'hn' => $request->input,
                        'name' => 'walkin',
                        'app' => '',
                        'app_time' => date('H:i'),
                        'lang' => 'th'
                    ]
                ];
            }
        }

        return response()->json(['status' => 'success', 'result' => $data], 200);
    }
    function requestNumber(Request $request)
    {
        $checkGenerateNumber = Patient::where('date', date('Y-m-d'))->where('request_input', $request->request_input)->where('pre_vn_finish', true)->first();
        if($checkGenerateNumber !== null){

            return response()->json(['status' => 'success', 'result' => $checkGenerateNumber->pre_vn], 200);
        }

        $checkAttemp = RateLimiter::attempt(
            $request->hn,
            1,
            function() use ($request) {
                if($request->app_time == 'U'){
                    $type = 'U';
                }else{
                    switch (substr($request->app_time, 0,2)) {
                        case '07':
                            $type = 'A';
                            break;
                        case '08':
                            $type = 'B';
                            break;
                        case '09':
                            $type = 'C';
                            break;
                        case '10':
                            $type = 'D';
                            break;
                        case '11':
                            $type = 'E';
                            break;
                        case '12':
                            $type = 'H';
                            break;
                        default:
                            $type = 'M';
                            break;
                    }
                }
                $DataNumber = Number::where('date', date('Y-m-d'))->where('type', $type)->lockForUpdate()->first();
                if($DataNumber == null){
                    $DataNumber = new Number;
                    $DataNumber->date = date('Y-m-d');
                    $DataNumber->type = $type;
                    $DataNumber->save();
        
                    $DataNumber = Number::where('date', date('Y-m-d'))->where('type', $type)->lockForUpdate()->first();
                }
                $value = $DataNumber->number+1;
                $DataNumber->number = $value;
                $outputNumber = $type . str_pad($value, 3, '0', STR_PAD_LEFT);
                // New Patient , Logs
                $newPatient = new Patient;
                $newPatient->date = date('Y-m-d');
                $newPatient->request_input = $request->input;
                $newPatient->name = $request->name;
                $newPatient->lang = $request->lang;
                $newPatient->hn = $request->hn;
                $newPatient->pre_vn = $outputNumber;
                $newPatient->app =  $request->app;
                $newPatient->pre_vn_finish = true;

                $newPatientLog = new Patientlogs;
                $newPatientLog->date = date('Y-m-d');
                $newPatientLog->hn =  $request->hn;
                $newPatientLog->text = 'Request generate ' . $outputNumber . ' for : '. $request->app_time;
                $newPatientLog->save();

                $newPatientTask = new Patienttask;
                $newPatientTask->hn = $request->hn;
                $newPatientTask->assign = date('Y-m-d H:i:s');
                $newPatientTask->memo1 = $type;
                switch ($type) {
                    case 'U':
                        $level = 0;
                        break;
                    case 'A':
                        $level = 1;
                        break;
                    case 'B':
                        $level = 2;
                        break;
                    case 'C':
                        $level = 3;
                        break;
                    case 'D':
                        $level = 4;
                        break;
                    case 'E':
                        $level = 5;
                        break;
                    case 'H':
                        $level = 6;
                        break;
                    case 'M':
                        $level = 99;
                        break;
                    
                }
                $newPatientTask->memo5 = $level;
                $newPatientTask->code = 'b12_register';

                $newPatientLog = new Patientlogs;
                $newPatientLog->date = date('Y-m-d');
                $newPatientLog->hn = $request->hn;
                $newPatientLog->text = 'add Task b12_register';
                $newPatientLog->save();

                $newPatientTask->save();
                $newPatient->save();
                $DataNumber->save();

                return $outputNumber;
            }
        );

        if(!$checkAttemp){
            
            return response()->json('too many request :' , 429);
        }

        return response()->json(['status' => 'success', 'result' => $checkAttemp], 200);
    }
    function checkLocation(Request $request)
    {
        
    }
    function smsRequest($hn)
    {
        $searchPatient = Patient::where('date', date('Y-m-d'))->where('hn', $hn)->where('pre_vn_finish', true)->first();
        if($searchPatient !== null){
            $data = [
                'hn' => $searchPatient->hn,
                'name' => $searchPatient->name,
            ];
        }else{
            $data = [
                'hn' => $hn,
                'name' => 'NoData wait for HRIS',
            ];
        }

        return view("patient.sms")->with(compact('data'));
    }
}
