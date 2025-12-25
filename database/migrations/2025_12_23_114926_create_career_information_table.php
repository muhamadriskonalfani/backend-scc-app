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
        Schema::create('career_information', function (Blueprint $table) {
            $table->id();

            $table->enum('info_type', ['job_vacancy', 'apprenticeship']);
            $table->string('image')->nullable();
            $table->string('title');
            $table->text('description');

            $table->string('company_name');
            $table->string('location');

            $table->enum('status', ['pending', 'approved', 'rejected', 'ended'])
                ->default('pending');

            $table->date('expired_at')->nullable();

            $table->foreignId('created_by')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('career_information');
    }
};
