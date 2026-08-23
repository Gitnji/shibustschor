<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicantSubject extends Model
{
    use HasFactory;

    protected $fillable = [
        'applicant_id',
        'subject_name',
        'grade',
        'points',
    ];

    protected function casts(): array
    {
        return [
            'applicant_id' => 'integer',
            'points' => 'integer',
        ];
    }

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(Applicant::class);
    }
}