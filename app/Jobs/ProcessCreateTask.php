<?php
namespace App\Jobs;

use App\Models\Patient;
use App\Models\Patientlogs;
use App\Models\Patienttask;
use DB;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class ProcessCreateTask implements ShouldQueue, ShouldBeUniqueUntilProcessing
{
    use Queueable;

    public function uniqueId(): string
    {
        return 'generate-task';
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
            $allTasks = Patienttask::where('date', date('Y-m-d'))->get();
            foreach ($newDatas as $data) {
                $patient = collect($patients)->where('hn', $data->HN)->first();
                if ($patient == null) {
                    $patient       = new Patient;
                    $patient->date = date('Y-m-d');
                    $patient->hn   = $data->HN;
                    $patient->name = $data->FirstName . ' ' . $data->LastName;
                    $patient->lang = ($data->EnglishResult == 0) ? 'th' : 'en';
                    $patient->vn   = $data->VN;
                    $patient->save();

                    $this->setLog($patient, 'นำเข้าข้อมูลผู้ป่วยจาก NewUI');
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
                    case '02':
                        $checkValid = true;
                        $code       = 'b12_abi';
                        $text       = 'ABI';
                        break;
                    case '04':
                        $checkValid = true;
                        $code       = 'b12_estecho';
                        $text       = 'Est & Echo';
                        break;
                    case '06':
                        $checkValid = true;
                        $code       = 'b12_xray';
                        $text       = 'Xray';
                        break;
                    case '07':
                        $checkValid = true;
                        $code       = 'b12_ultrasound';
                        $text       = 'Ultrasound';
                        break;
                    case '08':
                        $checkValid = true;
                        $code       = 'b12_mammogram';
                        $text       = 'Mammogram';
                        break;
                    case '09':
                        $checkValid = true;
                        $code       = 'b12_bonedensity';
                        $text       = 'Bone Density';
                        break;
                }

                if ($checkValid) {
                    $task = collect($allTasks)->where('hn', $data->HN)->where('code', $code)->first();
                    if ($task == null) {
                        $newTask             = new Patienttask;
                        $newTask->patient_id = $patient->id;
                        $newTask->date       = date('Y-m-d');
                        $newTask->hn         = $data->HN;
                        $newTask->vn         = $data->VN;
                        $newTask->code       = $code;
                        if ($code == 'b12_vitalsign') {
                            $newTask->assign = now();
                        }
                        if ($code == 'b12_lab') {
                            $newTask->memo1 = $data->FacilityRequestNo;
                        }
                        $newTask->save();

                        // Patient Log
                        $this->setLog($patient, 'สร้างรายการ Check UP : ' . $text);
                        if ($code == 'b12_vitalsign') {
                            $this->setLog($patient, 'ลงทะเบียนคิวที่ : วัดความดัน');
                        }
                    }
                }
            }

            ProcessCreateTask::dispatch()->delay(5);
        } else {
            ProcessCreateTask::dispatch()->delay(60 * 30);
        }

    }

    private function setLog($patient, $text)
    {
        $newPatientLog             = new Patientlogs;
        $newPatientLog->patient_id = $patient->id;
        $newPatientLog->date       = date('Y-m-d');
        $newPatientLog->hn         = $patient->hn;
        $newPatientLog->text       = $text;
        $newPatientLog->user       = 'service';
        $newPatientLog->save();
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
