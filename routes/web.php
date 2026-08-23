<?php

use Illuminate\Support\Facades\Route;

Route::livewire('/', 'dashboard.applicant-ranking')
    ->name('dashboard');

Route::livewire('/results', 'results')
    ->name('results');

Route::livewire('/applicants/add', 'add-applicant')
    ->name('applicants.add');

Route::livewire('/referrals', 'referrals')
    ->name('referrals');