<?php

use App\Models\Referral;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $search = '';

    public string $filter = 'all';

    public string $sort = 'referrals';

    public string $direction = 'desc';

    public bool $showModal = false;

public ?int $editingReferralId = null;

public string $fullName = '';

public int|string $referralCount = 0;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilter(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $column): void
    {
        if ($this->sort === $column) {
            $this->direction = $this->direction === 'asc'
                ? 'desc'
                : 'asc';

            return;
        }

        $this->sort = $column;
        $this->direction = 'asc';
    }

    public function openAddModal(): void
{
    $this->resetValidation();

    $this->editingReferralId = null;

    $this->fullName = '';

    $this->referralCount = 0;

    $this->showModal = true;
}

public function openEditModal(int $id): void
{
    $this->resetValidation();

    $referral = Referral::findOrFail($id);

    $this->editingReferralId = $referral->id;

    $this->fullName = $referral->full_name;

    $this->referralCount = $referral->referral_count;

    $this->showModal = true;
}

public function closeModal(): void
{
    $this->showModal = false;

    $this->editingReferralId = null;

    $this->fullName = '';

    $this->referralCount = 0;

    $this->resetValidation();
}

public function saveReferral(): void
{
    $validated = $this->validate([
        'fullName' => [
            'required',
            'string',
            'max:255',
        ],

        'referralCount' => [
            'required',
            'integer',
            'min:0',
        ],
    ], [
        'fullName.required' => 'Please enter the referrer\'s name.',

        'referralCount.required' => 'Please enter the number of referrals.',

        'referralCount.integer' => 'Referral count must be a whole number.',

        'referralCount.min' => 'Referral count cannot be negative.',
    ]);

    if ($this->editingReferralId) {

        $referral = Referral::findOrFail(
            $this->editingReferralId
        );

        $referral->update([
            'full_name' => $validated['fullName'],
            'referral_count' => $validated['referralCount'],
        ]);

        session()->flash(
            'success',
            'Referrer updated successfully.'
        );

    } else {

        Referral::create([
            'full_name' => $validated['fullName'],
            'referral_count' => $validated['referralCount'],
            'applicant_id' => null,
        ]);

        session()->flash(
            'success',
            'Referrer added successfully.'
        );
    }

    $this->closeModal();

    $this->resetPage();
}

    #[Computed]
    public function referrers()
    {
        return Referral::query()
            ->with('applicant')
            ->when(
                $this->search !== '',
                function ($query) {
                    $query->where(
                        'full_name',
                        'ilike',
                        '%' . $this->search . '%'
                    );
                }
            )
            ->when(
                $this->filter === 'applicants',
                fn ($query) => $query->whereNotNull('applicant_id')
            )
            ->when(
                $this->filter === 'non_applicants',
                fn ($query) => $query->whereNull('applicant_id')
            )
            ->orderBy(
                $this->sort === 'name'
                    ? 'full_name'
                    : 'referral_count',
                $this->direction
            )
            ->paginate(10);
    }

    #[Computed]
    public function totalReferrers(): int
    {
        return Referral::count();
    }

    #[Computed]
    public function totalReferrals(): int
    {
        return Referral::sum('referral_count');
    }

    #[Computed]
    public function topReferrer(): ?Referral
    {
        return Referral::query()
            ->orderByDesc('referral_count')
            ->first();
    }
};
?>

<div class="min-h-screen bg-slate-50">
    @if (session('success'))

    <div class="mx-auto max-w-7xl px-6 pt-4 lg:px-8">

        <div class="flex items-center gap-3 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3">

            <svg
                class="h-5 w-5 shrink-0 text-emerald-600"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M5 13l4 4L19 7"
                />
            </svg>

            <p class="text-sm font-medium text-emerald-700">
                {{ session('success') }}
            </p>

        </div>

    </div>

@endif

    {{-- =====================================================
         PAGE HEADER
    ====================================================== --}}

    

    <div class="border-b border-slate-200 bg-white">

        <div class="mx-auto max-w-7xl px-6 py-6 lg:px-8">

            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                <div>

                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                        Scholarship Program
                    </p>

                    <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">
                        Referrals
                    </h1>

                    <p class="mt-1 text-sm text-slate-500">
                        Track people who refer applicants to the scholarship program.
                    </p>

                </div>
                


    {{-- Add button will be connected to the modal in Step 3 --}}
    
    <button
    type="button"
    wire:click="openAddModal"
    class="inline-flex items-center justify-center gap-2 rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800"
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

    Add Referrer

</button>

