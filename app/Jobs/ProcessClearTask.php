<?php

namespace App\Jobs;

use App\Models\Patient;
use App\Models\Patientlogs;
use App\Models\Patienttask;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use DB;

class ProcessClearTask implements ShouldQueue
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
        return 'clearTask';
    }

    public function __construct()
    {

    }

    public function handle(): void
    {
        $tasks = Patienttask::whereDate('date', date('Y-m-d'))
            ->whereNull('success')
            ->get();

        foreach($tasks as $task){
            $success = false;
            $text = null;
            if($task->code == 'b12_vitalsign'){
                $getVS = DB::connection('SSB')
                    ->table("HNOPD_VITALSIGN")
                    ->whereDate('VisitDate', date('Y-m-d'))
                    ->where('VN', $task->vn)
                    ->first();

                if($getVS !== null){
                    $success = true;
                    $text = 'Vital Sign';
                }
            } 
            else if($task->code == 'b12_lab'){
                $getLabReq = DB::connection('NewUI')
                    ->table("HIS_CHECKUP_STATION_DETAIL")
                    ->whereDate('VisitDate', date('Y-m-d'))
                    ->where('VN', $task->vn)
                    ->where('StationCode', '011')
                    ->first();

                $blood = DB::connection('SSB')
                    ->table('HNLABREQ_HEADER')
                    ->where('RequestNo', $getLabReq->FacilityRequestNo)
                    ->first();

                if($blood !== null && $blood->SpecimenReceiveDateTime !== null){
                    $success = true;
                    $text = 'Lab';
                }
            }

            if($success){
                $task->type = 'success';
                $task->success = date('Y-m-d H:i:s');
                $task->save();
    
                $newPatientLog = new Patientlogs;
                $newPatientLog->patient_id = $task->patient->id;
                $newPatientLog->date = date('Y-m-d');
                $newPatientLog->hn = $task->patient->hn;
                $newPatientLog->text = 'สำเร็จรายการที่ : '. $text;
                $newPatientLog->user = 'service';
                $newPatientLog->save();
    
                if($task->code == 'b12_vitalsign'){
                    $taskLab = Patienttask::whereDate('date', date('Y-m-d'))
                        ->where('vn', $task->vn)
                        ->where('code', 'b12_lab')
                        ->whereNull('assign')
                        ->first();
        
                    if($taskLab !== null){
                        $taskLab->assign = date('Y-m-d H:i:s');
                        $taskLab->save();
        
                        $newPatientLog = new Patientlogs;
                        $newPatientLog->patient_id = $task->patient->id;
                        $newPatientLog->date = date('Y-m-d');
                        $newPatientLog->hn = $task->patient->hn;
                        $newPatientLog->text = 'ลงทะเบียนคิวที่ : Lab';
                        $newPatientLog->user = 'service';
                        $newPatientLog->save();
                    }
                }
            }
        }

        ProcessClearTask::dispatch()->delay(30);
    }
}
