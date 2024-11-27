<?php

namespace App\Models;

use App\Models\Substation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Station extends Model
{
    //
    protected $fillable = [
        'code',
        'name',
        'wait',
        'hold'
    ];

    public function substations(): HasMany
    {
        return $this->HasMany(Substation::class);
    }
}
