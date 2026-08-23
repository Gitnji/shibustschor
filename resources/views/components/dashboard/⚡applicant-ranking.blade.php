<?php

use App\Models\Applicant;
use Livewire\Component;

new class extends Component
{
    public string $search = '';

    public string $trade = '';

    public string $pointsRange = '';

    public string $scholarshipType = '';

    public function clearFilters(): void
    {
        $this->reset([
            'search',
            'trade',
            'pointsRange',
            'scholarshipType',
        ]);
    }

    public ?int $selectedApplicantId = null;

public bool $showApplicantDrawer = false;

public bool $showDecisionModal = false;

public string $decision = '';

public string $discountPercentage = '';

public function openApplicant(int $id): void
{
    $this->selectedApplicantId = $id;

    $this->showApplicantDrawer = true;
}

public function closeApplicant(): void
{
    $this->selectedApplicantId = null;

    $this->showApplicantDrawer = false;
}

public function openDecisionModal(): void
{
    $this->decision = '';

    $this->discountPercentage = '';

    $this->showDecisionModal = true;
}

public function closeDecisionModal(): void
{
    $this->showDecisionModal = false;
}

public function makeDecision(): void
{
    $this->validate([
        'decision' => ['required', 'in:approved,partial,rejected'],

        'discountPercentage' => [
            'nullable',
            'integer',
            'min:1',
            'max:99',
            'required_if:decision,partial',
        ],
    ]);

    $applicant = Applicant::findOrFail(
        $this->selectedApplicantId
    );

    if ($this->decision === 'approved') {

        $applicant->update([
            'status' => 'approved',
            'discount_percentage' => 100,
        ]);

    } elseif ($this->decision === 'partial') {

        $applicant->update([
            'status' => 'partial',
            'discount_percentage' => (int) $this->discountPercentage,
        ]);

    } else {

        $applicant->update([
            'status' => 'rejected',
            'discount_percentage' => 0,
        ]);

    }

    $this->showDecisionModal = false;

    $this->showApplicantDrawer = false;

    $this->reset([
        'selectedApplicantId',
        'decision',
        'discountPercentage',
    ]);
}

    public function render()
    {
        $query = Applicant::query();

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        $query->when($this->search, function ($query) {

            $search = '%' . $this->search . '%';

            $query->where(function ($query) use ($search) {

                $query
                    ->where('full_name', 'ILIKE', $search)
                    ->orWhere('trade', 'ILIKE', $search);

            });

        });


        /*
        |--------------------------------------------------------------------------
        | Trade filter
        |--------------------------------------------------------------------------
        */

        $query->when(
            $this->trade,
            fn ($query) => $query->where('trade', $this->trade)
        );


        /*
        |--------------------------------------------------------------------------
        | Scholarship filter
        |--------------------------------------------------------------------------
        */

        $query->when(
            $this->scholarshipType,
            fn ($query) => $query->where(
                'scholarship_type',
                $this->scholarshipType
            )
        );


        /*
        |--------------------------------------------------------------------------
        | Points filter
        |--------------------------------------------------------------------------
        */

        $query->when($this->pointsRange, function ($query) {

            match ($this->pointsRange) {

                '5-10' => $query->whereBetween(
                    'total_points',
                    [5, 10]
                ),

                '10-15' => $query->whereBetween(
                    'total_points',
                    [11, 15]
                ),

                '15-20' => $query->whereBetween(
                    'total_points',
                    [16, 20]
                ),

                '20-25' => $query->whereBetween(
                    'total_points',
                    [21, 25]
                ),

                default => null,

            };

        });


        /*
        |--------------------------------------------------------------------------
        | Ranking
        |--------------------------------------------------------------------------
        */

        $applicants = $query
            ->orderByDesc('total_points')
            ->orderBy('full_name')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Dashboard statistics
        |--------------------------------------------------------------------------
        */

        $totalApplicants = Applicant::count();

        $fullScholarships = Applicant::where(
            'scholarship_type',
            'full'
        )->count();

        $partialScholarships = Applicant::where(
            'scholarship_type',
            'partial'
        )->count();

        $pendingDecisions = Applicant::where(
            'status',
            'pending'
        )->count();

        $averagePoints = round(
            Applicant::avg('total_points') ?? 0,
            1
        );

        $selectedApplicant = $this->selectedApplicantId
    ? Applicant::find($this->selectedApplicantId)
    : null;


        return $this->view([
            'applicants' => $applicants,
            'totalApplicants' => $totalApplicants,
            'fullScholarships' => $fullScholarships,
            'partialScholarships' => $partialScholarships,
            'pendingDecisions' => $pendingDecisions,
            'averagePoints' => $averagePoints,
            'selectedApplicant' => $selectedApplicant,
        ]);
    }
};
?>

