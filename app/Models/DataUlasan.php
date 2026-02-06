<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataUlasan extends Model
{
    use HasFactory;

    protected $table = 'data_ulasan';

    protected $fillable = [
        'analisis_id',
        'review_id',
        'user_name',
        'user_image',
        'rating',
        'review_content',
        'review_date',
        'thumbs_up',
        'reply_content',
        'reply_date',
        'sentiment',
        'confidence',
    ];

    protected $casts = [
        'rating' => 'integer',
        'thumbs_up' => 'integer',
        'review_date' => 'datetime',
        'reply_date' => 'datetime',
        'confidence' => 'float',
    ];

    public function analisis(): BelongsTo
    {
        return $this->belongsTo(Analisis::class, 'analisis_id');
    }
}
