<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResumeParse extends Model
{
    protected $fillable = [
        'user_id',
        'resume_id',
        'resume_text_id',
        'status',
        'schema_version',
        'data',
        'anonymized_data',
        'meta',
        'processing_started_at', 
        'processing_finished_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'data' => 'array',
        'anonymized_data' => 'array',    
    ];
    
    public function resume()
    {
        return $this->belongsTo(Resume::class);
    }

    public function resumeText()
    {
        return $this->belongsTo(ResumeText::class);
    }
}
