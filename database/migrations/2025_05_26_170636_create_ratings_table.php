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
        Schema::create('ratings', function (Blueprint $table) {
             $table->id();
            $table->unsignedTinyInteger('value'); // 1 to 5
            $table->text('comment')->nullable();
            $table->foreignId('user_id'); // who gave the rating

            // Polymorphic relation
            $table->morphs('rateable'); // creates rateable_id & rateable_type

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};