<div class="min-h-screen bg-slate-50">

    <div class="flex min-h-screen">

        {{-- =====================================================
             SIDEBAR
        ====================================================== --}}

        <aside class="hidden w-64 shrink-0 border-r border-slate-200 bg-white lg:flex lg:flex-col">

            {{-- Brand --}}
            <div class="flex h-20 items-center border-b border-slate-200 px-6">

                <div class="flex items-center gap-3">

                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-900">

                        <svg
                            class="h-5 w-5 text-white"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                                d="M12 14l9-5-9-5-9 5 9 5z"
                            />

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                                d="M5 12v5c3.5 2.5 10.5 2.5 14 0v-5"
                            />

                        </svg>

                    </div>

                    <div>

                        <div class="text-sm font-bold tracking-tight text-slate-900">
                           SHIBUST Scholarship
                        </div>

                        <div class="text-xs text-slate-500">
                            Ranking System
                        </div>

                    </div>

                </div>

            </div>


            {{-- Navigation --}}
            <nav class="flex-1 px-4 py-6">

                <div class="mb-3 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                    Main Menu
                </div>


                <div class="space-y-1">

                    {{-- Dashboard --}}
                    <a
                        href="{{ route('dashboard') }}"
                        class="flex items-center gap-3 rounded-lg bg-slate-900 px-3 py-2.5 text-sm font-medium text-white"
                    >

                        <svg
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                                d="M3 13h8V3H3v10zm10 8h8V11h-8v10zM3 21h8v-6H3v6zm10-18v6h8V3h-8z"
                            />
                        </svg>

                        <span>Dashboard</span>

                    </a>


                    {{-- Results --}}
                    <a
                        href="{{ route('results') }}"
                        class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-slate-900"
                    >

                        <svg
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                                d="M9 12l2 2 4-4m5.5 2a8.5 8.5 0 11-17 0 8.5 8.5 0 0117 0z"
                            />
                        </svg>

                        <span>Results</span>

                    </a>


                    {{-- Add Applicant --}}
                    <a
                        href="{{ route('applicants.add') }}"
                        class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-slate-900"
                    >

                        <svg
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                                d="M12 4v16m8-8H4"
                            />
                        </svg>

                        <span>Add Applicant</span>

                    </a>


                    {{-- Referrals --}}
                    <a
                        href="{{ route('referrals') }}"
                        class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-slate-900"
                    >

                        <svg
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                                d="M17 20h5v-2a4 4 0 00-4-4h-1m-4 6H3v-2a4 4 0 014-4h4a4 4 0 014 4v2zm-2-10a4 4 0 11-8 0 4 4 0 018 0zm8 1a3 3 0 10-3-3"
                            />
                        </svg>

                        <span>Referrals</span>

                    </a>

                </div>

            </nav>


            {{-- Sidebar footer --}}
            <div class="border-t border-slate-200 p-4">

                <div class="rounded-xl bg-slate-50 p-4">

                    <div class="flex items-center justify-between">

                        <div>

                            <p class="text-[11px] font-medium uppercase tracking-wide text-slate-400">
                                Session
                            </p>

                            <p class="mt-1 text-sm font-semibold text-slate-900">
                                2026 Scholarship
                            </p>

                        </div>

                        <span class="flex h-2.5 w-2.5 rounded-full bg-emerald-500"></span>

                    </div>

                    <p class="mt-3 text-xs text-slate-500">
                        Ranking system active
                    </p>

                </div>

            </div>

        </aside>


        {{-- =====================================================
             MAIN AREA
        ====================================================== --}}

        <main class="min-w-0 flex-1">


            {{-- Header --}}
            <header class="sticky top-0 z-20 flex h-20 items-center justify-between border-b border-slate-200 bg-white/95 px-5 backdrop-blur lg:px-8">

                <div class="flex items-center gap-4">

                    {{-- Mobile menu --}}
                    <button
                        type="button"
                        class="rounded-lg p-2 text-slate-500 transition hover:bg-slate-100 lg:hidden"
                        @click="mobileNavOpen = true"
                        aria-controls="mobile-navigation"
                        aria-label="Open navigation"
                    >
                        <svg
                            class="h-6 w-6"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                            aria-hidden="true"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16"
                            />
                        </svg>
                    </button>

                    <div>

                        <h1 class="text-lg font-bold tracking-tight text-slate-900 sm:text-xl">
                            Scholarship Ranking
                        </h1>

                        <p class="hidden text-sm text-slate-500 sm:block">
                            Manage and rank scholarship applicants
                        </p>

                    </div>

                </div>


                <div class="flex items-center gap-3">

                    <div class="hidden items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1.5 sm:flex">

                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>

                        <span class="text-xs font-medium text-slate-600">
                            2026 Session
                        </span>

                    </div>

                </div>

            </header>


            {{-- Content --}}
            <div class="p-5 lg:p-8">


                {{-- Page intro --}}
                <div class="mb-7 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">

                    <div>

                        <p class="text-sm font-medium text-slate-500">
                            Overview
                        </p>

                        <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">
                            Applicant Ranking
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Review applicants and their scholarship ranking.
                        </p>

                    </div>


                    {{-- Add applicant button --}}
                    <a href="{{ route('applicants.add') }}">
                    <button
                        type="button"
                        
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800"
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
                                d="M12 4v16m8-8H4"
                            />
                        </svg>
                         
                        Add Applicant
                        
                    </button>
                    </a>

                </div>


                {{-- =================================================
                     STATISTICS
                ================================================== --}}

                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">


                    {{-- Total applicants --}}
                    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">

                        <div class="flex items-start justify-between">

                            <div>

                                <p class="text-sm font-medium text-slate-500">
                                    Total Applicants
                                </p>

                                <p class="mt-3 text-2xl font-bold tracking-tight text-slate-900">
                                    {{ $totalApplicants }}
                                </p>

                            </div>

                            <div class="rounded-lg bg-slate-100 p-2.5">

                                <svg
                                    class="h-5 w-5 text-slate-600"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.8"
                                        d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2m7-10a4 4 0 100-8 4 4 0 000 8zm7-5v6m3-3h-6"
                                    />
                                </svg>

                            </div>

                        </div>

                    </div>


                    {{-- Full --}}
                    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">

                        <div class="flex items-start justify-between">

                            <div>

                                <p class="text-sm font-medium text-slate-500">
                                    Full Scholarships
                                </p>

                                <p class="mt-3 text-2xl font-bold tracking-tight text-slate-900">
                                    {{ $fullScholarships }}
                                </p>

                            </div>

                            <div class="rounded-lg bg-emerald-50 p-2.5">

                                <svg
                                    class="h-5 w-5 text-emerald-600"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.8"
                                        d="M12 3l2.7 5.47L21 9.39l-4.5 4.38 1.06 6.2L12 17.06l-5.56 2.91 1.06-6.2L3 9.39l6.3-.92L12 3z"
                                    />
                                </svg>

                            </div>

                        </div>

                    </div>


                    {{-- Partial --}}
                    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">

                        <div class="flex items-start justify-between">

                            <div>

                                <p class="text-sm font-medium text-slate-500">
                                    Partial Scholarships
                                </p>

                                <p class="mt-3 text-2xl font-bold tracking-tight text-slate-900">
                                    {{ $partialScholarships }}
                                </p>

                            </div>

                            <div class="rounded-lg bg-blue-50 p-2.5">

                                <svg
                                    class="h-5 w-5 text-blue-600"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.8"
                                        d="M12 8c-1.66 0-3 1.12-3 2.5S10.34 13 12 13s3 1.12 3 2.5S13.66 18 12 18m0-10V6m0 12v-2m8-4a8 8 0 11-16 0 8 8 0 0116 0z"
                                    />
                                </svg>

                            </div>

                        </div>

                    </div>


                    {{-- Pending --}}
                    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">

                        <div class="flex items-start justify-between">

                            <div>

                                <p class="text-sm font-medium text-slate-500">
                                    Pending Decisions
                                </p>

                                <p class="mt-3 text-2xl font-bold tracking-tight text-slate-900">
                                    {{ $pendingDecisions }}
                                </p>

                            </div>

                            <div class="rounded-lg bg-amber-50 p-2.5">

                                <svg
                                    class="h-5 w-5 text-amber-600"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.8"
                                        d="M12 8v4l3 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z"
                                    />
                                </svg>

                            </div>

                        </div>

                    </div>


                    {{-- Average --}}
                    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">

                        <div class="flex items-start justify-between">

                            <div>

                                <p class="text-sm font-medium text-slate-500">
                                    Average Points
                                </p>

                                <p class="mt-3 text-2xl font-bold tracking-tight text-slate-900">
                                    {{ $averagePoints }}
                                </p>

                            </div>

                            <div class="rounded-lg bg-violet-50 p-2.5">

                                <svg
                                    class="h-5 w-5 text-violet-600"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.8"
                                        d="M4 19V5m0 14h16M8 16v-5m4 5V7m4 9v-8"
                                    />
                                </svg>

                            </div>

                        </div>

                    </div>

                </div>


                {{-- =================================================
                     RANKING SECTION
                ================================================== --}}

                <div class="mt-8 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">


                    {{-- Table header --}}
                    <div class="border-b border-slate-200 px-5 py-5 sm:px-6">

                        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">

                            <div>

                                <h3 class="font-semibold text-slate-900">
                                    Applicants
                                </h3>

                                <p class="mt-1 text-sm text-slate-500">
                                    Ranked from highest to lowest points.
                                </p>

                            </div>


                            {{-- Search/filter UI --}}
                            <div class="flex flex-col gap-2 sm:flex-row">

                                {{-- Search --}}
                                <div class="relative">

                                    <svg
                                        class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M21 21l-4.35-4.35m1.35-5.65a7 7 0 11-14 0 7 7 0 0114 0z"
                                        />
                                    </svg>

                                    <input
    type="text"
    wire:model.live.debounce.300ms="search"
    placeholder="Search applicants..."
    class="h-10 w-full rounded-lg border border-slate-200 bg-white pl-9 pr-3 text-sm outline-none transition placeholder:text-slate-400 focus:border-slate-400 focus:ring-2 focus:ring-slate-100 sm:w-64"
