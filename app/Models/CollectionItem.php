<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CollectionItem extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = ['id', 'collection_id', 'parent_id', 'type', 'name', 'request_data', 'response_data', 'sort_order'];
    protected $casts = ['request_data' => 'array', 'response_data' => 'array'];

    public function collection()
    {
        return $this->belongsTo(Collection::class);
    }

    public function parent()
    {
        return $this->belongsTo(CollectionItem::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(CollectionItem::class, 'parent_id')->orderBy('sort_order');
    }

    public function isFolder()
    {
        return $this->type === 'folder';
    }

    public function isRequest()
    {
        return $this->type === 'request';
    }
}