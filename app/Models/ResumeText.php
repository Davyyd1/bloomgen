<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResumeText extends Model
{
    protected $fillable = [
        'resume_id',
        'source',
        'raw_text',
        'meta'
    ];

    protected $casts = ['meta' => 'array'];

    public function resume(){
        return $this->belongsTo(Resume::class);
    }

    public function resumeParse(){
        return $this->hasMany(ResumeParse::class);
    }
}
