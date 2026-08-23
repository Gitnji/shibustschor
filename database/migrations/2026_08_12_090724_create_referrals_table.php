<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();

            $table->string('full_name', 255);

            $table->unsignedInteger('referral_count')->default(0);

            $table->foreignId('applicant_id')
    ->nullable()
    ->unique()
    ->constrained('applicants')
    ->nullOnDelete();
            $table->timestamps();

            $table->index('full_name');
            $table->index('referral_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referrals');
    }
};