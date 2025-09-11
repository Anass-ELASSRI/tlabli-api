<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->constrained('users');
            $table->string('refresh_token'); // hashed
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('device_name')->nullable();
            $table->timestamp('expires_at');
            $table->boolean('revoked')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_tokens');
    }
};