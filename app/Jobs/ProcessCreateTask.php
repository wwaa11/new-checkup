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
            ->get();
        $patients = Patient::where('date', date('Y-m-d'))->get();
        $checkHN = [];
        foreach ($newDatas as $data) {
            try {
                if(!in_array($data->HN, $checkHN)) {
                    $search = collect($patients)->where('hn', $data->HN)->first();
                    if($search == null){
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
                        $newPatientLog->text = 'นำเข้าขูลผู้ป่วยจาก NewUI';
                        $newPatientLog->user = 'service';
                        $newPatientLog->save();
                    }else{
                        $patient = Patient::where('date', date('Y-m-d'))->where('hn', $data->HN)->whereNull('vn')->first();
                        if( $patient !== null ){
                            $patient->vn = $data->VN;
                            $patient->pre_vn_finish = 1;
                            $patient->save();

                            $newPatientLog = new Patientlogs;
                            $newPatientLog->patient_id = $patient->id;
                            $newPatientLog->date = date('Y-m-d');
                            $newPatientLog->hn = $data->HN;
                            $newPatientLog->text = 'เพิ่มข้อมูล VN จาก NewUI';
                            $newPatientLog->user = 'service';
                            $newPatientLog->save();
                        }else{
                            $patient = Patient::where('date', date('Y-m-d'))->where('hn', $data->HN)->first();
                        }
                    }

                    switch ($data->StationCode) {
                        case '01':
                            $code = 'b12_vitalsign';
                            break;
                        case '011':
                            $code = 'b12_lab';
                            break;
                            case '02':
                        $code = 'b12_ekg';
                            break;
                        case '03':
                            $code = 'b12_abi';
                            break;
                        case '04':
                            $code = 'b12_estecho';
                            break;
                        case '06':
                            $code = 'b12_chest';
                            break;
                        case '07':
                            $code = 'b12_ultrasound';
                            break;
                        case '08':
                            $code = 'b12_mammogram';
                            break;
                        case '09':
                            $code = 'b12_boneden';
                            break;
                        case '10':
                            $code = 'b12_gny';
                            break;
                        default:
                            $code = false;
                            break;
                    }
                    if($code){
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
                            $newPatientLog->text = 'สร้างรายการ Check UP '.$code;
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
                $checkHN[] = $data->HN;
            } 
            catch (\Throwable $th) {
                Log::channel('debug')->notice($data->HN . ' err Data.');
                Log::channel('debug')->notice($patient);
                Log::channel('debug')->notice($th);
            }
        }

        ProcessCreateTask::dispatch()->delay(5);
    }
}
