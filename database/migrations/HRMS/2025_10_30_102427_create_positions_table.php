<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mysql2')->create('positions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->unsignedBigInteger('component_id')->nullable();
            $table->unsignedBigInteger('unit_id')->nullable();
            $table->timestamps();

            $table->foreign('component_id')->references('id')->on('components')->onDelete('set null');
            $table->foreign('unit_id')->references('id')->on('units')->onDelete('set null');

            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
        });
    }

    public function down(): void
    {
        Schema::connection('mysql2')->dropIfExists('positions');
    }
};
