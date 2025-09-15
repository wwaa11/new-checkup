<?php
namespace App\Models;

use App\Models\Station;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Substation extends Model
{
    //
    protected $fillable = [
        'station_id',
        'name',
    ];

    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }

    public function doctor()
    {
        return $this->hasOne(SubstationDoctor::class, 'substation_id');
    }

    public function patientNow()
    {
        return $this->hasOne(Patient::class, 'vn', 'now')->where('date', date('Y-m-d'));
    }
}
