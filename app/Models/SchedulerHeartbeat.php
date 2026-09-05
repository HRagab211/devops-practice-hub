<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchedulerHeartbeat extends Model
{
    public $timestamps = false;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['id', 'last_ran_at'];

    protected function casts(): array
    {
        return ['last_ran_at' => 'datetime'];
    }
}
