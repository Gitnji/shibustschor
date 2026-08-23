<?php

use App\Models\Applicant;
use Livewire\Component;

new class extends Component
{
    public string $search = '';

    public string $resultType = 'selected';

    public string $trade = '';
    public ?int $selectedApplicantId = null;

public bool $showApplicantModal = false;


public function openApplicant(int $id): void
{
    $this->selectedApplicantId = $id;

    $this->showApplicantModal = true;
}


public function closeApplicant(): void
{
    $this->selectedApplicantId = null;

    $this->showApplicantModal = false;
}


    public function render()
    {
        /*
        |--------------------------------------------------------------------------
        | Base query
        |--------------------------------------------------------------------------
        */

        $query = Applicant::query();


        /*
        |--------------------------------------------------------------------------
        | Result type
        |--------------------------------------------------------------------------
        */

        if ($this->resultType === 'selected') {

            $query->whereIn('status', [
                'approved',
                'partial',
            ]);

        } elseif ($this->resultType === 'rejected') {

            $query->where(
                'status',
                'rejected'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        $query->when(
            $this->search,
            function ($query) {

                $search = '%' . $this->search . '%';

                $query->where(function ($query) use ($search) {

                    $query
                        ->where(
                            'full_name',
                            'ILIKE',
                            $search
                        )
                        ->orWhere(
                            'trade',
                            'ILIKE',
                            $search
                        );

                });

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Trade
        |--------------------------------------------------------------------------
        */

        $query->when(
            $this->trade,
            fn ($query) => $query->where(
                'trade',
                $this->trade
            )
        );

        
         

        /*
        |--------------------------------------------------------------------------
        | Results
        |--------------------------------------------------------------------------
        */

        $applicants = $query
            ->orderByDesc('total_points')
            ->orderBy('full_name')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Statistics
        |--------------------------------------------------------------------------
        */

        $totalApplicants = Applicant::count();

        $totalSelected = Applicant::whereIn(
            'status',
            ['approved', 'partial']
        )->count();

        $fullAwards = Applicant::where(
            'status',
            'approved'
        )->count();

        $partialAwards = Applicant::where(
            'status',
            'partial'
        )->count();

        $rejectedCount = Applicant::where(
            'status',
            'rejected'
        )->count();

        $pendingCount = Applicant::where(
            'status',
            'pending'
        )->count();

        /*
|--------------------------------------------------------------------------
| Current result statistics
|--------------------------------------------------------------------------
*/

$currentFullAwards = $applicants
    ->where('status', 'approved')
    ->count();

$currentPartialAwards = $applicants
    ->where('status', 'partial')
    ->count();

$currentAveragePoints = round(
    $applicants->avg('total_points') ?? 0,
    1
);

$selectedApplicant = $this->selectedApplicantId
    ? Applicant::find($this->selectedApplicantId)
    : null;


        return $this->view([
            'applicants' => $applicants,

            'totalApplicants' => $totalApplicants,
            'totalSelected' => $totalSelected,
            'fullAwards' => $fullAwards,
            'partialAwards' => $partialAwards,
            'rejectedCount' => $rejectedCount,
            'pendingCount' => $pendingCount,
            'currentFullAwards' => $currentFullAwards,
            'currentPartialAwards' => $currentPartialAwards,
            'currentAveragePoints' => $currentAveragePoints,
            'selectedApplicant' => $selectedApplicant,
        ]);
    }
};
?>

<div class="p-6 lg:p-8">

    {{-- =========================================================
         PAGE HEADER
    ========================================================== --}}

    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">

        <div>

            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                Scholarship Program
            </p>

            <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">
                Final Results
            </h1>

            <p class="mt-1 text-sm text-slate-500">
                Review scholarship awards and final applicant decisions.
            </p>

        </div>

        

        <div class="text-sm text-slate-500">

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

    </a><br>

            @if ($pendingCount > 0)

                <span class="font-medium text-amber-600">
                    {{ $pendingCount }}
                </span>

                applicant{{ $pendingCount === 1 ? '' : 's' }}
                still pending

            @else

                <span class="font-medium text-emerald-600">
                    All applications decided
                </span>

            @endif

        </div>

    </div>


    {{-- =========================================================
         STATISTICS
    ========================================================== --}}

    <div class="mt-7 grid gap-4 sm:grid-cols-2 xl:grid-cols-5">

        {{-- Total --}}
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">

            <p class="text-sm font-medium text-slate-500">
                Total Applicants
            </p>

            <p class="mt-2 text-2xl font-bold text-slate-900">
                {{ $totalApplicants }}
            </p>

        </div>


        {{-- Selected --}}
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">

            <p class="text-sm font-medium text-slate-500">
                Selected
            </p>

            <p class="mt-2 text-2xl font-bold text-emerald-600">
                {{ $totalSelected }}
            </p>

        </div>


        {{-- Full --}}
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">

            <p class="text-sm font-medium text-slate-500">
                Full Scholarships
            </p>

            <p class="mt-2 text-2xl font-bold text-slate-900">
                {{ $fullAwards }}
            </p>

        </div>


        {{-- Partial --}}
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">

            <p class="text-sm font-medium text-slate-500">
                Partial Awards
            </p>

            <p class="mt-2 text-2xl font-bold text-blue-600">
                {{ $partialAwards }}
            </p>

        </div>


        {{-- Rejected --}}
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">

            <p class="text-sm font-medium text-slate-500">
                Rejected
            </p>

            <p class="mt-2 text-2xl font-bold text-red-600">
                {{ $rejectedCount }}
            </p>

        </div>

    </div>

    {{-- =========================================================
     CURRENT RESULT SUMMARY
========================================================= --}}

<div class="mt-6 grid gap-4 sm:grid-cols-3">

    <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">

        <div class="flex items-center justify-between">

            <p class="text-sm font-medium text-slate-500">
                100% Awards
            </p>

            <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700">
                Full
            </span>

        </div>

        <p class="mt-3 text-2xl font-bold text-slate-900">
            {{ $currentFullAwards }}
        </p>

    </div>


    <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">

        <div class="flex items-center justify-between">

            <p class="text-sm font-medium text-slate-500">
                Partial Awards
            </p>

            <span class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-bold text-blue-700">
                Partial
            </span>

        </div>

        <p class="mt-3 text-2xl font-bold text-slate-900">
            {{ $currentPartialAwards }}
        </p>

    </div>


    <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">

        <div class="flex items-center justify-between">

            <p class="text-sm font-medium text-slate-500">
                Average Points
            </p>

            <span class="text-xs font-medium text-slate-400">
                Selected
            </span>

        </div>

        <p class="mt-3 text-2xl font-bold text-slate-900">
            {{ $currentAveragePoints }}
        </p>

    </div>

</div>


    {{-- =========================================================
         RESULTS CARD
    ========================================================== --}}

    <div class="mt-8 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

        {{-- =========================================================
     RESULT CATEGORIES
========================================================= --}}

<div class="border-b border-slate-200 px-5 pt-4 sm:px-6">

    <div class="flex gap-6 overflow-x-auto">

        <button
            type="button"
            wire:click="$set('trade', '')"
            class="{{ $trade === '' ? 'border-slate-900 text-slate-900' : 'border-transparent text-slate-500 hover:text-slate-700' }} whitespace-nowrap border-b-2 pb-3 text-sm font-semibold transition"
        >
            All Results
        </button>


        <button
            type="button"
            wire:click="$set('trade', 'Science')"
            class="{{ $trade === 'Science' ? 'border-slate-900 text-slate-900' : 'border-transparent text-slate-500 hover:text-slate-700' }} whitespace-nowrap border-b-2 pb-3 text-sm font-semibold transition"
        >
            Science
        </button>


        <button
            type="button"
            wire:click="$set('trade', 'Arts')"
            class="{{ $trade === 'Arts' ? 'border-slate-900 text-slate-900' : 'border-transparent text-slate-500 hover:text-slate-700' }} whitespace-nowrap border-b-2 pb-3 text-sm font-semibold transition"
        >
            Arts
        </button>


        <button
            type="button"
            wire:click="$set('trade', 'Commercial')"
            class="{{ $trade === 'Commercial' ? 'border-slate-900 text-slate-900' : 'border-transparent text-slate-500 hover:text-slate-700' }} whitespace-nowrap border-b-2 pb-3 text-sm font-semibold transition"
        >
            Commercial
        </button>

    </div>

</div>

        {{-- Header --}}

        <div class="border-b border-slate-200 px-5 py-5 sm:px-6">

            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

                <div>

                    <h2 class="font-semibold text-slate-900">
                        {{ $resultType === 'selected' ? 'Selected Applicants' : 'Rejected Applicants' }}
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        {{ $applicants->count() }}
                        result{{ $applicants->count() === 1 ? '' : 's' }}
                        displayed
                    </p>

                </div>


                {{-- Filters --}}

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
                                d="m21 21-4.35-4.35m2.35-5.65a8 8 0 1 1-16 0 8 8 0 0 1 16 0Z"
                            />

                        </svg>

                        <input
                            type="text"
                            wire:model.live.debounce.300ms="search"
                            placeholder="Search applicant..."
                            class="h-10 w-full rounded-lg border border-slate-200 bg-white pl-9 pr-3 text-sm outline-none transition focus:border-slate-400 focus:ring-2 focus:ring-slate-100 sm:w-52"
                        >

                    </div>


                    {{-- Result type --}}

                    <select
                        wire:model.live="resultType"
                        class="h-10 rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-600 outline-none focus:border-slate-400 focus:ring-2 focus:ring-slate-100"
                    >

                        <option value="selected">
                            Selected
                        </option>

                        <option value="rejected">
                            Rejected
                        </option>

                    </select>


                    {{-- Trade --}}

                    <select
    wire:model.live="trade"
    class="h-10 rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-600 outline-none focus:border-slate-400 focus:ring-2 focus:ring-slate-100"
>

    <option value="">
        Trade
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

                </div>

            </div>

        </div>


        {{-- =====================================================
             TABLE
        ====================================================== --}}

        <div class="overflow-x-auto">

            <table class="w-full text-left">

                <thead class="border-b border-slate-100 bg-slate-50">

                    <tr>

                        <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500 sm:px-6">
                            Rank
                        </th>

                        <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Applicant
                        </th>

                        <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Trade
                        </th>

                        <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Points
                        </th>

                        @if ($resultType === 'selected')

                            <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Requested
                            </th>

                            <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Award
                            </th>

                        @else

                            <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Requested
                            </th>

                            <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Status
                            </th>

                        @endif

                    </tr>

                </thead>


                <tbody class="divide-y divide-slate-100">

                    @forelse ($applicants as $index => $applicant)

                        <tr class="transition hover:bg-slate-50">

                            {{-- Rank --}}

                            <td class="px-5 py-4 sm:px-6">

                                @if ($index === 0)

                                    <span class="inline-flex h-7 min-w-7 items-center justify-center rounded-full bg-amber-50 px-2 text-xs font-bold text-amber-700">
                                        #1
                                    </span>

                                @elseif ($index === 1)

                                    <span class="inline-flex h-7 min-w-7 items-center justify-center rounded-full bg-slate-100 px-2 text-xs font-bold text-slate-700">
                                        #2
                                    </span>

                                @elseif ($index === 2)

                                    <span class="inline-flex h-7 min-w-7 items-center justify-center rounded-full bg-orange-50 px-2 text-xs font-bold text-orange-700">
                                        #3
                                    </span>

                                @else

                                    <span class="font-semibold text-slate-500">
                                        #{{ $index + 1 }}
                                    </span>

                                @endif

                            </td>


                            {{-- Applicant --}}

                            <td class="px-5 py-4">

                                <div class="flex items-center gap-3">

                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-slate-100 text-xs font-bold text-slate-700">

                                        {{ strtoupper(substr($applicant->full_name, 0, 1)) }}

                                    </div>

                                    <div>

                                        <button
                                            type="button"
                                            wire:click="openApplicant({{ $applicant->id }})"
                                            class="text-left font-medium text-slate-900 transition hover:text-slate-600"
                                        >
                                            {{ $applicant->full_name }}
                                        </button>

                                        <div class="flex items-center gap-2">

    <p class="text-xs text-slate-400">
        Applicant #{{ str_pad($applicant->id, 4, '0', STR_PAD_LEFT) }}
    </p>

    <span class="text-xs text-slate-300">
        •
    </span>

    <span class="text-xs font-medium text-slate-400">
        View
    </span>

</div>

                                    </div>

                                </div>

                            </td>


                            {{-- Trade --}}

                            <td class="px-5 py-4 text-sm text-slate-600">
                                {{ $applicant->trade }}
                            </td>


                            {{-- Points --}}

                            <td class="px-5 py-4">

                                <span class="font-bold text-slate-900">
                                    {{ $applicant->total_points }}
                                </span>

                            </td>


                            {{-- Requested --}}

                            <td class="px-5 py-4 text-sm text-slate-600">
                                {{ ucfirst($applicant->scholarship_type) }}
                            </td>


                            @if ($resultType === 'selected')

                                {{-- Award --}}

                                <td class="px-5 py-4">

                                    @if ($applicant->status === 'approved')

                                        <div class="flex flex-col">

                                            <span class="w-fit rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">
                                                100%
                                            </span>

                                            <span class="mt-1 text-xs text-slate-400">
                                                Full scholarship
                                            </span>

                                        </div>

                                    @else

                                        <div class="flex flex-col">

                                            <span class="w-fit rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700">
                                                {{ $applicant->discount_percentage }}%
                                            </span>

                                            <span class="mt-1 text-xs text-slate-400">
                                                Fee reduction
                                            </span>

                                        </div>

                                    @endif

                                </td>

                            @else

                                {{-- Rejected --}}

                                <td class="px-5 py-4">

                                    <span class="inline-flex rounded-full bg-red-50 px-3 py-1 text-xs font-semibold text-red-700">
                                        Rejected
                                    </span>

                                </td>

                            @endif

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="6"
                                class="px-6 py-14 text-center"
                            >

                                <div class="mx-auto max-w-sm">

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

                                    <p class="mt-4 font-semibold text-slate-900">
                                        No results found
                                    </p>

                                    <p class="mt-1 text-sm text-slate-500">
                                        Try changing your search or filters.
                                    </p>

                                </div>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- Footer --}}

        @if ($applicants->count() > 0)

            <div class="border-t border-slate-100 bg-slate-50 px-5 py-3 sm:px-6">

                <p class="text-xs text-slate-500">

                    Showing
                    <span class="font-semibold text-slate-700">
                        {{ $applicants->count() }}
                    </span>

                    result{{ $applicants->count() === 1 ? '' : 's' }}

                </p>

            </div>

        @endif

    </div>

    {{-- =========================================================
     RESULT DETAILS MODAL
========================================================= --}}

@if ($showApplicantModal && $selectedApplicant)

    <div
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm"
    >

        <div
            class="w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-2xl"
            wire:key="result-modal-{{ $selectedApplicant->id }}"
        >

            {{-- Header --}}

            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-5">

                <div>

                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                        Final Result
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


            {{-- Body --}}

            <div class="space-y-6 px-6 py-6">

                {{-- Applicant identity --}}

                <div class="flex items-center gap-4">

                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-slate-900 text-lg font-bold text-white">

                        {{ strtoupper(substr($selectedApplicant->full_name, 0, 1)) }}

                    </div>

                    <div>

                        <p class="font-semibold text-slate-900">
                            {{ $selectedApplicant->full_name }}
                        </p>

                        <p class="mt-1 text-sm text-slate-500">
                            {{ $selectedApplicant->trade }}
                            ·
                            {{ ucfirst($selectedApplicant->gender) }}
                        </p>

                    </div>

                </div>


                {{-- Ranking --}}

                <div class="grid grid-cols-2 gap-3">

                    <div class="rounded-xl bg-slate-50 p-4">

                        <p class="text-xs font-medium text-slate-500">
                            Points
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


                {{-- Scholarship information --}}

                <div>

                    <h3 class="text-sm font-semibold text-slate-900">
                        Scholarship Decision
                    </h3>


                    <div class="mt-3 rounded-xl border border-slate-200">

                        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">

                            <span class="text-sm text-slate-500">
                                Requested
                            </span>

                            <span class="text-sm font-semibold text-slate-900">
                                {{ ucfirst($selectedApplicant->scholarship_type) }}
                            </span>

                        </div>


                        <div class="flex items-center justify-between px-4 py-4">

                            <span class="text-sm text-slate-500">
                                Final Award
                            </span>


                            @if ($selectedApplicant->status === 'approved')

                                <span class="rounded-full bg-emerald-50 px-3 py-1.5 text-sm font-bold text-emerald-700">
                                    100% Scholarship
                                </span>

                            @elseif ($selectedApplicant->status === 'partial')

                                <span class="rounded-full bg-blue-50 px-3 py-1.5 text-sm font-bold text-blue-700">
                                    {{ $selectedApplicant->discount_percentage }}% Fee Reduction
                                </span>

                            @else

                                <span class="rounded-full bg-red-50 px-3 py-1.5 text-sm font-bold text-red-700">
                                    Rejected
                                </span>

                            @endif

                        </div>

                    </div>

                </div>


                {{-- GCE summary --}}

                <div>

                    <h3 class="text-sm font-semibold text-slate-900">
                        Academic Summary
                    </h3>

                    <div class="mt-3 rounded-xl border border-slate-200 p-4">

                        <div class="flex items-center justify-between">

                            <span class="text-sm text-slate-500">
                                Total GCE Points
                            </span>

                            <span class="font-bold text-slate-900">
                                {{ $selectedApplicant->total_points }}
                            </span>

                        </div>

                        <div class="mt-3 flex items-center justify-between">

                            <span class="text-sm text-slate-500">
                                Total Subjects
                            </span>

                            <span class="font-bold text-slate-900">
                                {{ $selectedApplicant->total_subjects }}
                            </span>

                        </div>

                    </div>

                </div>

            </div>


            {{-- Footer --}}

            <div class="border-t border-slate-200 bg-slate-50 px-6 py-4">

                <button
                    type="button"
                    wire:click="closeApplicant"
                    class="w-full rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800"
                >
                    Close
                </button>

            </div>

        </div>

    </div>

@endif

</div>