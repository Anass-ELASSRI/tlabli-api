<?php

use App\Models\Artisan;
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
        Schema::create('artisans', function (Blueprint $table) {
            $table->id();
            $table->string('profession');
            $table->text('skills');
            $table->text('bio')->nullable();
            $table->string('phone')->nullable();
            $table->unsignedTinyInteger('legal_status')->default(Artisan::LEGAL_STATUS_UNVERIFIED);
            $table->unsignedTinyInteger('current_step');
            $table->unsignedTinyInteger('experience_years');
            $table->unsignedTinyInteger('status')->default(Artisan::PROFILE_INCOMPLETE);
            $table->foreignId('user_id')->constrained('users');
            $table->string('languages'); // e.g. "English, French"
            $table->string('city');
            $table->json('social_links')->nullable(); // e.g. {"facebook": "...", "instagram": "..."}
            $table->decimal('rating', 3, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('artisans');
    }
};
