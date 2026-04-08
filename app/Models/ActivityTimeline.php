<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityTimeline extends Model
{
    protected $fillable = [
        'user_id',
        'resume_id',
        'resume_parse_id',
        'activity',
        'activity_type',
        'details',
        'is_public',
    ];

    protected $casts = [
        'details' => 'array',
        'is_public' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function resume()
    {
        return $this->belongsTo(Resume::class);
    }

    public function resume_parse()
    {
        return $this->belongsTo(ResumeParse::class);
    }
}
