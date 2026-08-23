<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // The referrals table migration already creates this unique index.
    }

    public function down(): void
    {
        // The referrals table migration owns the unique index.
    }
};
