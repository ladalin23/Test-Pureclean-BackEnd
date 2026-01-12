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
        Schema::create('purchaseds', function (Blueprint $table) {
            $table->id();
            $table->uuid('global_id')->unique();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('branch_id')->constrained('branches');
            $table->foreignId('admin_id')->constrained('admins');
            $table->foreignId('service_id')->constrained('services');
            $table->enum('status', ['Cold', 'Warm', 'Hot', 'Dry'])->default('Cold');
            $table->integer('det')->default(0);
            $table->integer('sft')->default(0);
            $table->integer('acn')->default(0);
            $table->integer('det_price')->default(0);
            $table->integer('sft_price')->default(0);
            $table->integer('acn_price')->default(0);
            $table->integer('service_price')->default(0);
            $table->smallInteger('is_gift')->default(0);
            $table->enum('payment_method', ['ABA', 'Cash'])->default('ABA');
            $table->float('total_price')->default(0);
            $table->string('contact')->nullable();
            $table->smallInteger('active')->default(value: 1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchaseds');
    }
};
