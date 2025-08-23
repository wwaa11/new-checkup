<?php
namespace App\Jobs;

use App\Models\Patient;
use App\Models\Patientlogs;
use App\Models\Patienttask;
use DB;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class ProcessCreateTask implements ShouldQueue
{
    use Queueable;
    public $tries   = 50;
    public $backoff = 60;

    public function uniqueId(): string
    {
        return 'created_task_' . now()->timestamp;
    }

    public function __construct()
    {

    }

    public function handle(): void
    {
        $hour = (int) date('H');
        if ($hour >= 5 && $hour <= 16) {

            $newDatas = DB::connection('NewUI')
                ->table('HIS_CHKUP_HEADER')
                ->join('HIS_CHECKUP_STATION_DETAIL', 'HIS_CHKUP_HEADER.RequestNo', '=', 'HIS_CHECKUP_STATION_DETAIL.CheckUpRequestNo')
                ->whereDate('HIS_CHECKUP_STATION_DETAIL.Visitdate', date('Y-m-d'))
                ->where('HIS_CHKUP_HEADER.Clinic', '1800')
                ->where('HIS_CHKUP_HEADER.ComputerLocation', 'LIKE', 'B12%')
                ->whereIn('HIS_CHECKUP_STATION_DETAIL.StationCode', ['01', '011'])
                ->select(
                    'HIS_CHECKUP_STATION_DETAIL.Visitdate',
                    'HIS_CHECKUP_STATION_DETAIL.HN',
                    'HIS_CHECKUP_STATION_DETAIL.VN',
                    'HIS_CHKUP_HEADER.FirstName',
                    'HIS_CHKUP_HEADER.LastName',
                    'HIS_CHKUP_HEADER.EnglishResult',
                    'HIS_CHECKUP_STATION_DETAIL.StationCode',
                    'HIS_CHECKUP_STATION_DETAIL.FacilityRequestNo',
                )
                ->orderBy('HIS_CHECKUP_STATION_DETAIL.Visitdate', 'DESC')
                ->orderBy('HIS_CHECKUP_STATION_DETAIL.VN', 'ASC')
                ->orderBy('HIS_CHECKUP_STATION_DETAIL.StationCode', 'ASC')
                ->get();

            $patients = Patient::where('date', date('Y-m-d'))->get();
            foreach ($newDatas as $data) {
                // Log::channel('debug')->error("loop start data" . $data->HN);
                $patient = collect($patients)->where('hn', $data->HN)->first();
                if ($patient == null) {
                    $patient       = new Patient;
                    $patient->date = date('Y-m-d');
                    $patient->hn   = $data->HN;
                    $patient->name = $data->FirstName . ' ' . $data->LastName;
                    $patient->lang = ($data->EnglishResult == 0) ? 'th' : 'en';
                    $patient->vn   = $data->VN;
                    $patient->save();

                    $newPatientLog             = new Patientlogs;
                    $newPatientLog->patient_id = $patient->id;
                    $newPatientLog->date       = date('Y-m-d');
                    $newPatientLog->hn         = $data->HN;
                    $newPatientLog->text       = 'นำเข้าข้อมูลผู้ป่วยจาก NewUI';
                    $newPatientLog->user       = 'service';
                    $newPatientLog->save();
                }

                $checkValid = false;
                switch ($data->StationCode) {
                    case '01':
                        $checkValid = true;
                        $code       = 'b12_vitalsign';
                        $text       = 'Vital Sign';
                        break;
                    case '011':
                        $checkValid = true;
                        $code       = 'b12_lab';
                        $text       = 'Lab';
                        break;
                }

                if ($checkValid) {

                    $task = Patienttask::where('hn', $data->HN)
                        ->where('date', date('Y-m-d'))
                        ->where('code', $code)
                        ->first();

                    if ($task == null) {
                        $newTask             = new Patienttask;
                        $newTask->patient_id = $patient->id;
                        $newTask->date       = date('Y-m-d');
                        $newTask->hn         = $data->HN;
                        $newTask->vn         = $data->VN;
                        $newTask->code       = $code;
                        if ($code == 'b12_vitalsign') {
                            $newTask->assign = date('Y-m-d H:i:s');
                        }
                        if ($code == 'b12_lab') {
                            $newTask->memo1 = $data->FacilityRequestNo;
                        }
                        $newTask->save();

                        $newPatientLog             = new Patientlogs;
                        $newPatientLog->patient_id = $patient->id;
                        $newPatientLog->date       = date('Y-m-d');
                        $newPatientLog->hn         = $data->HN;
                        $newPatientLog->text       = 'สร้างรายการ Check UP : ' . $text;
                        $newPatientLog->user       = 'service';
                        $newPatientLog->save();

                        if ($code == 'b12_vitalsign') {
                            $newPatientLog             = new Patientlogs;
                            $newPatientLog->patient_id = $patient->id;
                            $newPatientLog->date       = date('Y-m-d');
                            $newPatientLog->hn         = $data->HN;
                            $newPatientLog->text       = 'ลงทะเบียนคิวที่ : วัดความดัน';
                            $newPatientLog->user       = 'service';
                            $newPatientLog->save();
                        }
                    }
                }
            }

            ProcessCreateTask::dispatch()->delay(5);
        } else {
            ProcessCreateTask::dispatch()->delay(60 * 30);
        }

    }

    public function failed(?Throwable $exception): void
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL            => 'https://api.line.me/v2/bot/message/push',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_POSTFIELDS     => '{
            "to": "U3d7ba4f0386437906a68612c1cce5eba",
            "messages":[
                {
                    "type":"text",
                    "text":"Services Error",
                    "quickReply": {
                        "items": [
                        {
                            "type": "action",
                            "action": {
                                "type": "uri",
                                "label": "ServicesPages!!!",
                                "uri": "https://pr9webhub.praram9.com/checkup/serviceStart"
                            }
                        }
                        ]
                    }
                }
            ]
        }',
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . env('LINE_Token') . '', 'Content-Type: application/json',
            ],
        ]);
        $response = curl_exec($curl);
        curl_close($curl);
    }
}
