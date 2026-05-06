<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocialMediaPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'gym_id',
        'created_by',
        'title',
        'content',
        'media_path',
        'video_path',
        'platforms',
        'publish_results',
        'status',
        'published_at',
    ];

    protected $casts = [
        'platforms' => 'array',
        'publish_results' => 'array',
        'published_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function gym()
    {
        return $this->belongsTo(Gym::class);
    }
}

