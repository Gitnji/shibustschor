<?php

use App\Models\Applicant;
use App\Models\ApplicantSubject;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use App\Models\Referral;

new class extends Component
{
    /*
    |--------------------------------------------------------------------------
    | Applicant information
    |--------------------------------------------------------------------------
    */

    public string $full_name = '';

    public string $gender = '';

    public string $trade = '';

    public string $scholarship_type = '';

    public int $total_subjects = 0;



public int $total_points = 0;

/*
|--------------------------------------------------------------------------
| GCE Subjects
|--------------------------------------------------------------------------
*/

public array $subjects = [];


    /*
    |--------------------------------------------------------------------------
    | Temporary calculated points
    |--------------------------------------------------------------------------
    |
    | GCE points will be implemented in Phase 3 Step 2.
    |
    */

    // public int $total_points = 0;


    /*
    |--------------------------------------------------------------------------
    | Save applicant
    |--------------------------------------------------------------------------
    */


    /*
|--------------------------------------------------------------------------
| GCE Grade Points
|--------------------------------------------------------------------------
*/

protected array $gradePoints = [
    'A' => 5,
    'B' => 4,
    'C' => 3,
    'D' => 2,
    'E' => 1,
];


public function updatedTotalSubjects($value): void
{
    $count = (int) $value;

    if ($count < 0) {
        $count = 0;
    }

    if ($count > 30) {
        $count = 30;
    }

    $currentSubjects = $this->subjects;

    $this->subjects = [];

    for ($i = 0; $i < $count; $i++) {

        $this->subjects[] = [
            'name' => $currentSubjects[$i]['name'] ?? '',
            'grade' => $currentSubjects[$i]['grade'] ?? '',
        ];
    }

    $this->calculatePoints();
}

public function updatedSubjects(): void
{
    $this->calculatePoints();
}


public function calculatePoints(): void
{
    $this->total_points = 0;

    foreach ($this->subjects as $subject) {

        $grade = strtoupper(
            trim($subject['grade'] ?? '')
        );

        if (isset($this->gradePoints[$grade])) {

            $this->total_points +=
                $this->gradePoints[$grade];

        }
    }
}

    public function saveApplicant(): void
{
    $this->calculatePoints();

    $this->validate([
        'full_name' => [
            'required',
            'string',
            'min:3',
            'max:255',
        ],

        'gender' => [
            'required',
            'in:Male,Female',
        ],

        'trade' => [
            'required',
            'in:Science,Arts,Commercial',
        ],

        'scholarship_type' => [
            'required',
            'in:full,partial',
        ],

        'total_subjects' => [
            'required',
            'integer',
            'min:1',
            'max:30',
        ],

        'subjects' => [
            'required',
            'array',
            'size:' . $this->total_subjects,
        ],

        'subjects.*.name' => [
            'required',
            'string',
            'max:100',
        ],

        'subjects.*.grade' => [
            'required',
            'in:A,B,C,D,E',
        ],
    ]);


    DB::transaction(function () {

        $applicant = Applicant::create([
            'full_name' => trim($this->full_name),

            'gender' => $this->gender,

            'trade' => $this->trade,

            'scholarship_type' => $this->scholarship_type,

            'total_subjects' => $this->total_subjects,

            'total_points' => $this->total_points,

            'status' => 'pending',

            'discount_percentage' => 0,

            'decision_note' => null,
        ]);

        Referral::create([
    'full_name' => $applicant->full_name,
    'referral_count' => 0,
    'applicant_id' => $applicant->id,
]);


        foreach ($this->subjects as $subject) {

            $grade = strtoupper(
                trim($subject['grade'])
            );

            ApplicantSubject::create([
                'applicant_id' => $applicant->id,

                'subject_name' => trim(
                    $subject['name']
                ),

                'grade' => $grade,

                'points' => $this->gradePoints[$grade],
            ]);
        }
    });


    session()->flash(
        'success',
        'Applicant and GCE results saved successfully.'
    );


    $this->reset([
        'full_name',
        'gender',
        'trade',
        'scholarship_type',
        'total_subjects',
        'total_points',
        'subjects',
    ]);
}
};
?>

<div class="p-6 lg:p-8">

    {{-- =========================================================
         PAGE HEADER
    ========================================================== --}}

    <div>

        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">
            Scholarship Program
        </p>

        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">

    <div>

        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">
            {{-- Scholarship Program --}}
        </p>

        <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">
            Add Applicant
        </h1>

        <p class="mt-1 text-sm text-slate-500">
            {{-- Register a new applicant for scholarship consideration. --}}
        </p>

    </div>


    <a
        href="{{ route('dashboard') }}"
        wire:navigate
        class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50"
    >

        <svg
            class="h-4 w-4"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
        >

            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M15 19l-7-7 7-7"
            />

        </svg>

        Dashboard

    </a>

