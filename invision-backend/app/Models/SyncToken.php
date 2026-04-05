<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasTenant;

class SyncToken extends Model
{
    use HasTenant;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'device_id',
        'last_pulled_at',
        'last_pushed_at',
        'pending_count',
    ];

    protected function casts(): array
    {
        return [
            'last_pulled_at' => 'datetime',
            'last_pushed_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
