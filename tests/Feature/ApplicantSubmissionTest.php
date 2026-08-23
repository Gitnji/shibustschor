<?php

namespace Tests\Feature;

use App\Models\Applicant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ApplicantSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_applicant_can_be_saved_with_subjects(): void
    {
        Livewire::test('add-applicant')
            ->set('full_name', 'John Doe')
            ->set('gender', 'Male')
            ->set('trade', 'Science')
            ->set('scholarship_type', 'full')
            ->set('total_subjects', 3)
            ->set('subjects.0.name', 'Math')
            ->set('subjects.0.grade', 'A')
            ->set('subjects.1.name', 'Physics')
            ->set('subjects.1.grade', 'B')
            ->set('subjects.2.name', 'Chemistry')
            ->set('subjects.2.grade', 'C')
            ->call('saveApplicant');

        $this->assertEquals(1, Applicant::count());
    }
}
