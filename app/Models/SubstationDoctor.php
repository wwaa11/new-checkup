<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubstationDoctor extends Model
{
    /** @use HasFactory<\Database\Factories\SubstationDoctorFactory> */
    use HasFactory;

    protected $fillable = [
        'substation_id',
        'doctor_code',
        'doctor_name',
    ];

    public function substation()
    {
        return $this->belongsTo(Substation::class);
    }
}
