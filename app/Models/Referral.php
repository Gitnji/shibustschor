<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Referral extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name',
        'referral_count',
        'applicant_id',
    ];

    protected function casts(): array
    {
        return [
            'referral_count' => 'integer',
            'applicant_id' => 'integer',
        ];
    }

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(Applicant::class);
    }

    public function isApplicant(): bool
    {
        return $this->applicant_id !== null;
    }
}