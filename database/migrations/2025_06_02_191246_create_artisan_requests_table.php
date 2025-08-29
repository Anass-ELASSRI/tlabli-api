<?php

use App\Models\ArtisanRequest;
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
        Schema::create('artisan_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artisan_id')->constrained();
            $table->foreignUuid('user_id')->nullable()->constrained();
            $table->string('subject')->nullable();
            $table->text('message');
            $table->unsignedTinyInteger('status')->default(ArtisanRequest::STATUS_PENDING);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('artisan_requests');
    }
};
