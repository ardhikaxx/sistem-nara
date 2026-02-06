<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Analisis extends Model
{
    use HasFactory;

    protected $table = 'analisis';

    protected $fillable = [
        'nama_analisis',
        'tanggal_analisis',
        'total_review_positif',
        'total_review_netral',
        'total_review_negatif',
    ];

    protected $casts = [
        'tanggal_analisis' => 'date',
        'total_review_positif' => 'integer',
        'total_review_netral' => 'integer',
        'total_review_negatif' => 'integer',
    ];

    public function dataUlasan(): HasMany
    {
        return $this->hasMany(DataUlasan::class, 'analisis_id');
    }
}
