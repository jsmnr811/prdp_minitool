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
        Schema::create('sidlan_responses', function (Blueprint $table) {
            $table->id();
            $table->string('dataset_id')->nullable();
            $table->string('cluster')->nullable();
            $table->string('region')->nullable();
            $table->string('province')->nullable();
            $table->string('group_status')->nullable();
            $table->longText('data')->nullable(); // JSON data from API
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sidlan_responses');
    }
};
