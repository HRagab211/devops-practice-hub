<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scheduler_heartbeats', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->timestamp('last_ran_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scheduler_heartbeats');
    }
};
