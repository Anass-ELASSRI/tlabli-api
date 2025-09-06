<?php

use App\Models\User;
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
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('full_name');
            $table->string('phone')->unique();
            $table->string('city');
            $table->string('email')->nullable()->unique();
            $table->string('role')->default(\App\Enums\UserRoles::Client->value);
            $table->string('password');
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->boolean('is_deleted')->default(false);
            $table->string('refresh_token')->nullable();
            $table->string('status')->default(\App\Enums\UserStatus::NotVerified->value);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
