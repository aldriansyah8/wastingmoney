<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user_name',
        'user_email',
        'request_id',
        'title',
        'artist_band',
        'merch_type',
        'released_year',
        'merch_edition',
        'category',
        'description',
        'contact',
        'image_url',
        'file_name',
        'additional_data',
        'status',
        'admin_notes',
        'approved_at',
        'rejected_at',
    ];

    protected $casts = [
        'additional_data' => 'array',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function item()
    {
        return $this->hasOne(Item::class);
    }
}
