<?php

namespace Database\Seeders;

use App\Models\Applicant;
use Illuminate\Database\Seeder;

class ApplicantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $applicants = [
            [
                'full_name' => 'John Nji',
                'gender' => 'Male',
                'trade' => 'Science',
                'scholarship_type' => 'full',
                'total_subjects' => 5,
                'total_points' => 23,
                'status' => 'pending',
                'discount_percentage' => 0,
            ],
            [
                'full_name' => 'Mary Asong',
                'gender' => 'Female',
                'trade' => 'Commercial',
                'scholarship_type' => 'full',
                'total_subjects' => 5,
                'total_points' => 21,
                'status' => 'approved',
                'discount_percentage' => 100,
            ],
            [
                'full_name' => 'Peter Tabi',
                'gender' => 'Male',
                'trade' => 'Arts',
                'scholarship_type' => 'partial',
                'total_subjects' => 6,
                'total_points' => 19,
                'status' => 'partial',
                'discount_percentage' => 50,
            ],
            [
                'full_name' => 'Sarah Fon',
                'gender' => 'Female',
                'trade' => 'Science',
                'scholarship_type' => 'full',
                'total_subjects' => 5,
                'total_points' => 18,
                'status' => 'pending',
                'discount_percentage' => 0,
            ],
            [
                'full_name' => 'David Mbella',
                'gender' => 'Male',
                'trade' => 'Commercial',
                'scholarship_type' => 'partial',
                'total_subjects' => 6,
                'total_points' => 16,
                'status' => 'partial',
                'discount_percentage' => 40,
            ],
            [
                'full_name' => 'Grace Njoh',
                'gender' => 'Female',
                'trade' => 'Arts',
                'scholarship_type' => 'full',
                'total_subjects' => 5,
                'total_points' => 14,
                'status' => 'rejected',
                'discount_percentage' => 0,
                'decision_note' => 'Scholarship quota reached.',
            ],
            [
                'full_name' => 'Michael Tchinda',
                'gender' => 'Male',
                'trade' => 'Science',
                'scholarship_type' => 'partial',
                'total_subjects' => 5,
                'total_points' => 12,
                'status' => 'pending',
                'discount_percentage' => 0,
            ],
            [
                'full_name' => 'Esther Ngong',
                'gender' => 'Female',
                'trade' => 'Commercial',
                'scholarship_type' => 'partial',
                'total_subjects' => 5,
                'total_points' => 9,
                'status' => 'pending',
                'discount_percentage' => 0,
            ],
        ];

        foreach ($applicants as $applicant) {
            Applicant::create($applicant);
        }
    }
}