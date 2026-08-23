<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Applicant extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name',
        'gender',
        'trade',
        'scholarship_type',
        'total_subjects',
        'total_points',
        'status',
        'discount_percentage',
        'decision_note',
    ];

    protected function casts(): array
    {
        return [
            'total_subjects' => 'integer',
            'total_points' => 'integer',
            'discount_percentage' => 'integer',
        ];
    }

    public function subjects(): HasMany
    {
        return $this->hasMany(ApplicantSubject::class);
    }

//     public function subjects(): HasMany
// {
//     return $this->hasMany(ApplicantSubject::class);
// }

public function referral(): HasOne
{
    return $this->hasOne(Referral::class);
}

}