<?php

namespace App\Jobs;

use App\Models\Patient;
use App\Models\Patientlogs;
use App\Models\Patienttask;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use DB;
use Log;

class ProcessCreateTask implements ShouldQueue
{
    use Queueable;
    public $tries = 5;

    public function backoff(): array
    {
        return [30, 60, 600];
    }

    public function middleware(): array
    {
        return [new WithoutOverlapping(date('Y-m-d'))];
    }

    public function uniqueId(): string
    {
        return 'newUI';
    }

    public function __construct()
    {

    }

    public function handle(): void
    {
        $newDatas = DB::connection('NewUI')
            ->table('HIS_CHKUP_HEADER')
            ->join('HIS_CHECKUP_STATION_DETAIL','HIS_CHKUP_HEADER.RequestNo','=','HIS_CHECKUP_STATION_DETAIL.CheckUpRequestNo')
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
            ->orderBy('HIS_CHECKUP_STATION_DETAIL.VN', 'ASC')
            ->orderBy('HIS_CHECKUP_STATION_DETAIL.StationCode', 'ASC')
            ->get();

        foreach ($newDatas as $data) {
            $patient = Patient::where('date', date('Y-m-d'))->where('hn', $data->HN)->first();
            if($patient == null){
                $patient = new Patient;
                $patient->date = date('Y-m-d');
                $patient->hn = $data->HN;
                $patient->name = $data->FirstName.' '.$data->LastName;
                $patient->lang = ($data->EnglishResult == 0) ? 'th' : 'en';
                $patient->vn = $data->VN;
                $patient->save();

                $newPatientLog = new Patientlogs;
                $newPatientLog->patient_id = $patient->id;
                $newPatientLog->date = date('Y-m-d');
                $newPatientLog->hn = $data->HN;
                $newPatientLog->text = 'นำเข้าข้อมูลผู้ป่วยจาก NewUI';
                $newPatientLog->user = 'service';
                $newPatientLog->save();
            }else{
                $patient = Patient::where('date', date('Y-m-d'))->where('hn', $data->HN)->first();
            }

            $code = false;
            switch ($data->StationCode) {
                case '01':
                    $code = 'b12_vitalsign';
                    $text = 'Vital Sign';
                    break;
                case '011':
                    $code = 'b12_lab';
                    $text = 'Lab';
                    break;
                default:
                    $code = 'skip'; 
                    break;
            }

            if($code !== 'skip'){
                $task = Patienttask::where('hn', $data->HN)
                    ->where('date', date('Y-m-d'))
                    ->where('code',$code)
                    ->first();

                if($task == null){
                    $newTask = new Patienttask;
                    $newTask->patient_id = $patient->id;
                    $newTask->date = date('Y-m-d');
                    $newTask->hn = $data->HN;
                    $newTask->vn = $data->VN;
                    $newTask->code = $code;
                    if($code == 'b12_vitalsign'){
                        $newTask->assign = date('Y-m-d H:i:s');
                    }
                    if($code == 'b12_lab'){
                        $newTask->memo1 = $data->FacilityRequestNo;
                    }
                    $newTask->save();

                    $newPatientLog = new Patientlogs;
                    $newPatientLog->patient_id = $patient->id;
                    $newPatientLog->date = date('Y-m-d');
                    $newPatientLog->hn = $data->HN;
                    $newPatientLog->text = 'สร้างรายการ Check UP : '.$text;
                    $newPatientLog->user = 'service';
                    $newPatientLog->save();

                    if($code == 'b12_vitalsign'){
                        $newPatientLog = new Patientlogs;
                        $newPatientLog->patient_id = $patient->id;
                        $newPatientLog->date = date('Y-m-d');
                        $newPatientLog->hn = $data->HN;
                        $newPatientLog->text = 'ลงทะเบียนคิวที่ : วัดความดัน';
                        $newPatientLog->user = 'service';
                        $newPatientLog->save();
                    }
                }
            }
        }

        ProcessCreateTask::dispatch()->delay(5);
    }
}
