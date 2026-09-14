<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supervisor_verifications', function (Blueprint $table) {
            $table->decimal('face_lbph_distance', 10, 4)
                ->nullable()
                ->after('face_match_passed');

            $table->string('face_comparison_decision', 30)
                ->nullable()
                ->after('face_lbph_distance');
        });
    }

    public function down(): void
    {
        Schema::table('supervisor_verifications', function (Blueprint $table) {
            $table->dropColumn([
                'face_lbph_distance',
                'face_comparison_decision',
            ]);
        });
    }
};