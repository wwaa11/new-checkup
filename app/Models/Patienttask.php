<?php

namespace App\Models;

use App\Models\Patient;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Patienttask extends Model
{
    //

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }
}
