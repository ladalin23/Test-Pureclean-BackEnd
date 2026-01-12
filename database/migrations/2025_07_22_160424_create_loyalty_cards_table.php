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
        Schema::create('loyalty_cards', function (Blueprint $table) {
            $table->id();
            $table->uuid('global_id')->unique();
            $table->foreignId('user_id')->constrained('users');
            $table->integer('points')->default(0);
            $table->foreignId('purchase1_id')->nullable()->constrained('purchaseds');
            $table->foreignId('purchase2_id')->nullable()->constrained('purchaseds');
            $table->foreignId('purchase3_id')->nullable()->constrained('purchaseds');
            $table->foreignId('purchase4_id')->nullable()->constrained('purchaseds');
            $table->foreignId('purchase5_id')->nullable()->constrained('purchaseds');
            $table->foreignId('purchase6_id')->nullable()->constrained('purchaseds');
            $table->foreignId('purchase7_id')->nullable()->constrained('purchaseds');
            $table->foreignId('purchase8_id')->nullable()->constrained('purchaseds');
            $table->foreignId('purchase9_id')->nullable()->constrained('purchaseds');
            $table->foreignId('purchase10_id')->nullable()->constrained('purchaseds');
            $table->foreignId('purchase11_id')->nullable()->constrained('purchaseds');
            $table->foreignId('first_reward_id')->nullable()->constrained('rewards');
            $table->foreignId('second_reward_id')->nullable()->constrained('rewards');
            $table->datetime('expires_at')->nullable();
            $table->smallInteger('active')->default(value: 1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loyalty_cards');
    }
};
