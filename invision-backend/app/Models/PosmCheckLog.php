<?php

namespace App\Models;

use App\Enums\PosmCondition;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosmCheckLog extends Model
{
    protected $fillable = [
        'posm_placement_id',
        'checked_by',
        'condition',
        'photo_path',
        'notes',
        'replacement_requested',
    ];

    protected function casts(): array
    {
        return [
            'condition' => PosmCondition::class,
            'replacement_requested' => 'boolean',
        ];
    }

    public function placement(): BelongsTo
    {
        return $this->belongsTo(PosmPlacement::class, 'posm_placement_id');
    }

    public function checkedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by');
    }
}
