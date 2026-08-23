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
        Schema::create('applicants', function (Blueprint $table) {
            $table->id();

            $table->string('full_name');
            $table->string('gender');
            $table->string('trade');

            $table->enum('scholarship_type', [
                'full',
                'partial',
            ]);

            $table->unsignedInteger('total_subjects');
            $table->unsignedInteger('total_points')->default(0);

            $table->enum('status', [
                'pending',
                'approved',
                'partial',
                'rejected',
            ])->default('pending');

            $table->unsignedTinyInteger('discount_percentage')
                ->default(0);

            $table->text('decision_note')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applicants');
    }
};