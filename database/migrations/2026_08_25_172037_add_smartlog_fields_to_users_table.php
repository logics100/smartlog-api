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
    Schema::table('users', function (Blueprint $table) {

        $table->string('dwu_id', 50)
              ->nullable()
              ->unique();

        $table->foreignId('department_id')
              ->nullable()
              ->constrained('departments')
              ->nullOnDelete();

        $table->foreignId('year_level_id')
              ->nullable()
              ->constrained('year_levels')
              ->nullOnDelete();

        $table->enum('role', [
            'ICT_ADMIN',
            'HOD',
            'LECTURER',
            'STUDENT'
        ])->default('STUDENT');

        $table->string('phone', 30)->nullable();

        $table->boolean('is_active')->default(true);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
{
    Schema::table('users', function (Blueprint $table) {

        $table->dropForeign(['department_id']);
        $table->dropForeign(['year_level_id']);

        $table->dropColumn([
            'dwu_id',
            'department_id',
            'year_level_id',
            'role',
            'phone',
            'is_active'
        ]);
    });
}
};
