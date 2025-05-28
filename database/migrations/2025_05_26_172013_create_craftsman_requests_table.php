<?php

use App\Models\CraftsmanRequest;
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
        Schema::create('craftsman_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('craftsman_id')->constrained();
            $table->foreignId('user_id')->nullable()->constrained();
            $table->string('subject')->nullable();
            $table->text('message');
            $table->unsignedTinyInteger('status')->default(CraftsmanRequest::STATUS_PENDING);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('craftsman_requests');
    }
};
