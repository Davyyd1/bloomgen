<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resume extends Model
{
    protected $fillable = [
        'user_id',
        'company_id',
        'original_name',
        'stored_path',
        'mime_type',
        'size_bytes',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
