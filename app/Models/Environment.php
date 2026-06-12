<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Environment extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['id', 'name', 'variables'];

    protected $casts = [
        'variables' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}