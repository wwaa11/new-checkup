<?php
namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Patientlogs;
use App\Models\Patienttask;
use App\Models\Station;
use App\Models\Substation;
use App\Models\SubstationDoctor;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ObsController extends Controller
{
    private function sendNotiLine($hn, $vn, $location, $Fullname)
    {
        if (env('APP_ENV') == 'local') {
            return;
        }

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
            Log::channel('line')->error("Error for HN: {$hn}, VN: {$vn}, Location: {$location} - HTTP Status: {$httpcode}, Response: {$response}");
        } else {
            // Log success or non-error responses
            Log::channel('line')->info("Info for HN: {$hn}, VN: {$vn}, Location: {$location} - Response: {$response}");
        }
        curl_close($curl);
    }

    public function auth()
    {
        return view('obs.auth');
    }

    public function index()
    {
        $stations = Station::where("code", 'b12_gny')->first();

        return view('obs.index', compact('stations'));
    }

    public function registeration()
    {
        $stations = Station::where("code", 'b12_gny')->first();

        return view('obs.registeration', compact('stations'));
    }

    public function updateDoctor(Request $request)
    {
        $substationId = $request->input('substation_id');
        $doctorCode   = $request->input('doctor_code');

        try {
            $response = Http::withHeaders(['token' => env('API_TOKEN_STAFF')])
                ->post('http://172.20.1.12/dbstaff/api/getuser', ['userid' => $doctorCode])
                ->json();
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 0,
                'message' => 'Cannot reach user service',
            ], 422);
        }
        if (is_array($response) && ($response['status'] ?? 0) == 1 && isset($response['user'])) {
            $userData = $response['user'];

            $doctor = SubstationDoctor::updateOrCreate([
                'substation_id' => $substationId,
            ], [
                'doctor_code' => $doctorCode,
                'doctor_name' => $userData['name'],
            ]);
        } else {
            return response()->json([
                'status'  => 0,
                'message' => 'Doctor not found',
            ], 422);
        }

        return response()->json(['status' => 1, 'message' => 'Doctor updated successfully']);
    }

    public function removeDoctor(Request $request)
    {
        $substationId = $request->substation_id;
        SubstationDoctor::where('substation_id', $substationId)->delete();

        return response()->json(['status' => 1, 'message' => 'Doctor remove successfully']);
    }

    public function registerPatient(Request $request)
    {
        $substationId = $request->input('substation_id');
        $hn           = $request->input('hn');

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

                return response()->json(['status' => 'unsuccess', 'message' => 'VN not found!'], 404);
            }
        }

        $substation = Substation::find($substationId);
        $station    = $substation->station;

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
            $task->memo5      = $substationId;
        }
        $task->success = null;
        $task->assign  = date('Y-m-d H:i:s');
        $task->type    = 'process';
        $task->save();

        $newPatientLog             = new Patientlogs;
        $newPatientLog->patient_id = $patient->id;
        $newPatientLog->date       = date('Y-m-d');
        $newPatientLog->hn         = $patient->hn;
        $newPatientLog->text       = 'ลงทะเบียนคิวที่ : ' . $station->name . '-' . $substation->name;
        $newPatientLog->user       = Auth::user()->userid;
        $newPatientLog->save();

        return response()->json([
            'status'  => 1,
            'message' => 'Register success : ' . $hn,
        ]);
    }

    public function substation($id)
    {
        $substation = Substation::with('patientNow')->find($id);
        if ($substation->now !== null) {
            $patient = Patient::where('date', date('Y-m-d'))
                ->where('vn', $substation->now)
                ->first();

            if ($patient == null) {
                $substation->now = null;
                $substation->save();
            }
        }

        return view('obs.substation', compact('substation'));
    }

    public function getTask(Request $request)
    {
        $substation = Substation::find($request->substation_id);

        $tasks = Patienttask::whereDate('created_at', date('Y-m-d'))
            ->where('code', $substation->station->code)
            ->where('type', $request->type)
            ->where('memo5', $request->substation_id)
            ->whereNotNull('assign')
            ->whereNull('success')
            ->orderBy('assign', 'asc')
            ->get();
        foreach ($tasks as $task) {
            $task->waitingTime = $task->waitingTime();
            $task->patient     = $task->patient;
        }

        return response()->json(['status' => 'success', 'tasks' => $tasks], 200);
    }

    public function skipPatient(Request $request)
    {
        $substation = Substation::find($request->substation_id);
        $taskId     = $request->id;
        if ($request->id == 'auto') {
            if ($substation->now == null) {

                return response()->json(['status' => 'unsuccess', 'message' => 'ไม่มีผู้ป่วยในคิว'], 404);
            }

            $taskId = Patienttask::whereDate('created_at', date('Y-m-d'))
                ->where('code', $substation->station->code)
                ->where('vn', $substation->now)
                ->first()->id;

            $substation->now = null;
            $substation->save();
        }

        $task            = Patienttask::find($taskId);
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
        $newPatientLog->text       = 'ปรับคิวไปยัง Waiting ที่ : ' . $substation->name;
        $newPatientLog->user       = Auth::user()->userid;
        $newPatientLog->save();

        return response()->json(['status' => 'success', 'message' => 'ปรับคิวไปยัง Waiting สำเร็จ'], 200);
    }

    public function callPatient(Request $request)
    {
        $taskId     = $request->id;
        $substation = Substation::find($request->substation_id);
        if ($substation->now != null) {

            return response()->json(['status' => 'unsuccess', 'message' => 'มีผู้ป่วยอยู่ในคิว'], 404);
        }
        if ($taskId == 'auto') {
            $task = Patienttask::whereDate('created_at', date('Y-m-d'))
                ->where('code', $substation->station->code)
                ->where('type', 'process')
                ->where('memo5', $request->substation_id)
                ->whereNull('success')
                ->orderBy('assign', 'asc')
                ->first();
        } else {
            $task = Patienttask::find($taskId);
        }
        if ($task == null) {

            return response()->json(['status' => 'unsuccess', 'message' => 'ไม่พบการลงทะเบียน'], 404);
        }
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

        return response()->json(['status' => 'success', 'message' => 'เรียกผู้ป่วยมา ' . $substation->name . ' สำเร็จ'], 200);
    }

    public function callAgainPatient(Request $request)
    {
        $substation = Substation::find($request->substation_id);

        $task = Patienttask::whereDate('created_at', date('Y-m-d'))
            ->where('code', $substation->station->code)
            ->where('memo5', $request->substation_id)
            ->where('vn', $substation->now)
            ->first();
        if ($task == null) {

            return response()->json(['status' => 'unsuccess', 'message' => 'ไม่พบการลงทะเบียน'], 404);
        }
        $task->call      = date('Y-m-d H:i:s');
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

        return response()->json(['status' => 'success', 'message' => 'เรียกผู้ป่วยมา ' . $substation->name . ' สำเร็จ'], 200);
    }

    public function successPatient(Request $request)
    {
        $substation = Substation::find($request->substation_id);

        $task = Patienttask::whereDate('created_at', date('Y-m-d'))
            ->where('code', $substation->station->code)
            ->where('memo5', $request->substation_id)
            ->where('vn', $substation->now)
            ->first();
        if ($task == null) {

            return response()->json(['status' => 'unsuccess', 'message' => 'ไม่พบการลงทะเบียน'], 404);
        }
        $task->type    = 'success';
        $task->success = date('Y-m-d H:i:s');
        $task->save();

        $substation->now = null;
        $substation->save();

        $newPatientLog             = new Patientlogs;
        $newPatientLog->patient_id = $task->patient->id;
        $newPatientLog->date       = date('Y-m-d');
        $newPatientLog->hn         = $task->patient->hn;
        $newPatientLog->text       = 'สำเร็จรายการที่ : ' . $substation->name;
        $newPatientLog->user       = Auth::user()->userid;
        $newPatientLog->save();

        return response()->json(['status' => 'success', 'message' => 'สำเร็จ'], 200);
    }

    public function cancelPatient(Request $request)
    {
        $taskId        = $request->id;
        $substation    = Substation::find($request->substation_id);
        $task          = Patienttask::find($taskId);
        $task->type    = 'success';
        $task->success = date('Y-m-d H:i:s');
        $task->save();

        $newPatientLog             = new Patientlogs;
        $newPatientLog->patient_id = $task->patient->id;
        $newPatientLog->date       = date('Y-m-d');
        $newPatientLog->hn         = $task->patient->hn;
        $newPatientLog->text       = 'ลบคิวที่ : ' . $substation->name . ' : ' . $request->reason;
        $newPatientLog->user       = Auth::user()->userid;
        $newPatientLog->save();

        return response()->json(['status' => 'success', 'message' => 'สำเร็จ'], 200);
    }

    // Display
    public function display()
    {
        $station = Station::where('code', 'b12_gny')->first();

        return view('obs.display', compact('station'));

    }

    public function displayList(Request $request)
    {
        $datas = [
            'room' => [],
            'wait' => [],
        ];
        $station = Station::find($request->station_id);
        foreach ($station->substations as $substation) {
            $datas['room'][$substation->id] = [
                'now'     => [
                    'vn'     => '-',
                    'lang'   => null,
                    'call'   => 0,
                    'doctor' => ($substation->doctor) ? $substation->doctor->doctor_name : 'ไม่ระบุแพทย์',
                ],
                'process' => [],
            ];
        }

        $tasks = Patienttask::whereDate('created_at', date('Y-m-d'))
            ->where('code', $station->code)
            ->whereNull('success')
            ->orderBy('memo5', 'asc')
            ->orderBy('assign', 'asc')
            ->get();
        foreach ($tasks as $task) {
            if ($task->type == 'wait') {
                $datas['wait'][] = $task->vn;
            } else {
                if ($task->type === 'work') {
                    $datas['room'][$task->memo5]['now']['vn']   = $task->vn;
                    $datas['room'][$task->memo5]['now']['lang'] = $task->patient->lang;
                    $datas['room'][$task->memo5]['now']['call'] = $task->call_time;
                } else {
                    $datas['room'][$task->memo5]['process'][] = $task->vn;
                }
            }
        }

        return response()->json(['status' => 'success', 'datas' => $datas], 200);
    }

    public function displayUpdateCall(Request $request)
    {
        $task = Patienttask::whereDate('created_at', date('Y-m-d'))
            ->where('code', 'b12_gny')
            ->where('vn', $request->vn)
            ->first();

        $task->call_time = $task->call_time + 1;
        $task->save();

        return response()->json('success', 200);
    }
}
