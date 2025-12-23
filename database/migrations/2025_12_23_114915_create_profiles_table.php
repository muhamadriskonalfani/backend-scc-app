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
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('user_unique_id', 100)->unique();
            $table->string('image')->nullable();
            $table->string('phone', 20)->nullable();

            $table->year('graduation_year')->nullable();

            $table->text('testimonial')->nullable();
            $table->text('bio')->nullable();
            $table->text('education')->nullable();
            $table->text('skills')->nullable();
            $table->text('experience')->nullable();

            $table->string('linkedin_url')->nullable();
            $table->string('cv_file')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
