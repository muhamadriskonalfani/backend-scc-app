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

            // $table->string('full_name');
            $table->string('student_id_number', 50)->unique();

            $table->foreignId('faculty_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('study_program_id')
                ->constrained()
                ->restrictOnDelete();

            $table->year('entry_year');
            $table->year('graduation_year')->nullable();

            // $table->string('domicile')->nullable();
            // $table->string('whatsapp_number', 20)->nullable();

            $table->enum('employment_status', ['bekerja', 'wirausaha', 'lanjut studi', 'belum bekerja'])->nullable();
            $table->string('current_workplace')->nullable();
            $table->enum('company_scale', ['local', 'national', 'international'])->nullable();
            $table->string('job_title')->nullable();
            $table->enum('job_category', ['formal', 'informal', 'wirausaha', 'freelance'])->nullable();
            $table->enum('employment_type', ['full-time', 'part-time', 'kontrak', 'magang'])->nullable();
            $table->enum('employment_sector', ['pendidikan', 'IT', 'keuangan', 'manufaktur', 'lainnya'])->nullable();
            $table->string('monthly_income_range')->nullable();
            
            $table->enum('job_study_relevance_level', ['sangat sesuai', 'sesuai', 'kurang', 'tidak sesuai'])->nullable();
            $table->string('suggestion_for_university')->nullable();

            // $table->integer('current_job_duration_months')->nullable();

            
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
