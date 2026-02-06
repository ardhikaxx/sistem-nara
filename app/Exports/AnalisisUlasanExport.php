<?php

namespace App\Exports;

use App\Models\DataUlasan;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AnalisisUlasanExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(private readonly int $analisisId)
    {
    }

    public function query()
    {
        return DataUlasan::query()
            ->where('analisis_id', $this->analisisId)
            ->orderBy('id');
    }

    public function headings(): array
    {
        return [
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
    }

    public function map($row): array
    {
        return [
            $row->review_id,
            $row->user_name,
            $row->user_image,
            $row->rating,
            $row->review_content,
            $row->review_date?->toDateTimeString(),
            $row->thumbs_up,
            $row->reply_content,
            $row->reply_date?->toDateTimeString(),
            $row->sentiment,
            $row->confidence,
        ];
    }
}
