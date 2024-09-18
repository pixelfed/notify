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
        Schema::create('instances', function (Blueprint $table) {
            $table->id();
            $table->string('domain')->unique()->index();
            $table->string('secret')->nullable()->index();
            $table->json('ips')->nullable();
            $table->boolean('is_supported')->default(false)->index();
            $table->boolean('is_allowed')->default(true)->index();
            $table->timestamp('software_last_checked_at')->nullable();
            $table->timestamp('instance_last_checked_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instances');
    }
};
