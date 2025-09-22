<?php
namespace App\Jobs;

use App\Models\Patient;
use App\Models\Patientlogs;
use App\Models\Patienttask;
use DB;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessClearTask implements ShouldQueue
{
    use Queueable;
    public $tries = 5;

    public $backoff = 60;

    public function uniqueId(): string
    {
        return 'clear_task_' . now()->timestamp;
    }

    public function __construct()
    {

    }

    public function handle(): void
    {
        $tasks = Patienttask::whereDate('date', date('Y-m-d'))
            ->whereNull('success')
            ->get();

        $ssbVitalSigns = DB::connection('SSB')
            ->table("HNOPD_VITALSIGN")
            ->whereDate('VisitDate', date('Y-m-d'))
            ->get();

        foreach ($tasks as $task) {
            $success = false;
            $text    = null;
            if ($task->code == 'b12_vitalsign') {
                $listVitalSign = collect($ssbVitalSigns)
                    ->where('VN', $task->vn)
                    ->first();

                if ($listVitalSign !== null) {
                    $success = true;
                    $text    = 'Vital Sign';
                }
            } else if ($task->code == 'b12_lab') {
                $listLab = DB::connection('SSB')
                    ->table('HNLABREQ_HEADER')
                    ->where('RequestNo', $task->memo1)
                    ->select('SpecimenReceiveDateTime')
                    ->first();

                if ($listLab !== null && $listLab->SpecimenReceiveDateTime !== null) {
                    $success = true;
                    $text    = 'Lab';
                }
            }

            if ($success) {
                $task->type    = 'success';
                $task->success = date('Y-m-d H:i:s');
                $task->save();
                $this->setLog($task->patient, 'สำเร็จรายการที่ : ' . $text);

                if ($task->code == 'b12_vitalsign') {
                    $taskLab = Patienttask::whereDate('date', date('Y-m-d'))
                        ->where('vn', $task->vn)
                        ->where('code', 'b12_lab')
                        ->whereNull('assign')
                        ->first();

                    if ($taskLab !== null) {
                        $taskLab->assign = date('Y-m-d H:i:s');
                        $taskLab->save();

                        $this->setLog($task->patient, 'ลงทะเบียนคิวที่ : Lab');
                    }
                }
            }
        }

        ProcessClearTask::dispatch()->onQueue('clearing')->delay(300);
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
}
