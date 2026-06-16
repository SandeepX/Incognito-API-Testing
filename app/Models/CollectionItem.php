<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CollectionItem extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['id', 'collection_id', 'parent_id', 'type', 'name', 'method', 'url', 'request_data', 'order', 'description', 'examples'];

    protected $casts = [
        'request_data' => 'array',
        'examples' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(CollectionItem::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(CollectionItem::class, 'parent_id')->orderBy('order');
    }

    public function isFolder(): bool
    {
        return $this->type === 'folder';
    }

    public function isRequest(): bool
    {
        return $this->type === 'request';
    }
}
