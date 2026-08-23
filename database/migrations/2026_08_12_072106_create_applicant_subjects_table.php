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
        Schema::create('applicant_subjects', function (Blueprint $table) {

            $table->id();

            $table->foreignId('applicant_id')
                ->constrained('applicants')
                ->cascadeOnDelete();

            $table->string('subject_name', 100);

            $table->string('grade', 1);

            $table->unsignedTinyInteger('points');

            $table->timestamps();

            $table->index([
                'applicant_id',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applicant_subjects');
    }
};