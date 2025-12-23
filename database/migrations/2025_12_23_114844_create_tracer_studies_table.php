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
        Schema::create('tracer_studies', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('full_name');
            $table->string('student_id_number', 50);

            $table->foreignId('faculty_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('study_program_id')
                ->constrained()
                ->restrictOnDelete();

            $table->string('domicile');
            $table->string('whatsapp_number', 20);

            $table->year('entry_year');
            $table->year('graduation_year');

            $table->string('current_workplace')->nullable();
            $table->integer('current_job_duration_months')->nullable();

            $table->enum('company_scale', ['local', 'national', 'international'])->nullable();
            $table->string('job_title')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracer_studies');
    }
};