>

                                </div>


                                {{-- Trade --}}
                               <select
    wire:model.live="trade"
    class="h-10 rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-600 outline-none focus:border-slate-400 focus:ring-2 focus:ring-slate-100"
>
    <option value="">All Trades</option>

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


                                {{-- Points --}}
                                <select
    wire:model.live="pointsRange"
    class="h-10 rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-600 outline-none focus:border-slate-400 focus:ring-2 focus:ring-slate-100"
>
    <option value="">All Points</option>

    <option value="5-10">
        5–10 points
    </option>

    <option value="10-15">
        10–15 points
    </option>

    <option value="15-20">
        15–20 points
    </option>

    <option value="20-25">
        20–25 points
    </option>
</select>


                                {{-- Scholarship --}}
                                <select
    wire:model.live="scholarshipType"
    class="h-10 rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-600 outline-none focus:border-slate-400 focus:ring-2 focus:ring-slate-100"
>
    <option value="">All Scholarships</option>

    <option value="full">
        Full
    </option>

    <option value="partial">
        Partial
    </option>
</select>

                                {{-- Clear filters --}}
                                @if ($search || $trade || $pointsRange || $scholarshipType)

                                <button
                                    type="button"
                                    wire:click="clearFilters"
                                    class="h-10 rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-600 outline-none transition hover:bg-slate-50 focus:border-slate-400 focus:ring-2 focus:ring-slate-100"
                                >
                                    Clear Filters
                                </button>
                                @endif

                            </div>

                        </div>

                    </div>


                    {{-- Table --}}
                    <div class="overflow-x-auto">

                        <table class="w-full min-w-[1000px] text-left">

                            <thead class="bg-slate-50">

                                <tr class="border-b border-slate-200">

                                    <th class="px-6 py-3.5 text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                                        Rank
                                    </th>

                                    <th class="px-6 py-3.5 text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                                        Applicant
                                    </th>

                                    <th class="px-6 py-3.5 text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                                        Trade
                                    </th>

                                    <th class="px-6 py-3.5 text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                                        Points
                                    </th>

                                    <th class="px-6 py-3.5 text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                                        Subjects
                                    </th>

                                    <th class="px-6 py-3.5 text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                                        Scholarship
                                    </th>

                                    <th class="px-6 py-3.5 text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                                        Status
                                    </th>

                                    <th class="px-6 py-3.5 text-right text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                                        Action
                                    </th>

                                </tr>

                            </thead>


                            <tbody class="divide-y divide-slate-100">

                                @forelse ($applicants as $index => $applicant)

                                    <tr class="transition hover:bg-slate-50">


                                        {{-- Rank --}}
                                        <td class="whitespace-nowrap px-6 py-4">

                                            @if ($index === 0)

                                                <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-amber-100 text-sm font-bold text-amber-700">
                                                    1
                                                </span>

                                            @elseif ($index === 1)

                                                <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-200 text-sm font-bold text-slate-700">
                                                    2
                                                </span>

                                            @elseif ($index === 2)

                                                <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-orange-100 text-sm font-bold text-orange-700">
                                                    3
                                                </span>

                                            @else

                                                <span class="text-sm font-semibold text-slate-500">
                                                    {{ $index + 1 }}
                                                </span>

                                            @endif

                                        </td>


                                        {{-- Applicant --}}
                                        <td class="whitespace-nowrap px-6 py-4">

                                            <div class="flex items-center gap-3">

                                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-slate-900 text-xs font-bold text-white">
                                                    {{ strtoupper(substr($applicant->full_name, 0, 1)) }}
                                                </div>

                                                <div>

                                                    <p class="text-sm font-semibold text-slate-900">
                                                        {{ $applicant->full_name }}
                                                    </p>

                                                    <p class="mt-0.5 text-xs text-slate-400">
                                                        APP-{{ str_pad($applicant->id, 4, '0', STR_PAD_LEFT) }}
                                                    </p>

                                                </div>

                                            </div>

                                        </td>


                                        {{-- Trade --}}
                                        <td class="whitespace-nowrap px-6 py-4">

                                            <span class="text-sm text-slate-600">
                                                {{ $applicant->trade }}
                                            </span>

                                        </td>


                                        {{-- Points --}}
                                        <td class="whitespace-nowrap px-6 py-4">

                                            <span class="text-sm font-bold text-slate-900">
                                                {{ $applicant->total_points }}
                                            </span>

                                            <span class="text-xs text-slate-400">
                                                pts
                                            </span>

                                        </td>


                                        {{-- Subjects --}}
                                        <td class="whitespace-nowrap px-6 py-4">

                                            <span class="text-sm text-slate-600">
                                                {{ $applicant->total_subjects }}
                                            </span>

                                        </td>


                                        {{-- Scholarship --}}
                                        <td class="whitespace-nowrap px-6 py-4">

                                            @if ($applicant->scholarship_type === 'full')

                                                <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                                    Full
                                                </span>

                                            @else

                                                <span class="inline-flex rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">
                                                    Partial
                                                </span>

                                            @endif

                                        </td>


                                        {{-- Status --}}
                                        <td class="whitespace-nowrap px-6 py-4">

                                            @if ($applicant->status === 'approved')

                                                <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                                    Approved
                                                </span>

                                            @elseif ($applicant->status === 'partial')

                                                <span class="inline-flex rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">
                                                    {{ $applicant->discount_percentage }}% Award
                                                </span>

                                            @elseif ($applicant->status === 'rejected')

                                                <span class="inline-flex rounded-full bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-700">
                                                    Rejected
                                                </span>

                                            @else

                                                <span class="inline-flex rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">
                                                    Pending
                                                </span>

                                            @endif

                                        </td>


                                        {{-- Action --}}
                                        <td class="whitespace-nowrap px-6 py-4 text-right">

                                            <button
                                                type="button"
                                                wire:click="openApplicant({{ $applicant->id }})"
                                                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50"
                                            >

                                                More

                                                <svg
                                                    class="h-3.5 w-3.5"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M9 5l7 7-7 7"
                                                    />
                                                </svg>

                                            </button>

                                        </td>

                                    </tr>

                                @empty

                                    <tr>

                                        <td
                                            colspan="8"
                                            class="px-6 py-16 text-center"
                                        >

                                            <p class="text-sm font-semibold text-slate-900">
                                                No applicants found
                                            </p>

                                            <p class="mt-1 text-sm text-slate-500">
                                                Applicants will appear here once they are added.
                                            </p>

                                        </td>

                                    </tr>

                                @endforelse

                            </tbody>

                        </table>

                    </div>


                    {{-- Table footer --}}
                    <div class="flex flex-col gap-2 border-t border-slate-200 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">

                        <p class="text-xs text-slate-500">

    Showing
    <span class="font-semibold text-slate-700">
        {{ $applicants->count() }}
    </span>

    of

    <span class="font-semibold text-slate-700">
        {{ $totalApplicants }}
    </span>

    applicants

