<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReportTemplate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'created_by',
        'name',
        'type',
        'description',
        'config',
        'layout',
        'is_shared',
        'is_favorite',
        'schedule',
        'last_generated_at',
    ];

    protected $casts = [
        'config' => 'array',
        'layout' => 'array',
        'is_shared' => 'boolean',
        'is_favorite' => 'boolean',
        'last_generated_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function exports()
    {
        return $this->hasMany(SavedExport::class);
    }
}
