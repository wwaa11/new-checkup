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
        return $this->HasMany(Patienttask::class);
    }

    protected function casts(): array
    {
        return [
            'logs' => 'object',
        ];
    }
}