</div>

        <p class="mt-1 text-sm text-slate-500">
            Register a new applicant for scholarship consideration.
        </p>

    </div>


    {{-- =========================================================
         SUCCESS MESSAGE
    ========================================================== --}}

    @if (session()->has('success'))

        <div class="mt-6 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3">

            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-100">

                <svg
                    class="h-4 w-4 text-emerald-600"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >

                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="m5 12 4 4L19 8"
                    />

                </svg>

            </div>

            <p class="text-sm font-medium text-emerald-700">
                {{ session('success') }}
            </p>

        </div>

    @endif


    {{-- =========================================================
         FORM
    ========================================================== --}}

    <form
        wire:submit="saveApplicant"
        class="mt-8"
    >

        <div class="grid gap-6 lg:grid-cols-3">

            {{-- =================================================
                 PERSONAL INFORMATION
            ================================================== --}}

            <div class="lg:col-span-2 rounded-xl border border-slate-200 bg-white shadow-sm">

                <div class="border-b border-slate-200 px-6 py-5">

                    <h2 class="font-semibold text-slate-900">
                        Personal Information
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Basic information about the applicant.
                    </p>

                </div>


                <div class="grid gap-5 p-6 sm:grid-cols-2">

                    {{-- Full name --}}

                    <div class="sm:col-span-2">

                        <label
                            for="full_name"
                            class="text-sm font-medium text-slate-700"
                        >
                            Full Name
                        </label>

                        <input
                            id="full_name"
                            type="text"
                            wire:model="full_name"
                            placeholder="Enter applicant's full name"
                            class="mt-2 h-11 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none transition placeholder:text-slate-400 focus:border-slate-400 focus:ring-2 focus:ring-slate-100"
                        >

                        @error('full_name')

                            <p class="mt-1.5 text-xs text-red-600">
                                {{ $message }}
                            </p>

                        @enderror

                    </div>


                    {{-- Gender --}}

                    <div>

                        <label
                            for="gender"
                            class="text-sm font-medium text-slate-700"
                        >
                            Gender
                        </label>

                        <select
                            id="gender"
                            wire:model="gender"
                            class="mt-2 h-11 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-600 outline-none focus:border-slate-400 focus:ring-2 focus:ring-slate-100"
                        >

                            <option value="">
                                Select gender
                            </option>

                            <option value="Male">
                                Male
                            </option>

                            <option value="Female">
                                Female
                            </option>

                        </select>

                        @error('gender')

                            <p class="mt-1.5 text-xs text-red-600">
                                {{ $message }}
                            </p>

                        @enderror

                    </div>


                    {{-- Trade --}}

                    <div>

                        <label
                            for="trade"
                            class="text-sm font-medium text-slate-700"
                        >
                            Trade
                        </label>

                        <select
                            id="trade"
                            wire:model="trade"
                            class="mt-2 h-11 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-600 outline-none focus:border-slate-400 focus:ring-2 focus:ring-slate-100"
                        >

                            <option value="">
                                Select trade
                            </option>

                            <option value="Science">
                                Science
                            </option>

                            <option value="Arts">
                                Arts
                            </option>

                            <option value="Commercial">
                                Commercial
                            </option>

                        </select>

                        @error('trade')

                            <p class="mt-1.5 text-xs text-red-600">
                                {{ $message }}
                            </p>

                        @enderror

                    </div>

                </div>

            </div>


            {{-- =================================================
                 SCHOLARSHIP INFORMATION
            ================================================== --}}

            <div class="rounded-xl border border-slate-200 bg-white shadow-sm">

                <div class="border-b border-slate-200 px-6 py-5">

                    <h2 class="font-semibold text-slate-900">
                        Scholarship
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Applicant's requested scholarship.
                    </p>

                </div>


                <div class="space-y-5 p-6">

                    {{-- Scholarship type --}}

                    <div>

                        <label
                            for="scholarship_type"
                            class="text-sm font-medium text-slate-700"
                        >
                            Scholarship Type
                        </label>

                        <select
                            id="scholarship_type"
                            wire:model="scholarship_type"
                            class="mt-2 h-11 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-600 outline-none focus:border-slate-400 focus:ring-2 focus:ring-slate-100"
                        >

                            <option value="">
                                Select type
                            </option>

                            <option value="full">
                                Full Scholarship
                            </option>

                            <option value="partial">
                                Partial Scholarship
                            </option>

                        </select>

                        @error('scholarship_type')

                            <p class="mt-1.5 text-xs text-red-600">
                                {{ $message }}
                            </p>

                        @enderror

                    </div>


                    {{-- Subjects --}}

                    <div>

                        <label
                            for="total_subjects"
                            class="text-sm font-medium text-slate-700"
                        >
                            Total GCE Subjects
                        </label>

                        <input
                            id="total_subjects"
                            type="number"
                            min="1"
                            max="30"
                            wire:model.live="total_subjects"
                            placeholder="e.g. 8"
                            class="mt-2 h-11 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none transition placeholder:text-slate-400 focus:border-slate-400 focus:ring-2 focus:ring-slate-100"
                        >

                        @error('total_subjects')

                            <p class="mt-1.5 text-xs text-red-600">
                                {{ $message }}
                            </p>

                        @enderror

                    </div>


                    {{-- Points preview --}}

                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">

    <div class="flex items-center justify-between">

        <div>

            <p class="text-xs font-medium uppercase tracking-wide text-slate-500">
                Current Score
            </p>

            <p class="mt-1 text-xs text-slate-400">
                Based on GCE grades
            </p>

        </div>


        <div class="text-right">

            <p class="text-3xl font-bold text-slate-900">
                {{ $total_points }}
            </p>

            <p class="text-xs text-slate-400">
                points
            </p>

        </div>

    </div>

