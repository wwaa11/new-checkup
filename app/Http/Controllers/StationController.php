<?php
namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Patientlogs;
use App\Models\Patienttask;
use App\Models\Station;
use App\Models\Substation;
use App\Models\User;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class StationController extends Controller
{
    public function test()
    {

        return response()->json('index new check up page ' . date('Y-m-d H:i:s'), 200);
    }
    public function Auth()
    {

        return view('station.login');
    }
    public function AuthCheck(Request $request)
    {
        $userid   = $request->userid;
        $password = $request->password;
        $response = Http::withHeaders([
            'token' => env('API_TOKEN_STAFF'),
        ])->post('http://172.20.1.12/dbstaff/api/auth', [
            'userid'   => $userid,
            'password' => $password,
        ])->object();
        if ($response->status == 1) {
            session(['userid' => $response->user->userid, 'name' => $response->user->name]);

            $user = User::firstOrCreate([
                'userid' => $response->user->userid,
                'name'   => $response->user->name,
            ]);

            if (Auth::loginUsingId($user->id)) {

                return response()->json(['status' => 1, 'text' => 'Authentication Success!'], 200);
            } else {

                return response()->json(['status' => 0, 'text' => 'Authentication Success , User not found!'], 200);
            }
        }

        return response()->json(['status' => 0, 'text' => 'Authentication Failed!'], 200);

    }

    public function sendNotiLine($hn, $vn, $location, $Fullname)
    {
        if (strpos($Fullname, ' ') !== false) {
            $name  = explode(' ', $Fullname);
            $first = $name[0];
            $last  = $name[1];
        } else {
            $first = $Fullname;
            $last  = '';
        }

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $targetUrl = env('LINE_STAGE') . 'api/v1/esp-queue-api/notification';
        Log::channel('line')->info("Attempting to call URL: {$targetUrl}");

        curl_setopt_array($curl, [
            CURLOPT_URL            => env('LINE_STAGE') . 'api/v1/esp-queue-api/notification',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_POSTFIELDS     => '{
                "hn_no": "' . $hn . '",
                "vh_no": "' . $vn . '",
                "queue_status": "start",
                "notification_details": [
                    {
                        "lang_type":"th",
                        "first_name": "' . $first . '",
                        "last_name": "' . $last . '",
                        "station_name": "' . $location . '",
                        "clinic":"ศูนย์ตรวจสุขภาพ อาคาร B ชั้น 12"
                    },
                    {
                        "lang_type":"en",
                        "first_name": "' . $first . '",
                        "last_name": "' . $last . '",
                        "station_name": "' . $location . '",
                        "clinic": "Check UP Center building B floor 12"
                    }
                ]
            }',
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'x-token: ' . env('LINE_STAGE_Key') . '',
            ],
        ]);
        $response = curl_exec($curl);
        // Get HTTP status code
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        // Check for cURL errors
        if (curl_errno($curl)) {
            $error_msg = curl_error($curl);
            Log::channel('line')->error("cURL Error for HN: {$hn}, VN: {$vn}, Location: {$location} - Error: {$error_msg}");
        } elseif ($httpcode >= 400) { // Check for HTTP error codes (4xx or 5xx)
            Log::channel('line')->error("API Error for HN: {$hn}, VN: {$vn}, Location: {$location} - HTTP Status: {$httpcode}, Response: {$response}");
        } else {
            // Log success or non-error responses
            Log::channel('line')->info("Success/Info for HN: {$hn}, VN: {$vn}, Location: {$location} - Response: {$response}");
        }
        curl_close($curl);
    }
    public function StationIndex()
    {
        $stations    = [];
        $substations = Substation::all();
        foreach ($substations as $subst) {
            if ($subst->station->code == 'b12_vitalsign' || $subst->station->code == 'b12_lab') {
                $stations[$subst->station->name][] = [
                    'station_id' => $subst->station->id,
                    'id'         => $subst->id,
                    'name'       => $subst->name,
                ];
            }
        }

        return view('station.index', ['stations' => $stations]);
    }
    public function Substation($id)
    {
        $substation = Substation::find($id);
        if ($substation->now !== null) {
            $patient = Patient::where('date', date('Y-m-d'))
                ->where('vn', $substation->now)
                ->first();
            if ($patient == null) {
                $substation->now = null;
                $substation->save()
                ;
                $patient = (object) [
                    'enabled' => 0,
                ];
            } else {
                $patient->enabled = 1;
            }
        } else {
            $patient = (object) [
                'enabled' => 0,
            ];
        }

        return view('station.substation')->with(compact('substation', 'patient'));
    }
    public function Register($id)
    {
        $station = Station::find($id);

        return view('station.register')->with(compact('station'));
    }
    public function registerTask(Request $request)
    {
        $hn      = $request->hn;
        $station = Station::find($request->station_id);

        $patient = Patient::whereDate('date', date('Y-m-d'))
            ->where(function ($query) use ($hn) {
                $query->where('hn', $hn)
                    ->orwhere('vn', $hn);
            })
            ->first();

        if ($patient == null) {
            $ssbVN = DB::connection('SSB')
                ->table('HNOPD_MASTER')
                ->whereDate('VisitDate', date('Y-m-d'))
                ->where(function ($query) use ($hn) {
                    $query->where('HN', $hn)
                        ->orwhere('VN', $hn);
                })
                ->select('VisitDate', 'VN', 'HN', )
                ->first();

            if ($ssbVN !== null) {
                $ssbInfo = DB::connection('SSB')
                    ->table('HNPAT_INFO')
                    ->join('HNPAT_NAME', 'HNPAT_INFO.HN', 'HNPAT_NAME.HN')
                    ->where('HNPAT_INFO.HN', $ssbVN->HN)
                    ->orderBy('HNPAT_NAME.SuffixSmall', 'asc')
                    ->first();

                $patient            = new Patient;
                $patient->date      = date('Y-m-d');
                $patient->hn        = $ssbInfo->HN;
                $ssbInfo->FirstName = mb_substr($ssbInfo->FirstName, 1);
                if (str_contains($ssbInfo->FirstName, '\\')) {
                    $firstName          = explode("\\", $ssbInfo->FirstName);
                    $ssbInfo->FirstName = $firstName[1] . $firstName[0];
                }
                $patient->name = $ssbInfo->FirstName . ' ' . mb_substr($ssbInfo->LastName, 1);
                $patient->lang = ($ssbInfo->NationalityCode == 'THA') ? 'th' : 'en';
                $patient->vn   = $ssbVN->VN;
                $patient->save();

                $newPatientLog             = new Patientlogs;
                $newPatientLog->patient_id = $patient->id;
                $newPatientLog->date       = date('Y-m-d');
                $newPatientLog->hn         = $ssbInfo->HN;
                $newPatientLog->text       = 'ลงทะเบียนข้อมูลผู้ป่วยใหม่ นอกเหนือจาก NewUI';
                $newPatientLog->user       = Auth::user()->userid;
                $newPatientLog->save();
            } else {
                return response()->json(['status' => 'unsuccess', 'text' => 'VN not found!'], 200);
            }
        }

        $task = Patienttask::whereDate('date', date('Y-m-d'))
            ->where('code', $station->code)
            ->where('hn', $patient->hn)
            ->first();
        if ($task == null) {
            $task             = new Patienttask;
            $task->patient_id = $patient->id;
            $task->date       = date('Y-m-d');
            $task->hn         = $patient->hn;
            $task->vn         = $patient->vn;
            $task->code       = $station->code;
        }
        $task->success = null;
        $task->assign  = date('Y-m-d H:i:s');
        $task->type    = 'process';
        $task->save();

        $newPatientLog             = new Patientlogs;
        $newPatientLog->patient_id = $patient->id;
        $newPatientLog->date       = date('Y-m-d');
        $newPatientLog->hn         = $patient->hn;
        $newPatientLog->text       = 'ลงทะเบียนคิวที่ : ' . $station->name;
        $newPatientLog->user       = Auth::user()->userid;
        $newPatientLog->save();

        return response()->json(['status' => 'success', 'patient' => $patient], 200);
    }
    public function getTask(Request $request)
    {
        $substation = Substation::find($request->substation_id);
        $tasks      = Patienttask::join('patients', 'patients.id', 'patienttasks.patient_id')
            ->whereDate('patients.date', date('Y-m-d'))
            ->where('patienttasks.code', $substation->station->code)
            ->where('patienttasks.type', $request->type)
            ->whereNotNull('patienttasks.assign')
            ->whereNull('patienttasks.success')
            ->orderBy('patienttasks.assign', 'asc')
            ->select(
                'patients.name',
                'patients.hn',
                'patients.vn',
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
            $pre_time         = date_create($task->assign);
            $diff             = $now_time->diff($pre_time);
            $task->Time       = ($diff->h * 60) + $diff->i;
            $task->assignTime = date('H:i', strtotime($task->assign));
        }

        return response()->json(['status' => 'success', 'tasks' => $tasks], 200);
    }
    public function getSSP(Request $request)
    {
        $ALLgetssp = DB::connection('SSB')
            ->table("HNLABREQ_HEADER")
            ->join("HNLABREQ_MEMO", "HNLABREQ_HEADER.RequestNo", "HNLABREQ_MEMO.RequestNo")
            ->whereDate('EntryDateTime', date('Y-m-d'))
            ->select('HNLABREQ_HEADER.HN', 'HNLABREQ_MEMO.RemarksMemo')
            ->get();

        $changeTask = Patienttask::whereDate('date', date('Y-m-d'))
            ->where('code', 'b12_lab')
            ->whereNull('memo5')
            ->get();

        foreach ($changeTask as $task) {
            $getssp = collect($ALLgetssp)->where('HN', $task->hn)->where('RemarksMemo', 'like', "ssp");
            if (count($getssp) > 0) {
                $newPatientLog             = new Patientlogs;
                $newPatientLog->patient_id = $task->patient_id;
                $newPatientLog->date       = date('Y-m-d');
                $newPatientLog->hn         = $task->hn;
                $newPatientLog->text       = 'Set SSP';
                $newPatientLog->user       = 'service';
                $newPatientLog->save();

                $task->memo5 = 1;
                $task->save();
            }
        }

        $tasks = Patienttask::join('patients', 'patients.id', 'patienttasks.patient_id')
            ->whereDate('patients.date', date('Y-m-d'))
            ->where('patienttasks.code', 'b12_lab')
            ->whereIn('memo5', [1, 2])
            ->orderBy('patienttasks.memo5', 'asc')
            ->orderBy('patienttasks.vn', 'asc')
            ->select(
                'patients.hn',
                'patients.vn',
                'patients.name',
                'patienttasks.memo5',
            )
            ->get();

        return response()->json(['status' => 'success', 'tasks' => $tasks], 200);
    }
    public function changeSSPTask(Request $request)
    {
        $changeTask = Patienttask::whereDate('date', date('Y-m-d'))
            ->where('code', 'b12_lab')
            ->where('vn', $request->vn)
            ->first();

        $changeTask->memo5 = 2;
        $changeTask->save();

        $newPatientLog             = new Patientlogs;
        $newPatientLog->patient_id = $changeTask->patient_id;
        $newPatientLog->date       = date('Y-m-d');
        $newPatientLog->hn         = $changeTask->hn;
        $newPatientLog->text       = 'SSP Change';
        $newPatientLog->user       = Auth::user()->userid;
        $newPatientLog->save();

        return response()->json(['status' => 'success', 'tasks' => $changeTask], 200);

    }
    public function allTask(Request $request)
    {
        $substation = Substation::find($request->substation_id);
        $tasks      = Patienttask::join('patients', 'patients.id', 'patienttasks.patient_id')
            ->whereDate('patients.date', date('Y-m-d'))
            ->where('patienttasks.code', $substation->station->code)
            ->whereNull('patienttasks.assign')
            ->orderBy('patienttasks.created_at', 'asc')
            ->select(
                'patients.hn',
                'patients.vn',
                'patients.name',
            )
            ->get();

        return response()->json(['status' => 'success', 'tasks' => $tasks], 200);
    }
    public function callTask(Request $request)
    {
        $substation = Substation::find($request->substation_id);
        if ($substation->now !== null) {
            $now_task = Patienttask::whereDate('date', date('Y-m-d'))
                ->where('vn', $substation->now)
                ->where('code', $substation->station->code)
                ->first();
            if ($now_task->type == 'success') {
                $newPatientLog             = new Patientlogs;
                $newPatientLog->patient_id = $now_task->patient->id;
                $newPatientLog->date       = date('Y-m-d');
                $newPatientLog->hn         = $now_task->patient->hn;
                $newPatientLog->text       = 'ลบคิวออกจาก Call : ' . $substation->name;
                $newPatientLog->user       = Auth::user()->userid;
                $newPatientLog->save();
            } else {
                $now_task->type   = 'wait';
                $now_task->assign = date('Y-m-d H:i:s');
                $now_task->call   = null;
                $now_task->memo4  = 'เรียกคิวอื่นโดยไม่ได้กด Hold';
                $now_task->save();

                $newPatientLog             = new Patientlogs;
                $newPatientLog->patient_id = $now_task->patient->id;
                $newPatientLog->date       = date('Y-m-d');
                $newPatientLog->hn         = $now_task->patient->hn;
                $newPatientLog->text       = 'ปรับคิวไปยัง Waiting ที่ : ' . $substation->name;
                $newPatientLog->user       = Auth::user()->userid;
                $newPatientLog->save();
            }
        }
        $type = ($request->vn == 'undefined') ? false : $request->vn;
        if ($type) {
            $task = Patienttask::whereDate('date', date('Y-m-d'))
                ->where('vn', $type)
                ->where('code', $substation->station->code)
                ->whereNotNull('assign')
                ->orderBy('assign', 'asc')
                ->first();
        } else {
            $task = Patienttask::whereDate('date', date('Y-m-d'))
                ->where('code', $substation->station->code)
                ->where('type', 'process')
                ->whereNotNull('assign')
                ->orderBy('assign', 'asc')
                ->first();
        }
        if ($task !== null) {
            $task->type      = 'work';
            $task->call      = date('Y-m-d H:i:s');
            $task->call_time = 1;
            $task->save();

            $substation->now = $task->vn;
            $substation->save();

            $newPatientLog             = new Patientlogs;
            $newPatientLog->patient_id = $task->patient->id;
            $newPatientLog->date       = date('Y-m-d');
            $newPatientLog->hn         = $task->patient->hn;
            $newPatientLog->text       = 'เรียกคิวที่ : ' . $substation->name;
            $newPatientLog->user       = Auth::user()->userid;
            $newPatientLog->save();

            $this->sendNotiLine($task->patient->hn, $task->vn, $substation->name, $task->patient->name);
        }

        return response()->json(['status' => 'success'], 200);
    }
    public function callTaskAgain(Request $request)
    {
        $substation = Substation::find($request->substation_id);
        $task       = Patienttask::whereDate('date', date('Y-m-d'))
            ->where('vn', $request->vn)
            ->where('code', $substation->station->code)
            ->whereNotNull('assign')
            ->orderBy('assign', 'asc')
            ->first();

        $task->call_time = 1;
        $task->save();

        $newPatientLog             = new Patientlogs;
        $newPatientLog->patient_id = $task->patient->id;
        $newPatientLog->date       = date('Y-m-d');
        $newPatientLog->hn         = $task->patient->hn;
        $newPatientLog->text       = 'เรียกคิวที่ : ' . $substation->name;
        $newPatientLog->user       = Auth::user()->userid;
        $newPatientLog->save();

        $this->sendNotiLine($task->patient->hn, $task->vn, $substation->name, $task->patient->name);

        return response()->json(['status' => 'success'], 200);
    }
    public function holdTask(Request $request)
    {
        $substation = Substation::find($request->substation_id);
        if ($substation->now == $request->vn) {
            $substation->now = null;
            $substation->save();
        }
        $task = Patienttask::whereDate('date', date('Y-m-d'))
            ->where('vn', $request->vn)
            ->where('code', $substation->station->code)
            ->first();

        $task->type      = 'wait';
        $task->assign    = date('Y-m-d H:i:s');
        $task->call      = null;
        $task->call_time = 0;
        $task->memo4     = $request->reason;
        $task->save();

        $newPatientLog             = new Patientlogs;
        $newPatientLog->patient_id = $task->patient->id;
        $newPatientLog->date       = date('Y-m-d');
        $newPatientLog->hn         = $task->patient->hn;
        $newPatientLog->text       = 'ปรับคิวไปยัง Waiting ที่ : ' . $substation->station->name;
        $newPatientLog->user       = Auth::user()->userid;
        $newPatientLog->save();

        return response()->json(['status' => 'success'], 200);
    }
    public function successTask(Request $request)
    {
        $substation      = Substation::find($request->substation_id);
        $substation->now = null;
        $substation->save();

        $task = Patienttask::whereDate('date', date('Y-m-d'))
            ->where('vn', $request->vn)
            ->where('code', $substation->station->code)
            ->first();

        $task->type    = 'success';
        $task->success = date('Y-m-d H:i:s');
        $task->save();

        $newPatientLog             = new Patientlogs;
        $newPatientLog->patient_id = $task->patient->id;
        $newPatientLog->date       = date('Y-m-d');
        $newPatientLog->hn         = $task->patient->hn;
        $newPatientLog->text       = 'สำเร็จรายการที่ : ' . $substation->name;
        $newPatientLog->user       = Auth::user()->userid;
        $newPatientLog->save();

        if ($substation->station->code == 'b12_vitalsign') {
            $task = Patienttask::whereDate('date', date('Y-m-d'))
                ->where('vn', $request->vn)
                ->where('code', 'b12_lab')
                ->first();

            if ($task !== null) {
                $task->assign = date('Y-m-d H:i:s');
                $task->save();

                $newPatientLog             = new Patientlogs;
                $newPatientLog->patient_id = $task->patient->id;
                $newPatientLog->date       = date('Y-m-d');
                $newPatientLog->hn         = $task->patient->hn;
                $newPatientLog->text       = 'ลงทะเบียนคิวที่ : ห้องเจาะเลือด';
                $newPatientLog->user       = Auth::user()->userid;
                $newPatientLog->save();
            }
        }

        return response()->json(['status' => 'success'], 200);
    }
    public function deleteTask(Request $request)
    {
        $substation = Substation::find($request->substation_id);
        $task       = Patienttask::whereDate('date', date('Y-m-d'))
            ->where('vn', $request->vn)
            ->where('code', $substation->station->code)
            ->first();

        $task->type    = 'success';
        $task->success = date('Y-m-d H:i:s');
        $task->save();

        $newPatientLog             = new Patientlogs;
        $newPatientLog->patient_id = $task->patient->id;
        $newPatientLog->date       = date('Y-m-d');
        $newPatientLog->hn         = $task->patient->hn;
        $newPatientLog->text       = 'ลบคิวที่ : ' . $substation->name;
        $newPatientLog->user       = Auth::user()->userid;
        $newPatientLog->save();

        if ($substation->station->code == 'b12_vitalsign') {
            $task = Patienttask::whereDate('date', date('Y-m-d'))
                ->where('vn', $request->vn)
                ->where('code', 'b12_lab')
                ->first();

            if ($task !== null) {
                $task->assign = date('Y-m-d H:i:s');
                $task->save();

                $newPatientLog             = new Patientlogs;
                $newPatientLog->patient_id = $task->patient->id;
                $newPatientLog->date       = date('Y-m-d');
                $newPatientLog->hn         = $task->patient->hn;
                $newPatientLog->text       = 'ลงทะเบียนคิวที่ : ' . $substation->name;
                $newPatientLog->user       = Auth::user()->userid;
                $newPatientLog->save();
            }
        }

        return response()->json(['status' => 'success'], 200);
    }
    public function checksuccessTask(Request $request)
    {
        $code    = $request->code;
        $station = Station::where('code', $code)->first();
        $task    = Patienttask::whereDate('date', date('Y-m-d'))
            ->where('vn', $request->vn)
            ->where('code', $code)
            ->first();
        $isSuccess = false;

        if ($request->code == 'b12_vitalsign') {
            $getVS = DB::connection('SSB')
                ->table("HNOPD_VITALSIGN")
                ->whereDate('VisitDate', date('Y-m-d'))
                ->where('VN', $request->vn)
                ->first();

            if ($getVS !== null) {
                $isSuccess = true;
            }
        } else if ($request->code == 'b12_lab') {
            $blood = DB::connection('SSB')
                ->table('HNLABREQ_HEADER')
                ->where('RequestNo', $task->memo1)
                ->first();
            if ($blood !== null && $blood->SpecimenReceiveDateTime !== null) {
                $isSuccess = true;
            }
        }

        if ($isSuccess) {
            $substation      = Substation::find($request->substation_id);
            $substation->now = null;
            $substation->save();

            $task->type    = 'success';
            $task->success = date('Y-m-d H:i:s');
            $task->save();

            $newPatientLog             = new Patientlogs;
            $newPatientLog->patient_id = $task->patient->id;
            $newPatientLog->date       = date('Y-m-d');
            $newPatientLog->hn         = $task->patient->hn;
            $newPatientLog->text       = 'สำเร็จรายการที่ : ' . $station->name;
            $newPatientLog->user       = Auth::user()->userid;
            $newPatientLog->save();

            if ($substation->station->code == 'b12_vitalsign') {
                $task = Patienttask::whereDate('date', date('Y-m-d'))
                    ->where('vn', $request->vn)
                    ->where('code', 'b12_lab')
                    ->first();

                if ($task !== null) {
                    $task->assign = date('Y-m-d H:i:s');
                    $task->save();

                    $newPatientLog             = new Patientlogs;
                    $newPatientLog->patient_id = $task->patient->id;
                    $newPatientLog->date       = date('Y-m-d');
                    $newPatientLog->hn         = $task->patient->hn;
                    $newPatientLog->text       = 'ลงทะเบียนคิวที่ : Lab';
                    $newPatientLog->user       = Auth::user()->userid;
                    $newPatientLog->save();
                }
            }

            return response()->json(['status' => 'success'], 200);
        }

        return response()->json(['status' => 'unsuccess'], 200);
    }
    public function history(Request $request)
    {
        $date = ($request->date == 'today') ? date('Y-m-d') : $request->date;

        if ($request->input !== 'null') {
            $patient = Patient::whereDate('date', $date)
                ->where(function ($query) use ($request) {
                    $query->where('hn', $request->input)
                        ->orwhere('vn', $request->input);
                })
                ->first();
        } else {
            $patient = null;
        }

        return view('station.history')->with(compact('patient'));
    }
    public function displayPage($station)
    {
        switch ($station) {
            case 'vitalsign':
                $code = ['b12_vitalsign'];
                break;
            case 'lab':
                $code = ['b12_lab'];
                break;
            default:
                $code = [];
                break;
        }
        $stations  = Station::whereIn('code', $code)->get();
        $stationid = [];
        foreach ($stations as $key => $value) {
            $stationid[] = $value->id;
        }
        $stationid = json_encode($stationid);

        return view('station.display')->with(compact('stations', 'stationid'));
    }
    public function displayList(Request $request)
    {
        $station    = json_decode($request->station);
        $stations   = Station::whereIn('id', $station)->get();
        $substation = [];
        $code       = [];
        foreach ($stations as $station) {
            $code[] = $station->code;
            foreach ($station->substations as $sub) {
                if ($sub->now !== null) {
                    $patient = Patient::whereDate('date', date('Y-m-d'))
                        ->where('vn', $sub->now)
                        ->first();
                    if ($patient !== null) {
                        $task = Patienttask::whereDate('date', date('Y-m-d'))
                            ->where('vn', $sub->now)
                            ->where('code', $station->code)
                            ->first();

                        $substation[] = [
                            'id'   => $station->id . '_' . $sub->id,
                            'now'  => $sub->now,
                            'lang' => $patient->lang,
                            'call' => $task->call_time,
                        ];
                    }
                } else {
                    $substation[] = [
                        'id'   => $station->id . '_' . $sub->id,
                        'now'  => null,
                        'lang' => null,
                        'call' => null,
                    ];
                }
            }
        }
        $tasks = Patienttask::whereDate('date', date('Y-m-d'))
            ->where(function ($query) use ($code) {
                $query->whereIn('code', $code);
            })
            ->whereNotNull('assign')
            ->whereIn('type', ['process', 'wait'])
            ->orderby('assign', 'asc')
            ->get();
        $data = [
            'process' => [],
            'wait'    => [],
        ];
        foreach ($tasks as $value) {
            $data[$value->type][] = $value->vn;
        }

        return response()->json(['status' => 'success', 'substation' => $substation, 'data' => $data], 200);
    }
    public function updateCall(Request $request)
    {
        $task = Patienttask::whereDate('date', date('Y-m-d'))
            ->where('code', $request->station)
            ->where('vn', $request->vn)
            ->first();

        $task->call_time = $task->call_time + 1;
        $task->save();

        return response()->json('success', 200);
    }
    public function labCount()
    {
        $tasks = Patienttask::join('patients', 'patients.id', 'patienttasks.patient_id')
            ->whereDate('patients.date', date('Y-m-d'))
            ->where('patienttasks.code', 'b12_lab')
            ->whereNotNull('patienttasks.success')
            ->orderBy('patienttasks.success', 'asc')
            ->select(
                'patients.hn',
                'patients.vn',
                'patients.name',
            )
            ->get();

        return view('station.labcount')->with(compact('tasks'));
    }
}
