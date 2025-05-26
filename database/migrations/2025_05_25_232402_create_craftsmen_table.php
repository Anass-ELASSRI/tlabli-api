<?php

use App\Models\Craftman;
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
        Schema::create('craftsman', function (Blueprint $table) {
            $table->id();
            $table->string('profession');
            $table->text('skills');
            $table->string('phone')->nullable();
            $table->unsignedTinyInteger('legal_status')->default(Craftman::LEGAL_STATUS_UNVERIFIED);
            $table->unsignedTinyInteger('current_step');
            $table->unsignedTinyInteger('experience_years');
            $table->unsignedTinyInteger('status')->default(Craftman::PROFILE_INCOMPLETE);
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('craftsman');
    }
};
