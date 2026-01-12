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
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->uuid('global_id')->unique();
            $table->string('username');
            $table->string('email')->unique();
            $table->string('password');
            $table->foreignId('branch_id')->nullable()->constrained('branches');
            $table->enum('role', ['admin', 'super-admin', 'cashier'])->nullable();
            $table->smallInteger('active')->default(value: 1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