</div>

                </div>

            </div>

        </div>

        {{-- =========================================================
     GCE RESULTS
========================================================= --}}

<div class="mt-6 rounded-xl border border-slate-200 bg-white shadow-sm">

    <div class="border-b border-slate-200 px-6 py-5">

        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">

            <div>

                <h2 class="font-semibold text-slate-900">
                    GCE Results
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Enter each subject and the grade obtained.
                </p>

            </div>


            <div class="rounded-lg bg-slate-50 px-4 py-2">

                <span class="text-xs text-slate-500">
                    Total Points
                </span>

                <span class="ml-2 text-lg font-bold text-slate-900">
                    {{ $total_points }}
                </span>

            </div>

        </div>

    </div>


    <div class="p-6">

        @if ($total_subjects > 0)

            <div class="space-y-3">

                @foreach ($subjects as $index => $subject)

                    <div
                        wire:key="subject-{{ $index }}"
                        class="grid gap-3 sm:grid-cols-12"
                    >

                        {{-- Number --}}

                        <div class="flex items-center sm:col-span-1">

                            <span class="text-sm font-semibold text-slate-400">
                                {{ $index + 1 }}
                            </span>

                        </div>


                        {{-- Subject --}}

                        <div class="sm:col-span-8">

                            <input
                                type="text"
                                wire:model.live="subjects.{{ $index }}.name"
                                placeholder="Subject name"
                                class="h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none transition placeholder:text-slate-400 focus:border-slate-400 focus:ring-2 focus:ring-slate-100"
                            >

                            @error("subjects.$index.name")

                                <p class="mt-1 text-xs text-red-600">
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>


                        {{-- Grade --}}

                        <div class="sm:col-span-3">

                            <select
                                wire:model.live="subjects.{{ $index }}.grade"
                                class="h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-600 outline-none focus:border-slate-400 focus:ring-2 focus:ring-slate-100"
                            >

                                <option value="">
                                    Select grade
                                </option>

                                <option value="A">
                                    A — 5 points
                                </option>

                                <option value="B">
                                    B — 4 points
                                </option>

                                <option value="C">
                                    C — 3 points
                                </option>

                                <option value="D">
                                    D — 2 points
                                </option>

                                <option value="E">
                                    E — 1 point
                                </option>

                            </select>

                            @error("subjects.$index.grade")

                                <p class="mt-1 text-xs text-red-600">
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>

                    </div>

                @endforeach

            </div>


            {{-- Grade legend --}}

            <div class="mt-6 flex flex-wrap gap-2 border-t border-slate-100 pt-5">

                <span class="rounded-md bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                    A = 5
                </span>

                <span class="rounded-md bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">
                    B = 4
                </span>

                <span class="rounded-md bg-sky-50 px-2.5 py-1 text-xs font-semibold text-sky-700">
                    C = 3
                </span>

                <span class="rounded-md bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">
                    D = 2
                </span>

                <span class="rounded-md bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">
                    E = 1
                </span>

            </div>


        @else

            <div class="rounded-xl border border-dashed border-slate-200 py-12 text-center">

                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100">

                    <svg
                        class="h-6 w-6 text-slate-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >

                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1.8"
                            d="M9 12h6m-6 4h4m6-9.5V19a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h7.5L19 7.5Z"
                        />

                    </svg>

                </div>

                <p class="mt-3 text-sm font-medium text-slate-700">
                    No subjects added yet
                </p>

                <p class="mt-1 text-xs text-slate-400">
                    Enter the total number of GCE subjects above.
                </p>

            </div>

        @endif

    </div>

</div>

        {{-- =====================================================
             FORM ACTIONS
        ====================================================== --}}

        <div class="mt-6 flex items-center justify-end gap-3">

            <a
                href="{{ route('dashboard') }}"
                wire:navigate
                class="rounded-lg border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50"
            >
                Cancel
            </a>


           <button
    type="submit"
    wire:loading.attr="disabled"
    wire:target="saveApplicant"
    class="inline-flex items-center gap-2 rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-60"
>

    <span wire:loading.remove wire:target="saveApplicant">
        Add Applicant
    </span>


    <span
        wire:loading
        wire:target="saveApplicant"
        class="flex items-center gap-2"
    >

        <svg
            class="h-4 w-4 animate-spin"
            fill="none"
            viewBox="0 0 24 24"
        >

            <circle
                class="opacity-25"
                cx="12"
                cy="12"
                r="10"
                stroke="currentColor"
                stroke-width="4"
            />

            <path
                class="opacity-75"
                fill="currentColor"
                d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"
            />

        </svg>

        Saving...

    </span>

</button>

        </div>

    </form>

</div>