<?php

namespace App\Models;

use App\Models\Patienttask;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patient extends Model
{
    //

    public function tasks(): HasMany
    {
        return $this->HasMany(Patienttask::class, "patient_id", "id");
    }
    public function logs(): HasMany
    {
        return $this->HasMany(Patientlogs::class, "patient_id", "id");
    }
}