<a href="{{ route("dashboard") }}">
<button type="button"
    class="inline-flex items-center justify-center gap-2 rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800"
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
</button>
</a>
</button>



            </div>

        </div>

    </div>


    {{-- =====================================================
         CONTENT
    ====================================================== --}}

    <div class="mx-auto max-w-7xl px-6 py-6 lg:px-8">


        {{-- =================================================
             SUMMARY CARDS
        ================================================== --}}

        <div class="grid gap-4 md:grid-cols-3">


            {{-- Total Referrers --}}
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">

                <div class="flex items-start justify-between">

                    <div>

                        <p class="text-sm font-medium text-slate-500">
                            Total Referrers
                        </p>

                        <p class="mt-2 text-3xl font-bold text-slate-900">
                            {{ $this->totalReferrers }}
                        </p>

                        <p class="mt-1 text-xs text-slate-400">
                            People registered as referrers
                        </p>

                    </div>


                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-100">

                        <svg
                            class="h-5 w-5 text-slate-600"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                            />
                        </svg>

                    </div>

                </div>

            </div>


            {{-- Total Referrals --}}
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">

                <div class="flex items-start justify-between">

                    <div>

                        <p class="text-sm font-medium text-slate-500">
                            Total Referrals
                        </p>

                        <p class="mt-2 text-3xl font-bold text-slate-900">
                            {{ $this->totalReferrals }}
                        </p>

                        <p class="mt-1 text-xs text-slate-400">
                            Applicants referred
                        </p>

                    </div>


                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-100">

                        <svg
                            class="h-5 w-5 text-slate-600"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M18 9l-6-6-6 6m12 0v6a2 2 0 01-2 2H8a2 2 0 01-2-2V9m12 0H6"
                            />
                        </svg>

                    </div>

                </div>

            </div>


            {{-- Top Referrer --}}
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">

                <div class="flex items-start justify-between">

                    <div class="min-w-0">

                        <p class="text-sm font-medium text-slate-500">
                            Top Referrer
                        </p>

                        @if ($this->topReferrer)

                            <p class="mt-2 truncate text-lg font-bold text-slate-900">
                                {{ $this->topReferrer->full_name }}
                            </p>

                            <p class="mt-1 text-xs text-slate-400">
                                {{ $this->topReferrer->referral_count }} referrals
                            </p>

                        @else

                            <p class="mt-2 text-lg font-bold text-slate-400">
                                No referrers yet
                            </p>

                        @endif

                    </div>


                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-slate-100">

                        <svg
                            class="h-5 w-5 text-slate-600"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622C17.176 19.29 21 14.591 21 9c0-1.042-.133-2.052-.382-3.016z"
                            />
                        </svg>

                    </div>

                </div>

            </div>

        </div>


        {{-- =================================================
             TABLE CARD
        ================================================== --}}

        <div class="mt-6 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">


            {{-- Toolbar --}}
            <div class="border-b border-slate-200 p-4">

                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">


                    {{-- Search --}}
                    <div class="relative w-full lg:max-w-sm">

                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">

                            <svg
                                class="h-4 w-4 text-slate-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M21 21l-4.35-4.35m2.35-5.65a8 8 0 11-16 0 8 8 0 0116 0z"
                                />
                            </svg>

                        </div>


                        <input
                            type="text"
                            wire:model.live.debounce.300ms="search"
                            placeholder="Search referrer..."
                            class="w-full rounded-lg border border-slate-200 bg-white py-2.5 pl-9 pr-4 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-slate-400 focus:ring-2 focus:ring-slate-100"
                        >

                    </div>


                    {{-- Filters --}}
                    <div class="flex flex-wrap gap-2">

                        <button
                            type="button"
                            wire:click="$set('filter', 'all')"
                            class="{{ $filter === 'all' ? 'bg-slate-900 text-white' : 'bg-white text-slate-600 hover:bg-slate-50' }} rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold transition"
                        >
                            All
                        </button>

                        <button
                            type="button"
                            wire:click="$set('filter', 'applicants')"
                            class="{{ $filter === 'applicants' ? 'bg-slate-900 text-white' : 'bg-white text-slate-600 hover:bg-slate-50' }} rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold transition"
                        >
                            Applicants
                        </button>

                        <button
                            type="button"
                            wire:click="$set('filter', 'non_applicants')"
                            class="{{ $filter === 'non_applicants' ? 'bg-slate-900 text-white' : 'bg-white text-slate-600 hover:bg-slate-50' }} rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold transition"
                        >
                            Non-applicants
                        </button>

                    </div>

                </div>

            </div>


            {{-- Table --}}
            <div class="overflow-x-auto">

                <table class="min-w-full">

                    <thead class="bg-slate-50">

                        <tr>

                            <th class="w-16 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-400">
                                #
                            </th>


                            <th class="px-5 py-3 text-left">

                                <button
                                    type="button"
                                    wire:click="sortBy('name')"
                                    class="flex items-center gap-1 text-xs font-semibold uppercase tracking-wide text-slate-400 hover:text-slate-700"
                                >
                                    Name

                                    @if ($sort === 'name')
                                        <span>{{ $direction === 'asc' ? '↑' : '↓' }}</span>
                                    @endif

                                </button>

                            </th>


                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-400">
                                Type
                            </th>


                            <th class="px-5 py-3 text-right">

                                <button
                                    type="button"
                                    wire:click="sortBy('referrals')"
                                    class="ml-auto flex items-center gap-1 text-xs font-semibold uppercase tracking-wide text-slate-400 hover:text-slate-700"
                                >
                                    Referrals

                                    @if ($sort === 'referrals')
                                        <span>{{ $direction === 'asc' ? '↑' : '↓' }}</span>
                                    @endif

                                </button>

                            </th>


                            <th class="w-28 px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-400">
                                Action
                            </th>

                        </tr>

                    </thead>


                    <tbody class="divide-y divide-slate-100">

                        @forelse ($this->referrers as $index => $referrer)

                            <tr class="transition hover:bg-slate-50">

                                <td class="px-5 py-4 text-sm text-slate-400">
                                    {{ $this->referrers->firstItem() + $index }}
                                </td>


                                <td class="px-5 py-4">

                                    <div class="flex items-center gap-3">

                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-slate-900 text-xs font-bold text-white">

                                            {{ strtoupper(substr($referrer->full_name, 0, 1)) }}

                                        </div>


                                        <div class="min-w-0">

                                            <p class="truncate text-sm font-semibold text-slate-900">
                                                {{ $referrer->full_name }}
                                            </p>

                                            @if ($referrer->applicant)

                                                <p class="text-xs text-slate-400">
                                                    Applicant #{{ str_pad($referrer->applicant->id, 4, '0', STR_PAD_LEFT) }}
                                                </p>

                                            @endif

                                        </div>

                                    </div>

                                </td>


                                <td class="px-5 py-4">

                                    @if ($referrer->applicant)

                                        <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                            Applicant
                                        </span>

                                    @else

                                        <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">
                                            Non-applicant
                                        </span>

                                    @endif

                                </td>


                                <td class="px-5 py-4 text-right">

                                    <span class="text-sm font-bold text-slate-900">
                                        {{ $referrer->referral_count }}
                                    </span>

                                </td>


                                <td class="px-5 py-4 text-right">

                                    {{-- Edit will be enabled in Step 3 --}}
                                    <button
    type="button"
    wire:click="openEditModal({{ $referrer->id }})"
    class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-50 hover:text-slate-900"
