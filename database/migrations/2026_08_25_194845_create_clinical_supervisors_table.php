<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinical_supervisors', function (Blueprint $table) {
            $table->id();

            $table->string('full_name', 150);

            $table->string('facility_name', 200);

            $table->string('profession', 100)->nullable();

            $table->string('position_title', 100)->nullable();

            $table->string('registration_number', 100)->nullable();

            $table->string('reference_face_path')->nullable();

            $table->boolean('is_verified_supervisor')->default(false);

            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinical_supervisors');
    }
};