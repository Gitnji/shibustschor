<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Only add the unique constraint if it does not already exist
        $exists = DB::selectOne("
            SELECT 1
            FROM pg_constraint
            WHERE conname = 'referrals_applicant_id_unique'
        ");

        if (!$exists) {
            Schema::table('referrals', function (Blueprint $table) {
                $table->unique('applicant_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('referrals', function (Blueprint $table) {
            $table->dropUnique(['applicant_id']);
        });
    }
};