>
    Edit
</button>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="5" class="px-5 py-12 text-center">

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
                                                stroke-width="2"
                                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"
                                            />
                                        </svg>

                                    </div>

                                    <p class="mt-3 text-sm font-semibold text-slate-700">
                                        No referrers found
                                    </p>

                                    <p class="mt-1 text-xs text-slate-400">
                                        Add a referrer to start tracking referrals.
                                    </p>

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>


            {{-- Pagination --}}
            @if ($this->referrers->hasPages())

                <div class="border-t border-slate-200 px-5 py-4">

                    {{ $this->referrers->links() }}

                </div>

            @endif

        </div>

    </div>

    {{-- =========================================================
     ADD / EDIT REFERRER MODAL
========================================================= --}}

@if ($showModal)

    <div
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        wire:key="referral-modal"
    >

        {{-- Backdrop --}}
        <div
            class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm"
            wire:click="closeModal"
        ></div>


        {{-- Modal --}}
        <div class="relative w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl">


            {{-- Header --}}
            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-5">

                <div>

                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                        Referrals
                    </p>

                    <h2 class="mt-1 text-lg font-bold text-slate-900">

                        {{ $editingReferralId ? 'Edit Referrer' : 'Add Referrer' }}

                    </h2>

                </div>


                <button
                    type="button"
                    wire:click="closeModal"
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


            {{-- Form --}}
            <div class="space-y-5 px-6 py-6">


                {{-- Name --}}
                <div>

                    <label class="text-sm font-medium text-slate-700">
                        Full Name
                    </label>

                    <input
                        type="text"
                        wire:model="fullName"
                        placeholder="Enter referrer's name"
                        class="mt-2 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-slate-400 focus:ring-2 focus:ring-slate-100"
                    >

                    @error('fullName')

                        <p class="mt-1.5 text-xs text-red-600">
                            {{ $message }}
                        </p>

                    @enderror

                </div>


                {{-- Referral Count --}}
                <div>

                    <label class="text-sm font-medium text-slate-700">
                        Number of People Referred
                    </label>

                    <input
                        type="number"
                        min="0"
                        wire:model="referralCount"
                        placeholder="0"
                        class="mt-2 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-slate-400 focus:ring-2 focus:ring-slate-100"
                    >

                    <p class="mt-1.5 text-xs text-slate-400">
                        You can update this number later when they refer more applicants.
                    </p>

                    @error('referralCount')

                        <p class="mt-1.5 text-xs text-red-600">
                            {{ $message }}
                        </p>

                    @enderror

                </div>

            </div>


            {{-- Footer --}}
            <div class="flex items-center justify-end gap-3 border-t border-slate-200 bg-slate-50 px-6 py-4">

                <button
                    type="button"
                    wire:click="closeModal"
                    class="rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50"
                >
                    Cancel
                </button>


                <button
                    type="button"
                    wire:click="saveReferral"
                    wire:loading.attr="disabled"
                    wire:target="saveReferral"
                    class="rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-60"
                >

                    <span
                        wire:loading.remove
                        wire:target="saveReferral"
                    >
                        {{ $editingReferralId ? 'Save Changes' : 'Add Referrer' }}
                    </span>


                    <span
                        wire:loading
                        wire:target="saveReferral"
                    >
                        Saving...
                    </span>

                </button>

            </div>

        </div>

    </div>

@endif

</div>