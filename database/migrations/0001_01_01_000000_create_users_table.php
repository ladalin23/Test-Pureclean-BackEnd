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
        Schema::create('users', function (Blueprint $table) {
            $table->id(); // This will be the primary key, auto-incrementing
            $table->uuid('global_id')->unique();
            // u_id is now just a bigInteger, nullable initially, and we'll fill it programmatically
            $table->bigInteger('u_id')->unique()->nullable(); // Add unique constraint for u_id
            $table->string('username');
            $table->string('email')->unique()->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->string('phone')->unique()->nullable();
            $table->longtext('profile_picture')->nullable();
            $table->enum('gender', ['male', 'female', 'others'])->nullable();
            $table->dateTime('dob')->nullable();
            $table->string('otp')->nullable();
            $table->dateTime('otp_expires_at')->nullable();
            $table->string('google_id')->nullable()->unique();
            $table->string('telegram_id')->nullable()->unique();
            $table->string('telegram_username')->nullable();
            $table->boolean('is_verify_google')->default(false);
            $table->boolean('is_verify_telegram')->default(false);
            $table->boolean('is_verify_email')->default(false);
            $table->boolean('is_verify_phone')->default(false);
            $table->smallInteger('active')->default(value: 1);
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};