<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Workspace extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = ['id', 'name'];
    protected $casts = [];

    public function collections()
    {
        return $this->hasMany(Collection::class);
    }
}