</p>

                    </div>

                </div>

            </div>

        </main>

    </div>
    {{-- =========================================================
     APPLICANT DETAILS DRAWER
========================================================= --}}

@if ($showApplicantDrawer && $selectedApplicant)

    <div
        class="fixed inset-0 z-50 overflow-hidden"
        wire:key="applicant-drawer-{{ $selectedApplicant->id }}"
    >

        {{-- Backdrop --}}
        <div
            class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm"
            wire:click="closeApplicant"
        ></div>


        {{-- Drawer --}}
        <div class="absolute inset-y-0 right-0 flex w-full max-w-xl">

            <div class="flex h-full w-full flex-col bg-white shadow-2xl">

                {{-- Header --}}
                <div class="flex items-center justify-between border-b border-slate-200 px-6 py-5">

                    <div>

                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                            Applicant Details
                        </p>

                        <h2 class="mt-1 text-lg font-bold text-slate-900">
                            {{ $selectedApplicant->full_name }}
                        </h2>

                    </div>

                    <button
                        type="button"
                        wire:click="closeApplicant"
                        class="rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                    >

                        <svg
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>

                    </button>

                </div>


                {{-- Content --}}
                <div class="flex-1 overflow-y-auto px-6 py-6">

                    {{-- Applicant identity --}}
                    <div class="flex items-center gap-4">

                        <div class="flex h-14 w-14 items-center justify-center rounded-full bg-slate-900 text-lg font-bold text-white">

                            {{ strtoupper(substr($selectedApplicant->full_name, 0, 1)) }}

                        </div>

                        <div>

                            <h3 class="font-bold text-slate-900">
                                {{ $selectedApplicant->full_name }}
                            </h3>

                            <p class="mt-1 text-sm text-slate-500">
                                Applicant #{{ str_pad($selectedApplicant->id, 4, '0', STR_PAD_LEFT) }}
                            </p>

                        </div>

                    </div>


                    {{-- Ranking summary --}}
                    <div class="mt-6 grid grid-cols-2 gap-3">

                        <div class="rounded-xl bg-slate-50 p-4">

                            <p class="text-xs font-medium text-slate-500">
                                Total Points
                            </p>

                            <p class="mt-2 text-2xl font-bold text-slate-900">
                                {{ $selectedApplicant->total_points }}
                            </p>

                        </div>

                        <div class="rounded-xl bg-slate-50 p-4">

                            <p class="text-xs font-medium text-slate-500">
                                Subjects
                            </p>

                            <p class="mt-2 text-2xl font-bold text-slate-900">
                                {{ $selectedApplicant->total_subjects }}
                            </p>

                        </div>

                    </div>


                    {{-- Personal information --}}
                    <div class="mt-8">

                        <h3 class="text-sm font-semibold text-slate-900">
                            Personal Information
                        </h3>

                        <div class="mt-3 divide-y divide-slate-100 rounded-xl border border-slate-200">

                            <div class="flex justify-between px-4 py-3">

                                <span class="text-sm text-slate-500">
                                    Full Name
                                </span>

                                <span class="text-sm font-medium text-slate-900">
                                    {{ $selectedApplicant->full_name }}
                                </span>

                            </div>

                            <div class="flex justify-between px-4 py-3">

                                <span class="text-sm text-slate-500">
                                    Gender
                                </span>

                                <span class="text-sm font-medium text-slate-900">
                                    {{ ucfirst($selectedApplicant->gender) }}
                                </span>

                            </div>

                            <div class="flex justify-between px-4 py-3">

                                <span class="text-sm text-slate-500">
                                    Trade
                                </span>

                                <span class="text-sm font-medium text-slate-900">
                                    {{ $selectedApplicant->trade }}
                                </span>

                            </div>

                            <div class="flex justify-between px-4 py-3">

                                <span class="text-sm text-slate-500">
                                    Scholarship Requested
                                </span>

                                <span class="text-sm font-medium text-slate-900">
                                    {{ ucfirst($selectedApplicant->scholarship_type) }}
                                </span>

                            </div>

                            <div class="flex justify-between px-4 py-3">

                                <span class="text-sm text-slate-500">
                                    Referrals
                                </span>

                                <span class="text-sm font-semibold text-slate-900">
                                    {{ $selectedApplicant->referrals_count ?? 0 }}
                                </span>

                            </div>

                        </div>

                    </div>

