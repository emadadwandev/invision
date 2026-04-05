<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavedExport extends Model
{
    protected $fillable = [
        'tenant_id',
        'user_id',
        'report_template_id',
        'title',
        'format',
        'file_path',
        'file_size',
        'parameters',
    ];

    protected $casts = [
        'parameters' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function template()
    {
        return $this->belongsTo(ReportTemplate::class, 'report_template_id');
    }
}
