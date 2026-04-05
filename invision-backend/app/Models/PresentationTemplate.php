<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PresentationTemplate extends Model
{
    protected $fillable = [
        'tenant_id',
        'name',
        'category',
        'description',
        'slide_definitions',
        'theme',
        'is_default',
    ];

    protected $casts = [
        'slide_definitions' => 'array',
        'theme' => 'array',
        'is_default' => 'boolean',
    ];
}
