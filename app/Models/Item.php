<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'submission_id',
        'title',
        'slug',
        'category',
        'description',
        'contact',
        'image_url',
        'views',
    ];

    public function submission()
    {
        return $this->belongsTo(Submission::class);
    }
}