{{-- =========================================================
     GCE RESULTS
========================================================= --}}

{{-- =========================================================
     GCE RESULTS
========================================================= --}}

<div class="mt-8">

    <div class="flex items-center justify-between">

        <div>

            <h3 class="text-sm font-semibold text-slate-900">
                GCE Results
            </h3>

            <p class="mt-1 text-xs text-slate-500">
                Academic results used for ranking.
            </p>

        </div>

        <div class="rounded-lg bg-slate-900 px-3 py-2 text-right">

            <p class="text-[10px] font-medium uppercase tracking-wide text-slate-400">
                Total
            </p>

            <p class="text-lg font-bold text-white">
                {{ $selectedApplicant->total_points }}
            </p>

        </div>

    </div>


    <div class="mt-3 overflow-hidden rounded-xl border border-slate-200">

        @if ($selectedApplicant->subjects->count())

            <div class="divide-y divide-slate-100">

                @foreach ($selectedApplicant->subjects as $index => $subject)

                    <div class="flex items-center justify-between px-4 py-3">

                        {{-- Subject --}}
                        <div class="flex min-w-0 items-center gap-3">

                            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-slate-100 text-[11px] font-semibold text-slate-500">
                                {{ $index + 1 }}
                            </span>

                            <span class="truncate text-sm font-medium text-slate-800">
                                {{ $subject->subject_name }}
                            </span>

                        </div>


                        {{-- Grade + Points --}}
                        <div class="ml-4 flex shrink-0 items-center gap-3">

                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 text-sm font-bold text-slate-700">
                                {{ $subject->grade }}
                            </span>

                            <span class="w-5 text-right text-sm font-semibold text-slate-900">
                                {{ $subject->points }}
                            </span>

                        </div>

                    </div>

                @endforeach

            </div>


            {{-- Total --}}
            <div class="flex items-center justify-between border-t border-slate-200 bg-slate-50 px-4 py-3">

                <span class="text-sm font-semibold text-slate-600">
                    Total GCE Points
                </span>

                <span class="text-lg font-bold text-slate-900">
                    {{ $selectedApplicant->total_points }}
                </span>

            </div>

        @else

            <div class="px-4 py-8 text-center">

                <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-slate-100">

                    <svg
                        class="h-5 w-5 text-slate-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a2 2 0 011.414.586l4.414 4.414A2 2 0 0119 9v10a2 2 0 01-2 2z"
                        />
                    </svg>

                </div>

                <p class="mt-3 text-sm font-medium text-slate-700">
                    No GCE results recorded
                </p>

                <p class="mt-1 text-xs text-slate-400">
                    This applicant does not have subject results yet.
                </p>

            </div>

        @endif

    </div>

