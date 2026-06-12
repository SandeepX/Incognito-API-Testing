<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Collection extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = ['id', 'workspace_id', 'name'];
    protected $casts = [];

    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }

    public function items()
    {
        return $this->hasMany(CollectionItem::class)->whereNull('parent_id')->orderBy('sort_order');
    }

    public function allItems()
    {
        return $this->hasMany(CollectionItem::class)->orderBy('sort_order');
    }
}