<?php
namespace App\Models;

use App\Models\Patient;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Patienttask extends Model
{
    //

    protected $fillable = [
        'code',
        'type',
        'memo1',
        'memo2',
        'memo3',
        'memo4',
        'memo5',
        'assign',
        'success',
    ];

    protected $casts = [
        'assign'  => 'datetime',
        'success' => 'datetime',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    private function setWaitingTime()
    {
        return $this->assign->diffInMinutes(now());
    }

    public function waitingTime()
    {
        return number_format($this->setWaitingTime(), 0);
    }
}