</div>


                    {{-- Current decision --}}
                    <div class="mt-8">

                        <h3 class="text-sm font-semibold text-slate-900">
                            Current Decision
                        </h3>

                        <div class="mt-3 rounded-xl border border-slate-200 p-4">

                            @if ($selectedApplicant->status === 'approved')

                                <div class="flex items-center justify-between">

                                    <div>

                                        <p class="font-semibold text-emerald-700">
                                            Full Scholarship
                                        </p>

                                        <p class="mt-1 text-xs text-slate-500">
                                            100% fee coverage
                                        </p>

                                    </div>

                                    <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                                        Approved
                                    </span>

                                </div>

                            @elseif ($selectedApplicant->status === 'partial')

                                <div class="flex items-center justify-between">

                                    <div>

                                        <p class="font-semibold text-blue-700">
                                            Partial Scholarship
                                        </p>

                                        <p class="mt-1 text-xs text-slate-500">
                                            {{ $selectedApplicant->discount_percentage }}% fee reduction
                                        </p>

                                    </div>

                                    <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                                        Awarded
                                    </span>

                                </div>

                            @elseif ($selectedApplicant->status === 'rejected')

                                <div class="flex items-center justify-between">

                                    <p class="font-semibold text-red-700">
                                        Application Rejected
                                    </p>

                                    <span class="rounded-full bg-red-50 px-3 py-1 text-xs font-semibold text-red-700">
                                        Rejected
                                    </span>

                                </div>

                            @else

                                <div class="flex items-center justify-between">

                                    <p class="font-semibold text-amber-700">
                                        Decision Pending
                                    </p>

                                    <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">
                                        Pending
                                    </span>

                                </div>

                            @endif

                        </div>

                    </div>

                </div>


                {{-- Footer --}}
                <div class="border-t border-slate-200 bg-white px-6 py-4">

                    <button
                        type="button"
                        wire:click="openDecisionModal"
                        class="w-full rounded-lg bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800"
                    >
                        Make Scholarship Decision
                    </button>

                </div>

            </div>

        </div>

    </div>

@endif
{{-- =========================================================
     DECISION MODAL
========================================================= --}}

@if ($showDecisionModal && $selectedApplicant)

    <div
        class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm"
    >

        <div
            class="w-full max-w-md rounded-2xl bg-white shadow-2xl"
            wire:key="decision-modal-{{ $selectedApplicant->id }}"
        >

            {{-- Header --}}
            <div class="border-b border-slate-200 px-6 py-5">

                <div class="flex items-center justify-between">

                    <div>

                        <h2 class="font-bold text-slate-900">
                            Scholarship Decision
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            {{ $selectedApplicant->full_name }}
                        </p>

                    </div>

                    <button
                        type="button"
                        wire:click="closeDecisionModal"
                        class="rounded-lg p-2 text-slate-400 hover:bg-slate-100"
                    >
                        ✕
                    </button>

                </div>

            </div>


            {{-- Body --}}
            <div class="space-y-4 px-6 py-6">

                <p class="text-sm text-slate-600">
                    Select the outcome for this applicant.
                </p>


                {{-- Full scholarship --}}
                <label
                    class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 p-4 transition hover:border-emerald-300 hover:bg-emerald-50/40"
                >

                    <input
                        type="radio"
                        wire:model.live="decision"
                        value="approved"
                        class="mt-1"
                    >

                    <div>

                        <p class="font-semibold text-slate-900">
                            Full Scholarship
                        </p>

                        <p class="mt-1 text-xs text-slate-500">
                            Award 100% coverage of the applicant's fees.
                        </p>

                    </div>

                </label>


                {{-- Partial --}}
                <label
                    class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 p-4 transition hover:border-blue-300 hover:bg-blue-50/40"
                >

                    <input
                        type="radio"
                        wire:model.live="decision"
                        value="partial"
                        class="mt-1"
                    >

                    <div class="flex-1">

                        <p class="font-semibold text-slate-900">
                            Partial Fee Reduction
                        </p>

                        <p class="mt-1 text-xs text-slate-500">
                            Specify the percentage of fees to waive.
                        </p>

                        @if ($decision === 'partial')

                            <div class="mt-3 flex items-center gap-2">

                                <input
                                    type="number"
                                    min="1"
                                    max="99"
                                    wire:model="discountPercentage"
                                    placeholder="e.g. 50"
                                    class="w-28 rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-slate-400 focus:ring-2 focus:ring-slate-100"
                                >

                                <span class="text-sm font-medium text-slate-500">
                                    % off
                                </span>

                            </div>

                        @endif

                    </div>

                </label>


                {{-- Reject --}}
                <label
                    class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 p-4 transition hover:border-red-300 hover:bg-red-50/40"
                >

                    <input
                        type="radio"
                        wire:model.live="decision"
                        value="rejected"
                        class="mt-1"
                    >

                    <div>

                        <p class="font-semibold text-slate-900">
                            Reject Application
                        </p>

                        <p class="mt-1 text-xs text-slate-500">
                            The applicant will not receive a scholarship award.
                        </p>

                    </div>

                </label>


                {{-- Validation errors --}}
                @error('decision')

                    <p class="text-sm text-red-600">
                        {{ $message }}
                    </p>

                @enderror


                @error('discountPercentage')

                    <p class="text-sm text-red-600">
                        {{ $message }}
                    </p>

                @enderror

            </div>


            {{-- Footer --}}
            <div class="flex gap-3 border-t border-slate-200 px-6 py-4">

                <button
                    type="button"
                    wire:click="closeDecisionModal"
                    class="flex-1 rounded-lg border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                >
                    Cancel
                </button>

                <button
                    type="button"
                    wire:click="makeDecision"
                    wire:loading.attr="disabled"
                    class="flex-1 rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800 disabled:opacity-50"
                >

                    <span wire:loading.remove wire:target="makeDecision">
                        Save Decision
                    </span>

                    <span wire:loading wire:target="makeDecision">
                        Saving...
                    </span>

                </button>

            </div>

        </div>

    </div>

@endif

</div>
